<?php

    /**
     * Data class to hold the details of an inbound message.
     * These data classes are used for insertion, removal
     * and transportation of data.  I prefer this to
     * creating them on the fly.
     * (Spot the Java monkey!) Rock on.
     *
     * @package datawrappers
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2010090601
     * @since 2007031212
     */
    class moodletxt_inbound_message {

        /**
         * Holds whether or not the object instance is valid.
         * This stems from not having a reliable way to prevent
         * object initialisation within the constructor. Roll on PHP 5!
         * @var boolean
         */
        var $valid;

        /**
         * Holds the record ID of the inbound message if known.
         * NOTE: Means absolutely nothing to the txttools system.
         * This is NOT the same as a message ticket.
         * @var int
         */
        var $id;

        /**
         * Holds the IDs of the folders in which this message belongs (optional)
         * @var array(int)
         */
        var $folderids = array();


        /**
         * Holds the ticket number that was assigned to this text message
         * by the txttools system.
         * @var int
         */
        var $ticketnumber;

        /**
         * Holds the text of the message received
         * @var string
         */
        var $messagetext;

        /**
         * Holds the source phone number for this text message
         * @var string
         */
        var $source;

        /**
         * Holds the name of the source for this message, if known
         * @var string
         */
        var $sourcename;

        /**
         * Holds the timestamp at which this message was received by txttools
         * NOTE: By txttools, NOT moodletxt
         * @var int
         */
        var $timereceived;

        /**
         * Holds whether or not this message has been read (1 or 0 - for DB)
         * @var int
         */
        var $read;

        /**
         * Holds the number the message was sent to
         * @var string
         */
        var $destinationNumber;

        /**
         * Holds the account ID the message was sent to
         * @var int
         */
        var $destinationAccountID;

        /**
         * Holds the account username the message was sent to
         * @var string
         */
        var $destinationAccountUsername;

        /**
         * Class constructor - takes a set of valid values
         * and initialises the data object.
         *
         * @param int $ticketnumber The ticket number assigned by the txttools system.
         * @param string $messagetext The text of the inbound message
         * @param string $source The phone number that the message came from
         * @param int $timereceived The time at which the message was received
         * @param int $read Whether or not the message has been read
         * @param int $folderid The folder to which this inbound message belongs. (Optional)
         * @param int $id The record ID of this message if known. (Optional)
         * @version 2010090601
         * @since 2007031212
         */
        function moodletxt_inbound_message($ticketnumber, $messagetext, $source, $timereceived,
                                        $read, $folderids = array(), $id = 0) {

            $this->sourcename = get_string('configdefaultsource', 'block_moodletxt');

            $this->set_id($id);
            $this->setFolderids($folderids);
            $this->setTicketnumber($ticketnumber);
            $this->setMessagetext($messagetext);
            $this->setSource($source);
            $this->setTimereceived($timereceived);
            $this->set_read($read);

        }

        /**
         * Get the DB record ID for this message
         * @return int Message DB record ID
         * @version 2010062112
         * @since 2007031212
         */
        function get_id() {
            return $this->id;
        }

        /**
         * Gets IDs of destination message folders
         * @return array(int) Message folder IDs
         * @version 2010062112
         * @since 2007031212
         */
        function get_folders() {
            return $this->folderids;
        }

        /**
         * Get the number of destination folders
         * for this message
         * @return int Number of destination folders
         * @version 2010062112
         * @since 2007031212
         */
        function get_folder_count() {
            return count($this->folderids);
        }

        /**
         * Get the ticket number for this message
         * @return int Message ticket
         * @version 2010062112
         * @since 2007032412
         */
        function get_ticket_number() {
            return $this->ticketnumber;
        }

        /**
         * Get the text of this inbound message
         * @return string Message text
         * @version 2010062112
         * @since 2007032412
         */
        function get_message_text() {
            return $this->messagetext;
        }

        /**
         * Get the name of the person
         * who sent in the message
         * @return string Source name
         * @version 2010062112
         * @since 2007032412
         */
        function get_source_name() {
            return $this->sourcename;
        }

        /**
         * Get the timestamp when this message
         * was received by the txttools sytem
         * @return int Time received (unix timestamp)
         * @version 2010062112
         * @since 2007032412
         */
        function get_time_received() {
            return $this->timereceived;
        }

        /**
         * Wipe the list of destination folders
         * for this message
         * @version 2010062112
         * @since 2007031212
         */
        function clear_folders() {
            $this->folderids = array();
        }

        /**
         * Get objects to be written to the database
         * @return array(stdClass) Message DB objects
         * @version 2010062112
         * @since 2007031212
         */
        function get_writeable_objects() {

            $returnarray = array();

            foreach($this->folderids as $folderid) {

                $writeobj = new stdClass;
                $writeobj->id = $this->get_id();
                $writeobj->folderid = $folderid;
                $writeobj->ticket = $this->get_ticket_number();
                $writeobj->messagetext = addslashes($this->get_message_text());
                $writeobj->source = addslashes($this->get_source());
                $writeobj->sourcename = addslashes($this->get_source_name());
                $writeobj->timereceived = $this->get_time_received();
                $writeobj->hasbeenread = $this->get_read();

                array_push($returnarray, $writeobj);

            }

            return $returnarray;

        }

        /**
         * Return whether or not this message has
         * been read (as an int, not a bool)
         * @return int Integer representing read state
         * @version 2010062112
         * @since 2007031212
         */
        function get_read() {
            return $this->read;
        }

        /**
         * Get the phone number that this SMS
         * message came from
         * @return string Source phone number
         * @version 2010062112
         * @since 2007032412
         */
        function get_source() {
            return $this->source;
        }

        /**
         * Get the phone number this message was sent to
         * @return string Destination phone number
         * @version 2010062112
         * @since 2009110112
         */
        function getDestinationNumber() {
            return $this->destinationNumber;
        }

        /**
         * Get the ID of the txttools account this message was sent to
         * @return int Txttools account ID
         * @version 2010062112
         * @since 2009110112
         */
        function getDestinationAccountID() {
            return $this->destinationAccountID;
        }

        /**
         * Get the username of the txttools account this message was sent to
         * @return string Txttools account username
         * @version 2010062112
         * @since 2009110112
         */
        function getDestinationAccountUsername() {
            return $this->destinationAccountUsername;
        }

        /**
         * Add a destination folder for this message
         * @param int $folderid Folder ID to add
         * @return boolean
         * @version 2010062112
         * @since 2007031212
         */
        function add_folder($folderid) {

            $folderid = (int) $folderid;

            // Check folder ID is valid
            if (! is_numeric($folderid)) {

                return false;

            }

            // Check folder ID wasn't already added
            if ($folderid > 0) {

                if (! in_array($folderid, $this->folderids)) {

                    array_push($this->folderids, $folderid);

                }

                return true;

            } else {

                return false;

            }

        }

        /**
         * Allows an array of folder IDs to be added to the message at once
         * @param array(int) $folderids Folder IDs to add
         * @return boolean
         * @version 2010062112
         * @since 2009110112
         */
        function add_folders($folderids) {

            if (! is_array($folderids))
                $this->add_folder($folderids);

            $result = true;

            foreach ($folderids as $folderid)
                $result = $result && $this->add_folder($folderid);

            return $result;

        }

        /**
         * Set the DB record ID for this message
         * @param int $id Database record ID
         * @return boolean
         * @version 2010090601
         * @since 2007031212
         */
        function set_id($id) {

            $id = (int) $id;

            // Check Id is valid
            if (! is_numeric($id))
                return false;


            if ($id > 0)
                $this->id = $id;

        }

        /**
         * Set whether or not this message
         * has been read by the user
         * @param int $read Integer representing read state
         * @return boolean Whether or not the set was accepted
         * @version 2010090601
         * @since 2007031212
         */
        function set_read($read) {

            $read = (int) $read;

            if (! is_numeric($read))
                return false;

            if ($read == 0 || $read == 1)
                $this->read = $read;

        }

        /**
         * Set the name of the person who
         * sent this SMS message in
         * @param string $newname Source name
         * @version 2010062112
         * @since 2007032412
         */
        function set_source_name($newname) {

            if ($newname != '')
                $this->sourcename = $newname;

        }

        /**
         * Set the phone number that this message was sent to
         * @param string $destinationNumber Destination phone number
         * @version 2010062112
         * @since 2009110112
         */
        function setDestinationNumber($destinationNumber) {
            $this->destinationNumber = $destinationNumber;
        }

        /**
         * Set the ID of the txttools account this message was sent to
         * @param int $destinationAcountID Destination txttools account ID
         * @version 2010062112
         * @since 2009110112
         */
        function setDestinationAccountID($destinationAcountID) {
            $this->destinationAccountID = $destinationAcountID;
        }

        /**
         * Set the username of the txttools account this message was sent to
         * @param string $destinationAccountUsername Destination txttools account username
         * @version 2010062112
         * @since 2009110112
         */
        function setDestinationAccountUsername($destinationAccountUsername) {
            $this->destinationAccountUsername = $destinationAccountUsername;
        }

        /**
         * Overwrite the existing folder IDs assigned to this message
         * with new ones
         * @param array(int) $folderids New folder IDs
         * @version 2010062112
         * @since 2010062112
         */
        function setFolderids($folderids) {
            $this->folderids = $folderids;
        }

        /**
         * Set txttools ticket number for this message
         * @param int $ticketnumber Txttools ticket number
         * @version 2010062112
         * @since 2010062112
         */
        function setTicketnumber($ticketnumber) {
            $this->ticketnumber = $ticketnumber;
        }

        /**
         * Set the text of this inbound message
         * @param string $messagetext Message text
         * @version 2010062112
         * @since 2010062112
         */
        function setMessagetext($messagetext) {
            $this->messagetext = $messagetext;
        }

        /**
         * Set the phone number this message came from
         * @param string $source Source phone number
         * @version 2010062112
         * @since 2010062112
         */
        function setSource($source) {
            $this->source = $source;
        }

        /**
         * Set the time at which this message was received.
         * <b>NOTE: Received by txttools, NOT moodletxt</b>
         * @param int $timereceived Unix timestamp - time received
         */
        function setTimereceived($timereceived) {
            $this->timereceived = $timereceived;
        }

    }

?>