<?php
/**
 * Information about a particular folder of the hierarchical 'location'
 * system. (At present, the location system does not actually store any
 * hiearchy info; you can't really create a folder independent of a
 * newsfeed.)
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 *//** */
 require_once(dirname(__FILE__).'/feed_system.php');

 class location_folder {

    private $name,$id,$parent=null;


    public function __construct() {
    }

    /**
     * Construct new folder from 'forwards' database information in which the first
     * field is root
     * @param array $fields Associative array from database of fields e.g. id0, name0
     */
    function init_forwards($fields) {
        for($count=0;isset($fields["id$count"]);$count++) {
        }
        $this->init_fields($fields,$count-1,-1);
    }

    /**
     * Construct new folder from 'forwards' database information in which the last
     * field is root
     * @param array $fields Associative array from database of fields e.g. id0, name0
     */
    function init_backwards($fields) {
        $this->init_fields($fields,0,1);
    }

    /**
     * Recursively initialise, including creating all ancestors.
     * @param array $fields Associative array from database of fields e.g. id0, name0
     * @param int $current Which position in array to use for creating this folder
     * @param int $direction Whether to go up +1 or down -1 in the array to find the
     *   parent (if present)
     */
    private function init_fields($fields,$current,$direction) {
        $this->name=$fields["name$current"];
        $this->id=$fields["id$current"];

        $current+=$direction;
        if(isset($fields["id$current"])) {
            $this->parent=new location_folder();
            $this->parent->init_fields($fields,$current,$direction);
        }
    }


    /** @return string Name of this folder */
    function get_name() {
        return $this->name;
    }
    /** @return int ID of this folder */
    function get_id() {
        return $this->id;
    }
    /** @return Full path of folder */
    function get_path() {
        if(is_null($this->parent)) {
            return '/';
        } else {
            $parentpath=$this->parent->get_path();
            if($parentpath!='/') {
                $parentpath.='/';
            }
            return $parentpath.$this->name;
        }
    }
     /**
      * Obtains the contents (like a directory listing) of this folder.
      * @return array Array of location_folder_thing items
      */
     function get_contents() {
         global $db; 
         $result=array();

        $id=$this->get_id();
        // Categories are positive, courses are negative
        if ($id == 'notequal') {
            // Category 0 = root so get subcategories only
            $rs=db_do("
SELECT cc.id AS id, cc.name AS cname, 0 as nfid, '' AS nfname
 FROM prefix_course_categories AS cc
 WHERE cc.parent = $id
 ORDER by 2;
            ");
        } else if ($id >= 0) {
            $rs=db_do("
-- Category sub-categories for courses with newsfeeds
SELECT cc.id AS id, cc.name AS cname, 0 as nfid, '' AS nfname
 FROM prefix_course_categories AS cc
  INNER JOIN prefix_course AS c ON cc.id = c.category
  INNER JOIN prefix_block_instance AS bi ON c.id = bi.pageid
  INNER JOIN prefix_block AS b ON bi.blockid = b.id
  INNER JOIN prefix_newsfeed AS nf ON bi.id = nf.blockinstance
 WHERE cc.parent = $id
  AND bi.pagetype = 'course-view'
  AND b.name = 'newsfeed'
 UNION
-- category courses with newsfeeds
  SELECT c.id AS id, c.shortname AS cname, nf.id as nfid,
         ".$db->concat('c.shortname',"' '",'nf.name')." AS nfname
  FROM prefix_course_categories AS cc
   INNER JOIN prefix_course AS c ON cc.id = c.category
   INNER JOIN prefix_block_instance AS bi ON c.id = bi.pageid
   INNER JOIN prefix_block AS b ON bi.blockid = b.id
   INNER JOIN prefix_newsfeed AS nf ON bi.id = nf.blockinstance
  WHERE cc.id = $id
   AND bi.pagetype = 'course-view'
   AND b.name = 'newsfeed'
  ORDER by 1
            ");
        } else {
            // Get course newsfeeds
            $id = -$id;
            $rs=db_do("
SELECT 0 AS id, '' AS cname, nf.id AS nfid,
       ".$db->concat('c.shortname',"' '",'nf.name')." AS nfname
 FROM prefix_course c
  INNER JOIN prefix_block_instance bi ON c.id = bi.pageid
  INNER JOIN prefix_block b ON bi.blockid = b.id
  INNER JOIN prefix_newsfeed nf ON bi.id = nf.blockinstance
 WHERE c.id = $id
  AND bi.pagetype = 'course-view'
  AND b.name = 'newsfeed'
 ORDER by 4
            ");
        }

        // Define previous course id for checking multiple newsfeeds for course
        // and previous newsfeed in case there aren't
        $prevcourseid = -1;
        $prevnewsfeed = null;
        $prevkey = null;

        while(!$rs->EOF) {
            $nfid = $rs->fields['nfid'];
            if (!$nfid) {
                // no newsfeed id, must be sub-category with course(s) and newsfeeds
                $cname = $rs->fields['cname'];
                $result['1'.strtoupper($cname)] = new location_folder_thing($rs->fields['id'], 1, $cname, 1);
            } else {
                $id = $rs->fields['id'];
                if (!$id) {
                    // no course id, must be newsfeed
                    $nfname = $rs->fields['nfname'];
                    $result['1'.strtoupper($nfname).$nfid] = new location_folder_thing($nfid, 2, $nfname, 0);
                } else {
                    // course id and newsfeed id, Check which - Note: Ordered by course id
                    if ($id == $prevcourseid) {
                        if (isset($prevnewsfeed)) {
                            // First duplicate course id, course with newsfeeds
                            $prevnewsfeed = null;
                            $cname = $rs->fields['cname'];
                            $result['1'.strtoupper($cname)] = new location_folder_thing(-$id, 1, $cname, 1);
                        }
                    } else {
                        $prevcourseid = $id;
                        if (isset($prevnewsfeed)) {
                            $result[$prevkey] = $prevnewsfeed;
                        }
                        $nfname = $rs->fields['nfname'];
                        $prevnewsfeed = new location_folder_thing($nfid, 2, $nfname, 0);
                        $prevkey = '1'.strtoupper($nfname).$nfid;
                    }
                }
            }
            $rs->MoveNext();
        }
        if (isset($prevnewsfeed)) {
            $result[$prevkey] = $prevnewsfeed;
        }

        // sort result array, folders followed by newsfeeds
        ksort($result);
        return $result;
     }

     const TYPE_FOLDER=1,TYPE_FEED=2;
 }

 /**
  * Represents a thing that can be inside a folder (either feed or another folder).
  */
 class location_folder_thing {
    private $id,$type,$name,$haschildren;
    function __construct($id,$type,$name,$haschildren) {
        $this->id=$id;
        $this->type=$type;
        $this->name=$name;
        $this->haschildren=$haschildren==1;
    }
    /** @return int Numeric database ID of thing */
    public function get_id() {
        return $this->id;
    }
    /** @return int Type of thing, either location_folder::TYPE_FOLDER or TYPE_FEED */
    public function get_type() {
        return $this->type;
    }
    /** @return string Display name of thing */
    public function get_name() {
        return $this->name;
    }
    /** @return bool True if this thing has any children of its own */
    public function has_children() {
        return $this->haschildren;
    }
    /** @return string For debug use basically */
    public function get_debug_string() {
       return ($this->type==location_folder::TYPE_FOLDER ? 'FOLDER:' : 'FEED:').
         $this->id.':'.$this->name;
    }
 }

?>