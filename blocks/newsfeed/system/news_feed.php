<?php
require_once(dirname(__FILE__).'/exceptions.php');
require_once(dirname(__FILE__).'/feed_system.php');
require_once(dirname(__FILE__).'/checked_fields.php');
require_once(dirname(__FILE__).'/news_entry_version.php');

/**
 * Represents one news feed.
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 */
class news_feed {
    private $id;
    private $fields;
    // Parent of this newsfeed. If there are multiple newsfeeds for the course,
    // the course id (negative) is the parent, otherwise if there is only one
    // newsfeed for the course, the course category id is the parent
    private $parent = null;
    private $courseshortname = null;
    private $coursestartdate = null;
    private $pres = null;

    /**
     * Initialises feed and sets default values.
     */
    public function __construct() {
        // Set up fields, and defaults that aren't null
        $this->fields=new checked_fields(array(
            'name' => array('/^[^\/]+$/',EXN_NEWSFEED_NAMEINVALID,'s'),
            'summary' => array('!NOTNULL',EXN_NEWSFEED_SUMMARYINVALID,'s'),
            'publicfeed' => array('!BOOLEAN',EXN_NEWSFEED_PUBLICINVALID,'b'),
            'defaultauthid' => array('!NULLOR/^[A-Z0-9!]+$/',EXN_NEWSFEED_AUTHIDINVALID,'s'),
            'deleted' => array('!BOOLEAN',EXN_NEWSFEED_DELETEDINVALID,'b'),
            'blockinstance' => array('/^[0-9]+$/', EXN_NEWSFEED_BLOCKINSTANCEINVALID, 'i'),
        ));
        $this->fields->set('summary','');
        $this->fields->set('publicfeed',true);
        $this->fields->set('deleted',false);
        $this->fields->set('blockinstance', 0);
    }

    /**
     * @return int ID of feed
     * @throws Exception If feed hasn't been created yet
     */
    public function get_id() {
        if(!$this->id) {
            throw new Exception("Attempt to access ID of uncreated feed",EXN_NEWSFEED_NOID);
        }
        return $this->id;
    }


    private $foldercache=null;

    /**
     * @return location_folder Folder object (will be obtained from database if not available)
     */
    public function get_folder() {
        if (!isset($this->parent)) {

            // Set newsfeed parent to category id if it is the only one for the
            // course otherwise set parent to the course id (negative)
            $blockinstanceid = $this->fields->get('blockinstance');
            $rs=db_do("
SELECT count(bi.pageid) as nfcount, bi.pageid, c.category
 FROM prefix_block_instance bi
  INNER JOIN prefix_course c ON bi.pageid = c.id
  INNER JOIN prefix_block b ON bi.blockid = b.id
 WHERE bi.pageid = (SELECT pageid FROM prefix_block_instance WHERE id = $blockinstanceid)
  AND bi.pagetype = 'course-view'
  AND b.name = 'newsfeed'
 GROUP BY bi.pageid, c.category
            ");
            if (!$rs || $rs->EOF) {
                error('Error getting parent for newsfeed blockinstance '.$blockinstanceid);
            }
            if ($rs->fields['nfcount'] >= 2) {
                $this->parent = -$rs->fields['pageid'];
            } else {
                $this->parent = $rs->fields['category'];
            }
        }
        if(!$this->foldercache) {
            $this->foldercache=feed_system::$inst->get_location_folder($this->parent, null);
        }
        return $this->foldercache;
    }

    /**
     * @return string Name of feed e.g. 'B747 News', or null if not yet set
     */
    public function get_name() {
        return $this->fields->get('name');
    }
    /**
     * Sets value of field. Set methods don't take effect until you
     * call save_changes().
     * @param string $name Name of feed e.g. 'B747 News' (required)
     * @throws Exception If field is invalid
     */
    public function set_name($name) {
        $this->fields->set('name',$name);
    }

    /**
     * @return string Course shortname and name of feed
     */
    public function get_full_name() {
        return trim($this->get_courseshortname().' '.$this->get_name());
    }

    /**
     * @return string Presentation code e.g. '06K', or null if none
     */
    public function get_pres() {
        if (is_null($this->pres)) {
            $this->pres = '';
            if(class_exists('ouflags') && ($result = get_course_code_pres($this->get_courseshortname()))) {
                $this->pres = $result[2];
            }
        }
        return $this->pres;
    }

    /**
     * Get course shortname. Assumes block instance is newsfeed block instance.
     * @return string Course shortname, or null if none
     */
    public function get_courseshortname() {
        global $CFG;
        if (is_null($this->courseshortname)) {
            $biid = $this->get_blockinstance();
            if (($this->courseshortname = get_field_sql("
SELECT shortname
 FROM {$CFG->prefix}course c
 INNER JOIN {$CFG->prefix}block_instance bi ON c.id = bi.pageid
 WHERE bi.id = {$biid} AND bi.pagetype = 'course-view'
")) === false) {
                $this->courseshortname = '';
            }
        }
        return $this->courseshortname;
    }

     /**
     * @return string Summary, e.g. 'This is a feed about x'
     */
    public function get_summary() {
        return $this->fields->get('summary');
    }
    /**
     * Sets value of field. Set methods don't take effect until you
     * call save_changes().
     * @param string $summary Summary, e.g. 'This is a feed about x'
     * @throws Exception If field is invalid
     */
    public function set_summary($summary) {
        $this->fields->set('summary',$summary);
    }

    /**
     * Get start date. Get newsfeed (which is now course) start date.
     * Assumes block instance is newsfeed block instance.
     * @return string Newsfeed/course startdate
     */
    public function get_start_date() {
        global $CFG;
        if (is_null($this->coursestartdate)) {
            $biid = $this->get_blockinstance();
            if (($this->coursestartdate = get_field_sql("
SELECT startdate
 FROM {$CFG->prefix}course c
 INNER JOIN {$CFG->prefix}block_instance bi ON c.id = bi.pageid
 WHERE bi.id = {$biid} AND bi.pagetype = 'course-view'
")) === false) {
                $this->coursestartdate = 0;
            }
        }
        return $this->coursestartdate;
    }

    /**
     * @return bool True if feed is published publicly for RSS readers, false otherwise
     */
    public function is_public() {
        return $this->fields->get('publicfeed');
    }
    /**
     * Sets value of field. Set methods don't take effect until you
     * call save_changes().
     * @param string $publicflag True if feed is published publicly for RSS readers
     * @throws Exception If field is invalid
     */
    public function set_public($publicflag) {
        $this->fields->set('publicfeed',$publicflag);
    }

    /**
     * @return string Default authid if there is one, or null if none
     */
    public function get_default_authid() {
        return $this->fields->get('defaultauthid');
    }
    /**
     * Sets value of field. Set methods don't take effect until you
     * call save_changes().
     * @param string $defaultauthid Default authid if there is one, or null if none
     * @throws Exception If field is invalid
     */
    public function set_default_authid($defaultauthid) {
        $this->fields->set('defaultauthid',$defaultauthid);
    }

    /**
     * @return bool True if deleted, false otherwise
     */
    public function is_deleted() {
        return $this->fields->get('deleted');
    }
    /**
     * Sets value of field. Set methods don't take effect until you
     * call save_changes(). (And note that 'deleted' is only a toggle, it doesn't
     * really delete anything.)
     * @param bool $deleted True if feed has been 'deleted', false otherwise
     * @throws Exception If field is invalid
     */
    public function set_deleted($deleted) {
        $this->fields->set('deleted',$deleted);
    }

    /**
     * @return int Block instance (id)
     */
    public function get_blockinstance() {
        return $this->fields->get('blockinstance');
    }
    /**
     * Sets value of field. Set methods don't take effect until you
     * call save_changes().
     * @param string $blockinstance Block instance (id)
     * @throws Exception If field is invalid
     */
    public function set_blockinstance($blockinstance) {
        $this->fields->set('blockinstance',$blockinstance);
    }

    /**
     * @return bool True if some of the fields have been changed and save_changes
     *   needs to be called.
     */
    public function is_changed() {
        return $this->fields->is_changed();
    }

    /**
     * Saves this feed as a new one for the new block instance
     * @param int $changedate No longer used
     *                        Newsfeed start date usage replaced by course start date
     * @param int $block_instance_id
     *   this parameter MUST be set, the newsfeed is added to this block instance
     *   and the calling function is responsible for updating the configdata
     *   field for this block instance with the returned new newsfeed id
     * @throws Exception
     */
    public function save_as_new($changedate, $block_instance_id, $startdateoffset) {

        // Create new copy
        $oldid=$this->get_id();
        $this->set_blockinstance($block_instance_id);
        $newid=$this->insert_into_db();

        // authids
        db_do("
INSERT INTO prefix_newsfeed_authids(newsfeedid,authid)
SELECT $newid,authid FROM prefix_newsfeed_authids WHERE newsfeedid=$oldid
            ");

        // Return the new/copied newsfeed id
        return $newid;
    }

    /**
     * Saves all pending changes to field values.
     * @throws Exception If feed hasn't been created yet
     */
    public function save_changes() {
        $id=$this->get_id(); // Checks it's valid
        if(!$this->fields->is_changed()) {
            return false;
        }

        $fields=$this->fields->get_update_string();
        db_do("UPDATE prefix_newsfeed SET $fields WHERE id=$id");
        $this->fields->clear_changed();
        feed_system::$inst->clear_feed_cache($id);
        return true;
    }

    /**
     * Initialises the feed from database query (called by feed_system).
     * @param array $dbfields Associative array of field values with
     *   appropriate names, including 'id' for ID
     */
    public function init_from_db($dbfields) {
        $this->id=$dbfields['id'];
        $this->fields->set_from_db($dbfields);
    }

    /**
     * Creates a new feed using the defined values.
     * @throws Exception If any required fields haven't been set yet
     */
    public function create_new() {
        // Check it wasn't already created
        if($this->id) {
            throw new Exception("Already created",
                EXN_NEWSFEED_GOTID);
        }

        return $this->insert_into_db();
    }

    /**
     * Inserts fields for this feed into the database.
     */
    protected function insert_into_db() {
        $this->id=$this->fields->moodle_insert('newsfeed',array());
        $this->fields->clear_changed();
        return $this->id;
    }

    /**
     * Called by subclass if creation is rolled back (so we know
     * it wasn't really created and shouldn't have ID).
     */
    protected function fail_create() {
        $this->id=null;
    }

    /**
     * Wipes all entries in this feed. (Only for use in autogenerated feeds)
     */
    public function wipe_all_entries() {
        $id=$this->get_id();
        $tw=new transaction_wrapper();
        db_do("
DELETE FROM prefix_newsfeed_files WHERE versionid IN (
SELECT
    v.id
FROM
    prefix_newsfeed_entries e
    INNER JOIN prefix_newsfeed_versions v ON v.entryid=e.id
WHERE
    e.newsfeedid=$id
)");
        db_do("
DELETE FROM prefix_newsfeed_versions WHERE entryid IN (
SELECT
    e.id
FROM
    prefix_newsfeed_entries e
WHERE
    e.newsfeedid=$id
)");
        db_do("
DELETE FROM prefix_newsfeed_entries WHERE newsfeedid=$id
");
        $tw->commit();
    }

    /**
     * Obtains all entries from a certain set of feeds, either approved
     * or latest versions. Entries will be sorted in reverse date order.
     * @param string $idcondition SQL condition that selects feeds, e.g. 'nf.id=3'
     * @param bool $approved True if the latest approved version should be used,
     *   and entries excluded if there is no approved version; otherwise returns latest
     *   versions
     * @param bool $onlynewest If true (default), only returns newest version from each
     *   entry
     * @param int $userid If provided, obtains access information for the given user ID in the
     *   relevant feed.
     * @param bool $includedeleted If true, includes deleted entries, otherwise doesn't
     * @return array Array of news_entry_version objects
     */
    private function get_feed_entries($idcondition,$approved,$onlynewest=true,
        $userid=null,$includedeleted=false,$versionsort=false) {
        $approvedcheck = $approved ? 'AND timeapproved IS NOT NULL' : '';
        $deletedcheck = $includedeleted ? '' : "AND v.deleted=0";
        $newestcheck = $onlynewest ? "AND v.id=
            (SELECT MAX(id) FROM prefix_newsfeed_versions WHERE entryid=e.id $approvedcheck)" : "";
        if($userid) {
            // USED TO BE Extra SELECT just to get roles for each involved feed
            // NOW get newsfeed blockinstances and check capabilities properly
            $rs=db_do("
SELECT
    nf.id, nf.blockinstance
FROM
    prefix_newsfeed AS nf
WHERE
    $idcondition
");
            $nfs = recordset_to_array($rs);
            $knownroles=array();
            foreach ($nfs as $nf) {

                $context = get_context_instance(CONTEXT_BLOCK, $nf->blockinstance);
                if (has_capability('block/newsfeed:post', $context, $userid)) {
                    if (!array_key_exists($nf->id, $knownroles)) {
                        $knownroles[$nf->id]=array();
                    }
                    $knownroles[$nf->id][] = internal_news_feed::ROLE_POSTER;
                }

                if (has_capability('block/newsfeed:approve', $context, $userid)) {
                    if (!array_key_exists($nf->id, $knownroles)) {
                        $knownroles[$nf->id]=array();
                    }
                    $knownroles[$nf->id][] = internal_news_feed::ROLE_APPROVER;
                }
            }
        }
        $sort=$versionsort ? 'v.id DESC,f.filename' : 'v.appearancedate DESC,v.entryid DESC,v.id DESC,f.filename';
        $rs=db_do("
SELECT
    nf.id AS newsfeedid,nf.name AS newsfeedname,c.startdate AS newsfeedstart,
    v.entryid,v.id,
    v.appearancedate,v.rollforward,v.authid,v.title,v.html,v.poster,v.timeposted,
    v.approver,v.timeapproved,v.deleted,v.link,
    f.filename,f.mimetype,f.filesize,
    u1.username AS posterusername,(u1.firstname || ' ' || u1.lastname) AS posterrealname,
    u2.username AS approverusername,(u2.firstname || ' ' || u2.lastname) AS approverrealname,
    c.id AS courseid, c.shortname AS courseshortname
FROM
    prefix_newsfeed nf
    INNER JOIN prefix_newsfeed_entries e ON nf.id=e.newsfeedid
    INNER JOIN prefix_newsfeed_versions v ON e.id=v.entryid
    LEFT OUTER JOIN prefix_newsfeed_files f ON v.id=f.versionid
    LEFT OUTER JOIN prefix_user u1 ON v.poster=u1.id
    LEFT OUTER JOIN prefix_user u2 ON v.approver=u2.id
    INNER JOIN prefix_block_instance bi ON nf.blockinstance = bi.id AND pagetype = 'course-view'
    INNER JOIN prefix_course c ON bi.pageid = c.id
WHERE
    $idcondition $newestcheck $approvedcheck $deletedcheck
ORDER BY
    $sort
        ");
        $current=0;
        $currentversion=null;
        $currentfeed=0;
        $result=array();
        while(!$rs->EOF) {
            // Check for a repeat of the current version; that indicates just a second file
            if($rs->fields['id']==$current) {
                $currentversion->file_from_db($currentfeed,$rs->fields);
                $rs->MoveNext();
                continue;
            }
            $current=$rs->fields['id'];
            $currentfeed=$rs->fields['newsfeedid'];
            $namepres=$rs->fields['newsfeedname'];
            if(!is_null($rs->fields['courseshortname'])) {
                $namepres = $rs->fields['courseshortname'].' '.$namepres;
            }

            unset($currentversion);
            $currentversion =& new news_entry_version();
            $currentversion->init_from_db(
                $currentfeed,$namepres,
                $rs->fields['id'],$rs->fields['entryid'],$rs->fields);
            if(isset($knownroles)) {
                $currentversion->set_roles(
                    array_key_exists($currentfeed,$knownroles)
                    ? $knownroles[$currentfeed]
                    : array());
            }
            if(!is_null($rs->fields['filename'])) {
                $currentversion->file_from_db($currentfeed,$rs->fields);
            }
            $result[] =& $currentversion;
            $rs->MoveNext();
        }
        return $result;
    }

    /**
     * Returns all entries in this feed (alone: does not have messages
     * from other feeds that have been included).
     * @param bool $approved If true, returns approved versions (otherwise
     *   returns latest)
     * @param bool $includedeleted If true, includes deleted entries
     * @return array Array of news_entry_version objects sorted by appearance date
     *   (newest first)
     */
    public function get_own_entries($approved=false,$includedeleted=false) {
        return $this->get_feed_entries('nf.id='.$this->get_id(),$approved,true,null,$includedeleted);
    }

    /**
     * @return mixed Earliest date (seconds since epoch) of entry displaying in this feed, or
     *   false if none.
     */
    function get_earliest_date($approved=true) {
        $existing=$this->get_entries($approved);
        $earliest=false;
        foreach($existing as $entry) {
            $date=$entry->get_date();
            if(!$earliest || ($date < $earliest)) {
                $earliest=$date;
            }
        }
        return $earliest;
    }

    /**
     * Returns all approved entries in this feed including any included feeds (recursive).
     * @param bool $approved If true (default), retrieves only approved entries. Otherwise
     *   retrieves newest versions.
     * @param int $userid If provided, obtains access information for the given user ID in the
     *   relevant feed.
     * @param bool $includedeleted If true, includes deleted entries
     * @return array Array of news_entry_version
     */
    public function get_entries($approved=true,$userid=null,$includedeleted=false) {
        // Get all the feeds in question
        $ids=$this->get_all_descendant_ids();
        return $this->get_feed_entries('nf.id IN ('.implode(',',$ids).')',$approved,true,$userid,$includedeleted);
    }

    /**
     * Obtains the latest version of a given entry.
     * @param int $entryid ID of entry
     * @return newsfeed_entry_version Latest version of that entry
     * @throws Exception If entry not found
     */
    public function get_entry_by_entry($entryid) {
        return $this->get_single_entry('e.id='.sql_int($entryid),true);
    }
    /**
     * Obtains the given version of an entry.
     * @param int $versionid ID of version
     * @return newsfeed_entry_version Specified version
     * @throws Exception If version not found
     */
    public function get_entry_by_version($versionid) {
        return $this->get_single_entry('v.id='.sql_int($versionid),false);
    }

    /**
     * Obtains a single entry from this newsfeed.
     * @param string $condition Additional condition (as well as being from this feed)
     * @param bool $onlylatest If true, assumes that multiple versions may be returned
     *   and filters the list to include only the latest
     * @return newsfeed_entry_version Specified version
     * @throws Exception If version not found (or more than one match the condition)
     */
    private function get_single_entry($condition,$onlylatest) {
        $entries=$this->get_feed_entries(
            'nf.id='.$this->get_id().' AND '.$condition,false,$onlylatest,null,true);
        if(count($entries)!=1) {
            throw new Exception("Requested feed entry not found",
                EXN_NEWSFEED_ENTRYNOTFOUND);
        }
        return $entries[0];
    }

    /**
     * Returns the IDs of all feeds included in this one (including this one). Currently
     * there is a nesting limit of depth 5.
     * @return array Array of feed IDs.
     */
    public function get_all_descendant_ids() {
        return feed_system::$inst->get_all_relative_ids($this->get_id(),false);
    }

    /**
     * Returns the IDs of all ancestors or descendants, including this one.
     * This is basically the list used in UI to prevent you adding things as
     * includes if they are already included or they include this.
     * @return array Array of all the IDs
     */
    public function get_all_relatives_ids() {
        return array_unique(
            array_merge($this->get_all_ancestor_ids(), $this->get_all_descendant_ids()));
    }

    /**
     * Returns the IDs of all feeds that include this one (including this one). Currently
     * there is a nesting limit of depth 5.
     * @return array Array of feed IDs.
     */
    public function get_all_ancestor_ids() {
        return feed_system::$inst->get_all_relative_ids($this->get_id(),true);
    }

    /**
     * Returns all versions of a message in reverse version (not message date) order.
     * @param $entryid Entry ID of message
     * @return array Array of news_entry_version objects
     */
    function get_entry_history($entryid) {
        $id=$this->get_id();
        $history=$this->get_feed_entries(
            'nf.id='.sql_int($id).' AND e.id='.sql_int($entryid),false,false,null,true,true);
        if(count($history)==0) {
            throw new Exception("No such entry $entryid in feed $id",EXN_NEWSFEED_INVALIDID);
        }
        return $history;
    }

    const
        NS_ATOM='http://www.w3.org/2005/Atom',
        NS_OU='http://learn.open.ac.uk/ns/newsfeed';

    private static function add_child($parent,$name,$contents=null,$lf=true) {
        if(is_null($contents)) {
            $new=$parent->ownerDocument->createElementNS(self::NS_ATOM,$name);
        } else {
            $new=$parent->ownerDocument->createElementNS(self::NS_ATOM,$name);
            $new->appendChild($parent->ownerDocument->createTextNode($contents));
        }
        if($lf && !$parent->firstChild) {
            $parent->appendChild($parent->ownerDocument->createTextNode("\n"));
        }
        $parent->appendChild($new);
        if($lf) {
            $parent->appendChild($parent->ownerDocument->createTextNode("\n"));
        }
        return $new;
    }

    private static function date_rfc3339($epoch) {
        return gmdate('Y-m-d\TH:i:s\Z',$epoch);
    }

    /**
     * Builds current content of feed into the Atom format file.
     * @param string $file Filename to create (will automatically
     *   make folders if needed)
     * @param int $now Current time (use only for testing, otherwise
     *   leave default)
     */
    function build($file,$now=0) {
        global $CFG;
        if(!$now) {
            $now=time();
        }

        // Create document
        $dom=new DOMDocument('1.0','UTF-8');
        $root=$dom->createElementNS(self::NS_ATOM,'feed');
        $dom->appendChild($root);
        $root->setAttributeNS(self::NS_OU,'ou:public',$this->is_public() ? 'yes' : 'no');
        $root->appendChild($dom->createTextNode("\n"));

        // Domain name, used for IDs (we assume this is owned by site operator in 2006)
        $domainname = preg_replace('~^.*//(www\.)?([^/]*)(/.*)?$~', '$2', $CFG->wwwroot);

        // Add basic metadata
        self::add_child($root,'id','tag:'.
            $domainname.',2006:newsfeed/'.$this->get_id());
        self::add_child($root,'title',$this->get_name());
        if($summary=$this->get_summary()) {
            self::add_child($root,'subtitle',$summary);
        }
        $author=self::add_child($root,'author');
        self::add_child($author,'name','The Open University');
        self::add_child($author,'uri','http://www.open.ac.uk/');

        // Note that the default link doesn't take into account authid, so this isn't
        // quite the same as the one we'd make available to an individual user.
        $selflink=self::add_child($root,'link');
        $selflink->setAttribute('rel','self');
        $id=$this->get_id();
        $selflink->setAttribute('href',"{$CFG->wwwroot}/blocks/newsfeed/publicfeed.php?feed=$id");

        $viewlink=self::add_child($root,'link');
        $viewlink->setAttribute('rel','alternate');
        $viewlink->setAttribute('href', "{$CFG->wwwroot}/blocks/newsfeed/ui/viewfeed.php?newsfeedid=$id");

        if(is_a($this,'external_news_feed')) {
            if($this->get_error()) {
                $error=$dom->createElementNS(self::NS_OU,'error');
                $root->appendChild($error);
                $error->appendChild($dom->createTextNode($this->get_error()));
            }
        }

        // Get updated time from all published entries
        $entries=$this->get_entries();
        $max=0; $minnext=0;
        foreach($entries as $entry) {
            $date=$entry->get_date();
            if($date <= $now) {
                $max=max($date,$max);
                $max=max($entry->get_time_approved(),$max);
            } else {
                if($minnext==0 || $date < $minnext) {
                    $minnext=$date;
                }
            }
        }
        // Updated date for whole feed is the most recent visible
        // entry date OR the date at which any change was approved,
        // whichever's later
        self::add_child($root,'updated',self::date_rfc3339($max));
        // Cache expiry is the time of the next known message (the cache
        // is automatically cleared when any data actually changes, so
        // this only affects pre-created messages).
        if($minnext) {
            $root->setAttributeNS(self::NS_OU,'ou:expires',$minnext);
        }

        self::add_child($root,'rights',
            html_entity_decode('&copy;',ENT_NOQUOTES,'UTF-8').' '.
            date('Y',$max).' The Open University');

        // OK, go through each entry
        foreach($entries as $entry) {
            // Only include ones that are published by now
            $date=$entry->get_date();
            if($date > $now) {
                continue;
            }

            // Make new entry
            $entryel=self::add_child($root,'entry');

            // Get globally unique entry ID using tag: URI. Note that this ID
            // is supposed to remain the same forever so it would really be
            // better to actually store it with each entry. But in lieu of
            // that we're using the entry ID.
            self::add_child($entryel,'id','tag:'.
                $domainname.',2006:newsfeedentry/'.$entry->get_entry_id());

            self::add_child($entryel,'title',$entry->get_title());

            if ($entry->get_link()) {
                $linkel=self::add_child($entryel, 'link');
                $linkel->setAttribute('rel', 'related');
                $linkel->setAttribute('href', $entry->get_link());
            }
            $linkel = self::add_child($entryel,'link');
            $linkel->setAttribute('rel','alternate');
            $entryid = $entry->get_entry_id();
            $linkel->setAttribute('href',
                "{$CFG->wwwroot}/blocks/newsfeed/ui/viewfeed.php?newsfeedid=$id#e$entryid");
            
            // Include attachment links at end of content
            $content=$entry->get_html_with_attachments();

            $contentel=self::add_child($entryel,'content',$content);
            $contentel->setAttribute('type','html');

            $published=$entry->get_date();
            $approved=$entry->get_time_approved();
            self::add_child($entryel,'updated',self::date_rfc3339($approved > $published ? $approved : $published));
            self::add_child($entryel,'published',self::date_rfc3339($published));

           $authids=$entry->get_authid();
            if($authids) {
                $entryel->appendChild($dom->createElementNS(self::NS_OU,'ou:authid',$authids));
                $entryel->appendChild($dom->createTextNode("\n"));
            }
        }

        // Make folder if needed
        if(!mkdir_recursive(dirname($file))) {
            throw new Exception(
                "Error creating folders for $file",
                EXN_NEWSFEED_BUILDERRORIO);
        }
        // Save document
        if(!file_put_contents($file,$dom->saveXML())) {
            throw new Exception(
                "Error writing $file",
                EXN_NEWSFEED_BUILDERRORIO);
        }
    }

    /** Called from news_entry_version after a new version is saved. Default does nothing. */
    public function notify_new_version() {
    }

    /** Called from news_entry_version after a version is approved. Default does nothing. */
    public function notify_approve() {
    }
}

?>