<?php

    /**
     * Data class to hold the details of a message status update.
     * These data classes are used for insertion, removal
     * and transportation of data.  I prefer this to
     * creating them on the fly.
     * (Spot the Java monkey!) Rock on.
     *
     * @package datawrappers
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2010091001
     * @since 2006090112
     */
    class moodletxt_message_status {

        /**
         * Holds whether or not the object instance is valid.
         * This stems from not having a reliable way to prevent
         * object initialisation within the constructor. Roll on PHP 5!
         */
        var $valid;

        /**
         * Holds the record ID of the sent message if known.
         * NOTE: Means absolutely nothing to the txttools system.
         * This is NOT the same as a message ticket.
         */
        var $id;

        /**
         * Holds the id of the message to which this status update belongs.
         * NOTE (Once again): "Entered" messages and sent messages are
         * NOT the same thing.
         */
        var $ticketnumber;

        /**
         * Holds the integer-based status flag for the status update.
         */
        var $status;

        /**
         * Holds a plain-english explanation of the status flag.
         */
        var $statusmessage;

        /**
         * Holds the time at which the status update was received
         */
        var $updatetime;

        /**
         * Class constructor - takes a set of valid values
         * and initialises the data object.
         *
         * @param int messageid The message to which this sent message belongs.
         * @param int status The status flag returned from the system.
         * @param string statusmessage A plain english version of the status flag.
         * @param int updatetime The time at which this update was received
         * @param int id The record ID of this message if known. (Optional)
         * @version 2010090701
         * @since 2006090112
         */
        function moodletxt_message_status($ticketno, $status, $statusmessage, $updatetime, $id = 0) {

            $this->set_ticket($ticketno);
            $this->set_status($status);
            $this->set_status_message($statusmessage);
            $this->set_update_time($updatetime);
            $this->set_id($id);

        }

        /**
         * Returns the DB record ID for this status, if known
         * @return int DB record ID
         * @version 2010062212
         * @since 2006090112
         */
        function get_id() {
            return $this->id;
        }

        /**
         * Returns the message ticket number this status refers to
         * @return int Message ticket
         * @version 2010062212
         * @since 2006090112
         */
        function get_ticket() {
            return $this->ticketnumber;
        }

        /**
         * Returns a txttools status code
         * @return int Status code
         * @version 2010062212
         * @since 2006090112
         */
        function get_status() {
            return $this->status;
        }

        /**
         * Returns a textual description of the status event
         * @return string Status message
         * @version 2010062212
         * @since 2006090112
         */
        function get_status_message() {
            return $this->statusmessage;
        }

        /**
         * Returns the time at which this update occurred
         * @return int Unix timestamp
         * @version 2010062212
         * @since 2006090112
         */
        function get_update_time() {
            return $this->updatetime;
        }

        /**
         * Set the ticket number this status event refers to
         * @param int $ticketnumber Message ticket
         * @version 2010062212
         * @since 2010062212
         */
        function set_ticket($ticketnumber) {
            $this->ticketnumber = $ticketnumber;
        }

        /**
         * Sets the DB record ID for this status event
         * @param int $id DB record ID
         * @version 2010090701
         * @since 2010062212
         */
        function set_id($id) {
            $id = (int) $id;
            
            if ($id > 0)
                $this->id = $id;
        }

        /**
         * Sets the txttools status code for this event
         * @param int $status Status code
         * @version 2010091001
         * @since 2010062212
         */
        function set_status($status) {
            $this->status = (int) $status;
        }

        /**
         * Sets a textual description of this status event
         * @param string $statusmessage Status message
         * @version 2010062212
         * @since 2010062212
         */
        function set_status_message($statusmessage) {
            $this->statusmessage = $statusmessage;
        }

        /**
         * Set the time at which this status update occurred
         * @param int $updatetime Unix timestamp
         * @version 2010090701
         * @since 2010062212
         */
        function set_update_time($updatetime) {
            $updatetime = (int) $updatetime;

            if ($updatetime > 0)
                $this->updatetime = $updatetime;
            
        }

    }

?>
