<?php
/**
 * Form for editing or creating a newsfeed.
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 *//** */
require_once(dirname(__FILE__).'/../../../config.php');
require_once(dirname(__FILE__).'/../system/feed_system.php');
require_once(dirname(__FILE__).'/sharedui.php');

require_once(dirname(__FILE__).'/newsfeed_editfeed_form.php');

// Handle page parameters
$returntofeed=optional_param('returntofeed','n',PARAM_RAW);
$courseid=optional_param('courseid',0,PARAM_INT);
if($newsfeedid=optional_param('newsfeedid',0,PARAM_INT)) {
    // Load feed
    $nf=feed_system::$inst->get_feed($newsfeedid);
    // Get folder path
} else {
    $nf=null;
    if (!empty($this->instance->configdata)) {
        $config = unserialize(base64_decode($this->instance->configdata));
        if (!empty($config->newsfeedid)) {
            $newsfeedid = $config->newsfeedid;
            // Load feed
            $nf=feed_system::$inst->get_feed($newsfeedid);
            // Get folder path
        }
    }
    $courseid = $this->instance->pageid;
}

// Get form and check action (or not)
$mform = new newsfeed_editfeed_form($nf);
if ($mform->is_cancelled()){
    // Form cancelled - Just set form cancelled flag
    $formcancelled = true;
} else if ($fromform=$mform->get_data()){
    try {
        $tw=new transaction_wrapper();

        if($fromform->type=='internal') {
            $external=false;
        } else if($fromform->type=='external') {
            $external=true;
        } else {
            error("Unrecognised feed type: $type");
        }

        if($newsfeedid==0) {
            // Create new
            $nf=($external ? new external_news_feed() : new internal_news_feed());
        } else {
            if($external!=is_a($nf,'external_news_feed')) {
                error("Cannot change type of existing news feed");
            }
        }

        $nf->set_name(stripslashes($fromform->nfname));
        $nf->set_summary(stripslashes($fromform->summary));
        $nf->set_blockinstance($this->instance->id);

        if(!$external) {
            $nf->set_public($fromform->publicfeed ? true : false);

            if($newsfeedid==0) {
                $nf->create_new();
            }

            $authids=array();
            if(class_exists('ouflags')) {
                foreach($fromform->optionalauthid as $authid) {
                    if($authid!=='') {
                        $authids[]=$authid;
                    }
                }
    
                $nf->set_authids($fromform->defaultauthid,$authids);
            } else {
                $nf->set_authids(null,array());
            }
        } else {
            $nf->set_public(false);

            $nf->set_url($fromform->url);
            $nf->set_check_freq($fromform->checkfreq);

            if($newsfeedid==0) {
                $nf->create_new();
            }
        }

        $nf->save_changes();

        if($external) {
            $nf->check(true);
        }

        $tw->commit();
    } catch(Exception $e) {
        error_exception($e,'Failed to edit news feed: '.$e->getMessage());
    }

    if ($data->newsfeedid == 0){
        $data->newsfeedid = $nf->get_id();
    }
} else {
// this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
// or on the first display of the form.

    //put data you want to fill out in the form into array $toform here
    $toform=new StdClass();
    if($nf) {
        $toform->newsfeedid=$newsfeedid;
        $toform->courseid=$courseid;
        $toform->nfname=$nf->get_name();
        $toform->summary=$nf->get_summary();
        if(is_a($nf,'external_news_feed')) {
            $toform->type='external';
            $toform->url=$nf->get_url();
            $toform->checkfreq=$nf->get_check_freq();
        } else {
            $toform->type='internal';
            $toform->publicfeed=$nf->is_public() ? 1 : 0;
            $toform->defaultauthid=$nf->get_default_authid();
            $toform->optionalauthid=$nf->get_optional_authids();
        }
/*
        echo "<ul class='course-admin-menu'>".
            "<li><a href='../admin/'>Global admin</a> </li>".
            "</ul>";
*/
        print "<div class='link_newsfeed'>".get_string('backtonewsfeed', 'block_newsfeed')."<a href='{$CFG->wwwroot}/blocks/newsfeed/ui/viewfeed.php?newsfeedid=$newsfeedid&amp;courseid=$courseid&amp;returntofeed=$returntofeed'>{$nf->get_name()}</a></div>";
    } else {
        $buttons=&$mform->_form->getElement('buttonar')->getElements();
        $buttons[0]->setValue(get_string('create'));
        $toform->newsfeedid=0;
    }
    if($returntofeed === 'y') {
        $toform->returntofeed='y';
    }

// already set up somehow            $toform->sesskey = $USER->sesskey;
    $toform->instanceid = $this->instance->id;
    $toform->blockaction = 'config';
    $toform->id = $this->instance->pageid;
    if (isset($this->config->showcount)) {
        $toform->showcount = $this->config->showcount;
    } else {
        $toform->showcount = 3;
    }
    if (isset($this->config->showsummaries)) {
        $toform->showsummaries = $this->config->showsummaries;
    }

    print_editinclude_tabs('edit',$newsfeedid,$courseid);

    $mform->set_data($toform);
    $mform->display();
}
?>