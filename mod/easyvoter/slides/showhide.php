<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: slides/showhide.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: This page sets the show or hide value and adds an update entry to the log
////////////////////////////////////////////////////////////////////////////

    require_once("../../../config.php");
    require_once("../lib.php");

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
		
    require_login($course->id);

//REDIRECT IF CURRENTLY BEING PRESENTED
	if(easyvoter_isActive($easyvoter->id)){
		redirect($CFG->wwwroot.'/mod/easyvoter/view.php?id='.$cm->id);
	}
	
	//CHECK USER ROLE ACCESS
	if(easyvoter_isPresenter($cm->id)){
		if($cm->visible>0){
			add_to_log($course->id, "easyvoter", "hide", "view.php?id=$cm->id", "$easyvoter->name");
			$cm->visible = 0;
		}else{
			add_to_log($course->id, "easyvoter", "show", "view.php?id=$cm->id", "$easyvoter->name");
			$cm->visible = 1;
		}
		update_record("course_modules", $cm);
		redirect($CFG->wwwroot.'/mod/easyvoter/view.php?id='.$cm->id,get_string('easyvoterupdating', 'easyvoter'),1);
	}else{
		redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);
	}
	
?>