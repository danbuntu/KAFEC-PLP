<?php 

///////////////////////////////////////////////////////////////////////////
//                                                                       //
//    The oncampus state is checked and the user directed to one of 3    //
//    possible locations depending on options                            //
//    The page accepts 2 parameters.                                     //
//    1) A URL for oncampus												 //
//    2) A URL for offcampus											 //
//	  3) If the parameter is blank then it redirects to notavailable.php //
//	  which tells them they can't access it on/off campus				 //
//    Version 2.0                                                        //
//    Red Morris 18 April 2008                                           //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

require_once("../config.php");

if (address_in_subnet($_SERVER['REMOTE_ADDR'], '10.1.0.0/14')) { //check this IP address.
	$location = 1;
} else {
	$location = 2;
}
$oncampus = '';
$offcampus = '';

if (isset($_GET["oncampus"])) { $oncampus = $_GET["oncampus"]; }
if (isset($_GET["offcampus"])) { $offcampus = $_GET["offcampus"]; }
    
if ($location==1) {
	if ($oncampus != '') {
		redirect($oncampus);
	} else {
		redirect($CFG->wwwroot."/oncampuscheck/notavailable.php?site=on");
	}
} elseif ($location==2) {
	if ($offcampus != '') {
		redirect($offcampus);
	} else {
		redirect($CFG->wwwroot."/oncampuscheck/notavailable.php?site=off");
	}
}