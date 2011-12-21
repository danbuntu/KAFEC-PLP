<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: editslides.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: This page allows the editing of questions
////////////////////////////////////////////////////////////////////////////
//BASIC SECURITY $sPageCaller VARIABLE MUST BE PRESENT TO LOAD FORM FILES
$sPageCaller = "editslides.php";
////////////////////////////////////////////////////////////////////////////

    require_once("../../config.php");
    require_once("lib.php");
		
    $id = optional_param('id', 0, PARAM_INT); // Course Module ID
	$action = optional_param('action', 'menu', PARAM_TEXT); // Acton add/edit
	$type = optional_param('type', 'info', PARAM_TEXT); // Question type
	$sid = optional_param('sid', 0, PARAM_INT); // Question id

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

//REDIRECT FOR ANY FORM CANCELED
 	$cancel = optional_param('cancel', '', PARAM_TEXT);
	if(strtolower($cancel)==='cancel'){
		redirect($CFG->wwwroot.'/mod/easyvoter/view.php?id='.$cm->id);
	}

/// Print the page header
	$streasyvoters = get_string("modulenameplural", "easyvoter");
	$streasyvoter  = get_string("modulename", "easyvoter");

	$navlinks = array();
	$navlinks[] = array('name' => $streasyvoters, 'link' => "index.php?id=$course->id", 'type' => 'activity');
	$navlinks[] = array('name' => format_string($easyvoter->name), 'link' => '', 'type' => 'activityinstance');
	
	$navigation = build_navigation($navlinks);
	
	print_header_simple(format_string($easyvoter->name), "", $navigation, "", "", true, update_module_button($cm->id, $course->id, $streasyvoter), navmenu($course, $cm));
	
	//EASYVOTER STYLES
	require_once('styles/default.php');
	
	//CHECK USER ROLE ACCESS
	if(easyvoter_isPresenter($cm->id)){
		
		//SET UP CONTAINERS
		//OPTIONS
		echo '<div style="'.$sStyleCenter.'"><div class="headingblock header" style="'.$sStyleHeadingBlock.'">'.get_string('easyvoteroptions', 'easyvoter').'</div><div id="easyvoter_options" class="generalbox" style="'.$sStyleContentBlock.'">';
		switch(strtolower($action)){
			case 'add';
				$aSlideTypes = easyvoter_slideTypes();
				$iCorrectType = 0;		
				foreach($aSlideTypes as $key => $value){
					//IF NO SUCH QUESTION TYPE REDIRECT;
					if($key===$type){
						require_once('slides/'.$key.'_form.php');
						$sForm = "new ".$key."_form('editslides.php', compact('course', 'category'))";
						eval("\$addForm = $sForm ;");
						//ANY CANCELLED FORM IS DEALT WITH AT THE TOP OF THE PAGE ($addForm->is_cancelled())
						if ($oFormData=$addForm->get_data()){
							//VALID
							$oFormData->instance = $cm->instance;
							$oFormData->numeral = easyvoter_numberOfSlides($cm->instance)+1;
							$oFormData->type = $key;
							$oFormData->timecreated = time();
							insert_record('easyvoter_slides', $oFormData);
							add_to_log($course->id, "easyvoter", "add slide", "editslides.php?id=$cm->id", "$easyvoter->name");
							redirect($CFG->wwwroot.'/mod/easyvoter/editslides.php?id='.$cm->id,'',0);
						}else{
							//INVALID OR FIRST RUN
							$addForm->display();
						}
						$iCorrectType++;
						break;
					}
				}
				if($iCorrectType<1){
					redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);
				}
				break;
			case 'edit';
				if(!$oSlide = get_record("easyvoter_slides", "id", $sid)){
					redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);
				}else{
					require_once('slides/'.$oSlide->type.'_form.php');
					$sForm = "new ".$oSlide->type."_form('editslides.php', compact('course', 'category'))";
					eval("\$editForm = $sForm ;");
					//ANY CANCELLED FORM IS DEALT WITH AT THE TOP OF THE PAGE ($addForm->is_cancelled())
					if ($oFormData=$editForm->get_data()){
						//VALID
						$oFormData->id = $oFormData->sid; //RESET ID TO SLIDE ID AND NOT MODULE ID
						$oFormData->timemodified = time();
						update_record('easyvoter_slides', $oFormData);
						add_to_log($course->id, "easyvoter", "edit slide", "editslides.php?id=$cm->id", "$easyvoter->name");
						redirect($CFG->wwwroot.'/mod/easyvoter/editslides.php?id='.$cm->id,'',0);
					}else{
						//INVALID OR FIRST RUN
						$oSlide->id=$cm->id; //SET ID TO MODULE ID AND NOT SLIDE ID
						$editForm->set_data($oSlide);
						$editForm->display();
					}
				}
				break;
			case 'importexport';
				require_once('slides/importexport_form.php');
				$importExportForm = new importexport_form('editslides.php', compact('course', 'category'));
				//ANY CANCELLED FORM IS DEALT WITH AT THE TOP OF THE PAGE ($addForm->is_cancelled())
				if ($oFormData=$importExportForm->get_data()){
					add_to_log($course->id, "easyvoter", "import slides", "editslides.php?id=$cm->id", "$easyvoter->name");
					redirect($CFG->wwwroot.'/mod/easyvoter/editslides.php?id='.$cm->id,'',0);
					//TODO REMOVE: $importExportForm->save_files($CFG->dataroot.'/'.$course->id.'/moddata/easyvoter/'.$cm->id.'/slides');
				}else{
					//INVALID OR FIRST RUN
					$importExportForm->display();
				}
				break;
			default:
				require_once('slides/menu_form.php');
				$menuForm = new menu_form('editslides.php', compact('course', 'category'));
				$menuForm->display();
				//ALSO ADDITIONAL FUNCTIONALITY APPENDED TO <SCRIPT> TAG BOTTOM OF PAGE FOR THIS FORM
		}
		echo '</div></div>';
		
		//QUESTIONS
		echo '<div style="'.$sStyleCenter.'"><div class="headingblock header" style="'.$sStyleHeadingBlock.'">'.get_string('easyvoterslides', 'easyvoter').'</div><div id="easyvoter_slides" class="generalbox" style="'.$sStyleContentBlock.'"></div></div>';
		
		//SET UP AJAX OBJECT FOR QUESTIONS
		echo '
		<script type="text/javascript" src="scripts/ajaxclass.js"></script>
		<script type="text/javascript">
			//<![CDATA[
				var oSlide = new ajaxObject();
				var iTimerDelay = 250;
				
				function refreshSlides(send){
					if(oSlide.ajaxSupported()){
						if(typeof(send)!=\'undefined\'){
							oSlide.ajaxSend(\'get\',\'slides/slides.php?cid='.$course->id.'&cmid='.$cm->id.'&inid='.$easyvoter->id.'\');
							setTimeout(\'refreshSlides()\',iTimerDelay);
						}else{
							if(oSlide.ajaxReadyState()<4){
								document.getElementById(\'easyvoter_slides\').innerHTML = "'.get_string('easyvoterloading', 'easyvoter').'";
								setTimeout(\'refreshSlides()\',iTimerDelay);
							}else{
								document.getElementById(\'easyvoter_slides\').innerHTML = oSlide.ajaxReturn();
							}
						}
					}
				}
			
				function sortSlide(numeral,action){
					if(oSlide.ajaxSupported()){
						if(typeof(numeral)!=\'undefined\'&&typeof(action)!=\'undefined\'){
							var bContinue = false;
							if(action==\'delete\'){
								if(numeral!='.$sid.'){
									bContinue = confirm("'.get_string('easyvoterconfirmdelete', 'easyvoter').'");
								}else{
									alert("'.get_string('easyvoternodeleteedit', 'easyvoter').'");
								}
							}else{
								bContinue = true;
							}
							if(bContinue){
								oSlide.ajaxSend(\'get\',\'slides/slides.php?cid='.$course->id.'&cmid='.$cm->id.'&inid='.$easyvoter->id.'&sid=\'+numeral+\'&action=\'+action);
								document.getElementById(\'easyvoter_slides\').innerHTML = "'.get_string('easyvoterloading', 'easyvoter').'";
								setTimeout(\'sortSlide()\',iTimerDelay);
							}
						}else{
							if(oSlide.ajaxReadyState()<4){
								document.getElementById(\'easyvoter_slides\').innerHTML = "'.get_string('easyvoterloading', 'easyvoter').'";
								setTimeout(\'sortSlide()\',iTimerDelay);
							}else{
								setTimeout(\'refreshSlides(1)\',iTimerDelay);
							}
						}
					}
				}
	
				if(oSlide.ajaxSupported()){
					oSlide.ajaxAdditionalHeaders(\'XMLHTTPCaller=editslides.php\');
					refreshSlides(\'1\');
				}else{
					document.getElementById(\'easyvoter_slides\').innerHTML = "'.get_string('easyvoternoajax', 'easyvoter').'";
				}
				
				';
				//ADD JAVASCRIPT ADDITIONAL FUNTIONALITY FOR MENU AND IMPORT EXPORT FORM
				if(strtolower($action)!=='add'&&strtolower($action)!=='edit'&&strtolower($action)!=='importexport'){
					echo'
					function importExport(){
							window.location="'.$CFG->wwwroot.'/mod/easyvoter/editslides.php?id='.$cm->id.'&action=importexport";
					}
						
					function deleteAll(send){
						if(oSlide.ajaxSupported()){
							if(document.getElementById(\'easyvoter_slides\').innerHTML.indexOf(\''.get_string('easyvoternoslides', 'easyvoter').'\')<0){
								if(typeof(send)!==\'number\'&&confirm("'.get_string('easyvoterconfirmdeleteall', 'easyvoter').'")){
										if(confirm("'.get_string('easyvoterconfirmdeleteallfinal', 'easyvoter').'")){
											oSlide.ajaxSend(\'get\',\'slides/slides.php?cid='.$course->id.'&cmid='.$cm->id.'&inid='.$easyvoter->id.'&action=deleteall\');
											document.getElementById(\'easyvoter_slides\').innerHTML = "'.get_string('easyvoterloading', 'easyvoter').'";
											setTimeout(\'deleteAll(1)\',iTimerDelay);				
										}
								}
								
								if(typeof(send)===\'number\'&&oSlide.ajaxReadyState()<4){
										document.getElementById(\'easyvoter_slides\').innerHTML = "'.get_string('easyvoterloading', 'easyvoter').'";
										setTimeout(\'deleteAll(1)\',iTimerDelay);
								}else if(typeof(send)===\'number\'){
										setTimeout(\'refreshSlides(1)\',iTimerDelay);
								}
								
							}else{
								alert(\''.get_string('easyvoternoslidestodelete', 'easyvoter').'\');
							}
						}
					}
					
					//NOTE BROWSERS OTHER THAN IE WILL PASS EVENT AS THE FIRST PARAMETER
					document.getElementById(\'id_importexport\').onclick = importExport;
					document.getElementById(\'id_deleteall\').onclick = deleteAll;
					';
				}elseif(strtolower($action)==='importexport'){
					echo'
					function exportCSV(send){
						var sCheck = document.getElementById(\'easyvoter_slides\').innerHTML;
						if(sCheck!==\'\'&&sCheck.indexOf(\''.get_string('easyvoternoslides', 'easyvoter').'\')<0&&sCheck.indexOf(\''.get_string('easyvoterloading', 'easyvoter').'\')<0){
							window.location="'.$CFG->wwwroot.'/mod/easyvoter/slides/export.php?id='.$cm->id.'";
						}else{
							alert(\''.get_string('easyvoternoslidestodownload', 'easyvoter').'\');
						}
					}
					document.getElementById(\'id_downloadcsv\').onclick = exportCSV;
					';
				}
				echo'		
			//]]>
		</script>
		';
	}else{
		redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);
	}
	
/// Finish the page
    print_footer($course);
?>