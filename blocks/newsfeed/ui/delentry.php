<?php
/**
 * Deletes the given newsfeed messge
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 *//** */
require_once('../../../config.php');
require_once('../system/feed_system.php');
require_once('sharedui.php');

global $CFG;

// Login, get feed and check access (applies to both form and data setting)
require_login();
check_post_and_sesskey();

$newsfeedid=required_param('newsfeedid',PARAM_INT);
$viewnewsfeedid=required_param('viewnewsfeedid',PARAM_INT);
$versionid=required_param('versionid',PARAM_INT);
$delete=required_param('delete',PARAM_INT);
try {
    $nf=feed_system::$inst->get_feed($newsfeedid);
    if(!is_a($nf,'internal_news_feed')) {
        error("Cannot alter to external feeds");
    }
    $nfcontext = get_context_instance(CONTEXT_BLOCK, $nf->get_blockinstance());
    if(!has_capability('block/newsfeed:post', $nfcontext)) {
        error("You don't have access to delete messages from this feed");
    }
    $v=$nf->get_entry_by_version($versionid);
    $v->set_deleted($delete ? true : false);
    $v->save_new();
    $courseid=optional_param('courseid',0,PARAM_INT);
    redirect('viewfeed.php?newsfeedid='.$viewnewsfeedid.($courseid?'&courseid='.$courseid:''));
} catch(Exception $e) {
    error_exception($e,'Failed to delete news message: '.$e->getMessage());
}
?>