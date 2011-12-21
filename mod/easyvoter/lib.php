<?php  // $Id: lib.php,v 1.8 2007/12/12 00:09:46 stronk7 Exp $
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: lib.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: Library of functions and constants for module easyVoter
//		 This file should have two well differenced parts:
//		     - All the core Moodle functions, neeeded to allow
//		       the module to work integrated in Moodle.
//		     - All the easyvoter specific functions, needed
//		       to implement all the module logic. Please, note
//		       that, if the module become complex and this lib
//		       grows a lot, it's HIGHLY recommended to move all
//		       these module specific functions to a new php file,
//		       called "locallib.php" (see forum, quiz...). This will
//		       help to save some memory when Moodle is performing
//		       actions across all modules.
////////////////////////////////////////////////////////////////////////////

//VARIABLES
$iMaxPartDefault = 30; //PARTISIPANTS
$iRefreshRateDefault = 5000; //MILLISECONDS
$iIdleDefault = 1200; //SECONDS

if (isset($CFG->easyvoter_maxparticipants)) {
	if(!is_numeric($CFG->easyvoter_maxparticipants)||$CFG->easyvoter_maxparticipants<1){
		$CFG->easyvoter_maxparticipants = $iMaxPartDefault;
	}
}else{
		$CFG->easyvoter_maxparticipants = $iMaxPartDefault;
}

if (isset($CFG->easyvoter_refreshrate)) {
	if(!is_numeric($CFG->easyvoter_refreshrate)||$CFG->easyvoter_refreshrate<1){
		$CFG->easyvoter_refreshrate = $iRefreshRateDefault;
	}
}else{
		$CFG->easyvoter_refreshrate = $iRefreshRateDefault;
}

if (isset($CFG->easyvoter_idle)) {
	if(!is_numeric($CFG->easyvoter_idle)||$CFG->easyvoter_idle<1){
		$CFG->easyvoter_idle = $iIdleDefault;
	}
}else{
		$CFG->easyvoter_idle = $iIdleDefault;
}


/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will create a new instance and return the id number 
 * of the new instance.
 *
 * @param object $instance An object from the form in mod.html
 * @return int The id of the newly inserted easyvoter record
 **/
function easyvoter_add_instance($easyvoter) {
	$easyvoter->timecreated = time();
	//ALWAYS DEFAULT GROUPMODE TO 0
	$easyvoter->groupmode = 0;
	
	$iInstance = insert_record('easyvoter', $easyvoter);
	
	return $iInstance;
}

/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will update an existing instance with new data.
 *
 * @param object $instance An object from the form in mod.html
 * @return boolean Success/Fail
 **/
function easyvoter_update_instance($easyvoter) {
    $easyvoter->timemodified = time();
    $easyvoter->id = $easyvoter->instance;
	//ALWAYS DEFAULT GROUPMODE TO 0
	$easyvoter->groupmode = 0;
	
	$bUpdated = update_record("easyvoter", $easyvoter);
	
	return $bUpdated;
}

/**
 * Given an ID of an instance of this module, 
 * this function will permanently delete the instance 
 * and any data that depends on it. 
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 **/
function easyvoter_delete_instance($id) {
	global $CFG,$COURSE;
	
    if (! $easyvoter = get_record('easyvoter', 'id', $id)) {
        return FALSE;
    }

    $result = true;
	
    //DELETE EASYVOTER_SLIDES TABLE
	delete_records('easyvoter_slides', 'instance', $easyvoter->id);
	
	//DELETE PRESENT TABLE ENTRIES
	delete_records('easyvoter_present', 'instance', $easyvoter->id);
	
	//DELETE RESPONSES TABLE ENTRIES
	delete_records('easyvoter_responses', 'instance', $easyvoter->id);
	
	//DELETE RESPONSES TABLE ENTRIES
	delete_records('easyvoter_results', 'instance', $easyvoter->id);
	
	//DELETE MODDATA FILES
	$sDir = $CFG->dataroot.'/'.$COURSE->id.'/moddata/easyvoter/'.$easyvoter->id;
	remove_dir($sDir);	

    if (! delete_records('easyvoter', 'id', $easyvoter->id)) {
        $result = FALSE;
    }

    return $result;
}

/**
 * Return a small object with summary information about what a 
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 **/
function easyvoter_user_outline($course, $user, $mod, $easyvoter) {
    return $return;
}

/**
 * Print a detailed representation of what a user has done with 
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function easyvoter_user_complete($course, $user, $mod, $easyvoter) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity 
 * that has occurred in easyvoter activities and print it out. 
 * Return true if there was output, or FALSE is there was none. 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function easyvoter_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG;

    return FALSE;  //  True if anything was printed, otherwise FALSE 
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such 
 * as sending out mail, toggling flags etc ... 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function easyvoter_cron() {
    global $CFG;
	//REMOVE ENTRIES THAT HAVE BEEN IDLE FOR $CFG->easyvoter_idle
	$sSQL = 'timemodified < '.(time()-$CFG->easyvoter_idle);
	if($aInstances = get_records_select('easyvoter_present',$sSQL, 'instance ASC', 'instance')){
		foreach($aInstances as $oInstance){
			easyvoter_cleanUpDB($oInstance->instance);
		}
	}
    return true;
}

/**
 * Must return an array of grades for a given instance of this module, 
 * indexed by user.  It also returns a maximum allowed grade.
 * 
 * Example:
 *    $return->grades = array of grades;
 *    $return->maxgrade = maximum allowed grade;
 *
 *    return $return;
 *
 * @param int $easyvoterid ID of an instance of this module
 * @return mixed Null or object with an array of grades and with the maximum grade
 **/
function easyvoter_grades($easyvoterid) {
   return NULL;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of easyvoter. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $easyvoterid ID of an instance of this module
 * @return mixed boolean/array of students
 **/
function easyvoter_get_participants($easyvoterid) {
    return FALSE;
}

/**
 * This function returns if a scale is being used by one easyvoter
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $easyvoterid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 **/
function easyvoter_scale_used ($easyvoterid,$scaleid) {
    $return = FALSE;

    //$rec = get_record("easyvoter","id","$easyvoterid","scale","-$scaleid");
    //
    //if (!empty($rec)  && !empty($scaleid)) {
    //    $return = true;
    //}
   
    return $return;
}

/**
 * Checks if scale is being used by any instance of easyvoter.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any easyvoter
 */
function easyvoter_scale_used_anywhere($scaleid) {
    $bReturn = FALSE;
	//if ($scaleid and record_exists('easyvoter','grade',-$scaleid)) {
    //    $bReturn = TRUE;
    //}
	return $bReturn;
}

/**
 * Execute post-install custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, FALSE on error
 */
function easyvoter_install() {
     return true;
}

/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, FALSE on error
 */
function easyvoter_uninstall() {
    return true;
}

//////////////////////////////////////////////////////////////////////////////////////
/// Any other easyvoter functions go here.  Each of them must have a name that 
/// starts with easyvoter_
/// Remember (see note in first lines) that, if this section grows, it's HIGHLY
/// recommended to move all funcions below to a new "localib.php" file.

//FORCE GROUP MODE
function easyvoter_forceGroupMode($mod){
		$mod->groupmode = 0;
		return update_record("course_modules", $mod);
}

//CHECK PRESENTER ROLE
function easyvoter_isPresenter($cmid){
	$bPresenter = FALSE;
	if(is_numeric($cmid)){
		$ModuleContext = get_context_instance(CONTEXT_MODULE,$cmid);
		if(has_capability('mod/easyvoter:present',$ModuleContext)){
			$bPresenter = true;
		}
	}
	return $bPresenter;
}

//CHECK ADMINISTRATOR ROLE
function easyvoter_isAdmin(){
	global $USER;
	return is_siteadmin($USER->id);
}

//VALIDATE MAX PATICIPANTS
function easyvoter_validatMaxParticipants($maxparticipants){
	global $CFG;
	$iMaxParticipants = $CFG->easyvoter_maxparticipants;
	if(is_numeric($maxparticipants)){
		if($maxparticipants>0&&$maxparticipants<$iMaxParticipants){
			$iMaxParticipants = $maxparticipants;
		}
	}
	return  $iMaxParticipants;
}

//SLIDE TYPES 
//RETURNS ALL SLIDE TYPES IF NO PARAMETERS ARE PASSED
//IF PARAMETERS PRESENT EITHER RETURNS FORMATTED ENTRIES FOR ANSWER AND CONTROL AS ARRAY OR FALSE IS NOT VALID
//IF ADDITIONS OR DELETIONS ARE MADE TO QUESTION TYPES HERE - MAKE SURE CHARGES ARE MADE TO 
//PRESENT/PRESENT_SLIDES.PHP JAVASCRIPT setType()
//PRESENT/PARTICIPATE_SLIDES.PHP JAVASCRIPT setType()
//PRESENT/PRESENT.PHP JAVASCRIPT responses IF GRAPHING REQUIRED
//ALSO ADD FORM QUESTION TYPE FORM TO SLIDES FOLDER
function easyvoter_slideTypes($type='all',$answer='',$control=''){
		$aReturn = FALSE;
		switch($type){
			case 'all':
				$aReturn = array('info'=>'easyvoterinfotype','nume'=>'easyvoternumetype','mcho'=>'easyvotermchotype','text'=>'easyvotertexttype','true'=>'easyvotertruetype');
				break;
			case 'info':
				$aReturn = array('','');
				break;
			case 'nume':
				if(is_numeric($answer)){
					$aReturn = array(trim($answer),'');
				}elseif(trim($answer)===''){
					$aReturn = array('','');
				}
				break;
			case 'mcho':
				if($answer!=='ANY'&&trim($answer)!==''&&!empty($control)){
					$sAnswer = strtoupper($answer);			
					if(($sAnswer=='A'||$sAnswer=='B'||$sAnswer=='C'||$sAnswer=='D')&&is_numeric($control)&&$control>0&&$control<5){
						if($control==2&&($sAnswer=='C'||$sAnswer=='D')){
							$aReturn = FALSE;
						}elseif($control==3&&$sAnswer=='D'){
							$aReturn = FALSE;
						}else{
							$aReturn = array($sAnswer,$control);
						}
					}
				}elseif((trim($answer)===''||$answer=='ANY')&&!empty($control)){
					$aReturn = array('',$control);
				}
				break;
			case 'text':
				if(is_string($answer)){
					if(strlen(trim($answer))<50){
						//SOLCOL REPLACE POUND SIGN
						//STRIP_TAGS
						$aReturn = array(substr(trim($answer),0,50),'');
					}
				}elseif(trim($answer)===''){
					$aReturn = array('','');
				}
				break;
			case 'true':
				if(strtoupper(trim($answer)==='TRUE')||strtoupper(trim($answer)==='FALSE')){
					if(strtoupper(trim($answer)==='TRUE')){
						$aReturn = array('TRUE','');
					}else{
						$aReturn = array('FALSE','');
					}
				}elseif(trim($answer)===''||$answer=='ANY'){
					$aReturn = array('','');
				}
				break;
			default:
				$aReturn = FALSE;
		}
		return $aReturn;
}

//NUMBER OF SLIDES
function easyvoter_numberOfSlides($inid){
	if(is_numeric($inid)){
		return count_records('easyvoter_slides', 'instance', $inid);
	}
}

//NUMBER OF RESULTS RECORDED
function easyvoter_resultsRecorded($inid){
	if(is_numeric($inid)){
		return count_records('easyvoter_results', 'instance', $inid);
	}
}

//CLEAN UP DATABASE (CLEANUP UP INSTANCES RUNNING AND RESPONSES FROM PARTICIPANTS)
function easyvoter_cleanUpDB($inid){
	global $USER;
	$bReturn = true;
	if(is_numeric($inid)&&get_field('easyvoter_present','id','presenter',$USER->id)){
		if(delete_records('easyvoter_responses','instance',$inid)){
			if(!delete_records('easyvoter_present','instance',$inid)){
				$bReturn = FALSE;
			}			
		}else{
			$bReturn = FALSE;
		}
	}else{
		$bReturn = FALSE;
	}
	return $bReturn;
}

//INSTANCE ACTIVE (CHECK IF CURRENT EASYVOTER INSTANCE IS IN USE)
//RETURN FALSE IF NOT OF ARRAY [PRESENTER USER ID, AND IF AVAILABLE PRESENTER FIRSTNAME AND SURNAME]
function easyvoter_isActive($inid){
	$aReturn = FALSE;
	if(is_numeric($inid)){
		if($iUserID = get_field('easyvoter_present','presenter','instance',$inid)){
			$aReturn = array('id'=>$iUserID);
			if($sFullname=easyvoter_userFullname($iUserID)){
				$aReturn['fullname'] = $sFullname;
			}else{
				$aReturn['fullname'] = '';
			}
		}
	}
	return $aReturn;
}

//FULLNAME (RETURNS FIRST AND LAST NAME OF USER)
function easyvoter_userFullname($id){
	$bReturn = FALSE;
	if(is_numeric($id)){
		if($oFullname=get_record('user','id',$id,'','','','','firstname,lastname')){
			$bReturn = $oFullname->firstname.' '.$oFullname->lastname;
		}
	}
	return $bReturn;
}

//DELETE RESULTS ENTRY AND ASSOCIATED FILE
function easyvoter_deleteResult($resultid){
	global $USER,$CFG,$COURSE;
	$bReturn = FALSE;
	if(is_numeric($resultid)){
		$sSQL = 'id='.$resultid;
		if($oInstance=get_record_select('easyvoter_results',$sSQL,'id,instance,presenter,resultsfile')){
			$sDir = $CFG->dataroot.'/'.$COURSE->id.'/moddata/easyvoter/'.$oInstance->instance;
			if(file_exists($sDir.'/'.$oInstance->resultsfile)&&($USER->id==$oInstance->presenter||easyvoter_isAdmin())){
				if(@unlink($sDir.'/'.$oInstance->resultsfile)){
					$bReturn = TRUE;
				}
			}else{
				//IF FILE HAS ALREADY BEEN DELETED MANUALLY FROM THE MODDATA FOLDER JUST REMOVE ENTRY
				$bReturn = TRUE;
			}
			if($bReturn){
				$bReturn = FALSE;
				if(delete_records('easyvoter_results','id',$oInstance->id)){
					//CHECK IF LAST ENTRY AND REMOVE DIRECTORY
					if(!get_records('easyvoter_results','instance',$oInstance->instance)){
						remove_dir($sDir);
					}				
					$bReturn = TRUE;
				}
			}			
		}
	}
	return $bReturn;
}
?>
