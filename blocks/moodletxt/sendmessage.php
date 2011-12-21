<?php

    /**
     * Page to send a text message to Moodle installation users
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011030701
     * @since 2006081012
     */

    /*
      ###########################################################
      # SET UP
      ############################################################
    */

    // Get config and required libraries
    require_once('../../config.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/lib.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/db/sqllib.php');

    // Get XML connector classes
    require_once($CFG->dirroot . '/blocks/moodletxt/xml/moodletxt_xml_controller.php');

    // Get encryption class
    require_once($CFG->dirroot . '/blocks/moodletxt/encryption.php');

    // Check for course ID
    $courseid = $_SESSION['moodletxt_last_course'];
    $instanceid = $_SESSION['moodletxt_last_instance'];

    if ((! empty($courseid)) && (! $course = get_record('course', 'id', $courseid))) {
        error(get_string('errorbadcourseid', 'block_moodletxt'));
    }

    if (empty($instanceid) || ! is_int($instanceid))
        error(get_string('errorbadinstanceid', 'block_moodletxt'));

    // User MUST be logged in
    require_login($course->id, false);

    // Check that user is allowed to send messages
    $blockcontext = get_context_instance(CONTEXT_BLOCK, $instanceid);
    require_capability('block/moodletxt:sendmessages', $blockcontext, $USER->id);

    // Set up error arrays
    $errorArray = array();
    $xmlerrors = array();
    $slideErrors = array(1 => false, 2 => false, 3 => false, 4 => false);

    // Get txttools account details
    $txttoolsAccounts = get_records('block_mtxt_accounts', 'outboundenabled', '1', 'username ASC');

    if (! is_array($txttoolsAccounts) || count($txttoolsAccounts) == 0)
        error(get_string('errornoaccountspresent', 'block_moodletxt'));


    // Grab contact groups for course
    $userGroups = get_groups($course->id);
    
    $courseUsers = moodletxt_get_course_users($course->id, 'u.lastname ASC, u.firstname ASC', array(), 'u.id,u.firstname,u.lastname,u.phone1,u.phone2');
    
    // Get addresss books

    $sql = moodletxt_get_sql('sendgetpublicabs');
    $sql = sprintf($sql, $USER->id);
    
    $publicAddressBooks = get_records_sql($sql);
    
    $sql = moodletxt_get_sql('sendgetprivateabs');
    $sql = sprintf($sql, $USER->id);
    
    $privateAddressBooks = get_records_sql($sql);
    
    $allAddressBooks = moodletxt_merge_recordsets($publicAddressBooks, $privateAddressBooks);
    
    $abContacts = array();
    $abGroups = array();
    
    if (is_array($allAddressBooks) && count($allAddressBooks) > 0) {
        
        $keys = array_keys($allAddressBooks);
        $sqlfrag = "'" . implode("', '", $keys) . "'";
        
        $sql = moodletxt_get_sql('sendgetabcontacts');
        $sql = sprintf($sql, $sqlfrag);
        
        $abContacts = get_records_sql($sql);
        
        $sql = moodletxt_get_sql('sendgetabgroups');
        $sql = sprintf($sql, $sqlfrag);
        
        $abGroups = get_records_sql($sql);            
        
    }
    
    $userTemplates = get_records('block_mtxt_templates', 'userid', $USER->id);
    $userSignature = get_record('block_mtxt_uconfig', 'userid', $USER->id, 'setting', 'SIGNATURE');

    // Form scheduling timestamp vars
    $scheduleTimestamp = time();

    // Set up page
    $title = get_string('sendtitle', 'block_moodletxt');
    $heading = get_string('sendheading', 'block_moodletxt');
    $blocktitle = get_string('blocktitle', 'block_moodletxt');

    $inRecipients = array();
    $inMessage = '';

    $destinations = array();

    // Arrays used to populate form
    $moodleUsers = array();
    $moodleUserGroups = array();
    $addressBookContacts = array();
    $addressBookGroups = array();
    $additionalContacts = array();

    /*
      ############################################################
      # Get details of any contacts being replied to and
      # add them in for populating
      ############################################################
    */

    $inReplyType = optional_param('replytype', '', PARAM_ALPHA);
    $inReplyValue = optional_param('replyvalue', '', PARAM_RAW);

    if ($inReplyType != '') {

        switch($inReplyType) {

            case 'user':
                if (moodletxt_is_intval($inReplyValue))
                    array_push($inRecipients, 'u#' . $inReplyValue);
                break;
            case 'addressbook':
                if (moodletxt_is_intval($inReplyValue))
                    array_push($inRecipients, 'ab#' . $inReplyValue);
                break;
            case 'additional':
                if ($inReplyValue != '')
                    array_push($additionalContacts, array($inReplyValue, "Contact", "Unknown"));
                break;
            default:
                continue;

        }

    }

    /*
      ############################################################
      # If the user has submitted the form, process it
      ############################################################
    */

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        // Read in form vars
        $txttoolsAccount    = optional_param('txttoolsaccount', 0, PARAM_INT);
        $inRecipients       = optional_param('finalRecipients', '', PARAM_RAW);
        $inMessage          = htmlspecialchars(trim(optional_param('messageText', '', PARAM_NOTAGS)));
        $inSchedule         = optional_param('schedule', '', PARAM_ALPHA);
        $inScheduleDay      = optional_param('schedule_day', 0, PARAM_INT);
        $inScheduleMonth    = optional_param('schedule_month', 0, PARAM_INT);
        $inScheduleYear     = optional_param('schedule_year', 0, PARAM_INT);
        $inScheduleHour     = optional_param('schedule_hour', 0, PARAM_INT);
        $inScheduleMinute   = optional_param('schedule_minute', 0, PARAM_INT);
        $inSuppressUnicode  = optional_param('suppressUnicode', 0, PARAM_INT);

        $scheduleTimestamp = 0;
        if ($inSuppressUnicode != 1) $inSuppressUnicode = 0;

        // Check that the user is allowed to send a message
        // No form hacking please - we're antidisestablishmentarians
        if ($txttoolsAccount == 0) {

            $errorArray["nolink"] = get_string('errornoaccountselected', 'block_moodletxt');
            $slideErrors[3] = true;

        } else {

            // Check the link against the database
            if (count_records('block_mtxt_accounts', 'id', $txttoolsAccount, 'outboundenabled', '1') != 1) {

                $errorArray["nolink"] = get_string('errorformhackaccount', 'block_moodletxt');
                $slideErrors[3] = true;

            }

        }
        
        // Check that recipients were selected
        if ($inRecipients == '') {
            
            $errorArray['norecipients'] = get_string('errornorecipients', 'block_moodletxt');
            $slideErrors[1] = true;
            
        } else if (! is_array($inRecipients)) {
            
            $inRecipients = array($inRecipients);
            
        }

        for ($x = 0; $x < count($inRecipients); $x++)
            $inRecipients[$x] = htmlspecialchars(strip_tags(trim($inRecipients[$x])));

        // Check for existence of message
        if ($inMessage == '') {

            $errorArray['noMessage'] = get_string('errornomessage', 'block_moodletxt');
            $slideErrors[2] = true;

        }
        
        // Check that a scheduling option was selected
        if (($inSchedule != 'now') && ($inSchedule != 'schedule')) {

            $errorArray['noSchedule'] = get_string('errornoscheduling', 'block_moodletxt');
            $slideErrors[3] = true;

        } else if ($inSchedule == 'schedule') {

            if (! checkdate($inScheduleMonth, $inScheduleDay, $inScheduleYear)) {

                $errorArray['invalidDate'] = get_string('errorinvaliddate', 'block_moodletxt');
                $slideErrors[3] = true;

            } else {

                $scheduleTimestamp = usertime(gmmktime($inScheduleHour, $inScheduleMinute, 0,
                    $inScheduleMonth, $inScheduleDay, $inScheduleYear));

                if ($scheduleTimestamp < time()) {

                    $errorArray['pastDate'] = get_string('errordocbrown', 'block_moodletxt');
                    $slideErrors[3] = true;

                }

            }

        }

        /*
          ############################################################
          # Process recipients - now done outside no-error block
          # so that the form can be re-populated correctly.  Annoying.
          ############################################################
        */

        // Loop through recipients and group into seperate arrays
        foreach($inRecipients as $recipient) {

            $valuefrags = explode('#', $recipient);

            if (count($valuefrags) != 2 && count($valuefrags) != 4)
                continue;
                
            switch($valuefrags[0]) {
                    
                case 'u':
                    if (moodletxt_is_intval($valuefrags[1]))
                        array_push($moodleUsers, $valuefrags[1]);
                    break;
                case 'ug':
                    if (moodletxt_is_intval($valuefrags[1]))
                        array_push($moodleUserGroups, $valuefrags[1]);
                     break;
                case 'ab':
                    if (moodletxt_is_intval($valuefrags[1]))
                        array_push($addressBookContacts, $valuefrags[1]);
                    break;
                case 'abg':
                    if (moodletxt_is_intval($valuefrags[1]))
                        array_push($addressBookGroups, $valuefrags[1]);
                    break;
                case 'add':
                    array_push($additionalContacts, array($valuefrags[1], $valuefrags[2], $valuefrags[3]));
                    break;
                default:
                    continue;
                    
            }
                
        }
        
        $numberdestinations = 0;
            
        // Grab Moodle user details
        if (count($moodleUsers) > 0) {
                
            $sql = moodletxt_get_sql('sendgetuserdetails');
            $sql = sprintf($sql, '\'' . implode("', '", $moodleUsers) . '\'');

            $moodleUserRecs = get_records_sql($sql);
                
            if (is_array($moodleUserRecs) && count($moodleUserRecs) > 0) {
                    
                foreach($moodleUserRecs as $user) {
                    
                    $phonenumber = moodletxt_get_mobile_number($user);

                    if ($phonenumber == '')
                        continue;

                    /*
                     * Since MoodleTxt 2.1 and its betas, the txttools
                     * system is correctly returning UniqueID fields,
                     * however recipients still don't have IDs of their own,
                     * so we need to match by phone number, hence using the
                     * phone number as a key for easy lookup
                     */

                    $passobj = new moodletxt_UserRecipient($phonenumber, $user->firstname, $user->lastname, $user->username, $user->id);
                    $destinations[$passobj->getNumber()] = $passobj;

                    $numberdestinations++;                    
                        
                }
                   
            }
                
        }
            
        // Get Moodle user groups
        if (count($moodleUserGroups) > 0) {
                
            foreach($moodleUserGroups as $userGroup) {
                    
                // Get all users in group
                $userGroupMembers = get_group_users($userGroup, 'u.lastname ASC', '',
                    'u.id, u.username, u.firstname, u.lastname, u.phone1, u.phone2');
                        
                if (is_array($userGroupMembers) && count($userGroupMembers) > 0) {
                        
                    foreach($userGroupMembers as $user) {
                    
                        $phonenumber = moodletxt_get_mobile_number($user);

                        if ($phonenumber == '')
                            continue;

                        $passobj = new moodletxt_UserRecipient($phonenumber, $user->firstname, $user->lastname, $user->username, $user->id);
                        $destinations[$passobj->getNumber()] = $passobj;

                        $numberdestinations++;                    
                        
                    }
                        
                }
                                                            
            }                
                
        }
            
        // Grab address book contacts
        if (count($addressBookContacts) > 0) {
                
            $sql = moodletxt_get_sql('sendgetabcontactsbyid');
            $sql = sprintf($sql, '\'' . implode("', '", $addressBookContacts) . '\'');

            $addressBookRecs = get_records_sql($sql);
                
            if (is_array($addressBookRecs) && count($addressBookRecs) > 0) {
                    
                foreach($addressBookRecs as $contact) {

                    if ($contact->phoneno == '') continue;

                    $passobj = new moodletxt_ABRecipient($contact->phoneno, $contact->firstname, $contact->lastname, $contact->company, $contact->id);

                    if (! array_key_exists($passobj->getNumber(), $destinations)) {

                        $destinations[$passobj->getNumber()] = $passobj;
                        $numberdestinations++;

                    }
                        
                }
                    
            }
                            
        }
            
        // Grab address book groups
        if (count($addressBookGroups) > 0) {
               
            $sql = moodletxt_get_sql('sendgetabgroupmembers');
            $sql = sprintf($sql, '\'' . implode("', '", $addressBookGroups) . '\'');
                
            $abGroupRecs = get_records_sql($sql);
                
            if (is_array($abGroupRecs) && count($abGroupRecs) > 0) {
                
                foreach($abGroupRecs as $contact) {

                    if ($contact->phoneno == '') continue;

                    $passobj = new moodletxt_ABRecipient($contact->phoneno, $contact->firstname, $contact->lastname, $contact->company, $contact->id);

                    if (! array_key_exists($passobj->getNumber(), $destinations)) {

                        $destinations[$passobj->getNumber()] = $passobj;
                        $numberdestinations++;

                    }
                
                }
                    
            }
                
        }

        // Get additional users!
        if (count($additionalContacts) > 0){

            foreach ($additionalContacts as $contact) {

                $passobj = new moodletxt_AdditionalRecipient($contact[0], $contact[2], $contact[1]);

                if (! array_key_exists($passobj->getNumber(), $destinations)) {

                    $destinations[$passobj->getNumber()] = $passobj;
                    $numberdestinations++;

                }

            }

        }
            
        /*
          ############################################################
          # If no errors were found, send the message!
          ############################################################
        */

        if (count($errorArray) == 0) {

            // If recipients exist, send message
            if ($numberdestinations == 0) {

                $errorArray['noValidNumbers'] = get_string('errornovalidnumbers', 'block_moodletxt');
                $slideErrors[1] = true;

            } else {

                // Create new XML controller
                $xmlcontroller = new moodletxt_xml_controller();

                // Holds objects created from XML response
                $responseobjects = array();

                // Create timestamps for scheduler
                $timeSent = time();
                $finalSchedule = ($scheduleTimestamp > 0) ? $scheduleTimestamp : $timeSent;

                // Create message object
                $finalmessage = new moodletxt_message($txttoolsAccount, $USER->id, $inMessage, $timeSent, 1, $scheduleTimestamp, $inSuppressUnicode);

                // Write record of sent message to database
                $messageid = insert_record('block_mtxt_outbox', $finalmessage, true, 'id');
                $finalmessage->set_id($messageid);

                // SEND MESSAGE! WOOHOO!
                $responseobjects = $xmlcontroller->send_message($finalmessage, $destinations);

                $xmlerrors = moodletxt_get_xml_errors($responseobjects);

                if (count($xmlerrors) == 0) {

                    moodletxt_write_objects($responseobjects);

                    moodletxt_update_outbound_stats($txttoolsAccount, $USER->id, 1);

                    header('Location: sentmessages.php');

                    // DIE, PUNK!
                    die();

                } else {

                    // Kill message record
                    delete_records('block_mtxt_outbox', 'id', moodletxt_escape_string($messageid));

                }

            }

        }

    }

    /*
      ############################################################
      # SET UP PAGE AND OUTPUT
      ############################################################
    */

    $JStemplateList = '';
    $templateList = '';
    $JSaccountList = '';
    $accountList = '';
    $abList = '';
    $abGroupList = '';
    $userGroupList = '';
    $userList = '';
    $selectedRecipients = '';
    $userSigString = (is_object($userSignature)) ? $userSignature->value : '';

    // Sort out user template lists
    if (is_array($userTemplates)) {

        foreach ($userTemplates as $template) {

            // str_replace call is here to get around PHP bug -
            // quotes turned to double-quotes even with magic quotes turned off
            $JStemplateList .= "
        userTemplates[" . $template->id . "] = '" . str_replace("''", "\'", addslashes(preg_replace("(\r\n|\n|\r|\t)", " ", $template->template))) . "';";

            $templateList .= '
                    <option value="' . $template->id . '">' . moodletxt_restrict_length($template->template, 60) . '</option>';

        }

    }

    // User account list
    foreach ($txttoolsAccounts as $account) {

        $selectedString = '';

        if ((isset($txttoolsAccount)) && ($account->id == $txttoolsAccount))
            $selectedString = ' selected="selected"';

        // str_replace call is here to get around PHP bug -
        // quotes turned to double-quotes even with magic quotes turned off
        $JSaccountList .= "
            accountDescriptions[" . $account->id . "] = '" . str_replace("''", "\'", addslashes(preg_replace("(\r\n|\n|\r|\t)", " ", $account->description))) . "';";

        $accountList .= '
                    <option value="' . $account->id . '"'. $selectedString . '>' . $account->username . '</option>';

    }

    // List of contact groups
    if (is_array($userGroups)) {

        foreach ($userGroups as $group) {

            $optionString = '
                    <option class="mdltxt_opt_userGroup" value="ug#' . $group->id . '">' . $group->name . '</option>';

            if (in_array('ug#' . $group->id, $inRecipients))
                $selectedRecipients .= $optionString;
            else
                $userGroupList .= $optionString;

        }

    }

    // List of contact individuals
    if (is_array($courseUsers)) {
    
        foreach($courseUsers as $user) {

            $usernumber = moodletxt_get_mobile_number($user);

            // If this user doesn't have a text number, don't add them to the list
            if ($usernumber == '') continue;

            $optionString = '
                    <option class="mdltxt_opt_user" value="u#' . $user->id . '">' . $user->lastname . ', ' . $user->firstname . ' (' . $usernumber . ')</option>';

            if (in_array('u#' . $user->id, $inRecipients))
                $selectedRecipients .= $optionString;
            else
                $userList .= $optionString;

        }
    
    }


    // List of contact groups
    if (is_array($abGroups)) {

        foreach ($abGroups as $group) {

            $optionString = '
                    <option class="mdltxt_opt_abGroup" value="abg#' . $group->id . '">' . $group->name . '</option>';

            if (in_array('abg#' . $group->id, $inRecipients))
                $selectedRecipients .= $optionString;
            else
                $abGroupList .= $optionString;

        }

    }
    
    // List of contact individuals
    if (is_array($abContacts)) {
    
        foreach($abContacts as $contact) {

            $optionString = '
                    <option class="mdltxt_opt_abContact" value="ab#' . $contact->id . '">' . $contact->lastname . ', ' . $contact->firstname . ' (' . $contact->phoneno . ')</option>';

            if (in_array('ab#' . $contact->id, $inRecipients))
                $selectedRecipients .= $optionString;
            else
                $abList .= $optionString;

        }
    
    }

    if (is_array($additionalContacts)) {

        foreach($additionalContacts as $contact) {

            $selectedRecipients .= '
                    <option class="" value="add#' . $contact[0] . '#' . $contact[1] . '#' . $contact[2] . '">' .
                    $contact[1] . ', ' . $contact[2] . ' (' . $contact[0] . ')</option>';

        }

    }
    

    $courseinsert = '';

    // This if-else statement keeps the block navigation
    // compatible with Moodle 1.7.x and 1.8.x without requiring
    // a branch and separate release, which would have been overkill
    if (function_exists('build_navigation')) {

        $navigation = build_navigation(
            array(
                array('name' => $blocktitle, 'link' => 'moodletxt.php?courseid=' . $course->id, 'type' => 'misc'),
                array('name' => $heading, 'link' => '', 'type' => 'title')
            )
        );

        print_header_simple(
            $title,
            $heading,
            $navigation
        );

    } else {

        print_header(
            $title . ' ' . $course->fullname,
            $heading,
            '<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '">' . $course->shortname . '</a> -> <a href="moodletxt.php?courseid=' . $course->id . '">' . $blocktitle . '</a> -> ' . $heading,
            '',
            '',
            false,
            '&nbsp;'
        );
    }

    print_heading($heading);

    // Get output file
    require_once($CFG->dirroot . '/blocks/moodletxt/sendmessage_output.php');

    // Get footer
    print_footer();

?>