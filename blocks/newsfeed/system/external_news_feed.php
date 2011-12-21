<?php
require_once('news_feed.php');
require_once(dirname(__FILE__).'/../../../lib/snoopy/Snoopy.class.inc');

/**
 * Object representing data held for an external news feed 
 * (one that comes from RSS etc).
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 */
class external_news_feed extends news_feed {
    private $fields;
    
    /**
     * Calls parent constructor and sets up additional fields that are only
     * used for external feeds. 
     */
    public function __construct() {
        parent::__construct();
        
        // Set up fields, and defaults that aren't null
        $this->fields=new checked_fields(array(
            'url' => array('/^https?:\/\/[^ ]*$/',EXN_NEWSFEED_URLINVALID,'s'),
            'checkfreq' => array('/^[0-9]+$/',EXN_NEWSFEED_FREQINVALID,'i'),
            'lastcheck' => array('!NULLOR/^[0-9]+$/',EXN_NEWSFEED_DATEINVALID,'i'),
            'error' => array('!NULLOR/.+/',EXN_NEWSFEED_ERRORINVALID,'s')
        ));
    } 
    
    /**
     * No list of optional authids for external newsfeed.
     *
     * @return array Empty array of authid strings
     */
    public function get_optional_authids() {
        return array();
    }

    /** @return string URL of external feed */
    public function get_url() {
        return $this->fields->get('url');
    }
    /**
     * Sets field, checking value and updating 'changed' flag.
     * @param string $url URL of external feed
     * @throws Exception If URL is invalid
     */
    public function set_url($url) {
        if($this->get_url()!=$url) {
            $this->fields->set('url',$url);
            $this->set_last_check(null); // We haven't retrieved this one yet
        }
    }
    
    /**
     * @return int Time (seconds) after which feed should be be refreshed
     */
    public function get_check_freq() {
        return $this->fields->get('checkfreq');
    }
    /**
     * Sets field, checking value and updating 'changed' flag.
     * @param int $nextcheck Time (seconds) after which feed should be refreshed
     * @throws Exception If URL is invalid
     */
    public function set_check_freq($nextcheck) {
        $this->fields->set('checkfreq',$nextcheck);
    }
    
    /**
     * @return int Time (seconds since epoch) that field was last 
     *   refreshed (null if never)
     */
    public function get_last_check() {
        return $this->fields->get('lastcheck');
    }
    /**
     * Sets field, checking value and updating 'changed' flag.
     * @param int $nextcheck Time (seconds since epoch) that field was last refreshed
     * @throws Exception If field is invalid
     */
    public function set_last_check($lastcheck) {
        $this->fields->set('lastcheck',$lastcheck);
    }    
    
    /**
     * @return string Error information or null if none
     */
    public function get_error() {
        return $this->fields->get('error');
    }
    
    /**
     * Sets field, checking value and updating 'changed' flag.
     * @param string $error Error information or null if none
     * @throws Exception If field is invalid
     */
    public function set_error($error) {
        $this->fields->set('error',$error);
    }
    
    /** 
     * @return bool True if some of the fields have been changed and save_changes
     *   needs to be called.
     */    
    public function is_changed() {
        return parent::is_changed() || $this->fields->is_changed();
    }
    
    /**
     * Saves all pending changes to field values. 
     * @throws Exception If feed hasn't been created yet
     */
    public function save_changes() {
        if(!$this->is_changed()) {
            return false;
        }
        
        $tw=new transaction_wrapper();
        try {
            $fields=$this->fields->get_update_string();
            if($fields!='') {
                db_do("UPDATE prefix_newsfeed_external SET $fields WHERE newsfeedid=".$this->get_id());
            }
            parent::save_changes();
            $this->fields->clear_changed();    
            $tw->commit();
        } catch(Exception $e) {
            $tw->rollback();
            throw $e;
        }
        
        return true;
    }
    
    /**
     * Creates a new feed using the defined values. 
     * @throws Exception If any required fields haven't been set yet
     */    
    public function create_new() {
        $tw=new transaction_wrapper();
        try {
            $id=parent::create_new();
            list($names,$values)=$this->fields->get_insert_strings();
            db_do("INSERT INTO prefix_newsfeed_external(newsfeedid,$names) VALUES ($id,$values)");   
            $tw->commit();
        } catch(Exception $e) {
            parent::fail_create();
            $tw->rollback();
            throw $e;
        }
        $this->fields->clear_changed();    
        return $id; 
    }    
    
    /**
     * Initialises the feed from database query (called by feed_system).
     * @param array $dbfields Associative array of field values with
     *   appropriate names, including 'id' for ID
     */
    public function init_from_db($dbfields) {
        parent::init_from_db($dbfields);
        $this->fields->set_from_db($dbfields);
    }
    
    /**
     * Checks the feed to see whether it has been updated since last time.
     * If so, deletes all the messages and re-adds them.
     * @param bool $force If true, always rereads even if cached
     */
    public function check($force=false) {
        $lastcheck=$this->get_last_check();
        $this->set_last_check(time());
        if($force) {
            $lastcheck=0;
        }
        $tw=null;
        
        // Use Snoopy to request feed
        $headers=$lastcheck ? array('If-Modified-Since' => date('r',$lastcheck)) : null;
        $result=download_file_content($this->get_url(),$headers,null,true,5);
        if(!$result) {
            $this->set_error("Unable to retrieve $url; unknown error");
        } else if($result->status==304) {
            // Not modified
            $this->save_changes();
            return;
        } else if($result->status!=200) {
            $this->set_error("Unexpected response code $result->response_code");
        } else {
            // Safely retrieved something, at least. 
            
            // If it hasn't changed since last check, do nothing. (Some servers
            // ignore the if-modified-since.)
            foreach($result->headers as $header) {
                $matches=array();
                if(preg_match('/^Last-Modified: (.+)$/',$header,$matches)) {
                    $lastmodified=strtotime($matches[1]);
                    if($lastmodified < $lastcheck && !$force) {
                        $this->save_changes();
                        return;
                    }
                }
            }
            
            // TODO Futz with character encoding
            
            // Load as XML
            if(!$doc=DOMDocument::loadXML($result->results)) {
                $this->set_error("Feed is not valid XML");
            } else {
                // Get earliest date of existing entries
                $existing=$this->get_earliest_date();
                if(!$existing) {
                    $existing=time();
                }
                
                $tw=new transaction_wrapper();
                
                // Wipe existing entries
                $this->wipe_all_entries();
                
                // OK, now let's play fast and loose with the content just to
                // get any items...
                $items=array();
                $this->list_feed_items($doc->documentElement,$items);
                
                // Add each item. If items don't have dates, they are added
                // with the existing earliest date (which may be 'now' if
                // there were none before).
                $error=null;
                foreach($items as $item) {
                    try {
                        $e=new news_entry_version();
                        $e->set_date($item->date ? $item->date : $existing);
                        $e->set_title($item->title);
                        $e->set_link($item->link ? trim($item->link) : null);
                        $e->set_html($item->description ? $item->description : '');
                        $e->set_poster(news_entry_version::SYSTEM_USER,$e->get_date());
                        $e->save_new(true,$this->get_id(),false);
                        $e->approve(news_entry_version::SYSTEM_USER,$e->get_date(),false);
                    } catch(Exception $e) {
                        if($error===null) {
                            $error='Feed item caused error: '.$e->getMessage();
                        }
                    }
                }
                
                $this->set_error($error); // Yay!
            }
        }
        $this->save_changes();
        if($tw) {
            $tw->commit();
        }
    }
    
    private static function get_text($element) {
        $result='';
        for($child=$element->firstChild;$child;$child=$child->nextSibling) {
            switch($child->nodeType) {
                case XML_TEXT_NODE:
                case XML_CDATA_SECTION_NODE:
                    $result.=$child->nodeValue;
                    break;
                default:
                    // ?
                    break;
            }
        }
        return $result;
    }

    static function list_feed_items($element,&$results) {
        // Look for any element called 'item', whatever its namespace
        if($element->localName=='item' || $element->localName=='entry') {
            $link=null;
            $title=null;
            $date=null;
            $description=null;
            $atom=$element->localName=='entry'; 
            // Found an item! Well, maybe. Check which children we have
            for($child=$element->firstChild;$child;$child=$child->nextSibling) {
                if($child->nodeType!=XML_ELEMENT_NODE) {
                    continue;
                }
                switch($child->localName) {
                    case 'title':
                        if($child->ownerDocument->documentElement->tagName=='feed') {
                            $title=self::get_atom_content($child,false);
                        } else {
                            $title=self::get_text($child);
                        }
                        break;
                        
                    case 'link':
                        if($child->hasAttribute('href')) { // Atom uses attribute
                            $link=$child->getAttribute('href');
                        } else { // Other formats use content
                            $link=self::get_text($child);
                        }
                        break;
                        
                    case 'updated':
                        if($date) {
                            // Published overrides updated
                            break;
                        } 
                        // Fall through
                    case 'published':
                    case 'pubDate':
                    case 'date':
                        $date=strtotime(self::get_text($child));
                        break;
                        
                    case 'summary':
                        // Content overrides summary so don't set if we already
                        // have one. Also, this is only used for Atom.
                        if($atom && !$description) {
                            $description=self::get_atom_content($child,true);
                        }
                        break;
                                            
                    case 'content': // Atom content
                        if($atom) {
                            $description=self::get_atom_content($child,true);
                        } 
                        break;

                    case 'description': // RSS description, hopefully already as escaped HTML
                        $description=trim(self::get_text($child));
                        break;

                    default:
                        break;
                }
            }
            if($title && ($link || $description)) {
                
                $item=new stdClass;
                $item->title=$title;
                $item->link=$link ? $link : false;
                $item->description=$description ? $description : false;
                $item->date=$date ? $date : false;
                
                $results[]=$item;
            }            
        } else {
            // Recurse to child elements
            for($child=$element->firstChild;$child;$child=$child->nextSibling) {
                if($child->nodeType==XML_ELEMENT_NODE) {
                    self::list_feed_items($child,$results);
                }
            }
        }
    }     
    
    private static function get_atom_content($element,$returnhtml) {
        switch($element->getAttribute('type')) {
            case 'html' : // HTML, escaped (so we will get &lt; even here)
                $result=trim(self::get_text($element));
                break;
                
            case 'xhtml' : // XHTML, inside DIV
                // Convert <content> node to string
                $description=$element->ownerDocument->saveXML($element,LIBXML_NOEMPTYTAG);
                // Get rid of LFs, these confuse regex
                $description=str_replace("\r",'',str_replace("\n",'',$description));
                // Trash namespaces                
                $description=preg_replace('/(<[\/]?)[^> ]+:/','$1',$description);            
                // Extract everything between DIV tags     
                $result=trim(preg_replace('/^.*<div.*?>(.*)<\/div>[^<]*<\/.+>$/','$1',
                    $description));
                break;
            
            case 'text' : // Fall through
            default: // Text (so < here will be < not &lt;, so we escape it)
                $result=trim(htmlspecialchars(self::get_text($element)));
                break;
        }
        
        if(!$returnhtml) {
            // Decode any entities
            $result=html_entity_decode($result);
            // Get rid of tags
            $result=preg_replace('/<.*?>/','',$result);
            $result=trim($result);
        }
        return $result;                
    }
        
    // Roll forward external newsfeeds        
    public function roll_forward($newshortname, $startdate, $block_instance_id, $startdateoffset) {
        $tw=new transaction_wrapper();

        // Save copy
        // Passing on any existing block_instance id as a parameter
        try {
            $this->save_as_new($startdate, $block_instance_id, $startdateoffset);
        } catch(Exception $e) {
            $tw->rollback();
            throw $e;
        }

        $tw->commit();
    }

    /**
     * Saves this feed as a new one.
     * @param int $changedate Date (seconds since epoch) to make the 'start time';
     *   Not applicable for external news feeds
     * @param int $block_instance_id
     *   this parameter MUST be set, the newsfeed is added to this block instance
     *   and the calling function is responsible for updating the configdata
     *   field for this block instance with the returned new newsfeed id
     * @throws Exception
     */
    public function save_as_new($changedate, $block_instance_id, $startdateoffset) {
        $tw=new transaction_wrapper();

        // Call base class to do the actual news feed copy
        $oldid = $this->get_id();
        $newid = parent::save_as_new($changedate, $block_instance_id, $startdateoffset);

        // external
        db_do("
INSERT INTO prefix_newsfeed_external(newsfeedid, url, lastcheck, checkfreq)
SELECT $newid, url, 0, checkfreq FROM prefix_newsfeed_external WHERE newsfeedid=$oldid
            ");

        // Update external feed
        $this->check(true);

        $this->id=$newid;

        $tw->commit();
        return $newid;
    }
}
?>