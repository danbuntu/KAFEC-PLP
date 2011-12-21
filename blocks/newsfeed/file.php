<?php
/**
 * Form for providing newsfeed files. Note that this requires login but 
 * otherwise does not make any effort at security.
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 *//** */
require_once('../../config.php');
require_once('../../lib/filelib.php');
require_once('system/feed_system.php');

global $CFG,$USER;

$newsfeedid=required_param('newsfeedid',PARAM_INT);
$versionid=required_param('versionid',PARAM_INT);
$filename=required_param('filename',PARAM_RAW);

require_login();

// Get newsfeed details
try {
    $nf=feed_system::$inst->get_feed($newsfeedid);
    $v=$nf->get_entry_by_version($versionid);
    foreach($v->get_attachments() as $attachment) {
        if($filename===$attachment->get_filename()) {
            send_file($attachment->get_path(),$filename,86400,0,false,false,$attachment->get_mime_type());
            exit;
        }
    }
    error("Couldn't find requested file");     
} catch(Exception $e) {
    error_exception($e,'Error obtaining newsfeed entry details');  
}

?>