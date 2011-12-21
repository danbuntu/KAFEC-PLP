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
$courseid=optional_param('courseid',0,PARAM_INT);

require_login();
$sesskey=sesskey();

// Get newsfeed details
$nf=get_newsfeed_or_error($newsfeedid);
$nfname = htmlspecialchars($nf->get_name());

// Check permissions.
$internal=is_a($nf,'internal_news_feed'); // Can't edit external feeds
$nfcontext = get_context_instance(CONTEXT_BLOCK, $nf->get_blockinstance());
$allowupdate = has_capability('block/newsfeed:manage', $nfcontext);
$allowpost=$internal && has_capability('block/newsfeed:post', $nfcontext);
$allowapprove=$internal && has_capability('block/newsfeed:approve', $nfcontext);

if(!empty($USER->studentview)) {
    $allowpost=false;
    $allowapprove=false;
    $allowupdate=false;
}

$allowediting=$allowpost || $allowapprove; // Whether the button appears



// Check editing flag
if(!isset($USER->editingnewsfeeds)) {
    $USER->editingnewsfeeds=array();
}
$editing=!empty($USER->editingnewsfeeds[$newsfeedid]);
if($editing && !$allowediting) {
    unset($USER->editingnewsfeeds[$newsfeedid]);
    error('You do not have permission to edit this page.');
}

$strediting = !$allowediting ? '' : $editing ? get_string('turneditingoff') :  get_string('turneditingon');
$strupdate = get_string('editfeedsettings', LANGF);

if ($allowupdate) {
    $nfbi = get_field('newsfeed', 'blockinstance', 'id', $newsfeedid);
}

$buttons=(!$allowediting ? '' : "
<form method='post' action='toggleediting.php'>
<input type='hidden' name='sesskey' value='$sesskey' />
<input type='hidden' name='newsfeedid' value='$newsfeedid' />
<input type='hidden' name='courseid' value='$courseid' />
<input type='submit' value='$strediting' />
</form>").(!$allowupdate ? '' : "
<form method='get' action='{$CFG->wwwroot}/course/view.php'>
<input type='hidden' name='id' value='$courseid' />
<input type='hidden' name='sesskey' value='$sesskey' />
<input type='hidden' name='instanceid' value='$nfbi' />
<input type='hidden' name='blockaction' value='config' />
<input type='hidden' name='newsfeedid' value='$newsfeedid' />
<input type='hidden' name='courseid' value='$courseid' />
<input type='hidden' name='returntofeed' value='y' />
<input type='submit' value='$strupdate' />
</form>
        ");

if($nf->is_public()) {
    $auth='';
    if(is_sams_auth() && is_a($nf,'internal_news_feed')) {
        if(($check=$nf->get_default_authid()) && SamsAuth::$inst->match_authids($check)) {
            $auth='&auth='.$check; // Note the & is escaped later on
        }
        foreach($nf->get_optional_authids() as $check) {
            if(SamsAuth::$inst->match_authids($check)) {
                if($auth=='') {
                    $auth='&auth=';
                } else {
                    $auth.=',';
                }
                $auth.=$check;
            }
        }
    }
    $url=htmlspecialchars($CFG->wwwroot.'/blocks/newsfeed/publicfeed.php?feed='.$nf->get_id().$auth);
    $meta='<link rel="alternate" type="application/atom+xml" title="Atom feed" '.
        'href="'.$url.'" />';
    $pixpath=$CFG->pixpath;

    $feedicon='&nbsp;<a class="headerfeedicon" href="'.$url.'" title="Atom feed"><img src="feed.png" alt=""/></a>';
} else {
    $meta='';
    $feedicon='';
}

if($courseid) {
    // Displayed within a course context
    require_login($courseid);
    $course=get_record('course','id',$courseid);
    $strfeeds=get_string('feeds',LANGF);
    $nav=array();
    $nav[]=array('name'=>$nfname,'type'=>'newsfeed');
    print_header_simple($nfname, "",
         build_navigation($nav), "", $meta, true,
         $feedicon.$buttons);
} else {
    // Displayed within the admin/newsfeed context
    do_admin_access_and_header($nf->get_courseshortname().' '.$nfname,'','',$buttons,$nf,$meta,$feedicon);
}

if($editing) {
    $entries=$nf->get_entries(false,$USER->id,true);

    // Editing version
    $pixpath=$CFG->pixpath;
    $strapprove=get_string('approve',LANGF);
    $strhistory=get_string('history',LANGF);
    $strpost=get_string('post',LANGF);
    $strpaste=get_string('paste',LANGF);
    $stredit=get_string('edit');
    $strcopy=get_string('copy');
    $strdelete=get_string('delete');
    $strundelete=get_string('undelete',LANGF);
    $strdeleted=get_string('deleted');

    print "<div class='nf_topbuttons'>";
    if($allowpost) {
        print "
<form action='editentry.php' method='get'>
<input type='hidden' name='newsfeedid' value='$newsfeedid' />
<input type='hidden' name='courseid' value='$courseid' />
<input type='hidden' name='action' value='createnew' />
<input type='submit' value='$strpost' />
</form>
            ";
        print "
<form action='editentry.php' method='post'>
<input type='hidden' name='viewnewsfeedid' value='$newsfeedid' />
<input type='hidden' name='courseid' value='$courseid' />
<input type='hidden' name='action' value='createcopy' />
";
        if($allowpost && !empty($USER->newsfeedclipboardversionid)) {
            $copynewsfeedid=$USER->newsfeedclipboardnewsfeedid;
            $copyversionid=$USER->newsfeedclipboardversionid;
            print "
<input type='hidden' name='newsfeedid' value='$copynewsfeedid' />
<input type='hidden' name='versionid' value='$copyversionid' />
<input type='hidden' name='sesskey' value='$sesskey' />
<input type='submit' value='$strpaste' />
                ";
        } else {
            print "
<input type='submit' value='$strpaste' disabled='disabled' />
                ";
        }

        print "</form>";
    }
    print "</div>";

    $now=time();
    $unapprovedcount=0;
    $futurecount=0;
    $youcannotapprovecount=0;
    foreach($entries as $entry) {
        if(!$entry->is_approved()) {
            $unapprovedcount++;
            if(!($allowupdate || $entry->feed_can_approve())) {
                $youcannotapprovecount++;
            }
        }
        if($entry->get_date() > $now) {
            $futurecount++;
        }
    }

    print '<div class="nf_toptext">';
    if($unapprovedcount) {
        $a=new stdClass;
        $a->count=$unapprovedcount;
        $a->plural=$unapprovedcount>1 ? 's' : '';
        $a->pluralhas=$unapprovedcount>1 ? 'have' : 'has';
        $stryoucannotapprove='';
        if($allowapprove && $youcannotapprovecount) {
            $stryoucannotapprove=' '.get_string('youcannotapprove',LANGF,
              $youcannotapprovecount==$unapprovedcount
                ? get_string('youcannotapprove_any',LANGF)
                : get_string('youcannotapprove_some',LANGF));
        }

        print '<p><strong>'.get_string('approvecount',LANGF,$a).'</strong> '.get_string('approveexplanation',LANGF).
            $stryoucannotapprove.'</p>';
    }
    if($futurecount) {
        print '<p>'.get_string('futureexplanation',LANGF).'</p>';
    }
    print '</div>';

    foreach($entries as $entry) {
        $thisnewsfeedid=$entry->get_newsfeed_id();
        $versionid=$entry->get_id();
        $entryid=$entry->get_entry_id();
        $canpostthis=!$entry->is_deleted() && ($allowupdate || $entry->feed_can_post());
        $canapprovethis=!$entry->is_approved() && ($allowupdate || $entry->feed_can_approve());
        $canundeletethis=$entry->is_deleted() && ($allowupdate || $entry->feed_can_post());
        $stdparams="
<input type='hidden' name='viewnewsfeedid' value='$newsfeedid' />
<input type='hidden' name='newsfeedid' value='$thisnewsfeedid' />
<input type='hidden' name='versionid' value='$versionid' />
<input type='hidden' name='entryid' value='$entryid' />
".($courseid ? "<input type='hidden' name='courseid' value='$courseid' />": '');
        $editbutton=!$canpostthis  ? '' : "
<form action='editentry.php' method='get'>
$stdparams
<input type='hidden' name='action' value='editexisting' />
<input type='image' src='$pixpath/t/edit.gif' alt='$stredit' title='$stredit' />
</form>
            ";
        $delbutton=!$canpostthis ? '' : "
<form action='delentry.php' method='post'>
$stdparams
<input type='hidden' name='delete' value='1' />
<input type='hidden' name='sesskey' value='$sesskey' />
<input type='image' src='$pixpath/t/delete.gif' alt='$strdelete' title='$strdelete' />
</form>
            ";
        $copybutton=!$canpostthis ? '' : "
<form action='copyentry.php' method='post'>
$stdparams
<input type='hidden' name='sesskey' value='$sesskey' />
<input type='image' src='$pixpath/t/copy.gif' alt='$strcopy' title='$strcopy'/>
</form>
            ";
        $undelbutton=!$canundeletethis ? '' : "
<form action='delentry.php' method='post'>
$stdparams
<input type='hidden' name='delete' value='0' />
<input type='hidden' name='sesskey' value='$sesskey' />
<input type='image' src='undelete.gif' alt='$strundelete' title='$strundelete' />
</form>
            ";

        $thisfeedname=htmlspecialchars($entry->get_newsfeed_name_pres());
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

        $approvebutton=!$canapprovethis ? '' : "
<form action='approveentry.php' method='post'>
$stdparams
<input type='hidden' name='sesskey' value='$sesskey' />
<input type='submit' value='$strapprove' />
</form>
            ";
        $historybutton="
<form action='entryhistory.php' method='get'>
$stdparams
<input type='submit' value='$strhistory' />
</form>
            ";

        $mainbit=$entry->is_deleted() ? "
<div class='nf_subject nf_deleted'><span>$strdeleted:</span> $entrysubject</div>
        " : "
<div class='nf_date'>$entrydate</div>
<div class='nf_subject'>$entrysubject</div>
<div class='nf_message'>$entrymessage</div>
";

        $classes='';
        if($entry->is_deleted()) {
            $classes.=' nf_deleted';
        }
        if(!$entry->is_approved()) {
            $classes.=' nf_unapproved';
        }
        if($entry->get_date() > $now) {
            $classes.=' nf_future';
        }

        if($authid=$entry->get_authid()) {
            $authidinfo='<div class="nf_authid">'.get_string('authidrestricted',LANGF,$authid).'</div>';
        } else {
            $authidinfo='';
        }

        print "
<div class='nf_entry$classes' id='e$entryid'>
 <div class='nf_buttons'>
  $editbutton
  $delbutton
  $copybutton
  $undelbutton
 </div>
 <div class='nf_content'>
  <div class='nf_feed'>$thisfeedname</div>
  <div class='nf_entryblock'>
   <div class='nf_visiblepart'>
    $mainbit
   </div>
   <div class='nf_admin'>
     $authidinfo
     $entrychangedetails
     <div class='nf_buttons'>
      $approvebutton
      $historybutton
     </div>
    <div style='clear:both'></div>
   </div>
  </div>
 </div>
</div>
            ";
    }
} else {
    // Nonediting version uses Atom feed, not database
    $doc=DOMDocument::loadXML(feed_system::$inst->get_feed_data($newsfeedid,false));
    $nl=$doc->getElementsByTagName('entry');
    for($i=0;$i<$nl->length;$i++) {
        print_atom_entry($nl->item($i));
    }

    if(!$internal && ($error=$nf->get_error()) && $allowupdate) {
        $msg=get_string('externalerror',LANGF,display_entry_date($nf->get_last_check(),true));
        print "<div class='nf_externalerror'>$msg: <span>$error</span></div>";
    }
}

// Footer
print_footer();
?>