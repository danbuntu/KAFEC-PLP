<?php
/**
 * Form for editing or creating a newsfeed message.
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 *//** */
require_once('../../../config.php');
require_once('../system/feed_system.php');
require_once('sharedui.php');

require_once('newsfeed_editentry_form.php');

global $CFG;

// Login, get feed and check access (applies to both form and data setting)
require_login();

try {
    $newsfeedid=required_param('newsfeedid',PARAM_INT);
    $viewnewsfeedid=optional_param('viewnewsfeedid',$newsfeedid,PARAM_INT);
    $courseid=optional_param('courseid',0,PARAM_INT);
    $viewurl='viewfeed.php?newsfeedid='.$viewnewsfeedid.($courseid?'&courseid='.$courseid:'');

    $nf=feed_system::$inst->get_feed($newsfeedid);
    if(!is_a($nf,'internal_news_feed')) {
        error("Cannot post to external feeds");
    }
    $nfcontext = get_context_instance(CONTEXT_BLOCK, $nf->get_blockinstance());
    if(!has_capability('block/newsfeed:post', $nfcontext)) {
        error("You don't have access to post to this feed");
    }
    $action=optional_param('action','',PARAM_RAW);
    $versionid=optional_param('versionid',0,PARAM_INT);
    // Obtain object representing version
    if($versionid==0) {
        // Create new
        $v=null;
    } else {
        // Load existing
        $v=$nf->get_entry_by_version($versionid);
    }

    // 'Create copy' works by posting to this script but is not handled
    // via form.
    if($_SERVER['REQUEST_METHOD']=='POST' && $action=='createcopy') {
        if(!$v) {
            error('Version ID required');
        }
        do_task_access(false);
        $tw=new transaction_wrapper();
        $v->save_new(true,$viewnewsfeedid);
        $tw->commit();

        redirect($viewurl);
        exit;
    }

    $mform = new newsfeed_editentry_form($nf,$v);
    if ($mform->is_cancelled()){
        redirect($viewurl);
        exit;
    } else if ($fromform=$mform->get_data()){
        do_task_access(false);
        $tw=new transaction_wrapper();

        if(!$v) {
            $v=new news_entry_version();
        }

        // Create authid list
        $authid='';
        foreach((array)$fromform as $key=>$value) {
            $prefix='authid'.$fromform->newsfeedid.'_';
            if(substr($key,0,strlen($prefix))==$prefix) {
                if(strlen($authid)!=0) {
                    $authid.=' ';
                }
                $authid.=substr($key,strlen($prefix));
            }
        }

        // Set basic fields of object
        $v->set_authid($authid ? $authid : null);
        $v->set_title(stripslashes($fromform->title));
        $v->set_html(stripslashes($fromform->text));
        $v->set_poster();
        $v->set_date($fromform->appearancedate);
        $v->set_roll_forward($fromform->rollforward==1 ? true : false);

        // Handle new attachments...
        foreach($_FILES as $file) {
            if($file['tmp_name']) {
                $a=news_attachment::create($file['tmp_name'],$file['name']);
                $v->add_attachment($a);
            }
        }
        // ...and deleted ones
        foreach((array)$fromform as $name=>$value) {
            $matches=array();
            if(preg_match('/^deleteattachment([0-9]+)$/',$name,$matches)) {
                $filename=stripslashes($fromform->{'attachment'.$matches[1]});
                // Find the existing attachments...
                $found=false;
                foreach($v->get_attachments() as $attachment) {
                    if($attachment->get_filename() == $filename) {
                        $v->remove_attachment($attachment);
                        $found=true;
                        break;
                    }
                }
                if(!$found) {
                    error("Unable to find attachment $value");
                }
            }
        }

        // Save changes
        if($versionid==0) {
            $v->save_new(true,$newsfeedid);
        } else {
            $v->save_new();
        }
        $tw->commit();
        redirect($viewurl);
        exit;
} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.

        // Get newsfeed name
        $nfname = $newsfeedid == $viewnewsfeedid ? htmlspecialchars($nf->get_name()) : htmlspecialchars($nf->get_full_name());

        //put data you want to fill out in the form into array $toform here
        $toform=new StdClass();

        $toform->newsfeedid=$newsfeedid;
        $toform->viewnewsfeedid=optional_param('viewnewsfeedid',$newsfeedid,PARAM_INT);
        if($v) {
            $toform->versionid=$v->get_id();
        }
        $toform->courseid=$courseid;

        if($v) {
            $toform->title=$v->get_title();
            $toform->text=$v->get_html();
            if($v->get_authid()) {
                $authids=explode(' ',$v->get_authid());
            } else {
                $authids=array();
            }
            for($i=0;$i<count($authids);$i++) {
                $toform->{'authid'.$nf->get_id().'_'.$authids[$i]}=1;
            }
            if($default=$nf->get_default_authid()) {
                $toform->{'authid'.$nf->get_id().'_'.$default}=0; // Override any default
            }
            $toform->appearancedate=$v->get_date();
            $toform->rollforward=$v->should_roll_forward();

            do_admin_access_and_header($nfname,'viewfeed.php?newsfeedid='.$newsfeedid,get_string('editmessage',LANGF),'',$nf);
            print_heading(get_string('editmessage',LANGF));
        } else {
            $buttons=&$mform->_form->getElement('buttonar')->getElements();
            $buttons[0]->setValue(get_string('create'));
            do_admin_access_and_header($nfname,'viewfeed.php?newsfeedid='.$newsfeedid,get_string('addnewmessage',LANGF),'',$nf);
            print_heading(get_string('addnewmessage',LANGF));
        }

        $mform->set_data($toform);
        $mform->display();

        print_footer();
    }
} catch(Exception $e) {
    error_exception($e,'Failed to edit news message: '.$e->getMessage());
}
?>