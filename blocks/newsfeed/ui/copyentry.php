<?php
/**
 * Stores a newsfeed entry ID in the session
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 *//** */
require_once('../../../config.php');
require_once('sharedui.php');

// Login, get feed and check access (applies to both form and data setting)
require_login();
check_post_and_sesskey();

$newsfeedid=required_param('newsfeedid',PARAM_INT);
$viewnewsfeedid=required_param('viewnewsfeedid',PARAM_INT);
$versionid=required_param('versionid',PARAM_INT);        

global $USER;
$USER->newsfeedclipboardnewsfeedid=$newsfeedid;
$USER->newsfeedclipboardversionid=$versionid;

$courseid=optional_param('courseid',0,PARAM_INT);
redirect('viewfeed.php?newsfeedid='.$viewnewsfeedid.($courseid?'&courseid='.$courseid:''));
?>