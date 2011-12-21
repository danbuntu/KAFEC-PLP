<?PHP //$Id: block_suggestionbox'.php, v1.0 2008/02/06 by Red Morris of Mid-Kent College $
    class block_suggestionbox extends block_base {
	    function init() {
			$this->version = 20080206;
	    	$this->title =  "Suggestion Box"; 
	    }
	    
		function instance_allow_multiple() {
	    	return false;
	    }
		
		function hide_header() {
			return false;
		}
		
		// Sets the title
		function specialization() {
	    	$this->title =  "Suggestion Box"; 
	    }
		
	    function get_content() {
		    global $USER, $CFG, $SESSION;
			// Code is run many times, so check if it's run before to save processing
		    if ($this->content !== NULL) {
		        return $this->content;
		    }
			
			$this->content = new stdClass; 
	        
			if (isset($USER->username)) {
				// only 2 possible contexts, site or course
				if ($this->instance->pageid == SITEID) { // site context
					  $currentcontext = get_context_instance(CONTEXT_SYSTEM, SITEID);
				} else { // course context
					$currentcontext = get_context_instance(CONTEXT_COURSE, $this->instance->pageid);
				}
				
				if (has_capability('moodle/course:update', $currentcontext)) {
					// Teacher
					$numofrecords = count_records('block_suggestionbox', 'courseid', $this->instance->pageid);
					$this->content->text = "<img src=\"".$CFG->pixpath. "/t/email.gif\"> <a title=\"Submit Suggestion\" onclick=\"return openpopup('/blocks/suggestionbox/view.php?id=".$this->instance->pageid."action=submit','suggestionbox','scrollbars=1,height=460,width=620');\" href=\"" .$CFG->wwwroot. "/blocks/suggestionbox/view.php?id=".$this->instance->pageid."&action=view\">Suggestions ($numofrecords)</a>";
				} else {
					// Student
					$this->content->text = "<a title=\"Submit Suggestion\" onclick=\"return openpopup('/blocks/suggestionbox/view.php?id=".$this->instance->pageid."action=submit','suggestionbox','scrollbars=1,height=460,width=620');\" href=\"" .$CFG->wwwroot. "/blocks/suggestionbox/view.php?id=".$this->instance->pageid."&action=submit\">Click here</a> to make suggestions to your tutors anonymously.</br>";
				}
			} else {
				$this->content->text = '';
			}
				        
	        $this->content->footer = '';
			
	        return $this->content;
		}
	}
?>