<?php

require_once($CFG->dirroot . '/blocks/moodletxt/data/moodletxt_Recipient.php');

/**
 * Data bean for a recipient added on-the-fly
 * 
 * ROLL ON MOODLE 2.0 AND PHP 5!
 * 
 * @package datawrappers 
 * @author Greg J Preece <support@txttools.co.uk>
 * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
 * @version 2010062112
 * @since 2008090912
 */
class moodletxt_AdditionalRecipient extends moodletxt_Recipient {        

    /**
     * Constructor - initialises data bean
     * @param string $phoneno Recipient's mobile phone number
     * @param string $firstName Recipient's first name (Recommended)
     * @param string $lastName Recipient's last name (Recommended)
     * @version 2010062112
     * @since 2008090912
     */
    function moodletxt_AdditionalRecipient($phoneno, $firstName = '', $lastName = '') {
        
        parent::moodletxt_Recipient($phoneno, $firstName, $lastName);
                
    }
        
}

?>