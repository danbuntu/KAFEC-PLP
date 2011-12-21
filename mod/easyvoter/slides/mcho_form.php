<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: slides/mcho_form.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: Form for multiple choice slide
////////////////////////////////////////////////////////////////////////////
//BASIC SECURITY TO STOP PAGE BEING DISPLAYED IF $sPageCaller VARIABLE NOT FOUND
if(!isset($sPageCaller)||$sPageCaller!=='editslides.php'){
	require_once("../../../config.php");
	redirect($CFG->wwwroot);
}
////////////////////////////////////////////////////////////////////////////

require_once($CFG->libdir.'/formslib.php');

class mcho_form extends moodleform {

    function definition() {
        global $USER, $CFG;
		$id = optional_param('id', 0, PARAM_INT); // Course Module ID
		$sid = optional_param('sid', 0, PARAM_INT); // Slide ID
		
		
		$mform =& $this->_form;
		if($sid>0){
			$mform->addElement('header', 'addedit', get_string('easyvotermchotype', 'easyvoter').' ('.get_string('easyvoteredit', 'easyvoter').')');
		}else{
			$mform->addElement('header', 'addedit', get_string('easyvotermchotype', 'easyvoter').' ('.get_string('easyvoteradd', 'easyvoter').')');
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
		
	//NUMBER OF OPTIONS
		$aNum = array(2=>2,3=>3,4=>4);
		$mform->addElement('select', 'control', get_string('easyvoternumberoptions', 'easyvoter'), $aNum);
		
	//CORRECT ANSWER
		$aABCD = array('ANY'=>get_string('easyvoterany', 'easyvoter'),'A'=>get_string('easyvotera', 'easyvoter'),'B'=>get_string('easyvoterb', 'easyvoter'),'C'=>get_string('easyvoterc', 'easyvoter'),'D'=>get_string('easyvoterd', 'easyvoter'));
		$mform->addElement('select', 'answer', get_string('easyvotercorrectanswer', 'easyvoter'), $aABCD);
		
	//HIDDEN FIELDS
		$mform->addElement('hidden', 'id', $id);
		$mform->addElement('hidden', 'type', 'mcho');
		if($sid>0){
			$mform->addElement('hidden', 'sid', $sid);
			$mform->addElement('hidden', 'action', 'edit');
		}else{
			$mform->addElement('hidden', 'action', 'add');
		}
		
	//ADD BUTTONS
		$this->add_action_buttons();
	}

//PERFORM EXTRA VALIDATION
    function validation($data){
        $errors= array();

		if(!easyvoter_slideTypes($data['type'],$data['answer'],$data['control'])){
			$errors['answer'] = get_string('easyvotervalmcho', 'easyvoter');
		}

        if(count($errors)>0){
            return $errors;
        }else{
			return true;
        }
		
    }
	
}
?>