<?php
/**
 * Approves a feed entry
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 *//** */
require_once('../../../config.php');
require_once('../system/feed_system.php');
require_once('sharedui.php');

do_task_access(false);
$newsfeedid=required_param('newsfeedid',PARAM_INT);
$viewnewsfeedid=required_param('viewnewsfeedid',PARAM_INT);
$versionid=required_param('versionid',PARAM_INT);
$courseid=optional_param('courseid',0,PARAM_INT);
$viewurl='viewfeed.php?newsfeedid='.$viewnewsfeedid.($courseid?'&courseid='.$courseid:'');
try {
    $nf=feed_system::$inst->get_feed($newsfeedid);
    $nfcontext = get_context_instance(CONTEXT_BLOCK, $nf->get_blockinstance());
    if(!has_capability('block/newsfeed:approve', $nfcontext)) {
        error("You don't have permission to approve items on this feed");
    }
    $v=$nf->get_entry_by_version($versionid);
    $v->approve();
} catch(Exception $e) {
    error_exception($e,$e->getMessage(),$viewurl);
}

redirect($viewurl);
?>