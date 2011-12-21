<?php
/**
 * Form for displaying a newsfeed to students (all that is visible) or to staff in editing mode.
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 *//** */
require_once('../../../config.php');
require_once('../system/feed_system.php');
require_once('sharedui.php');

global $CFG,$USER;

$newsfeedid=required_param('newsfeedid',PARAM_INT);
$viewnewsfeedid=required_param('viewnewsfeedid',PARAM_INT);
$entryid=required_param('entryid',PARAM_INT);
$courseid=optional_param('courseid',0,PARAM_INT);

require_login();
$sesskey=sesskey();

// Get newsfeed details and check access
$nf=get_newsfeed_or_error($newsfeedid);
require_newsfeed_access($nf);
$nfname = $newsfeedid == $viewnewsfeedid ? htmlspecialchars($nf->get_name()) : htmlspecialchars($nf->get_full_name());

// Get entries
try {
    $entries=$nf->get_entry_history($entryid);

    $endcrumb=get_string('messagehistory',LANGF,$entries[0]->get_title());
    if($courseid) {
        // Displayed within a course context
        require_login($courseid);
        $course=get_record('course','id',$courseid);
        $strfeeds=get_string('feeds',LANGF);
        $nav = array();
        $nav[] = array('name'=>$nfname, 'link'=>'viewfeed.php?newsfeedid='.$nf->get_id().'&courseid='.$courseid, 'type'=>'newsfeed');
        $nav[] = array('name'=>$endcrumb, 'type'=>'newsfeed');
        print_header_simple($nfname, "",
             build_navigation($nav), "", "", true);
    } else {
        // Displayed within the admin/newsfeed context
        do_admin_access_and_header($nfname,'viewfeed.php?newsfeedid='.$newsfeedid,$endcrumb,'',$nf);
    }

    print "<div id='blocks-newsfeed-ui-viewfeed'>";


    $nfcontext = get_context_instance(CONTEXT_BLOCK, $nf->get_blockinstance());
    $canapprove = has_capability('block/newsfeed:approve', $nfcontext);
    $canpost = has_capability('block/newsfeed:post', $nfcontext);

    // Editing version
    $strapprove=get_string('approve',LANGF);
    $strmakecurrent=get_string('makecurrent',LANGF);
    $strcurrentdraft=get_string('currentdraft',LANGF);
    $strstudentvisible=get_string('studentvisible',LANGF);

    $first=true;
    $gotapproved=false;
    foreach($entries as $entry) {
        $versionid=$entry->get_id();

        $stdparams="
<input type='hidden' name='viewnewsfeedid' value='$viewnewsfeedid' />
<input type='hidden' name='newsfeedid' value='$newsfeedid' />
<input type='hidden' name='versionid' value='$versionid' />
<input type='hidden' name='entryid' value='$entryid' />
<input type='hidden' name='sesskey' value='$sesskey' />
            ".($courseid ? "<input type='hidden' name='courseid' value='$courseid' />": '');

        $button='';
        if($canapprove && $first && !$entry->is_approved()) {
            $button="
<form action='approveentry.php' method='post'>
$stdparams
<input type='submit' value='$strapprove' />
</form>
            ";
        }

        if($canpost && !$first) {
            $button="
<form action='makecurrent.php' method='post'>
$stdparams
<input type='submit' value='$strmakecurrent' />
</form>
            ";
        }

        $entrydate=display_entry_date($entry->get_date());
        $entrysubject=htmlspecialchars($entry->get_title());
        $entrymessage=$entry->get_html_with_attachments();

        $details=array();
        if($entry->get_poster_username()) {
            $thisdetails=new stdClass;
            $thisdetails->langstring='entryupdated';
            $thisdetails->username=$entry->get_poster_username();
            $thisdetails->realname=$entry->get_poster_realname();
            $thisdetails->time=$entry->get_time_posted();
            $details[]=$thisdetails;
        }
        if($entry->get_approver_username()) {
            $thisdetails=new stdClass;
            $thisdetails->langstring='entryapproved';
            $thisdetails->username=$entry->get_approver_username();
            $thisdetails->realname=$entry->get_approver_realname();
            $thisdetails->time=$entry->get_time_approved();
            $details[]=$thisdetails;
        }
        $entrychangedetails='';
        foreach($details as $thisdetails) {
            $thisdetails->timedisplay=display_entry_date($thisdetails->time,true);
            $textversion=get_string($thisdetails->langstring,LANGF,$thisdetails);
            $entrychangedetails.="<div class='nf_entrychange'>$textversion</div>";
        }

        $mainbit=$entry->is_deleted() ? "
<div class='nf_subject nf_deleted'><span>$strdeleted:</span> $entrysubject</div>
        " : "
<div class='nf_date'>$entrydate</div>
<div class='nf_subject'>$entrysubject</div>
<div class='nf_message'>$entrymessage</div>
";

        if($authid=$entry->get_authid()) {
            $authidinfo='<div class="nf_authid">'.get_string('authidrestricted',LANGF,$authid).'</div>';
        } else {
            $authidinfo='';
        }

        if(!$gotapproved && $entry->is_approved()) {
            $gotapproved=true;
            $info="<div class='nf_info'>$strstudentvisible</div>";
        } else if($first) {
            $info="<div class='nf_info'>$strcurrentdraft</div>";
        } else {
            $info='';
        }

        $first=false;

        print "
<div class='nf_entry'>
 <div class='nf_content'>
  $info
  <div class='nf_entryblock'>
   <div class='nf_visiblepart'>
    $mainbit
   </div>
   <div class='nf_admin'>
     $authidinfo
     $entrychangedetails
     <div class='nf_buttons'>
      $button
     </div>
    <div style='clear:both'></div>
   </div>
  </div>
 </div>
</div>
            ";
    }

} catch(Exception $e) {
    error_exception($e,"Error displaying message history for entry $entryid");
}

print "</div>";

// Footer
print_footer();
?>