<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: present.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: This page presets the easyVoter instance to students
////////////////////////////////////////////////////////////////////////////
//BASIC SECURITY $sPageCaller VARIABLE MUST BE PRESENT TO LOAD FORM FILES
$sPageCaller = "present.php";
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
		//CHECK IF PRESENTATION IS NOT ALREADY BEING RUN BY ANOTHER PRESENTER
		$isActiveByOtherPresenter = false;
		if($aActive = easyvoter_isActive($easyvoter->id)){
			if($aActive['id']!=$USER->id){
				$isActiveByOtherPresenter = true;
			}
		}
		if($isActiveByOtherPresenter){
			require_once("present/options_header.php");
			echo '
			<p class="error">'.get_string('easyvoterinusebyanother', 'easyvoter').$aActive['fullname'].'</p>
			<script type="text/javascript">
			//<![CDATA[
			refreshParent();
			setTimeout(\'window.close()\',3000);
			//]]>
			</script>
			';
			require_once("present/options_footer.php");
		}else{
			require_once('present/present_form.php');
			$presentForm = new present_form('present.php', compact('course', 'category'));		
			//OPTIONS FOR RUNNING PRESENTATION
			if($presentForm->is_cancelled()){
				require_once("present/options_header.php");
				echo'
					<script type="text/javascript">
					//<![CDATA[
					window.close();
					//]]>
					</script>
				';
				require_once("present/options_footer.php");
			}elseif($oFormData=$presentForm->get_data()){
				if(empty($oFormData->continueon)){
					$oFormData->continueon = '';
				}
				switch($oFormData->continueon){
					case 'yes':
						//CONTINUE
						//EASYVOTER PRESENT UPDATE
						//SET ID TO PRESENT INSTANCE ID
						$oFormData->id = get_field('easyvoter_present','id','instance',$easyvoter->id,'presenter',$USER->id);
						if($oFormData->presentanon!='0'&&$oFormData->presentanon!='1'){
							$oFormData->presentanon = 0;
						}
						if($oFormData->resultsanon!='0'&&$oFormData->resultsanon!='1'){
							$oFormData->resultsanon = 1;
						}
						$oFormData->timemodified = time();
						if(update_record('easyvoter_present',$oFormData)){
							require_once("present/present_slides.php");
							add_to_log($course->id, "easyvoter", "present", "view.php?id=$cm->id", "$easyvoter->name");
						}else{
							require_once("present/options_header.php");
							echo '
							<p class="error">'.get_string('easyvoterunabletocontinue', 'easyvoter').'</p>
							<script type="text/javascript">
							//<![CDATA[
							setTimeout(\'window.close()\',3000);
							//]]>
							</script>
							';
							require_once("present/options_footer.php");
						}
						break;
					case 'no':
						//RESTART
						//DELETE PREVIOUS ENTRIES
						$inid = get_field('easyvoter_present','instance','instance',$easyvoter->id,'presenter',$USER->id);
						easyvoter_cleanUpDB($inid);
						//WRITE INFO TO DATABASE
						$oFormData->instance = $easyvoter->id;
						$oFormData->presenter = $USER->id;
						$oFormData->numeral = 0;
						if($oFormData->presentanon!='0'&&$oFormData->presentanon!='1'){
							$oFormData->presentanon = 0;
						}
						if($oFormData->resultsanon!='0'&&$oFormData->resultsanon!='1'){
							$oFormData->resultsanon = 1;
						}
						$oFormData->timecreated = time();
						$oFormData->timemodified = time();
						insert_record('easyvoter_present', $oFormData);
						require_once("present/present_slides.php");
						add_to_log($course->id, "easyvoter", "present", "view.php?id=$cm->id", "$easyvoter->name");
						break;
					case 'saveandclose':
						//WRITE RESULTS OUTPUT
						$oFormData->id = get_field('easyvoter_present','id','instance',$easyvoter->id,'presenter',$USER->id);
						if($oFormData->presentanon!='0'&&$oFormData->presentanon!='1'){
							$oFormData->presentanon = 0;
						}
						if($oFormData->resultsanon!='0'&&$oFormData->resultsanon!='1'){
							$oFormData->resultsanon = 1;
						}
						$oFormData->timemodified = time();
						if(update_record('easyvoter_present',$oFormData)){
							redirect($CFG->wwwroot.'/mod/easyvoter/results/results_output.php?id='.$cm->id);
						}
						break;
					case 'close':
						//DELETE PREVIOUS ENTRIES
						$inid = get_field('easyvoter_present','instance','instance',$easyvoter->id,'presenter',$USER->id);
						easyvoter_cleanUpDB($inid);
						require_once("present/options_header.php");
						echo '
						<script type="text/javascript">
						//<![CDATA[
						refreshParent();
						window.close();
						//]]>
						</script>
						';
						require_once("present/options_footer.php");
						break;
					default;
						//WRITE INFO TO DATABASE - CHECK PAGE NOT REFRESHED FIRST WITH IF STATEMENT
						if(!$inid = get_field('easyvoter_present','instance','instance',$easyvoter->id,'presenter',$USER->id)){
							$oFormData->instance = $easyvoter->id;
							$oFormData->presenter = $USER->id;
							$oFormData->numeral = 0;
							if($oFormData->presentanon!='0'&&$oFormData->presentanon!='1'){
								$oFormData->presentanon = 0;
							}
							if($oFormData->resultsanon!='0'&&$oFormData->resultsanon!='1'){
								$oFormData->resultsanon = 1;
							}
							$oFormData->timecreated = time();
							$oFormData->timemodified = time();
							insert_record('easyvoter_present', $oFormData);
						}
						require_once("present/present_slides.php");
						add_to_log($course->id, "easyvoter", "present", "view.php?id=$cm->id", "$easyvoter->name");
				}		
			}else{
				require_once("present/options_header.php");
				$presentForm->display();
				require_once("present/options_footer.php");
			}
		}
	}else{
		//IF ACTIVE ALLOW PARTICIPANTS IN UP TO MAXIMUM
		if($inid = get_field('easyvoter_present','id','instance',$easyvoter->id)){
			//REDIRECT IF FULL
			$iParticipants = count_records('easyvoter_responses','instance',$easyvoter->id,'slideid','0');
			if($iParticipants<$easyvoter->maxparticipants){
					//ADD IF NEW PARTICIPANT
					if(count_records('easyvoter_responses','instance',$easyvoter->id,'slideid','0','participant',$USER->id)<1){
						$oDataObject = new object;
						$oDataObject->instance = $easyvoter->id;
						$oDataObject->slideid = 0;
						$oDataObject->participant = $USER->id;
						$oDataObject->fullname = $USER->firstname.' '.$USER->lastname;
						$oDataObject->timecreated = time();
						insert_record('easyvoter_responses',$oDataObject);
						add_to_log($course->id, "easyvoter", "participate", "view.php?id=$cm->id", "$easyvoter->name");
					}
						require_once("present/participate_slides.php");
			}else{
				//LET IN IF ALREADY A PARTICIPANT
				if(count_records('easyvoter_responses','instance',$easyvoter->id,'slideid','0','participant',$USER->id)<1){
					redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);
				}else{
					require_once("present/participate_slides.php");
				}
			}
		}else{
			redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);
		}
	}
?>