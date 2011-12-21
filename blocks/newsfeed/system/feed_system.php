<?php
// OU shared APIs which (for OU system) are present in local, elsewhere
// are incorporated in module
if(!@include_once(dirname(__FILE__).'/../../../local/utils.php')) {
    require_once(dirname(__FILE__).'/../local/utils_shared.php');
}
if(!class_exists('transaction_wrapper') && !@include_once(dirname(__FILE__).'/../../../local/transaction_wrapper.php')) {
    require_once(dirname(__FILE__).'/../local/transaction_wrapper.php');
}
require_once(dirname(__FILE__).'/exceptions.php');
require_once('internal_news_feed.php');
require_once('external_news_feed.php');
require_once('location_folder.php');

/**
 * Main class providing backend for newsfeed features.
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 */
class feed_system {
    const FEED_CACHE_FOLDER='/newsfeed/cache';
    const FEED_FILES_FOLDER='/newsfeed/files';

    /**
     * @return int Current version of database tables
     */
    function get_database_version() {
        return 2010012700;
    }

    /**
     * Called just before Moodle tries to install the database tables
     * for first time from the blank .sql files. Used to install the
     * roles and do other database setup.
     */
    function install_database() {
        global $CFG;

        // Ensure newsfeed capabilities updated first [Is this needed?]
        update_capabilities('block/newsfeed');
    }

    /** Feed cache (associative array of ID->news_feed) */
    private $feeds=array();

    /**
     * Obtains an object representing data held about a news feed.
     * @param int $id ID of desired feed
     * @return news_feed An external_news_feed or internal_news_feed object
     */
    function get_feed($id) {
        $result=$this->get_feeds(array($id));
        return $result[0];
    }

    /**
      * Obtains a particular feed from a course shortname. (If more than one
      * feed for the course shortname has the same name, it returns the first.)
      * @param string $cshortname Course shortname
      * @param string $name Name of feed
      * @return news_feed Feed object or null if not found
     */
    function find_feed_for_course_shortname($cshortname, $nfname) {
        $retrieved=$this->query_for_feeds("
FROM
    prefix_newsfeed nf
    INNER JOIN prefix_block_instance bi ON nf.blockinstance = bi.id
    INNER JOIN prefix_course c ON bi.pageid = c.shortname
    LEFT OUTER JOIN prefix_newsfeed_external e ON nf.id=e.newsfeedid
WHERE
    nf.name='$nfname' AND deleted=0
    AND bi.pagetype = 'course-view'
    AND c.shortname = '$cshortname'
            ");
        if(count($retrieved)<1) {
            return null;
        }
        return $retrieved[0];
    }

    /**
     * Obtains objects representing a list of news feeds.
     * @param array $ids Array of IDs of feed in question
     * @return array Array of external_news_feed or internal_news_feed objects
     *   (note: this is not necessarily in the same order as $ids).
     */
    function get_feeds($ids) {
        if(count($ids)==0) {
            return array();
        }

        $idlist=implode(',',$ids);
        if(!preg_match('/^[0-9]+(,[0-9]+)*$/',$idlist)) {
            throw new Exception("Invalid ID in list $idlist",EXN_NEWSFEED_INVALIDID);
        }
        $retrieved=$this->query_for_feeds("
FROM
    prefix_newsfeed nf
    LEFT OUTER JOIN prefix_newsfeed_external e ON nf.id=e.newsfeedid
WHERE
    nf.id IN ($idlist)
            ");
        if(count($retrieved)!=count($ids)) {
            throw new Exception("Missing feed from list $idlist",EXN_NEWSFEED_INVALIDID);
        }
        return $retrieved;
    }

    /**
     * Generic function that runs a query to obtain information about feeds.
     * (Basically just abstracted out of other methods to avoid code
     * duplication.)
     * @param string $fromwhere FROM and WHERE clauses of query
     * @return array Array of news_feed objects
     */
    private function query_for_feeds($fromwhere) {
        $result=array();
        $rs=db_do("
SELECT
    nf.id,
    nf.name,nf.summary,nf.publicfeed,nf.defaultauthid,nf.deleted,nf.blockinstance,
    e.newsfeedid,e.url,e.checkfreq,e.lastcheck,e.error
$fromwhere
            ");
        while(!$rs->EOF) {
            $feed=$this->init_feed($rs->fields);
            $result[]=$feed;
            $rs->MoveNext();
        }
        return $result;
    }

    /**
     * Obtains all the external feeds that are due to be updated.
     * "return array Array of external_news_feed objects
     */
    function get_due_external_feeds() {
        $now=time();
        return $this->query_for_feeds("
FROM
    prefix_newsfeed_external e
    INNER JOIN prefix_newsfeed nf ON e.newsfeedid=nf.id
WHERE
    e.checkfreq > 0 AND
    nf.deleted = 0 AND
    (e.lastcheck IS NULL OR e.lastcheck+e.checkfreq < $now)
            ");
    }

    /**
     * Obtains all the feeds included by a particular ID (usually called via
     * internal_news_feed function, which caches the result).
     * @param $id ID of parent feed
     * @return array Array of news_feed objects (0-length if none)
     */
    function get_included_feeds($id) {
        return $this->query_for_feeds("
FROM
    prefix_newsfeed_includes i
    INNER JOIN prefix_newsfeed nf ON i.childnewsfeedid=nf.id
    LEFT OUTER JOIN prefix_newsfeed_external e ON nf.id=e.newsfeedid
WHERE
    i.parentnewsfeedid=$id
            ");
    }

    /**
     * Obtains all the feeds that include the given one.
     * @param $id ID of child feed
     * @return array Array of news_feed objects (0-length if none)
     */
    function get_including_feeds($id) {
        return $this->query_for_feeds("
FROM
    prefix_newsfeed_includes i
    INNER JOIN prefix_newsfeed nf ON i.parentnewsfeedid=nf.id
    LEFT OUTER JOIN prefix_newsfeed_external e ON nf.id=e.newsfeedid
WHERE
    i.childnewsfeedid=$id
            ");
    }

    /**
     * Creates a newsfeed based on the results of a database query that joins the
     * newsfeed table with newsfeed_external.
     * @param $dbfields Value of $rs->fields
     * @return news_feed An external_news_feed or internal_news_feed object
     */
    private function init_feed($dbfields) {
        if(!is_null($dbfields['newsfeedid'])) {
            $nf=new external_news_feed();
        } else {
            $nf=new internal_news_feed();
        }
        $nf->init_from_db($dbfields);
        return $nf;
    }

    // Regex for locations (just so we can reuse elsewhere)
    const LOCATION_REGEX='/^\/(.*[^\/]|)$/';
    const MAX_FOLDER_NESTING=10;

    /**
     * Obtains a location_folder object.
     * @param int $id ID of folder, or null if path is specified
     * @param string $path Path of folder, or null if ID is specified
     * @return location_folder Folder object
     */
    public function get_location_folder($id,$path) {
        global $CFG;
        if(is_null($id) && is_null($path))  {
            throw new Exception("Must specify either ID or path",
                EXN_NEWSFEED_INVALIDPARAMS);
        }
        if(!is_null($id) && !is_null($path)) {
            throw new Exception("Must specify only one of ID or path, not both",
                EXN_NEWSFEED_INVALIDPARAMS);
        }

        $lf=new location_folder();
        if(!is_null($path)) {
            if(!preg_match(self::LOCATION_REGEX,$path)) {
                throw new Exception("Folder location not valid: $path",
                    EXN_NEWSFEED_LOCATIONINVALID);
            }
            // Split path into components
            $components=$path=='/' ? array('') : explode('/',$path);
            $joins='';
            $checks='';
            $selects='';
            for($i=1;$i<count($components);$i++) {
                if ($i != 1) {
                    $joins.=" INNER JOIN prefix_course_categories f$i ON f$i.parent=f".($i-1).".id";
                } else {
                    $joins.=" INNER JOIN prefix_course_categories f1 ON f1.parent=0";
                }
                $checks.=" AND f$i.name=".db_q($components[$i]);
                $selects.=", f$i.id AS id$i, f$i.name AS name$i";
            }

            $rs=db_do("
SELECT
  0 AS id0, '' AS name0
  $selects
FROM
  prefix_course_categories f0
  $joins
WHERE
  f0.id = (select min(id) from prefix_course_categories)
  $checks
                ");
            if($rs->EOF) {
                throw new Exception("Folder path does not exist: $path",
                    EXN_NEWSFEED_LOCATIONINVALID);
            }
            $lf->init_forwards($rs->fields);
        } else { // ID
            if(!preg_match('/^[0-9\-]+$/',$id)) {
                throw new Exception("Folder ID not valid: $id",
                    EXN_NEWSFEED_INVALIDID);
            }

            // Find the hierarchy
            $selects='';
            $joins='';
            for($i=1;$i<self::MAX_FOLDER_NESTING;$i++) {
                if ($i == 1 && $id <= 0) {
                    if ($id == 0) {
                        // Special case for category 0 (There isn't one in the database)
                        $joins.=" LEFT OUTER JOIN prefix_course_categories f1 ON f1.id = 0";
                    } else {
                        // Special case for course id (negative $id)
                        $joins.=" INNER JOIN prefix_course_categories f1 ON f1.id = f0.category";
                    }
                } else {
                    $joins.=" LEFT OUTER JOIN prefix_course_categories f$i ON f$i.id=f".($i-1).".parent";
                }
                $selects.=",f$i.id AS id$i,f$i.name AS name$i";
            }
            if ($id != 0) {
                if ($id < 0) {
                    // Special case for course id (negative $id)
                    $id = -$id;
                    $type = 'INTEGER';
                    if (preg_match('~^mysql~', $CFG->dbtype)) {
                        $type = 'SIGNED';
                    }
                    $rs=db_do("
SELECT
  0-CAST(f0.id AS $type) AS id0,f0.shortname AS name0
  $selects
FROM
  prefix_course f0
  $joins
WHERE
  f0.id=$id
                    ");
                } else {
                    $rs=db_do("
SELECT
  f0.id AS id0,f0.name AS name0
  $selects
FROM
  prefix_course_categories f0
  $joins
WHERE
  f0.id=$id
                    ");
                }
                if (!$rs->EOF) {
                    $i = 0;
                    $idi = $rs->fields["id$i"];
                    while(isset($idi) && !empty($idi)) {
                        $i++;
                        $idi = $rs->fields["id$i"];
                    }
                    $rs->fields["id$i"] = 0;
                    $rs->fields["name$i"] = '';
                }
            } else {
                // Special case for category 0 (There isn't one in the database)
                $rs=db_do("
SELECT
  0 AS id0, '' AS name0
  $selects
FROM
  prefix_course_categories f0
  $joins
WHERE
  f0.id = (select min(id) from prefix_course_categories)
                ");
            }
            if($rs->EOF) {
                throw new Exception("Folder ID not found: $id",
                    EXN_NEWSFEED_LOCATIONINVALID);
            }
            $lf->init_backwards($rs->fields);
        }
        return $lf;
    }

    /**
     * Obtains the filename for a cached feed.
     * @param int $newsfeedid ID of feed
     * @param bool $public If true looks for public version, else private
     * @return string Filename (may or may not actually exist!)
     *
     */
    private function get_cache_name($newsfeedid) {
        global $CFG;
        return $CFG->dataroot.self::FEED_CACHE_FOLDER.
            "/$newsfeedid.atom";
    }

    /**
     * If any feed data has been cached for a particular feed, clears
     * that data (does nothing otherwise). Also clears all feeds that
     * depend on that feed.
     * @param int $newsfeedid ID of newsfeed
     */
    function clear_feed_cache($newsfeedid) {
        // Get list of everything that includes feed (includes self)
        $parents=$this->get_all_relative_ids($newsfeedid,true);
        // Delete cached data for all these feeds
        foreach($parents as $id) {
            @unlink($this->get_cache_name($id));
        }
    }

    /**
     * Returns the IDs of either all parents/ancestors or all children/descendents of a
     * feed. Also includes the specified feed. There is a nesting limit of depth 5.
     * @param bool $parents If true, returns parents, otherwise returns children
     * @return array Array of feed IDs.
     */
    function get_all_relative_ids($id,$parents) {
        $sourcefield = $parents ? 'childnewsfeedid' : 'parentnewsfeedid';
        $destfield = $parents ? 'parentnewsfeedid' : 'childnewsfeedid';

        $rs=db_do("
SELECT
    i1.$destfield as r1,i2.$destfield as r2,i3.$destfield as r3,i4.$destfield as r4,i5.$destfield as r5
FROM
    prefix_newsfeed_includes i1
    LEFT OUTER JOIN prefix_newsfeed_includes i2 ON i2.$sourcefield=i1.$destfield
    LEFT OUTER JOIN prefix_newsfeed_includes i3 ON i3.$sourcefield=i2.$destfield
    LEFT OUTER JOIN prefix_newsfeed_includes i4 ON i4.$sourcefield=i3.$destfield
    LEFT OUTER JOIN prefix_newsfeed_includes i5 ON i5.$sourcefield=i4.$destfield
WHERE
    i1.$sourcefield=$id
        ");

        // Basically the results contain all paths through the hierarchy starting from this.
        // Sometimes it's important to track every element e.g. there may be only one return
        // value, say 1,3,5, and we need to track all those 3 elements. At other times this
        // could result in duplicates e.g. if there's a second result 1,3,6 - so we just make
        // up a set of everything.
        $array=array();
        $array[$id]=true;
        while(!$rs->EOF) {
            for($i=1;$i<=5;$i++) {
                $result=$rs->fields['r'.$i];
                if(!is_null($result)) {
                    $array[$result]=true;
                }
            }
            $rs->MoveNext();
        }
        return array_keys($array);
    }

    /**
     * Obtain feed data for a given feed ID.
     * @param int $newsfeedid Feed in question
     * @param bool $external True if the request is external (from outside
     *   the current server)
     * @throws Exception If an external request for a non-public feed
     *   (EXN_NEWSFEED_FEEDNOTPUBLIC), if there isn't a feed with that ID
     *   (EXN_NEWSFEED_INVALIDID), or if something else goes wrong.
     */
    public function get_feed_data($newsfeedid,$external) {
        $file=$this->get_cache_name($newsfeedid);
        if(!file_exists($file)) {
            $this->get_feed($newsfeedid)->build($file);
        }
        $contents=file_get_contents($file);
        // In case the file is blank or not XML, rebuild it then too
        if (strpos($contents, 'xmlns="http://www.w3.org/2005/Atom"') === false) {
            $this->get_feed($newsfeedid)->build($file);
            $contents=file_get_contents($file);
        }
        // Don't bother checking public if we're internal
        if($external) {
            // This regex check is slightly hacky compared to proper XML
            // parse, but since we're generating the format, it'll do.
            $matches=array();
            if(!preg_match('/ou:public="(yes|no)"/',$contents,$matches)) {
                throw new Exception("Newsfeed is missing public flag",
                    EXN_NEWSFEED_FEEDFORMATERROR);
            }
            if($matches[1]!='yes') {
                throw new Exception("Newsfeed is not publicly accessible",
                    EXN_NEWSFEED_FEEDNOTPUBLIC);
            }
        }
        // Check expiry
        $matches=array();
        if(preg_match('/ou:expires="([0-9]+)"/',$contents,$matches)) {
            if($matches[1] < time()) {
                // Delete file and recurse
                unlink($file);
                return $this->get_feed_data($newsfeedid,$external);
            }
        }
        return $contents;
    }

    /** Singleton instance */
    public static $inst;
}
feed_system::$inst=new feed_system();
?>