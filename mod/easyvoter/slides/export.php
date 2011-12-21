<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: slides/export.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: This page exports slides and adds to the log
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
		if($aSlides = get_records('easyvoter_slides', 'instance', $easyvoter->id, 'numeral ASC','name,content,type,answer,control')){
			$aNewLineArray = array("\r\n", "\n\r", "\n", "\r");
			header("Content-Transfer-Encoding: binary");
			header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
			header("Content-type: application/x-download");
			header("Content-Disposition: attachment; filename=".preg_replace('/[^a-zA-Z0-9_\-]/','_',$easyvoter->name).".csv");
			header("Pragma: public");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
echo 'name,content,type,answer,control
';
			foreach($aSlides as $oSlide){
				$sRow = '"'.addslashes($oSlide->name).'",';
				$sRow .= '"'.addslashes($oSlide->content).'",';
				$sRow .= $oSlide->type.',';
				$sRow .= '"'.addslashes($oSlide->answer).'",';
				$sRow .= '"'.addslashes($oSlide->control).'"';			
echo str_replace($aNewLineArray,'',$sRow).'
';
			}
			//TRY TO ELIMINATE USE OF SPECIFIC NEWLINE CHARACTER
			//echo 'name,content,type,answer,control'."\n";
			//foreach($aSlides as $oSlide){
			//	$sRow = '"'.addslashes($oSlide->name).'",';
			//	$sRow .= '"'.addslashes($oSlide->content).'",';
			//	$sRow .= $oSlide->type.',';
			//	$sRow .= '"'.addslashes($oSlide->answer).'",';
			//	$sRow .= '"'.addslashes($oSlide->control).'"';			
			//	echo str_replace($aNewLineArray,'',$sRow)."\n";
			//}
			add_to_log($course->id, "easyvoter", "export slides", "view.php?id=$cm->id", "$easyvoter->name");
		}else{
			redirect($CFG->wwwroot.'/mod/easyvoter/editslides.php?id='.$cm->id, get_string('easyvoternoslidestodownload', 'easyvoter'), 3);
		}
	}else{
		redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);
	}
	
?>