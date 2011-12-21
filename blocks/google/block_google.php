<?PHP //$Id: block_google'.php, v1.0 2006/10/26 by Red Morris of Mid-Kent College $
    class block_google extends block_base {
	    function init() {
			$this->version = 20070220;
	    	$this->title =  "Google Search"; 
	    }
	    
		function instance_allow_multiple() {
	    	return false;
	    }
		
		function hide_header() {
			return true;
		}
		
		// Sets the title
		function specialization() {
	    	$this->title =  "Google Search"; 
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
			
			// User is logged in
			$this->content->text = '<center>
<FORM method=GET action="http://www.google.com/search">
<input type=hidden name=ie value=UTF-8>
<input type=hidden name=oe value=UTF-8>
<A HREF="http://www.google.com/search?safe=vss">
<IMG SRC="' . $CFG->wwwroot . '/blocks/google/Google_Safe.gif" border="0" ALT="Google" width="115" height="45" align="absmiddle"></A>
<INPUT TYPE=text name=q size=22 maxlength=255 value="">
<INPUT type=hidden name=safe value=strict>
<INPUT type=submit name=sa value="Google Search">
</FORM>
</center>';
	        
	        $this->content->footer = '';
			
	        return $this->content;
		}
	}
?>