<?php

    require_once($CFG->dirroot . '/blocks/moodletxt/xml/moodletxt_xml_builder.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/xml/moodletxt_xml_parser.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/xml/moodletxt_connector.php');

    /**
     * Class to control the construction and
     * parsing of XML moving in and out of the system.
     * 1 - A moodletxt_xml_builder object creates the XML request packet.
     * 2 - A connector object transmits this and returns the result.
     * 3 - A moodletxt_xml_parser object parses the returned XML.
     *
     * @package xmlconnection
     * @author Greg J Preece, <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2010062912
     * @since 2006081012
     */
    class moodletxt_xml_controller {

        /**
         * Holds reference to an moodletxt_xml_builder object,
         * used in building XML request packets.
         * @var moodletxt_xml_builder
         */
        var $builder;

        /**
         * Holds reference to an moodletxt_xml_parser object,
         * used in parsing returned request packets.
         * @var moodletxt_xml_parser
         */
        var $parser;

        /**
         * Holds reference to a connector object,
         * used to transmit the packets to txttools.
         * @var moodletxt_connector
         */
        var $connector;

        /**
         * Class constructor - creates the builder,
         * parser and connector objects used by the controller.
         * @version 2010062212
         * @since 2006081012
         */
        function moodletxt_xml_controller() {

            // Initialise objects
            $this->builder = new moodletxt_xml_builder();
            $this->parser = new moodletxt_xml_parser();
            $this->connector = new moodletxt_connector();

        }

        /**
         * Method to send messages via the XML connector.
         * Builds the XML from the message objects passed,
         * sends that to txttools via a connector, and
         * returns parsed the result.
         *
         * @param array(moodletxt_message) $messages The message objects to be sent
         * @param array(string => moodletxt_Recipient) $recipients Message recipients (number => recipient)
         * @return mixed
         * @version 2010062912
         * @since 2007081012
         */
        function send_message($message, $recipients) {

            // Get XML from builder
            $xmlrequest = $this->builder->build_message($message, $recipients);

            // Send XML to connector object
            $response = $this->connector->send_request($xmlrequest);

// To test the parser without sending, comment out the line above, and uncomment this lot

/*$response = '<?xml version="1.0" ?>
 <Response>
    <MessageStatus>
        <MessageText><![CDATA[Message to test the status update part of my lovely system.  Lovely.]]></MessageText>
        <Ticket>12296</Ticket>
        <Status>1</Status>
        <StatusMessage>Sent</StatusMessage>
        <Phone>+441234567890</Phone>
        <Type>1</Type>
        <UniqueID>9999</UniqueID>
    </MessageStatus>
</Response>';*/

            // Set account ID for parsing
            $this->parser->setCurrentAccountID($message->get_txttools_account());

            // Parse response
            $responseobjects = $this->parser->parse($response, $recipients);

            return $responseobjects;

        }

        /**
         * Method to get status updates for sent messages from the system
         * Builds the required XML request, sends that to txttools via a
         * connector, and then returns the parsed result
         *
         * @param array(int) $messagetickets An array of message ticket numbers to get status for
         * @param int $txttoolsaccount The txttools account ID to use for authentication
         * @return mixed
         * @version 2010062912
         * @since 2006101012
         */
        function get_status_updates($messagetickets, $txttoolsaccount) {

            // Get XML from builder
            $xmlrequest = $this->builder->build_status_request($messagetickets, $txttoolsaccount);

            // Send XML to connector object
            $response = $this->connector->send_request($xmlrequest);

            // Set account ID for parsing
            $this->parser->setCurrentAccountID($txttoolsaccount);

            // Parse response
            $responseobjects = $this->parser->parse($response);

            return $responseobjects;

        }

        /**
         * Method to check whether a given username and password is valid
         * by generating a dummy XML request and sending it to the txttools
         * system.  If no objects are returned, then the username/password
         * are valid.
         *
         * @param string $username The username to check
         * @param string $password The password to check
         * @return array(object) An array of parsed response objects
         * @version 2010062212
         * @since 2007011712
         */
        function check_account_validity($username, $password) {

            // Get XML from builder
            $xmlrequest = $this->builder->build_dummy_request($username, $password);

            // Send XML to connector object
            $response = $this->connector->send_request($xmlrequest);

            // Parse response
            $responseobjects = $this->parser->parse($response);

            return $responseobjects;

        }

        /**
         * Method to get credit information for a given
         * txttools account
         * @param int $txttoolsaccount Txttools account ID
         * @return array(object) An array of parsed response objects
         * @version 2010062912
         * @since 2010062512
         */
        function get_account_credit_info($txttoolsaccount) {

            // Get XML from builder
            $xmlrequest = $this->builder->build_credit_request($txttoolsaccount);

            // Send XML to connector object
            $response = $this->connector->send_request($xmlrequest);

            // Set account ID for parsing
            $this->parser->setCurrentAccountID($txttoolsaccount);

            // Parse response
            $responseobjects = $this->parser->parse($response);

            return $responseobjects;

        }

        /**
         * Method to grab the latest MoodleTxt RSS update, parse
         * it into item objects, and return it to the caller
         *
         * @version 2008111012
         * @since 2007012912
         */
        function get_rss_update() {

            // Get RSS via connector
            $response = $this->connector->get_feed();

            // Parse response
            $responseobjects = $this->parser->parse($response);

            return $responseobjects;

        }

        /**
         * Method to get latest inbound messages for a given account
         * or set of accounts
         *
         * @param array(object) $useraccounts Txttools account credentials
         * @version 2010062212
         * @since 2007030812
         */
        function get_inbound_messages($useraccounts) {

            $finalobjects = array();

            foreach($useraccounts as $useraccount) {

                // Build XML request
                $xmlrequest = $this->builder->get_inbound_messages($useraccount);

                // Send XML to connector object
                $response = $this->connector->send_request($xmlrequest);

// To test the parser without connecting, comment out the line above, and uncomment this lot

/*$response = array("<?xml version='1.0' encoding='UTF-8'?>
<Response>
    <InboundMessage>
        <Ticket>384137</Ticket>
        <MessageText><![CDATA[TANTY Testing Greg's inbound]]></MessageText>
        <Phone><![CDATA[+447908633288]]></Phone>
        <Destination><![CDATA[+447624803347]]></Destination>
        <Date>1255600824</Date>
        <DestinationAccount><![CDATA[gpreece]]></DestinationAccount>
    </InboundMessage>
    <MessagesLeftInSet>0</MessagesLeftInSet>
</Response>");*/

                // Parse response
                $this->parser->setCurrentAccountID($useraccount->id);
                $responseobjects = $this->parser->parse($response);

                // Index by account used.  Classic.
                $finalobjects[$useraccount->id] = $responseobjects;

            }

            return $finalobjects;

        }

    }

?>