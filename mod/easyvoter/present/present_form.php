<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: present/present_form.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: Form for present options before running presentation
////////////////////////////////////////////////////////////////////////////
//BASIC SECURITY TO STOP PAGE BEING DISPLAYED IF $sPageCaller VARIABLE NOT FOUND	
if(!isset($sPageCaller)||$sPageCaller!=='present.php'){
	require_once("../../../config.php");
	redirect($CFG->wwwroot);
}
////////////////////////////////////////////////////////////////////////////

require_once($CFG->libdir.'/formslib.php');

class present_form extends moodleform {
	
    function definition() {
        global $USER, $CFG;
		$id = optional_param('id', 0, PARAM_INT); // Course Module ID
		if(!$inid = get_field('course_modules', 'instance', 'id', $id)) {
        	$inid = 0;
		}
		
		$mform =& $this->_form;
		$mform->addElement('header', 'present', get_string('easyvoterpresentactive', 'easyvoter'));
		
		$aPresentAnon = array(1=>get_string('easyvoteranonymous', 'easyvoter'),0=>get_string('easyvoterfullname', 'easyvoter'));
		$aResultsAnon = array(0=>get_string('easyvoterfullname', 'easyvoter'),1=>get_string('easyvoteranonymous', 'easyvoter'));
	
	//ALLOW TO CONTINUE IF ACCIDENTALLY CLOSED DOWN PRESENTATION
	if($oInstance = get_record('easyvoter_present', 'instance', $inid, 'presenter', $USER->id, '', '', 'presentanon,resultsanon')){
		//OPTIONS
		$aContinue = array('yes'=>get_string('easyvoterpresentcontinue', 'easyvoter'),'no'=>get_string('easyvoterpresentrestart', 'easyvoter'),'saveandclose'=>get_string('easyvoterpresentforceclosesave', 'easyvoter'),'close'=>get_string('easyvoterpresentforceclose', 'easyvoter'));
		$mform->addElement('select', 'continueon', get_string('easyvoterpresentactive', 'easyvoter'), $aContinue);
		$mform->addElement('select', 'presentanon', get_string('easyvoterpresentanon', 'easyvoter'), $aPresentAnon);
		$mform->setDefault('presentanon', $oInstance->presentanon);
		$mform->addElement('select', 'resultsanon', get_string('easyvoterresultsanon', 'easyvoter'), $aResultsAnon);
		$mform->setDefault('resultsanon', $oInstance->resultsanon);	
	}else{
		//OPTIONS
		$mform->addElement('select', 'presentanon', get_string('easyvoterpresentanon', 'easyvoter'), $aPresentAnon);
		$mform->addElement('select', 'resultsanon', get_string('easyvoterresultsanon', 'easyvoter'), $aResultsAnon);
	}

	//HIDDEN FIELDS
		$mform->addElement('hidden', 'id', $id);
		
	//ADD BUTTONS
		$this->add_action_buttons();
	}	
}
?>