<?php  // $Id: post_form.php,v 1.18.2.2 2007/03/13 07:37:07 nicolasconnault Exp $

require_once($CFG->libdir.'/formslib.php');

class eportfolio_new_categorie_form extends moodleform {

	function definition() {
		global $CFG;
		$mform    =& $this->_form;
		
        $mform->addElement('text', 'name', '', 'maxlength="254" size="10"');
        $mform->addRule('name', 'Missing name', 'required');
        $mform->setType('name', PARAM_ALPHAEXT);

		$mform->addElement('hidden', 'pid');
		$mform->setType('pid', PARAM_INT);
		
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
		
		$mform->addElement('hidden', 'cataction');
		$mform->setType('cataction', PARAM_ALPHA);
		
		$mform->addElement('hidden', 'edit');
		$mform->setType('edit', PARAM_INT);
		
		$mform->addElement('hidden', 'catconfirm');
		$mform->setType('catconfirm', PARAM_INT);
		
		$this->add_action_buttons(false);
	}

	function validation($data) {
	}

}
?>
