<?php
require_once(dirname(__FILE__).'/system/feed_system.php');
require_once(dirname(__FILE__).'/ui/sharedui.php');

/**
 * Newsfeed display block.
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 */
class block_newsfeed extends block_base {

    function init() {
        $this->title = get_string('newsfeed','block_newsfeed');
        $this->version = feed_system::$inst->get_database_version();
        $this->cron = 60*60; // 1 hour
    }

    function after_restore($restore) {
        // Try to roll forward newsfeeds if appropriate
        if(isset($this->config->newsfeedid) && ($id = $this->config->newsfeedid)) {
            // Get feed
            try {
                $existingfeed=feed_system::$inst->get_feed($id);

                // Get new course shortname and startdate
                $newcourse = get_record('course', 'id', $restore->course_id);
                $newshortname = $newcourse->shortname;
                $newstartdate = $restore->course_startdateoffset ? $newcourse->startdate : 0;

                // Copy or roll forward newsfeed
                $existingfeed->roll_forward($newshortname, $newstartdate,
                                            $this->instance->id, $restore->course_startdateoffset);
                $this->config->newsfeedid=$existingfeed->get_id();
            } catch(Exception $e) {
                // Feed doesn't exist, or something else went wrong; chuck it
                $this->config->newsfeedid=0;
            }

            // Old backups may not showsummaries parameter
            if(!isset($this->config->showsummaries)) {
                $this->config->showsummaries = 1;
            }

            return $this->instance_config_save($this->config);
        }
    }

    function get_content() {
        global $CFG;

        if ($this->content!==NULL) {
            return $this->content;
        }

        if($this->config) {
            if(!isset($this->config->showsummaries)) {
                $this->config->showsummaries=1;
            }
            if(!isset($this->config->showcount)) {
                $this->config->showcount=3;
            }
        }

        $this->content = new stdClass;
        $this->content->footer = ' ';
        if($this->config && $this->config->newsfeedid) {
            $newsfeedid=$this->config->newsfeedid;
            $doc = DOMDocument::loadXML(
                feed_system::$inst->get_feed_data($newsfeedid,false));
            if (!$doc) {
                $this->content->text = '<p>' . 
                    get_string('feed_error', 'block_newsfeed') . '</p>';
                $this->title = get_string('newsfeed', 'block_newsfeed');
                return $this->content;
            }

            if(!$this->config->showsummaries) {
                $this->content->text.='<div class="newsfeed_nosummaries">';
            }

            $nl=$doc->getElementsByTagName('entry');
            for($i=0;$i<$nl->length && $i<$this->config->showcount;$i++) {
                $this->content->text.=get_atom_entry($nl->item($i),true,null,$this->config->showsummaries);
            }
            $this->title = get_single_element_text($doc,'title');
            $shortname=get_field('course','shortname','id',$this->instance->pageid);
            if(strpos($this->title,$shortname.' ')===0) {
                $this->title=substr($this->title,strlen($shortname)+1);
            } else if(class_exists('ouflags') && $result=get_course_code_pres($shortname)) {
                // OU course-pres (ignore pres, it isn't in there)
                $this->title=preg_replace('/^'.$result[1].' /','',$this->title);
            }

            $courseid=$this->instance->pageid;
            $strviewall=get_string('viewall','block_newsfeed');
            $this->content->text.="
<div class='newsfeed_viewlink'>
<a href='{$CFG->wwwroot}/blocks/newsfeed/ui/viewfeed.php?newsfeedid=$newsfeedid&amp;courseid=$courseid'>
$strviewall
</a>
</div>";

            if(!$this->config->showsummaries) {
                $this->content->text.='</div>';
            }

        } else {
            $this->content->text = '<p>No feed selected</p>';
            $this->title = get_string('newsfeed','block_newsfeed');
        }

        return $this->content;
    }

    function after_install() {
        feed_system::$inst->install_database();
    }

    function before_delete() {
        // Delete tables in correct order (don't necessarily need to
        // include everything here as Moodle already deletes prefix_newsfeed_*,
        // this is just tables that have foreign key dependencies of one sort
        // or another).
        db_do('DROP TABLE prefix_newsfeed_includes');
        db_do('DROP TABLE prefix_newsfeed_external');
        db_do('DROP TABLE prefix_newsfeed_authids');
        db_do('DROP TABLE prefix_newsfeed_files');
        db_do('DROP TABLE prefix_newsfeed_versions');
        db_do('DROP TABLE prefix_newsfeed_entries');
        db_do('DROP TABLE prefix_newsfeed');
    }
    function has_config() {
        return false;
    }

    function cron() {
        try {
            $feeds=feed_system::$inst->get_due_external_feeds();
            mtrace('Updating '.count($feeds).' feeds');
            foreach($feeds as $feed) {
                $feed->check();
            }
        } catch(Exception $e) {
            mtrace('Error: '.$e->getMessage());
            return false;
        }
        return true;
    }

    function instance_allow_multiple() {
        return true;
    }

    function applicable_formats() {
        return array('all' => true, 'mod' => false, 'my' => false);
    }

    /**
     * Overriding default behavior: Frig to include newsfeed HTML QuickForm
     * rather than including just the config_instance.html file
     *
     * @uses $CFG
     * @return boolean
     * @todo finish documenting this function
     */
    function instance_config_print() {
        // Copied from parent - seems odd but assume it works as it's from core
        if (!$this->instance_allow_multiple() && !$this->instance_allow_config()) {
            return false;
        }

        print '</form>';
        include(dirname(__FILE__).'/ui/editfeed.php');
        print '<form>';

        return true;
    }

    /**
     * Serialize and store config data
     * @return boolean
     * @todo finish documenting this function
     */
    function instance_config_save($data,$pinned=false) {
        global $PAGE, $USER;

        // check if moving newsfeed block
        if (isset($data->submitmove)) {

            // Include are you sure page
            include(dirname(__FILE__).'/move_newsfeed.php');
            exit();
        }

        // Check if saving newsfeed config as result of editing (is there a better way)
        if (!isset($data->returntofeed)) {

            // Not saving newsfeed config as result of editing
            // Either course create from preloaded or restore
            $configdata = new stdClass();
            $configdata->newsfeedid = $data->newsfeedid;
            $configdata->showcount = $data->showcount;
            $configdata->showsummaries = $data->showsummaries;
            return parent::instance_config_save($configdata, $pinned);
        }

        // Saving newsfeed config as result of editing
        // Frig to check for add 3 fields to form button clicked (or maybe return!)
        if (optional_param('optionalauthid_add_fields','x',PARAM_RAW) !== 'x') {
            $url = $PAGE->url_get_full(array('instanceid' => $this->instance->id,
                                             'sesskey' => $USER->sesskey,
                                             'blockaction' => 'config',
                                             'optionalauthid_add_fields' => 'Add 3 fields to form',
                                             'optionalauthid_repeats' => optional_param('optionalauthid_repeats',0,PARAM_INT),
                                             'returntofeed' => optional_param('returntofeed','n',PARAM_RAW)));
            redirect($url);
        }

        // Frig to check for required fields on HTML QuickForm
        if (empty($data->nfname)) {
            $url = $PAGE->url_get_full(array('instanceid' => $this->instance->id,
                                             'sesskey' => $USER->sesskey,
                                             'blockaction' => 'config',
                                             'returntofeed' => optional_param('returntofeed','n',PARAM_RAW)));
            error(get_string('nonewsfeedname', 'block_newsfeed'), $url);
        }

        // Do original newsfeed form processing
        // but first reset flag to check if form cancelled
        $formcancelled = false;
        include(dirname(__FILE__).'/ui/editfeed.php');

        // Init return value
        $return = true;

        // Do not save config if form cancelled
        if (!$formcancelled) {

            // Otherwise save just the required config data
            $configdata = new stdClass();
            $configdata->newsfeedid = $data->newsfeedid;
            $configdata->showcount = $data->showcount;
            $configdata->showsummaries = $data->showsummaries;
            $return = parent::instance_config_save($configdata, $pinned);
        }

        // Check whether returning to course or feed
        if (($returntofeed = optional_param('returntofeed','n',PARAM_RAW)) !== 'y') {

            // Not returning to feed - just return value
            return $return;
        } else {

            // Returning to feed - error if failed to save config
            if(!$return) {
                $url = $PAGE->url_get_full(array('instanceid' => $this->instance->id,
                                                 'sesskey' => $USER->sesskey,
                                                 'blockaction' => 'config',
                                                 'returntofeed' => optional_param('returntofeed','n',PARAM_RAW)));
                error('Error saving block configuration', $url);
            }

            // Return to feed
            $courseid = optional_param('courseid',0,PARAM_INT);
            $url = "{$CFG->wwwroot}/blocks/newsfeed/ui/viewfeed.php?newsfeedid=$data->newsfeedid&amp;courseid=$courseid";
            redirect($url);
        }
    }

    function instance_delete() {
        global $CFG;

        if (isset($this->config->newsfeedid)) {

            // Set flag if deleting course to just delete to avoid other issues
            $bdelete = false;
            if((isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '/course/delete.php')) ||
               (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '/backup/restore.php'))) {
                $bdelete = true;
            }

            // Check for any newsfeed blocks that include this one
            $sql = "
SELECT n.id, n.name, bi.pageid
 FROM {$CFG->prefix}newsfeed_includes ni
 left outer join {$CFG->prefix}newsfeed n on ni.parentnewsfeedid = n.id
 left outer join {$CFG->prefix}block_instance bi on n.blockinstance = bi.id
 where childnewsfeedid = {$this->config->newsfeedid};
";

            // Get the newsfeed blocks that include this one
            $rs = get_recordset_sql($sql);

            // Error? if query failed, as something seriously wrong
            if (!$rs) {
                print_error ('delete_error', $module='block_newsfeed');
            }

            // Frig to allow newsfeed to be deleted if the only newsfeed block
            // that includes this one is the global 'All feeds view' newsfeed
            if ($rs->RecordCount() == 1 && $rs->fields['name'] == 'All feeds view') {
                $bdelete = true;
            }

            // Do not delete block if any includes exist
            if (!$bdelete && $rs->RecordCount() != 0) {

                // Continue to newsfeed includes page
                $url = $CFG->wwwroot.'/blocks/newsfeed/ui/editincludes.php?newsfeedid='.$this->config->newsfeedid.'&courseid='.$this->instance->pageid;
                print_error ('delete_forbidden', 'block_newsfeed', $url);

            }

            // Delete newsfeed includes
            delete_records('newsfeed_includes', 'childnewsfeedid', $this->config->newsfeedid);
            delete_records('newsfeed_includes', 'parentnewsfeedid', $this->config->newsfeedid);

            // If can delete block then mark newsfeed as deleted
            set_field('newsfeed', 'deleted', 1, 'id', $this->config->newsfeedid);
        }

    }

}
?>