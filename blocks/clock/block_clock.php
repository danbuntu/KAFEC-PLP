<?PHP //$Id: block_clock'.php, v1.0 2006/10/26 by Red Morris of Mid-Kent College $
    class block_clock extends block_base {
	    function init() {
			$this->version = 20070220;
	    	$this->title =  "Clock"; 
	    }
	    
		function instance_allow_multiple() {
	    	return false;
	    }
		
		function hide_header() {
			return true;
		}
		
		// Sets the title
		function specialization() {
	    	$this->title =  "Clock"; 
	    }
		
	    function get_content() {
		    global $USER, $CFG, $SESSION;
			// Code is run many times, so check if it's run before to save processing
		    if ($this->content !== NULL) {
		        return $this->content;
		    }
			
			$this->content = new stdClass; 
	        
			if (isset($USER->username)) {
				$loginID = $USER->username;
			} else {
				$loginID = '';
			}
			
			// The userid didn't return any records, probably because they don't have an account
			$this->content->text = '<embed pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" 
									src="' . $CFG->wwwroot . '/blocks/clock/ballclock.swf" width="185" height="185" 
									type="application/x-shockwave-flash" wmode="transparent" quality="high" />';
	        
	        $this->content->footer = '';
			
	        return $this->content;
		}
	}
?>