<?php

    /**
     * Data class to hold the details of a sent message.
     * These data classes are used for insertion, removal
     * and transportation of data.  I prefer this to
     * creating them on the fly.
     * (Spot the Java monkey!) Rock on.
     *
     * @package datawrappers
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2010062212
     * @since 2006082512
     */
    class moodletxt_sentmessage {

        /**
         * Holds whether or not the object instance is valid.
         * This stems from not having a reliable way to prevent
         * object initialisation within the constructor. Roll on PHP 5!
         * @var boolean
         */
        var $valid;

        /**
         * Holds the record ID of the sent message if known.
         * NOTE: Means absolutely nothing to the txttools system.
         * This is NOT the same as a message ticket.
         * @var int
         */
        var $id;

        /**
         * Holds the id of the message to which this sent message belongs.
         * NOTE (Once again): "Entered" messages and sent messages are
         * NOT the same thing.
         * @var int
         */
        var $messageid;

        /**
         * Holds the ticket number that was assigned to this text message
         * by the txttools system.
         * @var int
         */
        var $ticketNumber;

        /**
         * Holds the destination phone number for this text message
         * @var string
         */
        var $destination;

        /**
         * Holds a moodletxt_Recipient object representing the recipient
         * of this sent message
         * @var moodletxt_Recipient
         */
        var $recipientObject;

        /**
         * Class constructor - takes a set of valid values
         * and initialises the data object.
         *
         * @param int $messageid The message to which this sent message belongs.
         * @param int $ticketNumber The ticket number assigned by the txttools system.
         * @param string $destination The phone number that is the message's destination.
         * @param moodletxt_Recipient $recipientObject Object representing the message's recipient
         * @param int $id The record ID of this message if known. (Optional)
         * @version 2010062212
         * @since 2006082512
         */
        function moodletxt_sentmessage($messageid, $ticketNumber, $destination, $recipientObject=null, $id=0) {

            $isvalid = true;

            // Cast required integers to that type
            $messageid = (int) $messageid;
            $ticketNumber = (int) $ticketNumber;
            $id = (int) $id;

            /* Check that the parameters passed are valid */

            // Check integers are in valid range
            if (($messageid <= 0) || ($ticketNumber <= 0) || ($id < 0))
                $isvalid = false;


            // Check destination number against regex
            $destination = str_replace(" ", "", $destination);

            if (! ereg("^[+]{0,1}[0-9]{1,19}$", $destination))
                $isvalid = false;

            // If the object is invalid, set it as such
            if (! $isvalid) {

                $this->valid = false;

            // Otherwise, populate fields
            } else {

                if ($id > 0) {

                    $this->id = $id;

                }

                $this->messageid = $messageid;
                $this->ticketNumber = $ticketNumber;
                $this->destination = $destination;
                $this->recipientObject = $recipientObject;

            }

        }

        /**
         * Returns the destination phone number for this message
         * @return string Destination number
         * @version 2010062212
         * @since 2008111812
         */
        function getDestination() {
            return $this->destination;
        }

        /**
         * Returns the DB record ID of this sent message
         * @return int DB record ID
         * @version 2010062212
         * @since 2008111812
         */
        function getId() {
            return $this->id;
        }

        /**
         * Returns the outbox message ID this
         * SMS is associated with
         * @return int Message ID
         * @version 2010062212
         * @since 2008111812
         */
        function getMessageId() {
            return $this->messageid;
        }

        /**
         * Returns the txttools ticket number
         * assigned to this SMS
         * @return int Ticket number
         * @version 2010062212
         * @since 2008111812
         */
        function getTicketNumber() {
            return $this->ticketNumber;
        }

        /**
         * Returns the recipient of this SMS
         * as a descendant of moodletxt_Recipient
         * @return moodletxt_Recipient
         * @see moodletxt_Recipient
         * @version 2010062212
         * @since 2008082012
         */
        function getRecipientObject() {
            return $this->recipientObject;
        }

        /**
         * Set the DB record ID of this SMS
         * @param int $id DB record ID
         * @version 2010062212
         * @since 2010062212
         */
        function setId($id) {
            $this->id = $id;
        }

        /**
         * Set the ID of the outbox message
         * this SMS is associated with
         * @param int $messageid Message ID
         * @version 2010062212
         * @since 2010062212
         */
        function setMessageid($messageid) {
            $this->messageid = $messageid;
        }

        /**
         * Set the txttools ticket number assigned
         * to this SMS
         * @param int $ticketNumber Ticket number
         * @version 2010062212
         * @since 2010062212
         */
        function setTicketNumber($ticketNumber) {
            $this->ticketNumber = $ticketNumber;
        }

        /**
         * Set the destination phone number
         * for this SMS message
         * @param string $destination Phone number
         * @version 2010062212
         * @since 2010062212
         */
        function setDestination($destination) {
            $this->destination = $destination;
        }

        /**
         * Set the recipient for this SMS (as a descendant
         * of the moodletxt_Recipient object)
         * @param moodletxt_Recipient $recipientObject Recipient object
         * @see moodletxt_Recipient
         * @version 2010062212
         * @since 2010062212
         */
        function setRecipientObject($recipientObject) {
            $this->recipientObject = $recipientObject;
        }

    }

?>
