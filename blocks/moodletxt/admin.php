<?php

    /**
     * Admin page for MoodleTxt.  Allows admins to configure
     * global settings for all MoodleTxt instances
     *
     * I've split the form processing by form, rather than by
     * POST/GET.  Makes for a few more conditional statements,
     * but it's easier to keep track of on a page like this.
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011030401
     * @since 2006081012
     */

    /*
      ############################################################
      # SET UP
      ############################################################
    */

    // Get config and required libraries
    require_once('../../config.php');
    require_once($CFG->libdir . '/datalib.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/lib.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/db/sqllib.php');
    
    // Get XML connector classes
    require_once($CFG->dirroot . '/blocks/moodletxt/xml/moodletxt_xml_controller.php');

    // Get encryption class
    require_once($CFG->dirroot . '/blocks/moodletxt/encryption.php');

    require_login(0, false);

    // Create site context
    $sitecontext = get_context_instance(CONTEXT_SYSTEM);

    // Check for admin
    if (! has_capability('block/moodletxt:adminusers', $sitecontext, $USER->id) &&
    ! has_capability('block/moodletxt:adminsettings', $sitecontext, $USER->id))
        error(get_string('errornopermission', 'block_moodletxt'));

    $errorArray = array();
    $noticeArray = array();

    // Create new XML controller
    $xmlcontroller = new moodletxt_xml_controller();

    // Check for form ID
    $formid = optional_param('formid', '', PARAM_ALPHA);

    /*
      ############################################################
      # "UPDATE ACCOUNT" FORM PROCESSING
      ############################################################
    */

    if (($_SERVER["REQUEST_METHOD"] == "POST") && ($formid == "accounts")) {

        // Make encrypter
        $encrypt_o_matic = new Encryption();

        // Get EK
        $EK = moodletxt_get_setting('EK');

        // Read in account ID
        $cpAccountID = optional_param('account', 0, PARAM_INT);
        $cpNewPassword = optional_param('newpassword', '', PARAM_ALPHANUM);
        $cpDefaultInbox = optional_param('defaultinbox', 0, PARAM_INT);

        // Get account object from DB to check existence
        $updateaccount = get_record('block_mtxt_accounts', 'id', $cpAccountID);

        // Check that an account is selected, and that it is valid
        if (! is_object($updateaccount)) {

            $errorArray['invalidaccount'] = get_string('errorinvalidaccount', 'block_moodletxt');

        } else {

            // Escape data that will not be modified by the form,
            // ready for write-back to the database
            $updateaccount->username = addslashes($updateaccount->username);
            $updateaccount->description = addslashes($updateaccount->description);

            // Check default inbox
            if (! has_capability('block/moodletxt:defaultinbox', $sitecontext, $cpDefaultInbox)) {

                $errorArray['addDefaultNotAdmin'] = get_string('errorinvaliddefault', 'block_moodletxt');

            } else {

                // Get inbox ID from user
                $defaultinboxobj = get_record('block_mtxt_inbox', 'userid', $cpDefaultInbox);

                if (is_object($defaultinboxobj)) {

                    $defaultinboxid = $defaultinboxobj->id;

                } else {

                    $defaultinboxid = moodletxt_create_inbox($cpDefaultInbox);

                }

            }

            if ((count($errorArray) == 0) && ($cpNewPassword != '')) {

                $objectarray = $xmlcontroller->check_account_validity($updateaccount->username, $cpNewPassword);

                // Only error objects can be returned from a dummy run
                if (count($objectarray) > 0) {

                    $errorarr = moodletxt_get_xml_errors($objectarray);

                    $errorArray = $errorArray + $errorarr;

                }

            }

            if (count($errorArray) == 0) {

                if ($cpNewPassword != '')
                    $updateaccount->password = $encrypt_o_matic->encrypt($EK, $cpNewPassword, 30);

                $updateaccount->defaultinbox = $defaultinboxid;

                update_record('block_mtxt_accounts', $updateaccount);

            }

        }

    }

    /*
      ############################################################
      # PROXY FORM PROCESSING FOR NEW INSTALLATIONS
      ############################################################
     */

    if ($_SERVER["REQUEST_METHOD"] == "POST" && ($formid == "newinstall")) {

        $setProxy_Host      = trim(optional_param('Proxy_Host',     '', PARAM_RAW));
        $setProxy_Port      = trim(optional_param('Proxy_Port',     '', PARAM_INT));
        $setProxy_Username  = trim(optional_param('Proxy_Username', '', PARAM_CLEAN));
        $setProxy_Password  = trim(optional_param('Proxy_Password', '', PARAM_CLEAN));

        $updatesuccess = true;

        if (! moodletxt_set_setting('Proxy_Host', $setProxy_Host))
            $updatesuccess = $updatesuccess && false;

        if (! moodletxt_set_setting('Proxy_Port', $setProxy_Port))
            $updatesuccess = $updatesuccess && false;

        if (! moodletxt_set_setting('Proxy_Username', $setProxy_Username))
            $updatesuccess = $updatesuccess && false;

        if (! moodletxt_set_setting('Proxy_Password', $setProxy_Password))
            $updatesuccess = $updatesuccess && false;

        if (! $updatesuccess)
            $errorArray['settingupdatesfailed'] = get_string('errorsettingsupdatefail', 'block_moodletxt');

    }

    /*
      ############################################################
      # "NEW ACCOUNT" FORM PROCESSING
      ############################################################
    */

    $addAccountName = '';
    $addDescription = '';

    if (($_SERVER["REQUEST_METHOD"] == "POST") && 
        ($formid == "addaccount" ) ||
        ($formid == "newinstall" && count($errorArray) == 0)) {

        // Make encrypter
        $encrypt_o_matic = new Encryption();

        // Get EK
        $EK = moodletxt_get_setting('EK');

        $addAccountName      = optional_param('accountname',        '', PARAM_CLEAN);
        $addPassword1        = optional_param('password1',          '', PARAM_ALPHANUM);
        $addPassword2        = optional_param('password2',          '', PARAM_ALPHANUM);
        $addDescription      = optional_param('accountdescription', '', PARAM_CLEAN);
        $addDefaultInboxUser = optional_param('defaultinbox',        0, PARAM_INT);

        // Check for username
        if ($addAccountName == '')
            $errorArray['addNoAccountName'] = get_string('errornousername', 'block_moodletxt');

        // Check that passwords exist and are the same
        if ($addPassword1 == '')
            $errorArray['addNoPassword'] = get_string('errornopassword', 'block_moodletxt');
        else if ($addPassword1 != $addPassword2)
            $errorArray['addNoMatch'] = get_string('errornopasswordmatch', 'block_moodletxt');

        // Check that account does not already exist
        $checkexistence = count_records('block_mtxt_accounts', 'username', $addAccountName);

        if ($checkexistence)
            $errorArray['addExists'] = get_string('erroraccountexists', 'block_moodletxt');

        // Check for admin
        if (! has_capability('block/moodletxt:defaultinbox', $sitecontext, $addDefaultInboxUser)) {

            $errorArray['addDefaultNotAdmin'] = get_string('errorinvaliddefault', 'block_moodletxt');

        } else {

            // Get inbox ID from user
            $defaultinboxobj = get_record('block_mtxt_inbox', 'userid', $addDefaultInboxUser);

            if (is_object($defaultinboxobj))
                $defaultinboxid = $defaultinboxobj->id;
            else
                $defaultinboxid = moodletxt_create_inbox($addDefaultInboxUser);

        }

        if (count($errorArray) == 0) {

            $objectarray = $xmlcontroller->check_account_validity($addAccountName, $addPassword1);

            // Only error objects can be returned from a dummy run
            if (count($objectarray) > 0) {

                $errorarr = moodletxt_get_xml_errors($objectarray);

                $errorArray = $errorArray + $errorarr;

            }

        }

        if (count($errorArray) == 0) {

            // Create class for insertion
            $insertObject = new stdClass;
            $insertObject->username = $addAccountName;
            $insertObject->password = $encrypt_o_matic->encrypt($EK, $addPassword1, 30);
            $insertObject->description = $addDescription;
            if ($CFG->dbtype == 'mssql_n') $insertObject->description = str_replace("\\'", "'", $insertObject->description);
            $insertObject->defaultinbox = moodletxt_escape_string($defaultinboxid);
            $insertObject->lastupdate = 0;
            
            if (insert_record('block_mtxt_accounts', $insertObject)) {

                $noticeArray['addInserted'] = get_string('adminaccountadded', 'block_moodletxt');

                // Clear out form vars
                $addAccountName = '';
                $addDescription = '';

            } else {

                $errorArray['addNotInserted'] = get_string('erroraccountinsertfailed', 'block_moodletxt');

            }

        }

    }

    /*
      ############################################################
      # "EDIT FILTERS" FORM PROCESSING
      ############################################################
    */

    if (($_SERVER["REQUEST_METHOD"] == "POST") && ($formid == "editfilters")) {

        // Read in form vars
        $selectedAccount            = trim(optional_param('filterAccountList', 0, PARAM_INT));
        $existingKeywordFilter      = trim(optional_param('existingKeywordFilterList', 0, PARAM_INT));
        $existingPhoneNumberFilter  = trim(optional_param('existingPhoneNumberFilterList', 0, PARAM_INT));
        $newKeywordFilter           = strtoupper(trim(optional_param('newKeywordFilter', '', PARAM_ALPHANUM)));
        $newPhoneNumberFilter       = trim(optional_param('newPhoneNumberFilter', '', PARAM_RAW));
        $usersOnFilter              = optional_param('usersOnFilter', '', PARAM_RAW);

        // Check that the user has selected a valid account from the list
        if ($selectedAccount <= 0) {
            $errorArray['filterNoAccount'] = get_string('errorfilternoaccount', 'block_moodletxt');
        } else {

            // Verify account ID with database
            $selectedAccountObject = get_record('block_mtxt_accounts', 'id', $selectedAccount);

            if (! is_object($selectedAccountObject))
                $errorArray['filterNoAccount'] = get_string('errorfilternoaccount', 'block_moodletxt');

        }

        // Clean up any potential data cockups on the user list
        if ($usersOnFilter == '') {
            $usersOnFilter = array();
        } else if (! is_array($usersOnFilter)) {
            $usersOnFilter = array($usersOnFilter);
        }

        if ($newPhoneNumberFilter != '' && ! preg_match('/^\+?[0-9]{5,19}$/', $newPhoneNumberFilter))
            $errorArray['invalidNewPhoneFilter'] = get_string('errorfilterbadphoneno', 'block_moodletxt');

        /**
         * If either new filter field is populated, assume
         * we are to create a new filter
         */
        if ($newKeywordFilter != '' || $newPhoneNumberFilter != '') {

            // Check users have been selected
            if (count($usersOnFilter) == 0) {
                $errorArray['noUsersSelected'] = get_string('errorfilternousers', 'block_moodletxt');
            } else {

                $type = ($newKeywordFilter != '') ? 'keyword' : 'phoneno';
                $value = ($newKeywordFilter != '') ? $newKeywordFilter : $newPhoneNumberFilter;

                // Check that the filter does not already exist
                if (count_records('block_mtxt_filter', 'type', $type, 'value', $value))
                    $errorArray['filterExists'] = get_string('errorfilterexists', 'block_moodletxt');

            }

        } else {

            if ($existingKeywordFilter < 1 && $existingPhoneNumberFilter < 1)
                $errorArray['noFilterSelected'] = get_string('errorfilternotselected', 'block_moodletxt');

        }

        /**
         * That's all the required validation. Let's rock!
         */
        if (count($errorArray) == 0) {

            $inboxIds = array();

            // Loop over user IDs passed in and get their inbox IDs
            // Create inboxes where they do not exist
            foreach ($usersOnFilter as $userId) {

                $userInbox = get_record('block_mtxt_inbox', 'userid', addslashes($userId));

                if (is_object($userInbox))
                    array_push($inboxIds, $userInbox->id);
                else
                    array_push($inboxIds, moodletxt_create_inbox($userId));

            }

            if ($newKeywordFilter != '' || $newPhoneNumberFilter != '') {

                // Create and insert new filter record
                $newFilter = new stdClass;
                $newFilter->account = $selectedAccountObject->id;
                $newFilter->type = ($newKeywordFilter != '') ? 'keyword' : 'phoneno';
                $newFilter->value =  ($newKeywordFilter != '') ? $newKeywordFilter : $newPhoneNumberFilter;

                $newFilterId = insert_record('block_mtxt_filter', $newFilter);

                if ($newFilterId) {

                    $noticeArray['filterUpdated'] = get_string('adminnoticefilterupdated', 'block_moodletxt');

                    foreach ($inboxIds as $inboxId) {

                        // Create filter links to inboxes
                        $newFilterLink = new stdClass;
                        $newFilterLink->inbox = $inboxId;
                        $newFilterLink->filter = $newFilterId;

                        insert_record('block_mtxt_in_filter', $newFilterLink);

                    }

                    // Clear form vars
                    $selectedAccount = '';
                    $newKeywordFilter = '';
                    $newPhoneNumberFilter = '';

                }

            } else {

                $filterType = ($existingKeywordFilter > 0) ? 'keyword' : 'phoneno';
                $filterId = ($existingKeywordFilter > 0) ? $existingKeywordFilter : $existingPhoneNumberFilter;

                // Get existing filter links from the database
                $existingFilterLinks = get_records('block_mtxt_in_filter', 'filter', $filterId);

                // Shouldn't happen, but might be old data from < 2.4
                if (! is_array($existingFilterLinks))
                    $existingFilterLinks = array();

                // Extract inbox IDs from filter links received
                $existingFilterLinkedInboxes = array();
                foreach($existingFilterLinks as $filterLink)
                    array_push($existingFilterLinkedInboxes, $filterLink->inbox);

                // Compare inbox IDs to those sent in from the form
                $newFilterLinks = array_diff($inboxIds, $existingFilterLinkedInboxes);
                $removedFilterLinks = array_diff($existingFilterLinkedInboxes, $inboxIds);

                // Create new links
                foreach ($newFilterLinks as $inboxId) {

                    // Create filter links to inboxes
                    $newFilterLink = new stdClass;
                    $newFilterLink->inbox = $inboxId;
                    $newFilterLink->filter = $filterId;

                    insert_record('block_mtxt_in_filter', $newFilterLink);

                }

                // Remove dead links
                foreach($removedFilterLinks as $inboxId) {
                    delete_records('block_mtxt_in_filter', 'filter', $filterId, 'inbox', $inboxId);
                }

                $noticeArray['filterUpdated'] = get_string('adminnoticefilterupdated', 'block_moodletxt');

                // Check to see if any links are left
                if (count_records('block_mtxt_in_filter', 'filter', $filterId) == 0) {

                    // Delete filter from system
                    delete_records('block_mtxt_filter', 'id', $filterId);

                    $noticeArray['filterDeleted'] = get_string('adminnoticefilterdeleted', 'block_moodletxt');

                }

                // Clear form vars
                $selectedAccount = '';
                $existingKeywordFilter = '';
                $existingPhoneNumberFilter = '';

            }

        }

    }

    /*
      ############################################################
      # "SYSTEM SETTINGS" FORM PROCESSING
      ############################################################
    */

    if (($_SERVER["REQUEST_METHOD"] == "POST") && ($formid == "settings")) {

        // Read in form vars
        $setjQuery_Include_Enabled          = trim(optional_param('jQuery_Include_Enabled', 1, PARAM_INT));
        $setjQuery_UI_Include_Enabled       = trim(optional_param('jQuery_UI_Include_Enabled', 1, PARAM_INT));
        $setGet_Status_On_View              = trim(optional_param('Get_Status_On_View', 0, PARAM_INT));
        $setGet_Inbound_On_View             = trim(optional_param('Get_Inbound_On_View', 0, PARAM_INT));
        $setPush_Username                   = trim(optional_param('Push_Username', '', PARAM_TEXT));
        $setPush_Password                   = trim(optional_param('Push_Password', '', PARAM_TEXT));
        $setUse_Protocol                    = trim(optional_param('Use_Protocol', '', PARAM_ALPHA));
        $setProtocol_Warnings_On            = trim(optional_param('Protocol_Warnings_On', 1, PARAM_INT));
        $setRSS_Update_Interval             = trim(optional_param('RSS_Update_Interval', 3600, PARAM_INT));
        $setRSS_Expiry_Length               = trim(optional_param('RSS_Expiry_Length', 604800, PARAM_INT));
        $setNational_Prefix                 = trim(optional_param('National_Prefix', '0', PARAM_ALPHANUM));
        $setDefault_International_Prefix    = trim(optional_param('Default_International_Prefix', '+44', PARAM_RAW));
        $setPhone_Number_Source             = trim(optional_param('Phone_Number_Source', 'phone2', PARAM_ALPHANUM));
        $setDefault_Recipient_Name          = trim(optional_param('Default_Recipient_Name', get_string('configdefaultname', 'block_moodletxt'), PARAM_ALPHANUM));
        $setShow_Inbound_Numbers            = trim(optional_param('Show_Inbound_Numbers', 0, PARAM_INT));
        $setProxy_Host                      = trim(optional_param('Proxy_Host', '', PARAM_RAW));
        $setProxy_Port                      = trim(optional_param('Proxy_Port', 80, PARAM_INT));
        $setProxy_Username                  = trim(optional_param('Proxy_Username', '', PARAM_TEXT));
        $setProxy_Password                  = trim(optional_param('Proxy_Password', '', PARAM_TEXT));

        if ($setjQuery_Include_Enabled != 0)
            $setjQuery_Include_Enabled = 1;

        if ($setjQuery_UI_Include_Enabled != 0)
            $setjQuery_UI_Include_Enabled = 1;

        if ($setGet_Status_On_View != 1)
            $setGet_Status_On_View = 0;

        if ($setGet_Inbound_On_View != 1)
            $setGet_Inbound_On_View = 0;

        if ($setUse_Protocol != 'SSL')
            $setUse_Protocol = 'HTTP';

        if ($setProtocol_Warnings_On != 0)
            $setProtocol_Warnings_On = 1;

        if ($setRSS_Update_Interval <= 0)
            $setRSS_Update_Interval = 3600;

        if ($setRSS_Expiry_Length <= 0)
            $setRSS_Expiry_Length = 604800;

        if ($setPhone_Number_Source != 'phone2')
            $setPhone_Number_Source = 'phone1';

        if ($setShow_Inbound_Numbers != 1)
            $setShow_Inbound_Numbers = 0;


        // Flag to show whether all updates were successful
        $updatesuccess = true;

        $updatesuccess = $updatesuccess &&
            moodletxt_set_setting('jQuery_Include_Enabled', $setjQuery_Include_Enabled);

        $updatesuccess = $updatesuccess &&
            moodletxt_set_setting('jQuery_UI_Include_Enabled', $setjQuery_UI_Include_Enabled);

        // Get current record from DB
        $updatesuccess = $updatesuccess &&
            moodletxt_set_setting('Get_Status_On_View', $setGet_Status_On_View);

        $updatesuccess = $updatesuccess && 
            moodletxt_set_setting('Get_Inbound_On_View', $setGet_Inbound_On_View);

        $updatesuccess = $updatesuccess && 
            moodletxt_set_setting('Push_Username', $setPush_Username);


        if ($setPush_Password != '') {

            // Create new encrypter
            $encrypt_o_matic = new Encryption();

            // Get EK
            $EK = moodletxt_get_setting('EK');

            $setPush_Password = $encrypt_o_matic->encrypt($EK, $setPush_Password, 30);

            $updatesuccess = $updatesuccess && 
                moodletxt_set_setting('Push_Password', $setPush_Password);

        }

        $updatesuccess = $updatesuccess && 
            moodletxt_set_setting('Use_Protocol', $setUse_Protocol);

        $updatesuccess = $updatesuccess && 
            moodletxt_set_setting('Protocol_Warnings_On', $setProtocol_Warnings_On);

        $updatesuccess = $updatesuccess && 
            moodletxt_set_setting('RSS_Update_Interval', $setRSS_Update_Interval);

        $updatesuccess = $updatesuccess && 
            moodletxt_set_setting('RSS_Expiry_Length', $setRSS_Expiry_Length);

        if (eregi("^[0-9]+$", $setNational_Prefix)) {

            $updatesuccess = $updatesuccess && 
                moodletxt_set_setting('National_Prefix', $setNational_Prefix);

        } else {

            $errorArray['settinginvalidnatprefix'] = get_string('errorsetinvalidnatprefix', 'block_moodletxt');
            $updatesuccess = $updatesuccess && false;

        }

        if (eregi("^[+]{1}[0-9]+$", $setDefault_International_Prefix)) {

            $updatesuccess = $updatesuccess && 
                moodletxt_set_setting('Default_International_Prefix', $setDefault_International_Prefix);

        } else {

            $errorArray['settinginvalidprefix'] = get_string('errorsetinvalidprefix', 'block_moodletxt');
            $updatesuccess = $updatesuccess && false;

        }


        $updatesuccess = $updatesuccess && 
            moodletxt_set_setting('Phone_Number_Source', $setPhone_Number_Source);

        $updatesuccess = $updatesuccess && 
            moodletxt_set_setting('Default_Recipient_Name', $setDefault_Recipient_Name);

        $updatesuccess = $updatesuccess && 
            moodletxt_set_setting('Show_Inbound_Numbers', $setShow_Inbound_Numbers);

        $updatesuccess = $updatesuccess && 
            moodletxt_set_setting('Proxy_Host', $setProxy_Host);

        $updatesuccess = $updatesuccess && 
            moodletxt_set_setting('Proxy_Port', $setProxy_Port);

        $updatesuccess = $updatesuccess && 
            moodletxt_set_setting('Proxy_Username', $setProxy_Username);

        $updatesuccess = $updatesuccess &&
            moodletxt_set_setting('Proxy_Password', $setProxy_Password);
        
        if ($updatesuccess)
            $noticeArray['settingupdates'] = get_string('adminsettingsupdated', 'block_moodletxt');
        else
            $errorArray['settingupdatesfailed'] = get_string('errorsettingsupdatefail', 'block_moodletxt');

    }

    /*
      ############################################################
      # CHECK FOR SSL COMPATIBILITY
      ############################################################
    */

    $displayConnError = false;
    $displaySSLWarning = false;

    if (moodletxt_get_setting('Use_Protocol') == 'SSL') {

        // Try SSL connection
        $tryHost = 'www.txttools.co.uk';
        $tryPortSSL = 443;
        $tryPortHTTP = 80;

        if (! $fp = @fsockopen("ssl://" . $tryHost, $tryPortSSL, $errorNo, $errorStr, 30)) {

            // Try HTTP - check this is not just a network error
            if ($fp = @fsockopen($tryHost, $tryPortHTTP, $errorNo, $errorStr, 30)) {

                moodletxt_set_setting('Use_Protocol', 'HTTP');
                moodletxt_set_setting('Protocol_Warnings_On', '1');

                $displayConnError = true;

            } else {

              fclose($fp);

            }

        } else {

            fclose($fp);

        }

    }

    if ((moodletxt_get_setting('Use_Protocol') == 'HTTP') &&
        (moodletxt_get_setting('Protocol_Warnings_On') == '1') &&
        (! $displayConnError)) {

        $displaySSLWarning = true;

    }

    /*
      ############################################################
      # SET UP THE PAGE
      ############################################################
    */

    $title = get_string('admintitle', 'block_moodletxt');

    // Get course list
    $courses = get_courses('all', 'c.shortname ASC', 'c.id, c.shortname, c.fullname, c.category');

    // Get txttols accounts
    $accounts = get_records_sql(moodletxt_get_sql('admingetaccandinbox'));


    // Get "default inbox" list - actually a list of txttools accounts, but hey
    $adminlist = get_users_by_capability($sitecontext, 'block/moodletxt:defaultinbox', 'u.id,u.username,u.firstname,u.lastname', 'u.lastname ASC, u.firstname ASC');

    $defaultinboxlist = '';
    $defaultinboxlist2 = '';

    foreach($adminlist as $thisinbox) {

        $defaultinboxlist .= '<option id="definbox' . $thisinbox->id . '" value="' . $thisinbox->id . '" style="font-weight:bold;">' . $thisinbox->lastname . ', ' .
                    $thisinbox->firstname . ' (' . $thisinbox->username . ')</option>
';

        $defaultinboxlist2 .= '<option value="' . $thisinbox->id . '" style="font-weight:bold;">' . $thisinbox->lastname . ', ' .
                    $thisinbox->firstname . ' (' . $thisinbox->username . ')</option>
';

    }



    // Get all system settings
    $settings = get_records('block_mtxt_config', '', '', '', 'setting, value');

    $pushpath = $CFG->wwwroot . '/blocks/moodletxt/push.php';
    $sslpushpath = preg_replace('(http://)', 'https://', $pushpath);


    // Page header
    $heading = get_string('adminheading', 'block_moodletxt');
    $blocktitle = get_string('blocktitle', 'block_moodletxt');

    $stradmin = get_string('administration');
    $strconfiguration = get_string('configuration');
    $strmanageblocks = get_string('manageblocks');

    // Navigation after Moodle 1.9
    if (function_exists('build_navigation')) {

        $navigation = build_navigation(array(
            array('name' => $stradmin, 'link' => $CFG->wwwroot . '/admin/index.php', 'type' => 'activity'),
            array('name' => $strconfiguration, 'link' => '', 'type' => 'misc'),
            array('name' => $strmanageblocks, 'link' => $CFG->wwwroot . '/admin/blocks.php', 'type' => 'category'),
            array('name' => $blocktitle, 'link' => '', 'type' => 'title')
        ));

        print_header_simple(
            $title,
            $heading,
            $navigation
        );

    // Navigation before Moodle 1.9
    } else {

        print_header($title, $heading, '<a href="' . $CFG->wwwroot . '/admin/index.php">' . $stradmin . '</a> ->
                                        ' . $strconfiguration . ' ->
                                        <a href="' . $CFG->wwwroot . '/admin/blocks.php">' . $strmanageblocks . '</a> ->
                                        ' . $blocktitle, '', false, '&nbsp;');

    }

    print_heading($heading);

    $connErrorString = '';

    // Create error string if connection fails
    if ($displayConnError) {

        $connErrorString = '
    <h2>' . get_string('adminheadersslfailed', 'block_moodletxt') . '</h2>
    <p>
        ' . get_string('adminsslfailedpara1', 'block_moodletxt') . '
    </p>
    <p>
        ' . get_string('adminsslfailedpara2', 'block_moodletxt') . '
    </p>';

    }

    /*
      ############################################################
      # DISPLAY FULL CONTROL PANEL
      ############################################################
    */

    // If the system already has accounts stored, then display the full panel
    if ((is_array($accounts)) && (count($accounts) > 0)) {

        /*
          ############################################################
          # FETCH RSS FEED
          ############################################################
        */

        $lastupdate = moodletxt_get_setting('RSS_Last_Update');
        $updateinterval = moodletxt_get_setting('RSS_Update_Interval');

        $waittime = $lastupdate + $updateinterval;

        // If we have passed the waiting time, go fetch some RSS!
        if (time() > $waittime) {

          $RSSobjects = $xmlcontroller->get_rss_update();

          moodletxt_write_objects($RSSobjects);

          moodletxt_set_setting('RSS_Last_Update', time());

        }

        // Get latest un-expired RSS entry for display
        $sqlfrag = moodletxt_get_sql('admingetrssrecord');
        $sqlfrag = sprintf($sqlfrag, time());

        $latestRSSitem = get_record_sql($sqlfrag, false, true);


        /*
          ############################################################
          # CREATE CONTENT OUTPUT STRINGS
          ############################################################
        */

        $jsarray = '';
        $SSLErrorString = '';
        $RSSstring = '';
        $accountListString = '';
        $filterAccountListString = '';

        // Create Javascript array string
        foreach ($accounts as $account) {

            if ($account->userid != null) {

                $jsarray .= "inboxarr['" . $account->id . "'] = " . $account->userid . ";
";

            }

        }

        // Create error string if SSL fails
        if ($displaySSLWarning) {

            $SSLErrorString = '
    <h2>' . get_string('adminheadersslwarning', 'block_moodletxt') . '</h2>
    <p>
        ' . get_string('adminsslwarnpara1', 'block_moodletxt') . '
    </p>
    <p>
        ' . get_string('adminsslwarnpara2', 'block_moodletxt') . '
    </p>';

        }

        // Create RSS item if one needs to be displayed
        if ($latestRSSitem != false) {

            $RSSstring = '
    <!--
        RSS UPDATE
    -->
    <h2>' . get_string('adminheaderrssupdate', 'block_moodletxt') . '</h2>
    <div class="rssitem">
        <h3 class="rssitem">
            ' . userdate($latestRSSitem->pubtime, "%H:%M:%S,  %d %B %Y") . '<br />
            <a href="' . $latestRSSitem->link . '">' . $latestRSSitem->title . '</a>
        </h3>
        <p class="rssbody">
          ' . $latestRSSitem->description . '
        </p>
    </div>';

        }

        // Create account list for display
        $disableString = ' disabled="disabled"';

        foreach ($accounts as $account) {

            $accountListString .= '
                        <option value="' . $account->id . '">' . $account->username . '</option>';

            $filterAccountListString .= '
                        <option value="' . $account->id . '"';

            $filterAccountListString .= (isset($selectedAccount) && $account->id == $selectedAccount) ? ' selected="selected"' : '';
            $filterAccountListString .= '>' . $account->username . '</option>';

        }

        require_once($CFG->dirroot . '/blocks/moodletxt/admin_output.php');

    } else {

        /*
          ############################################################
          # OUTPUT "NEW INSTALLATION" PAGE
          ############################################################
        */

        require_once($CFG->dirroot . '/blocks/moodletxt/admin_output_new.php');

    }

    print_footer();

?>
