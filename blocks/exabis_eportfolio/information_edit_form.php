<?php  // $Id: post_form.php,v 1.18.2.2 2007/03/13 07:37:07 nicolasconnault Exp $

require_once($CFG->libdir.'/formslib.php');

class eportfolio_personal_information_form extends moodleform {

	function definition() {

		global $CFG;
		$mform    =& $this->_form;

		$mform->addElement('htmleditor', 'description', get_string('message', 'forum'), array('cols'=>50, 'rows'=>30));
		$mform->setType('description', PARAM_RAW);
		$mform->addRule('description', get_string('required'), 'required', null, 'client');
        $mform->setHelpButton('description', array('reading', 'writing', 'richtext'), false, 'editorhelpbutton');
                
		$mform->addElement('hidden', 'cataction');
		$mform->setType('cataction', PARAM_ALPHA);
		
		$mform->addElement('hidden', 'descid');
		$mform->setType('descid', PARAM_INT);
		
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
		
		$mform->addElement('hidden', 'edit');
		$mform->setType('edit', PARAM_BOOL);
		
		 $this->add_action_buttons();
	}

	function validation($data) {
	}
}
?>
