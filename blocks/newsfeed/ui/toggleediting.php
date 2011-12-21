<?php
/**
 * Toggles the editing flag for a newsfeed, then redirects back to view.
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 *//** */
require_once('../../../config.php');
require_once('sharedui.php');
require_once('../system/feed_system.php');

require_login();
require_sesskey();
$newsfeedid=required_param('newsfeedid',PARAM_INT);
$courseid=optional_param('courseid',0,PARAM_INT);

// Toggle feed
if(!isset($USER->editingnewsfeeds)) {
    $USER->editingnewsfeeds=array();
}
if(array_key_exists($newsfeedid,$USER->editingnewsfeeds)) {
    unset($USER->editingnewsfeeds[$newsfeedid]);
} else {
    require_newsfeed_access(get_newsfeed_or_error($newsfeedid));
    $USER->editingnewsfeeds[$newsfeedid]=true;
}

// Redirect
redirect('viewfeed.php?newsfeedid='.$newsfeedid.($courseid?'&courseid='.$courseid:''));
?>