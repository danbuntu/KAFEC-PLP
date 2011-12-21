<?php

 /**
  * Block-specific stylesheet for MoodleTxt
  *
  * @author Greg J Preece <support@txttools.co.uk>
  * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
  * @version 2010070812
  * @since 2007021412
  */

require_once($CFG->dirroot . '/blocks/moodletxt/style/jquery-ui.css');

// Check for Internet Exploder in all its many horrific incarnations
if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.') !== FALSE)
    require_once($CFG->dirroot . '/blocks/moodletxt/style/sendmessage_exploder6.css');

/*else if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.') !== FALSE)
    require_once($CFG->dirroot . '/blocks/moodletxt/style/sendmessage_exploder7.css');

else if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.') !== FALSE)
    require_once($CFG->dirroot . '/blocks/moodletxt/style/sendmessage_exploder8.css');*/
    
else
    require_once($CFG->dirroot . '/blocks/moodletxt/style/sendmessage_default.css');

require_once($CFG->dirroot . '/blocks/moodletxt/style/moodletxt.css');

?>