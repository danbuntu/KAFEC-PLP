<?php
// Script for admin users only which wipes the newsfeed cache. Generally only
// required in the event of system problems of some kind.
require_once('../../../config.php');
require_once('../system/feed_system.php');
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

$count = 0;
$folder = $CFG->dataroot . feed_system::FEED_CACHE_FOLDER;
if ($handle = opendir($folder)) {
    while (($file = readdir($handle)) !== false) {
        if (preg_match('/\.atom$/', $file)) {
            unlink($folder . '/' . $file);
            $count++;
        }
    }
    closedir($handle);
}

header('Content-Type: text/plain');
print "Newsfeed cache cleared ($count files deleted)";
?>