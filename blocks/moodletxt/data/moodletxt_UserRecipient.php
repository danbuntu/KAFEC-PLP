<?php

require_once($CFG->dirroot . '/blocks/moodletxt/data/moodletxt_Recipient.php');

/**
 * Data bean for a recipient who has a Moodle user account
 * 
 * ROLL ON MOODLE 2.0 AND PHP 5!
 * 
 * @package datawrappers 
 * @author Greg J Preece <support@txttools.co.uk>
 * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
 * @version 2008111812
 * @since 2008090912
 */
class moodletxt_UserRecipient extends moodletxt_Recipient {

    /**
     * Holds the DB record ID of this user, where known
     * @var int
     */
    var $id = 0;

    /**
     * Holds the Moodle username of this user
     * @var string
     */
    var $username = '';

    /**
     * Constructor - initialises data bean
     * @param string $phoneno Recipient's mobile phone number
     * @param string $firstName Recipient's first name (Recommended)
     * @param string $lastName Recipient's last name (Recommended)
     * @param string $username Recipient's Moodle username (Optional)
     * @param int $id DB record ID of the recipient (Optional)
     * @version 2010062112
     * @since 2008090912
     */
    function moodletxt_UserRecipient($number, $firstName = '', $lastName = '', $username = '', $id = 0) {

        parent::moodletxt_Recipient($number, $firstName, $lastName);
        $this->setUsername($username);
        $this->setId($id);
        
    }

    /**
     * Set the record ID of this recipient, if known
     * @param int $id Recipient's DB record ID
     * @version 2010062112
     * @since 2008090912
     */
    function setId($id) {
        
        if (moodletxt_is_intval($id))    
            $this->id = $id;
        
    }

    /**
     * Set the Moodle username of this recipient
     * @param string $username Recipient's username
     * @version 2010062112
     * @since 2008090912
     */
    function setUsername($username = '') {

        if ($username != '')
            $this->username = $username;

    }
        
    /**
     * Returns the DB record ID of this recipient, if known
     * @return int Recipient ID
     * @version 2010062112
     * @since 2008090912
     */
    function getId() {
        
        return $this->id;
        
    }

    /**
     * Returns the Moodle username of this recipient
     * @return string Recipient's Moodle username
     * @version 2010062112
     * @since 2008090912
     */
    function getUsername() {

        return $this->username;
        
    }

    /**
     * Returns the recipients full name, formatted for screen display
     * @return string Recipient's full name, display formatted
     * @version 2010062112
     * @since 2008090912
     */
    function getFullNameForDisplay() {

        return $this->getLastName() . ', ' . $this->getFirstName() . ' (' . $this->getUsername() . ')';

    }
    
}

?>