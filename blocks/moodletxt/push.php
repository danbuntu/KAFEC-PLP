<?php

    /**
     * This is the XML "Push" script for use with the txttools.co.uk
     * XML connector. Message status updates are sent via POST to this
     * script (when set up), and the updates are recorded in the database
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2009110312
     * @since 2006101012
     */

    // Get config and required libraries
    require_once('../../config.php');
    require_once($CFG->libdir.'/datalib.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/lib.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/db/sqllib.php');

    // Get XML connector classes
    require_once($CFG->dirroot . '/blocks/moodletxt/xml/moodletxt_xml_controller.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/inbound/InboundFilterManager.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/encryption.php');

    $parser = new moodletxt_xml_parser();
    $filterManager = new InboundFilterManager();

    // Make decrypter
    $encrypt_o_matic = new Encryption();

    // Get EK
    $EK = moodletxt_get_setting('EK');
    $Push_Username = moodletxt_get_setting('Push_Username');
    $Push_Password = trim($encrypt_o_matic->decrypt($EK, moodletxt_get_setting('Push_Password')));

    // Read in POST variables
    $inPushUser = required_param('u', PARAM_ALPHANUM);
    $inPushPass = required_param('p', PARAM_ALPHANUM);
    $inPayload  = required_param('x', PARAM_RAW);

    if (($inPushUser == $Push_Username) && ($inPushPass == $Push_Password)) {

        $parsedObjects = $parser->parse($inPayload);
        $filteredObjects = $filterManager->filterMessages($parsedObjects);
        moodletxt_write_objects($filteredObjects);

    }

    unset($parser);
    unset($encrypt_o_matic);

?>
