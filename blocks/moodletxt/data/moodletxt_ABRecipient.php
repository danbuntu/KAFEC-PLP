<?php

require_once($CFG->dirroot . '/blocks/moodletxt/data/moodletxt_Recipient.php');

/**
 * Data bean for a recipient who has an entry in the address book
 * 
 * ROLL ON MOODLE 2.0 AND PHP 5!
 * 
 * @package datawrappers 
 * @author Greg J Preece <support@txttools.co.uk>
 * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
 * @version 2010062112
 * @since 2008090912
 */
class moodletxt_ABRecipient extends moodletxt_Recipient {
    
    var $contactID = 0;
    var $companyName = '';

    /**
     * Constructor - initialises data bean
     * @param string $phoneno Recipient's mobile phone number
     * @param string $firstName Recipient's first name (Recommended)
     * @param string $lastName Recipient's last name (Recommended)
     * @param string $companyName Name of the company the recipient represents (Optional)
     * @param int $id DB record ID of the recipient (Optional)
     * @version 2010062112
     * @since 2008090912
     */
    function moodletxt_ABRecipient($phoneno, $firstName = '', $lastName = '', $companyName = '', $id = 0) {

        // Call super-constructor with common info
        parent::moodletxt_Recipient($phoneno, $firstName, $lastName);

        // Set bean properties
        $this->setCompany($companyName);
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
            $this->contactID = $id;
        
    }

    /**
     * Set the name of the company this recipient represents
     * @param string $name Company name
     * @version 2010062112
     * @since 2008090912
     */
    function setCompany($name) {
        
        if ($name != '')
            $this->companyName = $name;
        
    }

    /**
     * Returns the DB record ID of this recipient, if known
     * @return int Recipient ID
     * @version 2010062112
     * @since 2008090912
     */
    function getId() {
        
        return $this->contactID;
        
    }    

    /**
     * Returns the name of the company this recipient represents
     * @return string Company name
     * @version 2010062112
     * @since 2008090912
     */
    function getCompany() {
        
        return $this->companyName;
        
    }

    /**
     * Returns the recipient's full name, as computed from other values stored
     * @return string Recipient's full name
     * @version 2010062112
     * @since 2008090912
     */
    function getFullName() {
        
        if ($this->getFirstName() != '' || $this->getLastName() != '')
            return $this->getFirstName() . ' ' . $this->getLastName();
        else if ($this->getCompany() != '')
            return $this->getCompany();
        else
            return '';
        
    }

    /**
     * Returns the recipients full name, formatted for screen display
     * @return string Recipient's full name, display formatted
     * @version 2010062112
     * @since 2008090912
     */
    function getFullNameForDisplay() {

        if ($this->getFirstName() != '' || $this->getLastName() != '')
            return $this->getLastName() . ', ' . $this->getFirstName();
        else if ($this->getCompany() != '')
            return $this->getCompany();
        else
            return '';

    }
    
}

?>
