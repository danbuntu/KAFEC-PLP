<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: preview.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: This page allows the tutor to preview the questions as would be seen by the participants
////////////////////////////////////////////////////////////////////////////
//BASIC SECURITY $sPageCaller VARIABLE MUST BE PRESENT TO LOAD FORM FILES
$sPageCaller = "preview.php";
////////////////////////////////////////////////////////////////////////////

    require_once("../../config.php");
    require_once("lib.php");

    $id = optional_param('id', 0, PARAM_INT); // Course Module ID

    if (! $cm = get_record("course_modules", "id", $id)) {
        print_error('invalidrequest');
    }

    if (! $course = get_record("course", "id", $cm->course)) {
        print_error('invalidrequest');
    }
	
    if (! $easyvoter = get_record("easyvoter", "id", $cm->instance)) {
        print_error('invalidrequest');
    }
	
    if (! $slides = get_records('easyvoter_slides', 'instance', $easyvoter->id, 'numeral ASC','id,type,control')) {
        print_error('invalidrequest');
    }
	
	require_login($course->id);
	
	if(count($slides)<1){
		redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);
	}	
	
	$sArrayString = "[0,'intro','']";
	foreach($slides as $slide){
			$sArrayString .= ',[\''.$slide->id.'\',\''.$slide->type.'\',\''.$slide->control.'\']';
	}

	//CHECK USER ROLE ACCESS
	if(easyvoter_isPresenter($cm->id)){
		require_once("present/present_slides.php");
		add_to_log($course->id, "easyvoter", "preview", "view.php?id=$cm->id", "$easyvoter->name");
	}else{
		redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);
	}
?>