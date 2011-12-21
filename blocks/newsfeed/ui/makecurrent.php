<?php
/**
 * Makes a specified entry current in message history.
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
$newsfeedid=required_param('newsfeedid',PARAM_INT);
$nf=feed_system::$inst->get_feed($newsfeedid);
if(!is_a($nf,'internal_news_feed')) {
    error("Cannot post to external feeds");
}
$nfcontext = get_context_instance(CONTEXT_BLOCK, $nf->get_blockinstance());
if(!has_capability('block/newsfeed:post', $nfcontext)) {
    error("You don't have access to post to this feed");
}
do_task_access(false);

// Get other parameters
$viewnewsfeedid=required_param('viewnewsfeedid',PARAM_INT);
$entryid=required_param('entryid',PARAM_INT);
$courseid=optional_param('courseid',0,PARAM_INT);
$versionid=required_param('versionid',PARAM_INT);

try {
    // Obtain object representing version
    $v=$nf->get_entry_by_version($versionid);
    $v->set_poster();

    // Save it as new
    $v->save_new();
} catch(Exception $e) {
    error_exception($e,'Failed to make news message current: '.$e->getMessage());
}

redirect('entryhistory.php?viewnewsfeedid='.$viewnewsfeedid.
    '&newsfeedid='.$newsfeedid.'&entryid='.$entryid.
    ($courseid ? '&courseid='.$courseid : ''));
?>