<?php

    /**
     * Data class to hold the details of a text message.
     * These data classes are used for insertion, removal
     * and transportation of data. I prefer
     * this to creating them on the fly.
     * (Spot the Java monkey!) Rock on.
     *
     * @package datawrappers
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2010062212
     * @since 2006082512
     */
    class moodletxt_message {

        /**
         * Holds whether or not the object instance is valid.
         * This stems from not having a reliable way to prevent
         * object initialisation within the constructor. Roll on PHP 5!
         * @var bool
         */
        var $valid;

        /**
         * Holds the record ID of the message if known.
         * NOTE: Means absolutely nothing to the txttools system.
         * This is NOT the same as a message ticket.
         * @var int
         */
        var $id;

        /**
         * Holds the ID of the txttools account that
         * the message was sent from.
         * @var int
         */
        var $txttoolsaccount;

        /**
         * Holds the ID of the user who sent the message
         * @var int
         */
        var $userid;

        /**
         * Holds the text of the message sent.
         * @var string
         */
        var $messagetext;

        /**
         * Holds the time that the message was sent at
         * @var int
         */
        var $timesent;

        /**
         * Holds the time that the message was scheduled for
         * @var int
         */
        var $scheduledfor;

        /**
         * Holds the type of message being sent:
         * @see $validtypes
         * @var int
         */
        var $type;

        /**
         * Holds an array of valid message types
         * 1 - Bulk
         * 2 - Reverse charged
         * 3 - Interactive bulk
         */
        var $validtypes = array(1, 2, 3);

        /**
         * If set to 1, this message will be restricted
         * at txttools to *only* contain characters
         * in the GSM 03.38 character set
         * @var int
         */
        var $suppressUnicode = 0;

        /**
         * Class constructor - takes a set of valid values
         * and initialises the data object.
         *
         * @param int $id The record ID of the message if known. (Therefore optional)
         * @param int $txttoolsaccount The txttools account this message was sent through
         * @param int $userid The ID of the user who sent the message
         * @param string $messagetext The text of the message being sent
         * @param int $timesent The time at which the message was sent
         * @param int $scheduledfor The time the message was scheduled for (Optional)
         * @param boolean $suppressUnicode Whether the message should be GSM 03.38 only (Optional)
         * @param int $type The type of message being sent.
         * @see $validtypes
         * @version 2010062112
         * @since 2006082512
         */
        function moodletxt_message($txttoolsaccount, $userid, $messagetext, $timesent, $type,
                                    $scheduledfor = 0, $suppressUnicode = 0, $id = 0) {

            $isvalid = true;

            // Cast required integers to that type
            $txttoolsaccount = (int) $txttoolsaccount;
            $timesent = (int) $timesent;
            $type = (int) $type;
            $scheduledfor = (int) $scheduledfor;
            $id = (int) $id;
            $userid = (int) $userid;
            $suppressUnicode = (int) $suppressUnicode;

            /* Check that the parameters passed are valid */

            // Check integers are in valid range
            if (($txttoolsaccount <= 0) || ($timesent <= 0) ||
                ($scheduledfor < 0) || ($id < 0) || ($userid <= 0)) {

                $isvalid = false;

            }

            // Check message type is valid
            if (! in_array($type, $this->validtypes))
                $isvalid = false;


            // Check length of message
            $messagelength = strlen($messagetext);

            if (($messagelength == 0) || ($messagelength > 1600))
                $isvalid = false;


            // If object is invalid, set it as such
            if (! $isvalid) {

                $this->valid = false;

            // Otherwise, populate fields
            } else {

                // If no scheduled time specified, set it to time sent
                $scheduledfor = ($scheduledfor == 0) ? $scheduledfor = $timesent : $scheduledfor;

                $this->set_id($id);
                $this->setTxttoolsaccount($txttoolsaccount);
                $this->setTimesent($timesent);
                $this->setType($type);
                $this->setScheduledfor($scheduledfor);
                $this->setMessagetext($messagetext);
                $this->setUserid($userid);
                $this->setSuppressUnicode($suppressUnicode);

            }

        }

        /*
          ############################################################
          # GETTERS
          ############################################################
        */

        /**
         * Function to return the message's record ID
         *
         * @return int The record ID of this message
         * @version 2010062112
         * @since 2006092912
         */
        function get_id() {
            return $this->id;
        }

        /**
         * Returns the txttools account this message
         * was sent through
         *
         * @return int The ID of the txttools account used
         * @version 2010062112
         * @since 2010061512
         */
        function get_txttools_account() {
            return $this->txttoolsaccount;
        }

        /**
         * Returns the ID of the user who
         * sent this message
         *
         * @return int
         * @version 2010062112
         * @since 2010061512
         */
        function get_user_id() {
            return $this->userid;
        }

        /**
         * Function to return the actual text of the
         * message being sent
         *
         * @return string The message text being sent.
         * @version 2010062112
         * @since 2006092912
         */
        function get_message_text() {
            return $this->messagetext;
        }

        /**
         * Function to return the time at which this
         * message was sent
         *
         * @return int The time the message was sent at
         * @version 2010062112
         * @since 2006092912
         */
        function get_time_sent() {
            return $this->timesent;
        }

        /**
         * Function to get the time this message
         * is scheduled to be sent at
         *
         * @return int The scheduled time for sending
         * @version 2010062112
         * @since 2006092912
         */
        function get_scheduled_time() {
            return $this->scheduledfor;
        }

        /**
         * Returns this message's type (bulk, interactive, etc)
         * @return int Message type
         * @version 2010062112
         * @since 2006082512
         */
        function get_type() {
            return $this->type;
        }

        /**
         * Returns whether or not this message is being restricted
         * to the GSM 03.38 character set
         * @return boolean Is UTF-8 suppressed?
         * @version 2010062212
         * @since 2010062212
         */
        function isSuppressUnicode() {
            return $this->suppressUnicode > 0;
        }

        /*
          ############################################################
          # SETTERS
          ############################################################
        */

        /**
         * Sets the DB record ID of this message
         * @param int $id DB record ID
         * @return boolean Whether or not the set succeeded
         * @version 2010062112
         * @since 2006082512
         */
        function set_id($id) {

            if ($id <= 0) {

                return false;

            } else {

                $this->id = $id;

                return true;

            }

        }

        /**
         * Sets the ID of the txttools account this message was sent via
         * @param int $txttoolsaccount Txttools account ID
         * @version 2010062112
         * @since 2010062112
         */
        function setTxttoolsaccount($txttoolsaccount) {
            $this->txttoolsaccount = $txttoolsaccount;
        }

        /**
         * Sets the ID of the Moodle user that sent this message
         * @param int $userid User ID
         * @version 2010062112
         * @since 2010062112
         */
        function setUserid($userid) {
            if ($userid > 0)
                $this->userid = $userid;
        }

        /**
         * Sets the text of this message
         * @param string $messagetext Message text
         * @version 2010062112
         * @since 2010062112
         */
        function setMessagetext($messagetext) {
            $this->messagetext = $messagetext;
        }

        /**
         * Sets the time at which this message was sent from moodletxt
         * @param int $timesent Unix timestamp
         * @version 2010062112
         * @since 2010062112
         */
        function setTimesent($timesent) {
            $this->timesent = $timesent;
        }

        /**
         * Sets the time at which this message was scheduled to be sent from txttools
         * @param int $scheduledfor Unix timestamp
         * @version 2010062112
         * @since 2010062112
         */
        function setScheduledfor($scheduledfor) {
            $this->scheduledfor = $scheduledfor;
        }

        /**
         * Sets the type of message being sent (bulk, interactive, etc)
         * @param int $type Message type
         * @see $this->validtypes
         * @version 2010062112
         * @since 2010062112
         */
        function setType($type) {
            if (in_array($type, $this->validtypes))
                $this->type = $type;
        }

        /**
         * Set whether or not this message should be
         * restricted to the GSM 03.38 character set
         * @param boolean $suppressUnicode Suppress Unicode?
         * @version 2010062212
         * @since 2010062212
         */
        function setSuppressUnicode($suppressUnicode) {
            $this->suppressUnicode = ($suppressUnicode) ? 1 : 0;
        }

    }

?>
