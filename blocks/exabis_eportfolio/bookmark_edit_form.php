<?php  // $Id: bookmark_edit_form.php,v 1.3 2007/01/05 04:51:46 jamiesensei Exp $

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/filelib.php');

class comment_edit_form extends moodleform {
	function definition() {
		global $CFG, $USER;
		$mform    =& $this->_form;
		
        $mform->addElement('header', 'comment', get_string("addcomment", "block_exabis_eportfolio"));
        
		$mform->addElement('htmleditor', 'entry', get_string("comment", "block_exabis_eportfolio"), array('rows'=>10));
		$mform->setType('entry', PARAM_RAW);
		$mform->addRule('entry', get_string("commentshouldnotbeempty", "block_exabis_eportfolio"), 'required', null, 'client');
        $mform->setHelpButton('entry', array('writing', 'richtext'), false, 'editorhelpbutton');

        $this->add_action_buttons(false, get_string('add'));

        $mform->addElement('hidden', 'action');
		$mform->setType('action', PARAM_ACTION);
		$mform->setDefault('action', 'add');
        
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
		
		$mform->addElement('hidden', 'bookid');
		$mform->setType('bookid', PARAM_INT);
		$mform->setDefault('bookid', 0);
		
		$mform->addElement('hidden', 'userid');
		$mform->setType('userid', PARAM_INT);
		$mform->setDefault('userid', 0);
		
		$mform->addElement('hidden', 'original');
		$mform->setType('original', PARAM_INT);
		$mform->setDefault('original', 0);
	}
}

class note_edit_form extends moodleform {
	function definition() {
		global $CFG, $USER;
		$mform    =& $this->_form;
		
        $mform->addElement('header', 'general', get_string("note", "block_exabis_eportfolio"));
        
		$mform->addElement('text', 'name', get_string("title", "block_exabis_eportfolio"), 'size="60"');
		$mform->setType('name', PARAM_TEXT);
		$mform->addRule('name', get_string("titlenotemtpy", "block_exabis_eportfolio"), 'required', null, 'client');
		
        $mform->addElement('select', 'category', get_string("category", "block_exabis_eportfolio"), array());
		$mform->addRule('category', get_string("categorynotempty", "block_exabis_eportfolio"), 'required', null, 'client');
        $mform->setDefault('category', 0);
        $this->category_select_setup();

		$mform->addElement('htmleditor', 'intro', get_string("intro", "block_exabis_eportfolio"), array('rows'=>25));
		$mform->setType('intro', PARAM_RAW);
		$mform->addRule('intro', get_string("intronotempty", "block_exabis_eportfolio"), 'required', null, 'client');
        $mform->setHelpButton('intro', array('writing', 'richtext'), false, 'editorhelpbutton');

        $mform->addElement('format', 'format', get_string('format'));

        $this->add_action_buttons();

        $mform->addElement('hidden', 'action');
		$mform->setType('action', PARAM_ACTION);
		$mform->setDefault('action', '');
        
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
        
		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_INT);
		$mform->setDefault('id', 0);
	}
	
	function category_select_setup() {
	    global $CFG, $USER;
	    $mform =& $this->_form;
        $categorysselect =& $mform->getElement('category');
        $categorysselect->removeOptions();
        
        $outercategories = get_records_select("block_exabeporcate", "userid='$USER->id' AND pid=0", "name asc");
        $categories = array(); 
        if ( $outercategories ) {
            foreach ( $outercategories as $curcategory ) {
            	$categories[$curcategory->id] = format_string($curcategory->name);
                
    			$inner_categories = get_records_select("block_exabeporcate", "userid='$USER->id' AND pid='$curcategory->id'", "name asc");
    			if($inner_categories) {
	            	foreach ( $inner_categories as $inner_curcategory ) {
            			$categories[$inner_curcategory->id] = format_string($curcategory->name) . ' &rArr; ' . format_string($inner_curcategory->name);
	            	}
    			}
            }
        } else {
            $categories[0] = get_string("nocategories","block_exabis_eportfolio");
        }
        $categorysselect->loadArray($categories);
	}
}

class external_edit_form extends moodleform {
	function definition() {
		global $CFG, $USER;
		$mform    =& $this->_form;
		
        $mform->addElement('header', 'general', get_string("externallink", "block_exabis_eportfolio"));
        
		$mform->addElement('text', 'name', get_string("title", "block_exabis_eportfolio"), 'maxlength="255" size="60"');
		$mform->setType('name', PARAM_TEXT);
		$mform->addRule('name', get_string("titlenotemtpy", "block_exabis_eportfolio"), 'required', null, 'client');
		
        $mform->addElement('select', 'category', get_string("category", "block_exabis_eportfolio"), array());
		$mform->addRule('category', get_string("categorynotempty", "block_exabis_eportfolio"), 'required', null, 'client');
        $mform->setDefault('category', 0);
        $this->category_select_setup();

		$mform->addElement('text', 'url', get_string("url", "block_exabis_eportfolio"), 'maxlength="255" size="60"');
		$mform->setType('url', PARAM_TEXT);
		$mform->addRule('url', get_string("urlnotempty", "block_exabis_eportfolio"), 'required', null, 'client');

		$mform->addElement('htmleditor', 'intro', get_string("intro", "block_exabis_eportfolio"), array('rows'=>25));
		$mform->setType('intro', PARAM_RAW);
        $mform->setHelpButton('intro', array('writing', 'richtext'), false, 'editorhelpbutton');

        $mform->addElement('format', 'format', get_string('format'));

        $this->add_action_buttons();

        $mform->addElement('hidden', 'action');
		$mform->setType('action', PARAM_ACTION);
		$mform->setDefault('action', '');
        
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
	   
		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_INT);
		$mform->setDefault('id', 0);
	}
	
	function category_select_setup() {
	    global $CFG, $USER;
	    $mform =& $this->_form;
        $categorysselect =& $mform->getElement('category');
        $categorysselect->removeOptions();
        
        $outercategories = get_records_select("block_exabeporcate", "userid='$USER->id' AND pid=0", "name asc");
        $categories = array(); 
        if ( $outercategories ) {
            foreach ( $outercategories as $curcategory ) {
            	$categories[$curcategory->id] = format_string($curcategory->name);
                
    			$inner_categories = get_records_select("block_exabeporcate", "userid='$USER->id' AND pid='$curcategory->id'", "name asc");
    			if($inner_categories) {
	            	foreach ( $inner_categories as $inner_curcategory ) {
            			$categories[$inner_curcategory->id] = format_string($curcategory->name) . '&rArr; ' . format_string($inner_curcategory->name);
	            	}
    			}
            }
        } else {
            $categories[0] = get_string("nocategories","block_exabis_eportfolio");
        }
        $categorysselect->loadArray($categories);
	}
}

class file_edit_form extends moodleform {
	function definition() {
		global $CFG, $USER, $COURSE;
		$mform    =& $this->_form;
		
        $mform->addElement('header', 'general', get_string("file", "block_exabis_eportfolio"));
                
		$mform->addElement('text', 'name', get_string("title", "block_exabis_eportfolio"), 'maxlength="255" size="60"');
		$mform->setType('name', PARAM_TEXT);
		$mform->addRule('name', get_string("titlenotemtpy", "block_exabis_eportfolio"), 'required', null, 'client');
		
        $mform->addElement('select', 'category', get_string("category", "block_exabis_eportfolio"), array());
		$mform->addRule('category', get_string("categorynotempty", "block_exabis_eportfolio"), 'required', null, 'client');
        $mform->setDefault('category', 0);
        $this->category_select_setup();
        
		$mform->addElement('htmleditor', 'intro', get_string("intro", "block_exabis_eportfolio"), array('rows'=>25));
		$mform->setType('intro', PARAM_RAW);
        $mform->setHelpButton('intro', array('writing', 'richtext'), false, 'editorhelpbutton');

        $mform->addElement('format', 'format', get_string('format'));

        $this->add_action_buttons();

		$mform->addElement('hidden', 'assignmentid');
		$mform->setType('assignmentid', PARAM_INT);
     
        $mform->addElement('hidden', 'action');
		$mform->setType('action', PARAM_ACTION);
		$mform->setDefault('action', '');
        
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
        
		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_INT);
		$mform->setDefault('id', 0);
				
        $mform->addElement('hidden', 'filename');
		$mform->setType('filename', PARAM_RAW);
		$mform->setDefault('filename', '');
	}
	
	function category_select_setup() {
	    global $CFG, $USER;
	    $mform =& $this->_form;
        $categorysselect =& $mform->getElement('category');
        $categorysselect->removeOptions();
        
        $outercategories = get_records_select("block_exabeporcate", "userid='$USER->id' AND pid=0", "name asc");
        $categories = array(); 
        if ( $outercategories ) {
            foreach ( $outercategories as $curcategory ) {
            	$categories[$curcategory->id] = format_string($curcategory->name);
                
    			$inner_categories = get_records_select("block_exabeporcate", "userid='$USER->id' AND pid='$curcategory->id'", "name asc");
    			if($inner_categories) {
	            	foreach ( $inner_categories as $inner_curcategory ) {
            			$categories[$inner_curcategory->id] = format_string($curcategory->name) . '&rArr; ' . format_string($inner_curcategory->name);
	            	}
    			}
            }
        } else {
            $categories[0] = get_string("nocategories","block_exabis_eportfolio");
        }
        $categorysselect->loadArray($categories);
	}
}


class file_edit_form_new extends moodleform {
	function definition() {
		global $CFG, $USER, $COURSE;
		$mform    =& $this->_form;
		
        $mform->addElement('header', 'general', get_string("file", "block_exabis_eportfolio"));
        
		$mform->addElement('text', 'name', get_string("title", "block_exabis_eportfolio"), 'maxlength="255" size="60"');
		$mform->setType('name', PARAM_TEXT);
		$mform->addRule('name', get_string("titlenotemtpy", "block_exabis_eportfolio"), 'required', null, 'client');
		
        $mform->addElement('select', 'category', get_string("category", "block_exabis_eportfolio"), array());
		$mform->addRule('category', get_string("categorynotempty", "block_exabis_eportfolio"), 'required', null, 'client');
        $mform->setDefault('category', 0);
        $this->category_select_setup();
        
        $this->set_upload_manager(new upload_manager('attachment', true, false, $COURSE, false, 0, true, true, false));
        $mform->addElement('file', 'attachment', get_string("file", "block_exabis_eportfolio"));

		$mform->addElement('htmleditor', 'intro', get_string("intro", "block_exabis_eportfolio"), array('rows'=>25));
		$mform->setType('intro', PARAM_RAW);
        $mform->setHelpButton('intro', array('writing', 'richtext'), false, 'editorhelpbutton');

        $mform->addElement('format', 'format', get_string('format'));

        $this->add_action_buttons();

		$mform->addElement('hidden', 'assignmentid');
		$mform->setType('assignmentid', PARAM_INT);
     
        $mform->addElement('hidden', 'action');
		$mform->setType('action', PARAM_ACTION);
		$mform->setDefault('action', '');
        
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
        
		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_INT);
		$mform->setDefault('id', 0);
				
        $mform->addElement('hidden', 'filename');
		$mform->setType('filename', PARAM_RAW);
		$mform->setDefault('filename', '');
	}
	
	function category_select_setup() {
	    global $CFG, $USER;
	    $mform =& $this->_form;
        $categorysselect =& $mform->getElement('category');
        $categorysselect->removeOptions();
        
        $outercategories = get_records_select("block_exabeporcate", "userid='$USER->id' AND pid=0", "name asc");
        $categories = array(); 
        if ( $outercategories ) {
            foreach ( $outercategories as $curcategory ) {
            	$categories[$curcategory->id] = format_string($curcategory->name);
                
    			$inner_categories = get_records_select("block_exabeporcate", "userid='$USER->id' AND pid='$curcategory->id'", "name asc");
    			if($inner_categories) {
	            	foreach ( $inner_categories as $inner_curcategory ) {
            			$categories[$inner_curcategory->id] = format_string($curcategory->name) . '&rArr; ' . format_string($inner_curcategory->name);
	            	}
    			}
            }
        } else {
            $categories[0] = get_string("nocategories","block_exabis_eportfolio");
        }
        $categorysselect->loadArray($categories);
	}
}

?>