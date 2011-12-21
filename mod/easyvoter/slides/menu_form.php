<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: slides/menu_form.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: Form for menu to add slide
////////////////////////////////////////////////////////////////////////////
//BASIC SECURITY TO STOP PAGE BEING DISPLAYED IF $sPageCaller VARIABLE NOT FOUND
if(!isset($sPageCaller)||$sPageCaller!=='editslides.php'){
	require_once("../../../config.php");
	redirect($CFG->wwwroot);
}
////////////////////////////////////////////////////////////////////////////

require_once($CFG->libdir.'/formslib.php');

class menu_form extends moodleform {

    function definition() {
        global $USER, $CFG;
		$id = optional_param('id', 0, PARAM_INT); // Course Module ID
		
		$mform =& $this->_form;
		$mform->addElement('header', 'menu', get_string('easyvoteraddslide', 'easyvoter'));
		
    ///ADD TYPE
		$aSlideTypes = easyvoter_slideTypes();
		foreach($aSlideTypes as $key => $value){
			$aSlideTypes[$key] = get_string($value, 'easyvoter');
		}
        $mform->addElement('select', 'type', get_string('easyvotertype', 'easyvoter'), $aSlideTypes);
		
	//HIDDEN FIELDS
		$mform->addElement('hidden', 'id', $id);
		$mform->addElement('hidden', 'action', 'add');
		
	//ADD BUTTONS
		$buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('easyvoteradd', 'easyvoter'));
        $buttonarray[] = &$mform->createElement('button', 'importexport', get_string('easyvoterimportexport', 'easyvoter'));
		$buttonarray[] = &$mform->createElement('button', 'deleteall', get_string('easyvoterdeleteall', 'easyvoter'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
	}
	
}
?>