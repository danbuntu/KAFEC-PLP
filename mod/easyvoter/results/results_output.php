<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: redsults/pdf.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: This page exports the results as a pdf file in the moddata course folder
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

	function easyvoter_endResults($pagetitle){
		global $CFG;
		echo '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>'.$pagetitle.'</title>
		<script type="text/javascript" src="'.$CFG->wwwroot.'/mod/easyvoter/scripts/functionlibrary.js"></script>
		</head>
		<body>
		<script type="text/javascript">
		//<![CDATA[
		refreshParent();
		window.close();
		//]]>
		</script>
		</body>
		</html>
		';
	}
	
	//CHECK LOGIN BEFORE USING GLOBAL $USER OBJECT
	if (($resultsanon = get_field("easyvoter_present", "resultsanon", "instance", $easyvoter->id, 'presenter', $USER->id))===FALSE) {
       	print_error('invalidrequest');
    }
	
    if (! $slides = get_records("easyvoter_slides", "instance", $easyvoter->id, 'numeral ASC', 'id,name')) {
       	print_error('invalidrequest');
    }

    if (! $responses = get_records("easyvoter_responses", "instance", $easyvoter->id, 'participant ASC', 'id,slideid,participant,fullname,response')) {
		easyvoter_cleanUpDB($easyvoter->id);
		easyvoter_endResults(get_string('easyvoterpresent', 'easyvoter').$easyvoter->name);
		exit;       	
    }
	
	//CHECK USER ROLE ACCESS
	if(easyvoter_isPresenter($cm->id)){
		try{
			//SETUP DIRECTORY
			$sCourseDir = $CFG->dataroot.'/'.$course->id;
			$sModdataDir = $sCourseDir.'/moddata';
			$sEasyvoterDir = $sModdataDir.'/easyvoter';
			$sInstanceDir = $sEasyvoterDir.'/'.$easyvoter->id;
			$sFileName = 'easyvoter_'.date('d_M_Y_His').'.csv';
			$bDirError = true;
			
			if(check_dir_exists($sCourseDir,true,false)){
				if(check_dir_exists($sModdataDir,true,false)){
					if(check_dir_exists($sEasyvoterDir,true,false)){
						if(check_dir_exists($sInstanceDir,true,false)){
							$bDirError = false;
						}
					}
				}
			}
			
			if($bDirError){
				throw new Exception('errorcreatingdirectory');
			}
			
			//CREATE CSV FILE
			if($handle=@fopen($sInstanceDir.'/'.$sFileName,'wt')){
				if(flock($handle,LOCK_EX)){
					//FIELD HEADERS
					$aHeader = array(0=>get_string('easyvoterparticipant', 'easyvoter'),1=>get_string('easyvoterusername', 'easyvoter'));
					foreach($slides as $slide){
						$aHeader['slide_'.$slide->id] = $slide->name;
					}
					fputcsv($handle,$aHeader);
					
					//SORT RESPONSES
					$aResponses = array();
					$iCurrentParticipant = '';
					$iLoop = -1;
					foreach($responses as $response){
						if($response->participant!=$iCurrentParticipant){
							$iCurrentParticipant = $response->participant;
							$iLoop++;
							if($resultsanon>0){
								$aResponses[$iLoop] = array(0=>get_string('easyvoterparticipant', 'easyvoter').' '.($iLoop+1),1=>'');
							}else{
								if (!$sUsername=get_field('user','username','id',$response->participant)) {
									$sUsername='';
								}
								$aResponses[$iLoop] = array(0=>$response->fullname,1=>$sUsername);
							}
						}
						$aResponses[$iLoop]['slide_'.$response->slideid] = $response->response;
					}
					
					//WRITE RESPONSES
					foreach($aResponses as $response){
						$aContent = array();
						$iLoop = 0;
						foreach($aHeader as $SlideID=>$Value){
							if(isset($response[$SlideID])){
								$aContent[$iLoop] = $response[$SlideID];
							}else{
								$aContent[$iLoop] = '';
							}
							$iLoop++;
						}
						fputcsv($handle,$aContent);
					}
					flock($handle,LOCK_UN);
				}else{
					throw new Exception('errorlockingfile');
				}
				fclose($handle);
				
			}else{
				throw new Exception('errorcreatingfile');
			}
			
			//WRITE TO DB
			if(file_exists($sInstanceDir.'/'.$sFileName)){
				$oDataObject = new object;
				$oDataObject->instance = $easyvoter->id;
				$oDataObject->presenter = $USER->id;
				$oDataObject->participants = count($aResponses);
				$oDataObject->slides = count($slides);
				$oDataObject->resultsanon = $resultsanon;
				$oDataObject->resultsfile = $sFileName;
				$oDataObject->timecreated = time();
				insert_record('easyvoter_results',$oDataObject);
				easyvoter_cleanUpDB($easyvoter->id);
				add_to_log($course->id, "easyvoter", "save results", "view.php?id=$cm->id", "$easyvoter->name");
				easyvoter_endResults(get_string('easyvoterpresent', 'easyvoter').$easyvoter->name);
			}else{
				throw new Exception('errorcreatingfile');
			}
						
		}catch(Exception $e){
			if($e->getMessage()=='errorcreatingdirectory'){
				print_error('errorcreatingdirectory');
			}else{
				print_error('errorcreatingfile','','',$sFileName);
			}
		}
		
	}else{
		redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);
	}	
?>