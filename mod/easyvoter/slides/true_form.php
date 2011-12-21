<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: slides/true_form.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: Form for true or false slide
////////////////////////////////////////////////////////////////////////////
//BASIC SECURITY TO STOP PAGE BEING DISPLAYED IF $sPageCaller VARIABLE NOT FOUND
if(!isset($sPageCaller)||$sPageCaller!=='editslides.php'){
	require_once("../../../config.php");
	redirect($CFG->wwwroot);
}
////////////////////////////////////////////////////////////////////////////

require_once($CFG->libdir.'/formslib.php');

class true_form extends moodleform {

    function definition() {
        global $USER, $CFG;
		$id = optional_param('id', 0, PARAM_INT); // Course Module ID
		$sid = optional_param('sid', 0, PARAM_INT); // Slide ID
		
		$mform =& $this->_form;
		if($sid>0){
			$mform->addElement('header', 'addedit', get_string('easyvotertruetype', 'easyvoter').' ('.get_string('easyvoteredit', 'easyvoter').')');
		}else{
			$mform->addElement('header', 'addedit', get_string('easyvotertruetype', 'easyvoter').' ('.get_string('easyvoteradd', 'easyvoter').')');
		}
		
    ///ADD NAME
        $mform->addElement('text', 'name', get_string('easyvotername', 'easyvoter'), array('size'=>'64'));
		$mform->setType('name', PARAM_TEXT);
		$mform->addRule('name', null, 'required', null, 'client');
		
    ///ADD CONTENT
		$aOptions = array('rows'=>20);
    	$mform->addElement('htmleditor', 'content', get_string('easyvoterslidecontent', 'easyvoter'),$aOptions);
		$mform->setType('content', PARAM_RAW);
		$mform->addRule('content', get_string('required'), 'required', null, 'client');
        $mform->setHelpButton('content', array('writing', 'richtext'), false, 'editorhelpbutton');
		
	//CORRECT ANSWER
		$aTOrF = array('ANY'=>get_string('easyvoterany', 'easyvoter'),'TRUE'=>get_string('easyvotertrue', 'easyvoter'),'FALSE'=>get_string('easyvoterfalse', 'easyvoter'));
		$mform->addElement('select', 'answer', get_string('easyvotercorrectanswer', 'easyvoter'), $aTOrF);
		
	//HIDDEN FIELDS
		$mform->addElement('hidden', 'id', $id);
		$mform->addElement('hidden', 'type', 'true');
		if($sid>0){
			$mform->addElement('hidden', 'sid', $sid);
			$mform->addElement('hidden', 'action', 'edit');
		}else{
			$mform->addElement('hidden', 'action', 'add');
		}
		
	//ADD BUTTONS
		$this->add_action_buttons();
	}
	
}
?>