<?php //$Id
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: mod_form.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: This page prints a particular instance of easyVoter
//		This file defines de main easyvoter configuration form
//		  It uses the standard core Moodle (>1.8) formslib. For
//		  more info about them, please visit:
//		  
//		  http://docs.moodle.org/en/Development:lib/formslib.php
//		 
//		  The form must provide support for, at least these fields:
//			- name: text element of 64cc max
//		 
//		  Also, it's usual to use these fields:
//			- intro: one htmlarea element to describe the activity
//					 (will be showed in the list of activities of
//					  easyvoter type (index.php) and in the header 
//					  of the easyvoter main page (view.php).
//			- introformat: The format used to write the contents
//					  of the intro field. It automatically defaults 
//					  to HTML when the htmleditor is used and can be
//					  manually selected if the htmleditor is not used
//					  (standard formats are: MOODLE, HTML, PLAIN, MARKDOWN)
//					  See lib/weblib.php Constants and the format_text()
//					  function for more info
////////////////////////////////////////////////////////////////////////////

require_once ('moodleform_mod.php');

class mod_easyvoter_mod_form extends moodleform_mod {

	function definition() {

		global $COURSE,$CFG;
		$mform    =& $this->_form;

//-------------------------------------------------------------------------------
    /// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));
    /// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('easyvotername', 'easyvoter'), array('size'=>'64'));
		$mform->setType('name', PARAM_TEXT);
		$mform->addRule('name', null, 'required', null, 'client');
    /// Adding the optional "intro" and "introformat" pair of fields
		$aOptions = array('rows'=>20);
    	$mform->addElement('htmleditor', 'intro', get_string('easyvoterintro', 'easyvoter'),$aOptions);
		$mform->setType('intro', PARAM_RAW);
		$mform->addRule('intro', get_string('required'), 'required', null, 'client');
        $mform->setHelpButton('intro', array('writing', 'richtext'), false, 'editorhelpbutton');

        //$mform->addElement('format', 'introformat', get_string('format'));

//-------------------------------------------------------------------------------
    /// Adding the rest of easyvoter settings, spreeading all them into this fieldset
    /// or adding more fieldsets ('header' elements) if needed for better logic
        //$mform->addElement('static', 'label1', 'easyvotersetting1', 'Your easyvoter fields go here. Replace me!');

        $mform->addElement('header', 'easyvoterfieldset', get_string('easyvoterfieldset', 'easyvoter'));
		$aMaxArray = array();
		$iLoop = 1;
		for($iLoop=1;$iLoop<$CFG->easyvoter_maxparticipants+1;$iLoop++){
			$aMaxArray[$iLoop] = $iLoop;
		}
		arsort($aMaxArray);
		$mform->addElement('select', 'maxparticipants', get_string('easyvotermaxparticipants', 'easyvoter'), $aMaxArray);
		
//-------------------------------------------------------------------------------
        // add standard features, common to all modules
		$oFeatures = new object;
		$oFeatures->groups = false;
		$oFeatures->groupings = false;
		$oFeatures->groupmembersonly = false;
		$oFeatures->outcomes = false;
		$oFeatures->gradecat = false;
		$oFeatures->idnumber = false;
		$this->standard_coursemodule_elements($oFeatures);
//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();

	}
}

?>
