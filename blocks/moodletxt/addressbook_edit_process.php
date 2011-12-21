<?php

    /**
     * Script to process editing of address book contact records
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2009010712
     * @since 2009010712
     * @TODO Re-do this with better jQuery and some JSON dude
     */

    require_once('../../config.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/lib.php');

    error_reporting(0);

    require_login();

    // I'd be fancy and build the XML properly when needed,
    // but what's the point?
    $errorXML = '<?xml version="1.0"?>
<Response>
    <Error>
        <ErrorMessage>%1$s</ErrorMessage>
    </Error>
</Response>';

    $successXML = '<?xml version="1.0"?>
<Response>
    <Success></Success>
</Response>';

    $rowid     = required_param('rowid', PARAM_INT);
    $lastname  = stripslashes(required_param('lastname', PARAM_TEXT));
    $firstname = stripslashes(required_param('firstname', PARAM_TEXT));
    $company   = stripslashes(required_param('company', PARAM_TEXT));
    $phoneno   = stripslashes(required_param('phoneno', PARAM_TEXT));

    // Get contact record
    $contact = get_record('block_mtxt_ab_entry', 'id', moodletxt_escape_string($rowid));

    // Check that the record selected for this user and that they own the AB
    if (! is_object($contact))
        die(sprintf($errorXML, get_string('errorbadcontactid', 'block_moodletxt')));

    if (count_records('block_mtxt_ab', 'id', moodletxt_escape_string($contact->addressbook), 'owner', moodletxt_escape_string($USER->id)) == 0)
        die(sprintf($errorXML, get_string('errorbadbookid', 'block_moodletxt')));

    $contact->firstname = moodletxt_escape_string($firstname);
    $contact->lastname  = moodletxt_escape_string($lastname);
    $contact->company   = moodletxt_escape_string($company);
    $contact->phoneno   = moodletxt_escape_string($phoneno);

    if (update_record('block_mtxt_ab_entry', $contact))
        die($successXML);
    else
        die(sprintf($errorXML, get_string('errorupdatecontactfailed', 'block_moodletxt')));

?>