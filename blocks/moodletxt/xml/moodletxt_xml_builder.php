<?php

    require_once($CFG->dirroot . '/blocks/moodletxt/encryption.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/db/sqllib.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/lib.php');

    /**
     * Class to build XML requests used
     * by the MoodleTxt system. XML is constructed from
     * values passed in, and returned to the moodletxt_xml_controller,
     * where it is transmitted via a moodletxt_connector object.
     *
     * Current write was done for MoodleTxt 2.1.  I'm looking at ways of perfecting it.
     *
     * @package xmlconnection
     * @author Greg J Preece, <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011021001
     * @since 2006082512
     */
    class moodletxt_xml_builder {

        /**
         * Holds all XML <Request>s as they are completed
         * @var array(string)
         */
        var $xmlrequests = array();

        /**
         * Holds a buffer of the current <Request> being built
         * @var string
         */
        var $currentrequest = '';

        /**
         * Holds the current authentication block to use
         * when building <Request>s
         * @var string
         */
        var $currentAuthentication = '';

        /**
         * Holds <Message>, <RequestStatus> etc blocks as they are built
         * @var array(string)
         */
        var $xmlblocks = array();

        /**
         * Number of blocks that can be included in a single <Request>
         * Restored to 50 in v2.4/txttools 6.6.2 now that txttools bug
         * #365 has finally been closed
         * @var int
         */
        var $MAX_BLOCKS_PER_REQUEST = 50;

        /**
         * Begins a request block
         * @version 2010062212
         * @since 2007081512
         */
        function openRequest() {

            $this->currentrequest = '<Request>';

        }

        /**
         * End the current request block, adds it to
         * the array of request blocks made, and then
         * resets the current block buffer
         *
         * @version 2007081512
         * @since 2007081512
         */
        function closeRequest() {

            $this->currentrequest .= '
</Request>';
            array_push($this->xmlrequests, $this->currentrequest);
            $this->currentrequest = '';

        }

        /**
         * Method to clear out the current chached requests,
         * blocks and authentication - basically, reset the class
         *
         * @version 2007081512
         * @since 2007081512
         */
        function clearRequests() {

            $this->xmlrequests = array();
            $this->xmlblocks = array();
            $this->currentAuthentication = '';
            $this->currentrequest = '';

        }

        /**
         * Returns the current/completed set of
         * XML requests
         *
         * @version 2007081512
         * @since 2007081512
         */
        function getRequests() {

            return $this->xmlrequests;

        }

        /**
         * Adds the currently active authentication block to
         * the XML request being built.
         *
         * @version 2007081512
         * @since 2007081512
         */
        function appendAuthentication() {

            $this->currentrequest .= $this->currentAuthentication;

        }

        /**
         * Method to add a new XML block to the current cache
         *
         * @param string $requestblock The block to be added to the cache
         * @version 2010062212
         * @since 2007081512
         */
        function addRequestBlock($requestblock) {

            array_push($this->xmlblocks, $requestblock);

        }

        /**
         * Method to build an authentication block for outbound messaging
         *
         * @param int $txttoolsaccount The ID of the txttools account the request is being sent through
         * @version 2011030101
         * @since 2007081512
         */
        function build_outbound_authentication ($txttoolsaccount) {

            $accountobject = get_record('block_mtxt_accounts', 'id', addslashes($txttoolsaccount));

            // Create encryption object and decrypt password
            $encrypt_o_matic = new Encryption();

            // Get EK and decrypt password
            $key = moodletxt_get_setting('EK');
            $password = $encrypt_o_matic->decrypt($key, $accountobject->password);
            unset($encrypt_o_matic);

            // Make this authentication block the one to be used when
            // compiling XML requests
            $this->currentAuthentication = '
    <Authentication>
        <Username><![CDATA[' . $accountobject->username . ']]></Username>
        <Password><![CDATA[' . $password . ']]></Password>
    </Authentication>';

            unset($password);

        }

        /**
         * Replaces name tags in a message with their correct
         * textual values for a given recipient.
         *
         * @param string $messagetext The message text to filter
         * @param moodletxt_Recipient $recipient The recipient object to filter the message by
         * @return string The correctly filtered message text
         * @version 2010062212
         * @since 2007081512
         */
        function filterForTags ($messagetext, $recipient) {

            // Swap in name binds
            $messagetext = str_replace('%FIRSTNAME%', $recipient->getFirstName(), $messagetext);
            $messagetext = str_replace('%LASTNAME%', $recipient->getLastName(), $messagetext);
            $messagetext = str_replace('%FULLNAME%', $recipient->getFullName(), $messagetext);
                    
            return $messagetext;

        }

        /**
         * Method builds the individual <Message> blocks to be included
         * in the message being built.  These blocks are cached into an array,
         * then compiled into <Request> blocks later on.
         *
         * @param moodletxt_message $message The message object to base the block on
         * @param array(string => moodletxt_Recipient) $messagerecipients Message recipients (number => recipient)
         * @version 2010062212
         * @since 2007081512
         */
        function build_text_message_blocks ($message, $messagerecipients) {

            // Loop over recipients and create message blocks
            foreach ($messagerecipients as $recipient) {

                $messagetext = trim(stripslashes($message->get_message_text()));
                $messagetext = $this->filterForTags($messagetext, $recipient);

                // Chunk message text into blocks of 160 chars
                $messagechunks = str_split($messagetext, 160);

                // Build message blocks
                foreach($messagechunks as $thismessagetext) {

                    $unicodeFrag = ($message->isSuppressUnicode()) ? 'TRUE' : 'FALSE';

                    $this->addRequestBlock('
    <Message>
        <MessageText><![CDATA[' . $thismessagetext . ']]></MessageText>
        <Phone><![CDATA[' . $recipient->getNumber() . ']]></Phone>
        <Type>' . $message->get_type() . '</Type>
        <MessageDate>' . $message->get_scheduled_time() . '</MessageDate>
        <UniqueID>' . $message->get_id() . '</UniqueID>
        <SuppressUnicode><![CDATA[' . $unicodeFrag . ']]></SuppressUnicode>
    </Message>');

                }

            }

        }

        /**
         * Method to build an XML request to send messages.
         *
         * @param moodletxt_message $message The message object to be sent.
         * @param array(string => moodletxt_Recipient) $recipients Message recipients (number => recipient)
         * @return string The completed XML packet to be sent.
         * @version 2010062212
         * @since 2007081012
         */
        function build_message ($message, $recipients) {

            // Set the object into "outbound mode" by building some outbound authentication
            $this->build_outbound_authentication($message->get_txttools_account());

            $this->build_text_message_blocks($message, $recipients);

            return $this->compile_xml_requests();

        }

        /**
         * Method to build <RequestStatus> blocks for inclusion
         * in the XML requests being built.
         *
         * @param array(int) $messagetickets Ticket numbers
         * @version 2010062212
         * @since 2007081512
         */
        function build_status_request_blocks($messagetickets) {

            foreach($messagetickets as $ticket) {

                $this->addRequestBlock('
    <RequestStatus>
        <Ticket>' . $ticket . '</Ticket>
    </RequestStatus>');


            }

        }


        /**
         * Method to build an XML request to retrieve the
         * status of a given sent message or messages
         *
         * @param array $messagetickets  Message tickets
         * @param int $txttoolsaccount Txttools account ID
         * @return array(string) Built XML packets
         * @version 2010062212
         * @since 2006101012
         */
        function build_status_request ($messagetickets, $txttoolsaccount) {

            // Get authentication
            $this->build_outbound_authentication($txttoolsaccount);

            // Build  status blocks
            $this->build_status_request_blocks($messagetickets);

            return $this->compile_xml_requests();

        }

        /*
         * Method to build a dummy authentication-only packet,
         * which is used to validate txttools member details
         * prior to insertion/update
         *
         * @param string $testUser The username to test
         * @param string $testPass The password to test
         * @return array(string) Contains the dummy XML request
         * @version 2010062212
         * @since 2007011512
         */
        function build_dummy_request ($testUser, $testPass) {

            $xmlrequests = array();

            $request = trim('
<Request>
    <Authentication>
        <Username><![CDATA[' . $testUser . ']]></Username>
        <Password><![CDATA[' . $testPass . ']]></Password>
    </Authentication>
</Request>');

            array_push($xmlrequests, $request);

            return $xmlrequests;

        }

        /**
         * Method adds a credit request block to those
         * being included in the current request
         * @version 2011021001
         * @since 2010062512
         */
        function build_credit_request_block() {

            $this->addRequestBlock('
    <AccountDetails>
        <GetAccountDetails>
		<![CDATA[TRUE]]>
	</GetAccountDetails>
    </AccountDetails>');

        }

        /**
         * Method builds a credit request for a given txttools account
         * @param int $txttoolsaccount Txttools account ID
         * @return array(string) Built XML packets
         * @version 2010062512
         * @since 2010062512
         */
        function build_credit_request($txttoolsaccount) {

            $this->build_outbound_authentication($txttoolsaccount);

            $this->build_credit_request_block();

            return $this->compile_xml_requests();

        }

        /**
         * Method to build an <Authentication> block for
         * inbound connection/transfer
         * NOTE: Make sure that the password is decrypted
         *
         * @param object $useraccount The user account object to use
         * @version 2011030101
         * @since 2007081512
         */
        function build_inbound_authentication($useraccount) {

            // Create encryption object and decrypt password
            $encrypt_o_matic = new Encryption();

            // Get EK
            $key = moodletxt_get_setting('EK');
            $password = $encrypt_o_matic->decrypt($key, $useraccount->password);
            unset($encrypt_o_matic);

            // Set authentication block
            $this->currentAuthentication = '
    <Authentication>
        <Username><![CDATA[' . $useraccount->username . ']]></Username>
        <Password><![CDATA[' . $password . ']]></Password>
    </Authentication>';

            unset($password);
        }


        /**
         * Method to build an XML request to get inbound
         * messages for a given txttools account.
         *
         * @param object $useraccount The user account object to use
         * @version 2010062212
         * @since 2007030812
         */
        function get_inbound_messages($useraccount) {

            // Set the object to "inbound mode" by setting the auth block
            $this->build_inbound_authentication($useraccount);

            $lastUpdate = (int) moodletxt_get_setting('Inbound_Last_Update');

            // Add inbound request block
            $this->addRequestBlock('
    <RetrieveInbound>
        <RetrieveSince><![CDATA[' . $lastUpdate . ']]></RetrieveSince>
        <RetrieveType><![CDATA[All]]></RetrieveType>
        <RetrieveNumber><![CDATA[' . $this->MAX_BLOCKS_PER_REQUEST . ']]></RetrieveNumber>
    </RetrieveInbound>');

            return $this->compile_xml_requests();

        }

        /**
         * Function compiles the cached blocks and auth details
         * into a set of properly formatted XML requests, then
         * returns an array of these requests to the calling method
         *
         * @return array(string) An array of completed XML requests to be sent to txttools
         * @version 2010062212
         * @since 2007081412
         */
        function compile_xml_requests() {

            // Chunk XML blocks
            $chunkedBlocks = array_chunk($this->xmlblocks, $this->MAX_BLOCKS_PER_REQUEST);

            // Loop through chunks and build a request for each
            foreach($chunkedBlocks as $blockChunk) {

                // Begin request
                $this->openRequest();

                // Add on outbound authentication
                $this->appendAuthentication();

                // Append blocks
                foreach($blockChunk as $block) {

                    $this->currentrequest .= $block;

                }

                // Close request
                $this->closeRequest();

            }

            // Get requests from array prior to wipe
            $finalrequests = $this->getRequests();

            // Reset object
            $this->clearRequests();

            return $finalrequests;

        }

    }

?>