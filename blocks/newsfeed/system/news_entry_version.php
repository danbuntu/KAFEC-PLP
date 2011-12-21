<?php
require_once('news_attachment.php');
require_once('internal_news_feed.php');
/**
 * Represents a single entry in a news feed. The entry may have
 * multiple versions. Some data is dynamically retrieved.
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 */
 class news_entry_version {
    
    private $newsfeedid,$newsfeednamepres,$id,$entryid,$fields;
    private $posterusername,$posterrealname,$approverusername,$approverrealname;
    private $files=array();
    private $availableroles=null;
    private $fromdb=false;
    
    /**
     * Initialises feed and sets default values.
     */
    public function __construct() {
        // Set up fields, and defaults that aren't null
        $this->fields=new checked_fields(array(
            'appearancedate' => array('!NULLOR/^[0-9]+$/',EXN_NEWSFEED_DATEINVALID,'i'),
            'rollforward' => array('!BOOLEAN',EXN_NEWSFEED_ROLLFORWARDINVALID,'b'),            
            'authid' => array('!NULLOR/^[A-Z0-9!]+( [A-Z0-9!]+)*$/',EXN_NEWSFEED_AUTHIDINVALID,'s'),
            'title' => array('!NOTNULL',EXN_NEWSFEED_TITLEINVALID,'s'),
            'html' => array('!NOTNULL',EXN_NEWSFEED_HTMLINVALID,'s'),
            'poster' => array('!NULLOR/^[0-9]+$/',EXN_NEWSFEED_USERINVALID,'i'),
            'timeposted' => array('!NULLOR/^[0-9]+$/',EXN_NEWSFEED_DATEINVALID,'i'),
            'approver' => array('!NULLOR/^[0-9]+$/',EXN_NEWSFEED_USERINVALID,'i'),
            'timeapproved' => array('!NULLOR/^[0-9]+$/',EXN_NEWSFEED_DATEINVALID,'i'),
            'deleted' => array('!BOOLEAN',EXN_NEWSFEED_DELETEDINVALID,'b'),
            'link' => array('!NULLOR/^.+/',EXN_NEWSFEED_LINKINVALID,'s')
        ));
        $this->fields->set('appearancedate',time());
        $this->fields->set('rollforward',false);
        $this->fields->set('deleted',false);
    }     
    
    /**
     * Set up fields from prefix_newsfeed_versions table.
     * @param int $newsfeedid ID of newsfeed
     * @param string $newsfeednamepres Name and presentation of newsfeed
     * @param int $id ID within newsfeed_versions
     * @param int $entryid ID within newsfeed_entries
     * @param array $fields Associative array containing keys named
     *   after the fields in that table
     */
    function init_from_db($newsfeedid,$newsfeednamepres,$id,$entryid,$fields) {
        $this->newsfeedid=$newsfeedid;
        $this->newsfeednamepres=$newsfeednamepres;
        $this->id=$id;
        $this->entryid=$entryid;
        $this->fields->set_from_db($fields);
        $this->fields->clear_changed();
        $this->posterusername=$fields['posterusername'];
        $this->posterrealname=$fields['posterrealname'];
        if($this->get_poster_userid()===0) {
            $this->posterusername='system';
            $this->posterrealname='Automatically posted';
        }
        $this->approverusername=$fields['approverusername'];
        $this->approverrealname=$fields['approverrealname'];
        if($this->get_approver_userid()===0) {
            $this->approverusername='system';
            $this->approverrealname='Automatically approved';
        }
        $this->fromdb=true;
    }
    
    /**
     * Add a file object based on fields from prefix_newsfeed_files.
     * @param array $fields Associative array containing keys named
     *   after the fields in that table
     */
    function file_from_db($newsfeedid,$fields) {
        $this->files[]=new news_attachment($fields['filename'],$fields['mimetype'],$fields['filesize'],$newsfeedid);
    }
    
    /**
     * Get list of file attachments on this item.
     * @return array Array of news_attachment objects
     */
    function get_attachments() {
        return $this->files;
    }
    
    /**
     * Add an attachment to the list. Change does not take effect until save_new.
     * @param news_attachment $attachment New attachment
     */
    function add_attachment($attachment) {
        $this->files[]=$attachment;
    }
    
    /**
     * Remove an attachment from the list. Change does not take effect until save_new.
     * @param news_attachment $attachment Attachment to remove
     * @throws Exception If the attachment isn't in the list
     */
    function remove_attachment($attachment) {
        if(false===($key=array_search($attachment,$this->files))) {
            throw new Exception('Requested attachment does not exist',
                EXN_NEWSFEED_NOSUCHFILE);
        }
        unset($this->files[$key]);    
    }
    
    /**
     * @return int ID (within prefix_newsfeed_versions) of this version
     * @throws Exception If this version isn't in the database yet
     */
    function get_id() {
        if(!$this->id) {
            throw new Exception("Attempt to access ID of uncreated news entry version",EXN_NEWSFEED_NOID);
        }
        return $this->id;
    }
    
    /** @return int Time message should appear and be dated, in 
     *   seconds since epoch */
    function get_date() {
        return $this->fields->get('appearancedate');
    }
    /**
     * Sets value of field. Set methods don't take effect until you 
     * call save_new().
     * @param int $date Time message should appear and be dated, in 
     *   seconds since epoch
     * @throws Exception If field is invalid
     */
    function set_date($date) {
        $this->fields->set('appearancedate',$date);
    }
    
    /**
     * @return bool True if this entry should be rolled forward to 
     * future presentations, false otherwise
     */
    public function should_roll_forward() {
        return $this->fields->get('rollforward');
    }
    /**
     * Sets value of field. Set methods don't take effect until you 
     * call save_changes().
     * @param bool $rollforward True if this entry should be rolled forward to 
     * future presentations, false otherwise
     * @throws Exception If field is invalid
     */
    public function set_roll_forward($rollforward) {
        $this->fields->set('rollforward',$rollforward);
    }
    
    /**
     * @return bool True if this entry has been marked deleted, false otherwise
     */
    public function is_deleted() {
        return $this->fields->get('deleted');
    }
    /**
     * Sets value of field. Set methods don't take effect until you 
     * call save_changes().
     * @param bool $rollforward True if this entry should be marked 
     *   deleted, false otherwise
     * @throws Exception If field is invalid
     */
    public function set_deleted($deleted) {
        $this->fields->set('deleted',$deleted);
    }    
        
    /** @return string Space-separated list of authids, null if none */
    function get_authid() {
        return $this->fields->get('authid');
    }
    /**
     * Sets value of field. Set methods don't take effect until you 
     * call save_new().
     * @param string $authid Space-separated list of authids, null if none
     * @throws Exception If field is invalid
     */
    function set_authid($authid) {
        $this->fields->set('authid',$authid);
    }
    
    /** @return string Message title */
    function get_title() {
        return $this->fields->get('title');
    }
    /**
     * Sets value of field. Set methods don't take effect until you 
     * call save_new().
     * @param string $title Message title
     * @throws Exception If field is invalid
     */
    function set_title($title) {
        $this->fields->set('title',$title);
    }

    /** @return string URL or null if none */
    function get_link() {
        return $this->fields->get('link');
    }
    /**
     * Sets value of field. Set methods don't take effect until you 
     * call save_new().
     * @param string $title URL or null if none
     * @throws Exception If field is invalid
     */
    function set_link($link) {
        $this->fields->set('link',$link);
    }

    /** @return string Message HTML */
    function get_html() {
        return $this->fields->get('html');
    }
    
    /** @return string HTML for message with attachments at the end */
    function get_html_with_attachments() {
        global $CFG;
        $id=$this->get_newsfeed_id();
        $content=$this->get_html();
        $attachments=$this->get_attachments();
        if(count($attachments)>0) {
            $content.='<ul class="newsfeed_attachments">';
            $versionid=$this->get_id();
            foreach($attachments as $attachment) {
                $filename=$attachment->get_filename();
                $size=display_size($attachment->get_size());
                $description=get_mimetype_description($attachment->get_mime_type());
                $icon=mimeinfo_from_type("icon",$attachment->get_mime_type());
                
                // This format is designed to be similar to the one in resourcepage
                // so I can reuse the styles. Should also work as 'plain' html. 
                $content.="<li><a href='{$CFG->wwwroot}/blocks/newsfeed/file.php?newsfeedid=$id&amp;".
                    "versionid=$versionid&amp;filename=$filename'>".
                    "<span class='newsfeed_aicon'><img src='{$CFG->pixpath}/f/$icon' ".
                      " alt='' width='16' height='16'/></span> ".
                    "<span class='newsfeed_afilename'>$filename</span>".
                    "<span class='newsfeed_adetails'> ($size $description)</span>".
                    "</a></li>";
            }  
            $content.='</ul>';
        }
        return $content;
    }
    
    /**
     * Sets value of field. Set methods don't take effect until you 
     * call save_new().
     * @param string $html Message HTML
     * @throws Exception If field is invalid
     */
    function set_html($html) {
        $this->fields->set('html',$html);
    }

    /** @return int user ID of poster, or 0 if posted by system */
    function get_poster_userid() {
        return $this->fields->get('poster');
    }
    /** 
     * @return string Poster's username, or null if none
     * @throws Exception if version wasn't constructed from DB
     */
    function get_poster_username() {
        if(!$this->fromdb) {
            throw new Exception(
                "Poster usernames are only available when version is initialised from DB",
                EXN_NEWSFEED_NOTFROMDB);            
        }
        return $this->posterusername;
    }
    /** 
     * @return string Poster's realname, or null if none
     * @throws Exception if version wasn't constructed from DB
     */
    function get_poster_realname() {
        if(!$this->fromdb) {
            throw new Exception(
                "Poster realnames are only available when version is initialised from DB",
                EXN_NEWSFEED_NOTFROMDB);            
        }
        return $this->posterrealname;
    }
    /** @return int user ID of poster*/
    function get_time_posted() {
        return $this->fields->get('timeposted');
    }
    /**
     * Sets poster (and post time). Set methods don't take effect until you 
     * call save_new().
     * @param string $poster Moodle user ID of poster, or leave null to use 
     *   current, or self::SYSTEM_USER
     * @param int $timeposted Time posted - leave null to use current time
     * @throws Exception If field is invalid
     */
    function set_poster($poster=null,$timeposted=null) {
        if($poster===null) {
            global $USER;
            $poster=$USER->id;
        }
        if($timeposted===null) {
            $timeposted=time();
        } 
        $this->fields->set('poster',$poster);
        $this->fields->set('timeposted',$timeposted);
    }    
    
    /** @return bool True if this version is approved, false otherwise */
    function is_approved() {
        return $this->get_approver_userid()!==null;
    }

    /** @return int user ID of approver (null if not approved) */
    function get_approver_userid() {
        return $this->fields->get('approver');
    }
    /** 
     * @return string Approver's username, or null if none
     * @throws Exception if version wasn't constructed from DB
     */
    function get_approver_username() {
        if(!$this->fromdb) {
            throw new Exception(
                "Approver usernames are only available when version is initialised from DB",
                EXN_NEWSFEED_NOTFROMDB);            
        }
        return $this->approverusername;
    }
    /** 
     * @return string Approver's realname, or null if none
     * @throws Exception if version wasn't constructed from DB
     */
    function get_approver_realname() {
        if(!$this->fromdb) {
            throw new Exception(
                "Approver realnames are only available when version is initialised from DB",
                EXN_NEWSFEED_NOTFROMDB);            
        }
        return $this->approverrealname;
    }
    /** @return int time in seconds since epoch of approval (null if not approved)*/
    function get_time_approved() {
        return $this->fields->get('timeapproved');
    }
    
    const SYSTEM_USER=0;    
    
    /**
     * Updates the approver value of the existing version. This method is the 
     * only set-type method that doesn't require calling save_new; the
     * change occurs directly to database.
     * @param string $approver Moodle user ID of approver, or omit to use 
     *   current (does not check access), or self::SYSTEM_USER.
     * @param int $approvetime Time at which to mark approved (leave 0 for now)
     * @param bool $notify If true, notifies feed (may send emails). Set false if internally-generated
     * @throws Exception If field is invalid
     */
    function approve($approver=null,$approvetime=0,$notify=true) {
        if($approver===null) {
            global $USER;
            $approver=$USER->id;
        }
        if(!$approvetime) {
            $approvetime=time();
        }
        if($this->fields->is_changed()) {
            throw new Exception('Cannot approve a version that has changed',
                EXN_NEWSFEED_CHANGEDAPPROVE);
        }
        if(!$this->newsfeedid) {
            throw new Exception("Cannot approve a version that doesn't belong to a newsfeed",
                EXN_NEWSFEED_NONEWSFEED);
        }

        $this->fields->set('approver',$approver);
        $this->fields->set('timeapproved',$approvetime);
        $this->fields->clear_changed();
        
        db_do('UPDATE prefix_newsfeed_versions SET '.
            'approver='.sql_int($approver).',timeapproved='.sql_int($this->get_time_approved()).
            ' WHERE id='.$this->get_id());
        feed_system::$inst->clear_feed_cache($this->newsfeedid);
        
        if($notify) {
            feed_system::$inst->get_feed($this->newsfeedid)->notify_approve();
        }                 
    }
    
    /**
     * Saves this entry to database as a new message version.
     * @param bool $newentry If true, creates a new entry instead of using existing one 
     * @param int $newsfeedid News feed ID for new entries (leave 0 to use original source feed)
     * @param bool $notify If true, notifies feed (may send emails). Set false if internally-generated
     * @return int ID of new version
     */
    function save_new($newentry=false,$newsfeedid=0,$notify=true) {
        if(!$newentry && !$this->entryid) {
            throw new Exception('No entry details; must save as new entry',
                EXN_NEWSFEED_NOENTRY);
        } 
        if($newentry && !$newsfeedid) {
            if(!$this->newsfeedid) {
                throw new Exception('Must specify newsfeed ID',
                    EXN_NEWSFEED_NONEWSFEED);
            }
            $newsfeedid=$this->newsfeedid;
        }
        if(!$newentry && $newsfeedid) {
            throw new Exception('Cannot use existing entry in new feed',
                EXN_NEWSFEED_NOTNEWENTRY);
        }
        
        $tw=new transaction_wrapper();
        
        if($newentry) {
            $entry=new StdClass;
            $entry->newsfeedid=$newsfeedid;
            if(!($entry->id=insert_record('newsfeed_entries',$entry))) {
                throw new Exception('Failed to insert entry record');
            }
            $this->newsfeedid=$newsfeedid;
            $this->entryid=$entry->id;
        }

        // All new entries start off unapproved
        $this->fields->set('approver',null);
        $this->fields->set('timeapproved',null);
        
        try {
            list($names,$values)=$this->fields->get_insert_strings();
            $this->id=$this->fields->moodle_insert('newsfeed_versions',array('entryid'=>$this->entryid));
            $this->fields->clear_changed();
            
            // Add files
            foreach($this->files as $file) {
                $file->save($this->newsfeedid,$this->id);
            }    
            
            $tw->commit();
        } catch(Exception $e) {
            $tw->rollback();
            throw $e;
        }
        
        // Notify approvers
        if($notify) {
            $nf=feed_system::$inst->get_feed($this->newsfeedid);
            $nf->notify_new_version();
        }
        
        return $this->id;         
    }
    
    /** 
     * @return bool True if some of the fields have been changed and save_new
     *   needs to be called.
     */    
    public function is_changed() {
        return $this->fields->is_changed();
    }    
    
    /**
     * @return int ID of newsfeed if this has been posted to one or loaded from DB 
     */
    public function get_newsfeed_id() {
        return $this->newsfeedid;
    }
    
    /**
     * @return int ID of entry if this has been posted, or loaded from DB
     */
    public function get_entry_id() {
        return $this->entryid;
    }
    
    /**
     * This method only works after entry has been retrieved from database.
     * It always returns null for created entries.
     * @return string Name of news feed this was posted to (prefixed by the
     *   course shortname and a space), or null if not known
     */
    public function get_newsfeed_name_pres() {
        return $this->newsfeednamepres;
    }
    
    /**
     * Method used in news_feed to set roles available, on the feed to 
     * which this entry belongs, to the current user.
     * @param array $availableroles Array of role strings
     */
    function set_roles($availableroles) {
        $this->availableroles=$availableroles;
    }

    /**
     * @return bool True if current user has 'post' role on this feed
     * @throws Exception If user role info has not been obtained
     */    
    function feed_can_post() {
        return $this->feed_has_role(internal_news_feed::ROLE_POSTER); 
    }
    /**
     * @return bool True if current user has 'approve' role on this feed
     * @throws Exception If user role info has not been obtained
     */    
    function feed_can_approve() {
        return $this->feed_has_role(internal_news_feed::ROLE_APPROVER); 
    }
    
    /**
     * @return bool True if current user has given role on this feed
     * @throws Exception If user role info has not been obtained
     */    
    private function feed_has_role($rolename) {
        if(is_null($this->availableroles)) {
            throw new Exception('Entry roles not set',
                EXN_NEWSFEED_ROLESNOTSET);
        } 
        return in_array($rolename,$this->availableroles); 
    }
 }
?>