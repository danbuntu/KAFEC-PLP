<?php

    // Get data beans
    require_once($CFG->dirroot . '/blocks/moodletxt/data/moodletxt_message.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/data/moodletxt_message_status.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/data/moodletxt_sentmessage.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/data/moodletxt_connector_error.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/data/moodletxt_ABRecipient.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/data/moodletxt_UserRecipient.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/data/moodletxt_AdditionalRecipient.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/data/moodletxt_ParseNode.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/data/moodletxt_rss_item.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/data/moodletxt_inbound_message.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/data/moodletxt_ParseAccountDetails.php');

    // Get SAXY parser gear
    require_once($CFG->dirroot . '/blocks/moodletxt/lib/saxy/xml_saxy_parser.php');


    /**
     * Class to parse XML returned by the txttools system, and create
     * an appropriate data object from it. This data object is then
     * passed back to the calling script, via the XML controller.
     *
     * Many thanks to Dante Lorenso of devarticles.com for helping
     * me to overcome my multi-dimensional array woes in the first
     * parser version.  Even greater thanks to the DOMIT!/SAXY developers
     * for creating parsers that don't suck!
     *
     * @package xmlconnection
     * @author Greg J Preece, <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011021001
     * @since 2006092812
     */
     class moodletxt_xml_parser {

        /**
         * Holds the SAXY parser being used
         * @var SAXY_Parser
         */
        var $parser;
        
        /**
         * Holds the namepath of the element currently being parsed
         * @var string
         */
        var $elementPath = '';

        /**
         * Holds an object representing the node currently
         * being parsed
         * @var moodletxt_ParseNode
         */
        var $currentNode = null;
        
        /**
         * Holds an array of target elements  that we want to process.
         * The contents of these elements are stored and passed to
         * the method specified in this array (element => function)
         * @var array(string => string)
         */
        var $targetElements = array();
        
        /**
         * Tells methods dealing with character data, etc whether 
         * they should be storing the data or not
         * @var boolean
         */ 
        var $storeContent = false;

        /**
         * Holds destination phone numbers => recipient objects,
         * used when parsing status updates for sent messages,
         * to marry the returned status against the message record
         * @var array(string => moodletxt_Recipient)
         */
        var $destinations = array();

        /**
         * Holds the objects created from the parse, ready
         * to be returned to the calling object
         * @var array(object)
         */
        var $returnObjects = array();

        /**
         * Holds the timestamp of the last update to the RSS feed
         * @var int
         */
         var $rss_lastUpdate;

         /**
          * Holds the expiry time for RSS messages as set by the user
          * @var int
          */
         var $rss_expiryLength;

         /**
          * Holds the current txttools account ID being used
          * @var int
          */
         var $currentAccountID;

         /**
          * Annoyingly, the XML parser returns account details in two separate blocks.
          * Too late to change it now, so we have to hold a reference to the details
          * object so we can update it if any more blocks are encountered.
          * @var moodletxt_ParseAccountDetails
          */
         var $accountDetailsObject;

        /**
         * Constructor for the parser object. Sets up a PHP SAX
         * parser for use in parsing XML data.  A SAX parser
         * library is used rather than a PHP extension
         * for best compatibility across versions, and
         * speed across large XML packets.
         *
         * @version 2010062512
         * @since 2006092812
         */
        function moodletxt_xml_parser() {

            // Get RSS settings from DB
            $this->rss_lastUpdate = moodletxt_get_setting('RSS_Last_Update');
            $this->rss_expiryLength = moodletxt_get_setting('RSS_Expiry_Length');

            $this->setUpSAXY();

            // Define what elements we're interested in,
            // and what method to call when they are encountered (completed)
            $this->targetElements['/Response/MessageStatus']    = 'build_status_message';
            $this->targetElements['/Response/InboundMessage']   = 'build_inbound_message';
            $this->targetElements['/Response/AccountDetail']    = 'build_account_detail';
            $this->targetElements['/Response/Error']            = 'build_error_message';
            $this->targetElements['/rss/channel/item']          = 'build_rss_item';

        }

        /**
         * Method sets up the SAXY parser and assigns its
         * various handler methods.  SAXY appears to need instansiating
         * for each new parse run.
         * @version 2010062212
         * @since 2009011312
         */
        function setUpSAXY() {

            // Create XML parser
            $this->parser =& new SAXY_Parser();

            // Set up XML parser
            $this->parser->xml_set_doctype_handler(array(&$this, 'doctype'));
            $this->parser->xml_set_processing_instruction_handler(array(&$this, 'processing_instruction'));
            $this->parser->xml_set_element_handler(array(&$this, 'element_start'), array(&$this, 'element_end'));
            $this->parser->xml_set_character_data_handler(array(&$this, 'character_data'));
            $this->parser->xml_set_cdata_section_handler(array(&$this, 'CDATA_section'));
            $this->parser->xml_set_comment_handler(array(&$this, 'comment'));


        }

        /**
         * Defines what to do when a DOCTYPE is encountered - ie, nothing!
         * @param SAXY_Parser $parser Reference to the parser object
         * @param string $doctype DOCTYPE content
         * @version 2010062212
         * @since 2008111112
         */
        function doctype($parser, $doctype) {

            // Do nothing - we're not interested

        }

        /**
         * Defines what to do when processing instructions are found - again, nothing!
         * @param SAXY_Parser $parser Reference to the parser object
         * @param string $target PI target
         * @param string $data PI data
         * @version 2010062212
         * @since 2008111112
         */
        function processing_instruction($parser, $target, $data) {

            // Do nothing - we're not interested

        }

        /**
         * Called when an element is encountered while parsing.
         * In this method, we check whether the element is one we're interested
         * in and create a node object for it if it is
         * @param SAXY_Parser $parser Reference to the parser object
         * @param string $elementName XML element name
         * @param array $attributes XML element attributes
         * @version 2010062212
         * @since 2008111112
         */
        function element_start($parser, $elementName, $attributes=array()) {

            // Append element name to current element path
            $this->elementPath .= '/' . $elementName;

            // If this is one of the elements specified for interest,
            // turn on content storing (switch on the VCR)
            if (array_key_exists($this->elementPath, $this->targetElements))
                $this->storeContent = true;

            // If the VCR is on, record the element data
            // (Create a node object for it)
            if ($this->storeContent) {

                $newNode = new moodletxt_ParseNode($elementName, $attributes, $this->currentNode);

                // If there is an existing parent,
                // make the new node a child of it
                if ($this->currentNode !== null)
                    $this->currentNode->addChild($newNode);
                
                $this->currentNode = $newNode;

            }
                
            
        }

        /**
         * Called when an element being parsed ends.  Check for whether this is
         * the end of a block we're interested in.  If it is, collect the parse
         * nodes recorded so far and pass them to the appropriate method
         * for processing.
         * @param SAXY_Parser $parser Reference to the parser object
         * @param string $elementName XML element name
         * @version 2010062212
         * @since 2008111112
         */
        function element_end($parser, $elementName) {

            // Check whether this is a target element
            if (array_key_exists($this->elementPath, $this->targetElements)) {

                // Turn off the VCR
                $this->storeContent = false;

                // Pass nodes to target function
                $targetFunction = $this->targetElements[$this->elementPath];
                $this->$targetFunction($this->currentNode);

                // Put a new tape in the VCR
                $this->currentNode = null;
                
            }

            // Remove element name from current path
            $this->elementPath = substr($this->elementPath, 0, strrpos($this->elementPath, '/'));

            // Move up to parent node
            if ($this->currentNode !== null)
                $this->currentNode = $this->currentNode->getParentNode();
            
        }

        /**
         * Method called when character data is found.
         * Store character data in currently parsed node object.
         * @param SAXY_Parser $parser Reference to the parser object
         * @param string $chardata Character data
         * @version 2010062212
         * @since 2008111112
         */
        function character_data($parser, $chardata) {

            // If the VCR is on, record the data
            if ($this->storeContent)
                $this->currentNode->addCharData($chardata);
            
        }

        /**
         * Method called when CDATA sections are found.
         * Treat in the same manner as character data.
         * @param SAXY_Parser $parser Reference to the parser object
         * @param string $chardata Character data
         * @version 2010062212
         * @since 2008111112
         */
        function CDATA_section($parser, $chardata) {

            // If the VCR is on, record the data
            if ($this->storeContent)
                $this->currentNode->addCharData($chardata);

        }

        /**
         * Method called when comments are found in the XML
         * Ditch 'em!
         * @param SAXY_Parser $parser Reference to the parser object
         * @param string $comment Comment found in XML stream
         * @version 2010062212
         * @since 2008111112
         */
        function comment($parser, $comment) {
            
            //  Do nothing.  I don't care what the txttools server has to say

        }

        /**
         * Method called by other objects to parse XML packets passed in.
         * @param array(string) $xmlpackets Packets returned from txttools
         * @param array(string => moodletxt_Recipient) $destinations Recipients to send to
         * @return array(object) Objects built from XML
         * @version 2010062212
         * @since 2006092812
         */
        function parse($xmlpackets, $destinations = array()) {

            $this->setUpSAXY();
            $this->returnObjects = array();

            // If destinations are specified, save them
            if (is_array($destinations))
                $this->destinations = $destinations;

            // Ensure packets come in as array
            if (! is_array($xmlpackets))
                $xmlpackets = array($xmlpackets);

            // Parse packets and return parsed objects
            foreach ($xmlpackets as $packet)
                $this->parser->parse($packet);

            return $this->returnObjects;
            
        }

        /**
         * Builds message and status objects from parsed XML
         * @param moodletxt_ParseNode $nodes The node collection to create the object from
         * @version 2010062212
         * @since 2008111112
         */
        function build_status_message($nodes) {

            $recipientObject = null;

            // Ensure correct element is passed
            if ($nodes->getNodeName() != 'MessageStatus')
                return;

            // Check that all required children for message object
            // build exist within passed data
            if ($nodes->childExists('MessageText')
                && $nodes->childExists('Phone')
                && $nodes->childExists('Ticket')
                && $nodes->childExists('UniqueID')) {

                // I'd string these method calls together, but then
                // PHP 4 wouldn't love me any more
                $textNode = $nodes->getChild('MessageText');
                $phoneNode = $nodes->getChild('Phone');
                $ticketNode = $nodes->getChild('Ticket');
                $idNode = $nodes->getChild('UniqueID');


                // If destinations have been specified, check for recipient
                if ($this->destinations !== null) {

                    if (array_key_exists($phoneNode->getCharData(), $this->destinations)) {

                        $recipientObject = $this->destinations[$phoneNode->getCharData()];

                    }

                }

                // Build object and shove it onto the parsed objects array
                $sentmessageobject = new moodletxt_sentmessage($idNode->getCharData(), $ticketNode->getCharData(),
                    $phoneNode->getCharData(), $recipientObject);

                array_push($this->returnObjects, $sentmessageobject);

            }

            // Check if required children for status object exist
            if ($nodes->childExists('Ticket')
                && $nodes->childExists('Status')
                && $nodes->childExists('StatusMessage')){

                $ticketNode = $nodes->getChild('Ticket');
                $statusNode = $nodes->getChild('Status');
                $messageNode = $nodes->getChild('StatusMessage');

                // Build status object and shove onto array
                $statusObject = new moodletxt_message_status($ticketNode->getCharData(), $statusNode->getCharData(),
                    $messageNode->getCharData(), time());

                array_push($this->returnObjects, $statusObject);

            }

        }

        /**
         * Builds inbound message objects from parsed XML
         * @param moodletxt_ParseNode $nodes The elements to build from
         * @version 2010062212
         * @since 2008111112
         */
        function build_inbound_message($nodes){

            // Check this is correct element
            if ($nodes->getNodeName() != 'InboundMessage')
                return;

            // Check that required children exist for object build
            if ($nodes->childExists('MessageText')
                && $nodes->childExists('Phone')
                && $nodes->childExists('Date')
                && $nodes->childExists('Ticket')
                && $nodes->childExists('Destination')) {


                // I'd string these method calls together, but then
                // PHP 4 wouldn't love me any more
                $messageNode     = $nodes->getChild('MessageText');
                $phoneNode       = $nodes->getChild('Phone');
                $dateNode        = $nodes->getChild('Date');
                $ticketNode      = $nodes->getChild('Ticket');
                $destinationNode = $nodes->getChild('Destination');

                // Create object and shove onto array
                $messageObject = new moodletxt_inbound_message($ticketNode->getCharData(),
                    $messageNode->getCharData(), $phoneNode->getCharData(), $dateNode->getCharData(), 0);

                if ($nodes->childExists('DestinationAccount')) {

                    $destinationAccountNode = $nodes->getChild('DestinationAccount');
                    $messageObject->setDestinationAccountUsername($destinationAccountNode->getCharData());

                } else if ($this->getCurrentAccountID() !== null) {

                    $messageObject->setDestinationAccountID($this->getCurrentAccountID());

                }

                array_push($this->returnObjects, $messageObject);

            }
            
        }

        /**
         * Builds account detail objects returned from the connector
         * @param moodletxt_ParseNode $nodes The elements to build from
         * @version 2011021001
         * @since 2010062512
         */
        function build_account_detail($nodes) {

            // Check element is correct type
            if ($nodes->getNodeName() != 'AccountDetail')
                return;

            // If this object has already been added to the return array,
            // and we wish to update it, then we store the reference and
            // update it directly.  Not the nicest way of doing things by
            // any means, but hey, it works
            $addToArray = false;

            // Check whether or not the details object
            // has already been created
            if ($this->accountDetailsObject == null) {
                $this->accountDetailsObject = new moodletxt_ParseAccountDetails($this->currentAccountID);
                $addToArray = true;
            }

            // Populate account details object
            if ($nodes->childExists('MessagesUsed') &&
                $nodes->childExists('MessagesRemaining') &&
                $nodes->childExists('AccountType')) {

                $creditsUsedNode = $nodes->getChild('MessagesUsed');
                $creditsRemainingNode = $nodes->getChild('MessagesRemaining');
                $accountTypeNode = $nodes->getChild('AccountType');

                $this->accountDetailsObject->set_creditsused(
                        (int) $creditsUsedNode->getCharData()
                );

                $this->accountDetailsObject->set_creditsremaining(
                        (int) $creditsRemainingNode->getCharData()
                );

                $this->accountDetailsObject->set_accounttype(
                        (int) $accountTypeNode->getCharData()
                );
            }

            if ($addToArray)
                array_push($this->returnObjects, $this->accountDetailsObject);

        }

        /**
         * Builds error objects for errors returned by the connector
         * @param moodletxt_ParseNode $nodes Elements to build from
         * @version 2010062212
         * @since 2008111112
         */
        function build_error_message($nodes) {

            // Check element is correct type
            if ($nodes->getNodeName() != 'Error')
                return;

            // Check all required children exist
            if ($nodes->childExists('ErrorMessage')
                && $nodes->childExists('ErrorCode')) {

                $messageNode = $nodes->getChild('ErrorMessage');
                $codeNode = $nodes->getChild('ErrorCode');

                // Build error object and shove onto array
                $errorObject = new moodletxt_connector_error($messageNode->getCharData(),
                    time(), $codeNode->getCharData());

                array_push($this->returnObjects, $errorObject);

            }

        }

        /**
         * Builds RSS news items from the moodletxt feed
         * @param moodletxt_ParseNode $nodes Elements to build from
         * @version 2010062212
         * @since 2008111112
         */
        function build_rss_item($nodes) {

            // Check element passed is an RSS one
            if ($nodes->getNodeName() != 'item')
                return;

            // Check that all required children for object exist
            if ($nodes->childExists('title')
                && $nodes->childExists('link')
                && $nodes->childExists('pubDate')
                && $nodes->childExists('description')) {

                // Generate publication timestamps for item
                $dateNode = $nodes->getChild('pubDate');
                $pubTime = strtotime($dateNode->getCharData());

                // If this item was published after last update, build object
                if ($pubTime > $this->rss_lastUpdate) {

                    $titleNode = $nodes->getChild('title');
                    $linkNode = $nodes->getChild('link');
                    $descriptionNode = $nodes->getChild('description');

                    // Build RSS object and shove onto array
                    $rssObject = new moodletxt_rss_item($titleNode->getCharData(),
                        $linkNode->getCharData(), $pubTime, $descriptionNode->getCharData(), time() + $this->rss_expiryLength);

                    array_push($this->returnObjects, $rssObject);
                        
                }

            }

        }

        /**
         * Get the current txttools account results are being parsed for
         * @return int Txttools account ID
         * @version 2010062212
         * @since 2009110212
         */
        function getCurrentAccountID() {

            return $this->currentAccountID;

        }

        /**
         * Set the current txttools account results are being parsed for
         * @param int $currentAccountID Txttools account ID
         * @version 2010062212
         * @since 2009110212
         */
        function setCurrentAccountID($currentAccountID) {

            if (is_int($currentAccountID) && $currentAccountID > 0)
                $this->currentAccountID = $currentAccountID;

        }
                
    }

?>