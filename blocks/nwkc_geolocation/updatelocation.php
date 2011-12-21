<?php

require_once('../../config.php');
require_once($CFG->dirroot.'/user/profile/lib.php');  

global $USER, $CFG;

$lat      = optional_param('lat', 0, PARAM_RAW);
$lon      = optional_param('lon', 0, PARAM_RAW);
$location = optional_param('location', 0, PARAM_INT);

$sql = "SELECT 1 FROM ".$CFG->prefix."nwkc_geolocation WHERE studentid = ".$USER->id."";

if (count_records_sql($sql)) {
	
	$update_sql = "UPDATE ".$CFG->prefix."nwkc_geolocation
					SET timecheckedin = UNIX_TIMESTAMP(),
						lat = '".$lat."',
						lon = '".$lon."',
						location = '".$location."'
					WHERE studentid = ".$USER->id."";
	execute_sql($update_sql);
	
} else {
	
	$insert_sql = "INSERT ".$CFG->prefix."nwkc_geolocation (studentid,timecheckedin,lat,lon,location)
					VALUES (".$USER->id.",UNIX_TIMESTAMP(),'".$lat."','".$lon."','".$location."')";
	execute_sql($insert_sql);	
	
}

?>