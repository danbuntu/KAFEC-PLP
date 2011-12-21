<?PHP //$Id: block_howto'.php, v1.0 2007/10/19 by Red Morris of Mid-Kent College $
    class block_howto extends block_base {
	    function init() {
			$this->version = 20071019;
	    	$this->title =  "How To..."; 
	    }
	    
		function instance_allow_multiple() {
	    	return false;
	    }
		
		function hide_header() {
			return false;
		}
		
		// Sets the title
		function specialization() {
	    	$this->title =  "How To..."; 
	    }
		
		function showpopup() {
			$popup = window.open('', 'How To', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=auto,resizable=no,width=520,height=620'); 
			$popup.document.write('<a>This is how</a>');
		}
		
	    function get_content() {
		    global $USER, $CFG, $SESSION;
			// Code is run many times, so check if it's run before to save processing
		    if ($this->content !== NULL) {
		        return $this->content;
		    }
			
			$this->content = new stdClass; 
	        
			// only 2 possible contexts, site or course
			if ($this->instance->pageid == SITEID) { // site context
				  $currentcontext = get_context_instance(CONTEXT_SYSTEM, SITEID);
			} else { // course context
				$currentcontext = get_context_instance(CONTEXT_COURSE, $this->instance->pageid);
			}

			if (has_capability('moodle/course:update', $currentcontext)) {
				$this->content->text = "<ul class=\"list\">
										<li class=\"r0\">
											<span class=\"icon c0\"><img src=\"" .$CFG->pixpath. "/f/help.gif\" alt=\"\" /></span>
											<span class=\"c1\"><a title=\"Resource\" onclick=\"return openpopup('/blocks/howto/view.php?id=3165','howtopopup','scrollbars=1,height=520,width=620');\" href=\"" .$CFG->wwwroot. "/blocks/howto/view.php?id=3165\">Edit or Add Text and Titles</a></span>
										</li>
										<li class=\"r0\">
											<span class=\"icon c0\"><img src=\"" .$CFG->pixpath. "/f/help.gif\" alt=\"\" /></span>
											<span class=\"c1\"><a title=\"Resource\" onclick=\"return openpopup('/blocks/howto/view.php?id=3174','howtopopup','scrollbars=1,height=520,width=620');\" href=\"" .$CFG->wwwroot. "/blocks/howto/view.php?id=3174\">Upload Files</a></span>
										</li>
										<li class=\"r0\">
											<span class=\"icon c0\"><img src=\"" .$CFG->pixpath. "/f/help.gif\" alt=\"\" /></span>
											<span class=\"c1\"><a title=\"Resource\" onclick=\"return openpopup('/blocks/howto/view.php?id=3250','howtopopup','scrollbars=1,height=520,width=620');\" href=\"" .$CFG->wwwroot. "/blocks/howto/view.php?id=3250\">Display a Folder</a></span>
										</li>
										<li class=\"r0\">
											<span class=\"icon c0\"><img src=\"" .$CFG->pixpath. "/f/help.gif\" alt=\"\" /></span>
											<span class=\"c1\"><a title=\"Resource\" onclick=\"return openpopup('/blocks/howto/view.php?id=3384','howtopopup','scrollbars=1,height=520,width=620');\" href=\"" .$CFG->wwwroot. "/blocks/howto/view.php?id=3384\">See What Students See</a></span>
										</li>
										<li class=\"r0\">
											<span class=\"icon c0\"><img src=\"" .$CFG->pixpath. "/f/help.gif\" alt=\"\" /></span>
											<span class=\"c1\"><a title=\"Resource\" onclick=\"return openpopup('/blocks/howto/view.php?id=3406','howtopopup','scrollbars=1,height=520,width=620');\" href=\"" .$CFG->wwwroot. "/blocks/howto/view.php?id=3406\">Add or Move a Block</a></span>
										</li>
										<li class=\"r0\">
											<span class=\"icon c0\"><img src=\"" .$CFG->pixpath. "/f/help.gif\" alt=\"\" /></span>
											<span class=\"c1\"><a title=\"Resource\" onclick=\"return openpopup('/blocks/howto/view.php?id=3438','howtopopup','scrollbars=1,height=520,width=620');\" href=\"" .$CFG->wwwroot. "/blocks/howto/view.php?id=3438\">Hide Topics & Resources</a></span>
										</li>
										<li class=\"r0\">
											<span class=\"icon c0\"><img src=\"" .$CFG->pixpath. "/f/help.gif\" alt=\"\" /></span>
											<span class=\"c1\"><a title=\"Resource\" onclick=\"return openpopup('/blocks/howto/view.php?id=3895','howtopopup','scrollbars=1,height=520,width=620');\" href=\"" .$CFG->wwwroot. "/blocks/howto/view.php?id=3895\">Move Files</a></span>
										</li>
										<li class=\"r0\">
											<span class=\"icon c0\"><img src=\"" .$CFG->pixpath. "/f/help.gif\" alt=\"\" /></span>
											<span class=\"c1\"><a title=\"Resource\" onclick=\"return openpopup('/blocks/howto/view.php?id=3941','howtopopup','scrollbars=1,height=520,width=620');\" href=\"" .$CFG->wwwroot. "/blocks/howto/view.php?id=3941\">Create an Assignment</a></span>
										</li>
										<li class=\"r0\">
											<span class=\"icon c0\"><img src=\"" .$CFG->pixpath. "/f/help.gif\" alt=\"\" /></span>
											<span class=\"c1\"><a title=\"Resource\" onclick=\"return openpopup('/blocks/howto/view.php?id=3888','howtopopup','scrollbars=1,height=520,width=620');\" href=\"" .$CFG->wwwroot. "/blocks/howto/view.php?id=3888\">Create a Glossary</a></span>
										</li>
										<li class=\"r0\">
											<span class=\"icon c0\"><img src=\"" .$CFG->pixpath. "/f/help.gif\" alt=\"\" /></span>
											<span class=\"c1\"><a title=\"Resource\" onclick=\"return openpopup('/blocks/howto/view.php?id=4375','howtopopup','scrollbars=1,height=520,width=620');\" href=\"" .$CFG->wwwroot. "/blocks/howto/view.php?id=4375\">Create a Quiz</a></span>
										</li>
										<li class=\"r0\">
											<span class=\"icon c0\"><img src=\"" .$CFG->pixpath. "/f/help.gif\" alt=\"\" /></span>
											<span class=\"c1\"><a title=\"Resource\" onclick=\"return openpopup('/blocks/howto/view.php?id=3935','howtopopup','scrollbars=1,height=520,width=620');\" href=\"" .$CFG->wwwroot. "/blocks/howto/view.php?id=3935\">Create a Wiki</a></span>
										</li>
										</ul>";
			} else {
				$this->content->text = '';
			}
	        
	        $this->content->footer = '';
			
	        return $this->content;
		}
	}
?>