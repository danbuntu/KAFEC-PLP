<?PHP

//moodle stuff
//edit the path to the moodle config.php file below
//then comment or uncomment the following line to disable or enable moodle authentication integration
$_SESSION['integrate_with_moodle'] = true;

if($_SESSION['integrate_with_moodle']==true){

//The require path below is the path to the moodle installation config file 
//usually found in the root of the moodle installation
//this needs to be the path from root rather than something like ../../moodle/config.php
//e.g. this might be something like require("/home/molenetprojects/public_html/config.php");
//for a xampp/maxos install this should be something like require("/xampp/htdocs/moodle/config.php");
require("s:/htdocs/moodle/config.php");

//This ensures anyone visiting thie toolkits installation direct is redirected to the moodle login first
//if not already logged in to moodle
require_login();

$_SESSION['toolkits_firstname'] = $USER->firstname;//moodle session firstname
			
$_SESSION['toolkits_surname'] = $USER->lastname;//moodle session lastname

$_SESSION['toolkits_logon_username'] = $USER->username;//moodle session username
}

?>