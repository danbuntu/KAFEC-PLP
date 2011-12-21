<?php

/**
 * Parent data bean for message recipients - all
 * representation of Moodle users, address book contacts, 
 * etc inherit from this class.
 * 
 * ROLL ON MOODLE 2.0 AND PHP 5!
 * 
 * @package datawrappers 
 * @author Greg J Preece <support@txttools.co.uk>
 * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
 * @version 2010062112
 * @since 2008090912
 */ 
class moodletxt_Recipient {

    /**
     * Holds the destination phone number
     * @var string
     */
    var $recipientNumber = '+44';

    /**
     * Holds the recipient's first name
     * @var string
     */
    var $firstName = '';

    /**
     * Holds the recipient's last name
     * @var string
     */
    var $lastName = '';

    /**
     * Initialises the data bean
     * @param string $number Recipient's phone number (Required)
     * @param string $firstName Recipient's first name (Optional)
     * @param string $lastName Recipient's last name (Optional)
     * @version 2009031012
     * @since 2008090912
     */
    function moodletxt_Recipient($number, $firstName = '', $lastName = '') {
        
        $this->setNumber($number);
        $this->setFirstName($firstName);
        $this->setLastName($lastName);
        
    }

    /**
     * Performs checks on a passed destination number and sets it
     * @param string $number Recipient's phone number
     * @version 2010062112
     * @since 2008090912
     */
    function setNumber($number) {

        if (substr($number, 0, 2) == '07')
            $number = '+44' . substr($number, 1);

        if (substr($number, 0, 4) == '0044')
            $number = '+44' . substr($number, 4);

        $this->recipientNumber = $number;
        
    }

    /**
     * Sets the first name of this recipient
     * @param string $firstName Recipient's first name
     * @version 2008090912
     * @since 2008090912
     */
    function setFirstName($firstName = '') {

        if ($firstName != '')
            $this->firstName = $firstName;

    }

    /**
     * Sets the last name of this recipient
     * @param string $lastName Recipient's last name
     * @version 2008090912
     * @since 2008090912
     */
    function setLastName($lastName = '') {

        if ($lastName != '')
            $this->lastName = $lastName;

    }

    /**
     * Gets the phone number of this recipient
     * @return string Recipient's phone number
     * @version 2008090912
     * @since 2008090912
     */
    function getNumber() {
        
        return $this->recipientNumber;
        
    }

    /**
     * Gets the first name of this recipient
     * @return string Recipient's first name
     * @version 2008090912
     * @since 2008090912
     */
    function getFirstName() {

        return $this->firstName;

    }

    /**
     * Gets the last name of this recipient
     * @return string Recipient's last name
     * @version 2008090912
     * @since 2008090912
     */
    function getLastName() {

        return $this->lastName;

    }

    /**
     * Uses the first and last name fields to generate
     * a full name for this recipient, to be used
     * in message tags
     * @return string Recipient's full name
     * @version 2008090912
     * @since 2008090912
     */
    function getFullName() {

        return $this->getFirstName() . ' ' . $this->getLastName();

    }

    /**
     * Returns a full-name string to be used
     * in page table displays
     * @return string Recipient's full name, display formatted
     * @version 2008090912
     * @since 2008090912
     */
    function getFullNameForDisplay() {

        return $this->getLastName() . ', ' . $this->getFirstName();

    }

    
}

?>