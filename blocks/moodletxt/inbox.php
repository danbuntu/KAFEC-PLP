<?php

    /**
     * User inbox page for MoodleTxt
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011030701
     * @since 2007031512
     */

    /*
      ############################################################
      # SET UP
      ############################################################
    */

    // Get config and required libraries
    require_once('../../config.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/lib.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/db/sqllib.php');

    // Get files required for XML connection to get inbound messages
    require_once($CFG->dirroot . '/blocks/moodletxt/xml/moodletxt_xml_controller.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/inbound/InboundFilterManager.php');

    // Get encryption class
    require_once($CFG->dirroot . '/blocks/moodletxt/encryption.php');

    $errorArray = array();
    $noticeArray = array();

    // Check for course ID
    $courseid = $_SESSION['moodletxt_last_course'];
    $instanceid = $_SESSION['moodletxt_last_instance'];

    if (! empty($courseid) && ! $course = get_record('course', 'id', $courseid)) {
        error(get_string('errorbadcourseid', 'block_moodletxt'));
    }

    if (empty($instanceid) || ! is_int($instanceid))
        error(get_string('errorbadinstanceid', 'block_moodletxt'));

    // User MUST be logged in
    require_login($course->id, false);

    if (count_records('block_mtxt_accounts') == 0)
        error(get_string('errornoaccountspresent', 'block_moodletxt'));
    

    // Check that user is allowed to receive messages
    $blockcontext = get_context_instance(CONTEXT_BLOCK, $instanceid);
    require_capability('block/moodletxt:receivemessages', $blockcontext, $USER->id);

    // Check that this user has an inbox record
    $userinbox = get_record('block_mtxt_inbox', 'userid', $USER->id);

    if (! is_object($userinbox))
        error(get_string('errornopermission', 'block_moodletxt'));

    /*
     * Now that we know this user has an inbox record, it's time to
     * check with the txttools system for new messages!
     * This is about as clever as I can make it.  Checking every account for inbound
     * at the same time is going to be VERY time consuming. Methinks not. Instead, the
     * system checks for accounts linked to the user's inbox, either through filters
     * or as defaults.  array_merge() wipes out any duplicates.  If the inbox doesn't
     * have either, then there's not really any point bothering!
     */
    $setGetMessages = moodletxt_get_setting('Get_Inbound_On_View');

    if (! isset($inGetMessages))
        $inGetMessages = 0;

    if (($setGetMessages == 1) || ($inGetMessages == 1)) {

        // Get txttools accounts linked via filter
        $sql = moodletxt_get_sql('inboxgetlinkedaccounts');
        $sql = sprintf($sql, moodletxt_escape_string($userinbox->id));
        $filterlinks = get_records_sql($sql);

        // Get txttools accounts linked as defaults
        $defaultlinks = get_records_select('block_mtxt_accounts',
                'defaultinbox = \'' . moodletxt_escape_string($userinbox->id) . '\' AND inboundenabled = \'1\'');

        // Merge record sets - this is the final list of accounts to be checked
        $finallinks = moodletxt_merge_recordsets($filterlinks, $defaultlinks);

        // Create XML objects
        $xmlcontroller = new moodletxt_xml_controller();
        $filterManager = new InboundFilterManager();

        $inboundmessagesets = $xmlcontroller->get_inbound_messages($finallinks);
        $xmlerrors = moodletxt_get_xml_errors($inboundmessagesets, 'moodletxt_connector_error');

        if (count($xmlerrors) > 0)
            array_push($errorArray, get_string('errorinboxcantconnect', 'block_moodletxt'));

        // Filter into correct folders
        $accountids = array_keys($inboundmessagesets);

        foreach ($accountids as $accid) {

            $filteredObjects = $filterManager->filterMessages($inboundmessagesets[$accid]);
            moodletxt_write_objects($inboundmessagesets[$accid]);

        }

    }

    // Get user inbox list
    $sql = moodletxt_get_sql('inboxgetinboxlist');
    $inboxlist = get_records_sql($sql);

    // Get user folder list
    $folderlist = get_records('block_mtxt_in_folders', 'inbox', moodletxt_escape_string($userinbox->id), 'name ASC');

    // Check for folder ID
    $folderid = optional_param('folder', 0, PARAM_INT);
    $userfolder = null;

    if ($folderid < 1) {

        $userfolder = get_record('block_mtxt_in_folders', 'inbox', moodletxt_escape_string($userinbox->id),
                                 'name', 'Inbox', 'candelete', 0);

    } else {

        $userfolder = get_record('block_mtxt_in_folders', 'inbox', moodletxt_escape_string($userinbox->id), 'id',
                                     $folderid);

        if (! is_object($userfolder)) {

            $userfolder = get_record('block_mtxt_in_folders', 'inbox', moodletxt_escape_string($userinbox->id),
                                         $folderid);

        }

    }

     /**
     * POST PROCESSING
     */

    $inMessageIDs = array();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $inMessageIDs = optional_param('messageids', null, PARAM_RAW);
        $inListSwitch = optional_param('folderorinbox', 'folder', PARAM_ALPHA);
        $inSelectedAction = optional_param('selectedaction', '', PARAM_ALPHA);
        $inInboxList = optional_param('inboxlist', 0, PARAM_INT);
        $inFolderList = optional_param('folderlist', 0, PARAM_INT);

        // Clean array
        $inMessageIDs = clean_param($inMessageIDs, PARAM_INT);

        // Copy to folder or account?
        if ($inListSwitch == 'inbox') {

            $destinationFolder = $inInboxList;

        } else {

            $destinationFolder = $inFolderList;

        }

        if (is_array($inMessageIDs)) {

            switch ($inSelectedAction) {

                case 'killmaimburn':

                    if ($userfolder->name == 'Trash') {

                        $sqlfrag = 'folderid = ' . moodletxt_escape_string($userfolder->id) . ' AND
                            id IN (\'' . implode("', '", $inMessageIDs) . '\')';

                        delete_records_select('block_mtxt_in_mess', $sqlfrag);

                        break;

                    } else {

                        $inSelectedAction = 'move';

                        // Get trash folder ID
                        $trashcan = get_record('block_mtxt_in_folders', 'inbox', moodletxt_escape_string($userinbox->id),
                                'name', 'Trash', 'candelete', 0);

                        $destinationFolder = $trashcan->id;

                    }



                case 'move':

                    if ($destinationFolder == 0) {

                        $errorArray['noInboxSelected'] = 'No inbox/folder was selected to move the messages to.';

                        break;

                    } else {

                        if (is_array($inMessageIDs)) {

                            // Check inbox folder exists
                            $checkfolder = count_records('block_mtxt_in_folders', 'id', moodletxt_escape_string($destinationFolder),
                                        'name', 'Inbox', 'candelete', 0);

                            if ($checkfolder == 0) {

                                // Check that this is not a user folder
                                $checktrash = count_records('block_mtxt_in_folders', 'id', moodletxt_escape_string($destinationFolder),
                                    'inbox', moodletxt_escape_string($userinbox->id));

                                if ($checktrash == 0) {

                                    $errorArray['folderDoesNotExist'] = 'The folder ID given does not exist, or you are not authorised to move messages to it.  Possible form hacking detected.  Knock it off.';

                                    break;

                                }

                            }

                            // Check message IDs
                            $selectfrag = "id IN ('" . implode("', '", $inMessageIDs) . "')
                                AND folderid = '" . moodletxt_escape_string($userfolder->id) . "'";

                            $messagestomove = get_records_select('block_mtxt_in_mess', $selectfrag);

                            $finalmessageids = "'" . implode("', '", array_keys($messagestomove)) . "'";

                            // Move messages!
                            $sql = moodletxt_get_sql('inboxmovemessages');
                            $sql = sprintf($sql, moodletxt_escape_string($destinationFolder), $finalmessageids);

                            // Sorry for using execute_sql! But what else was I gonna use?
                            execute_sql($sql, false);

                        }

                        break;

                    }



                case 'copy':

                    if ($destinationFolder == 0) {

                        $errorArray['noInboxSelected'] = 'No inbox/folder was selected to copy the messages to.';

                        break;

                    } else {

                        if (is_array($inMessageIDs)) {

                            // Check inbox folder exists
                            $checkfolder = count_records('block_mtxt_in_folders', 'id', moodletxt_escape_string($destinationFolder),
                                        'name', 'Inbox', 'candelete', 0);

                            if ($checkfolder == 0) {

                                // Check that this is not a user folder
                                $checktrash = count_records('block_mtxt_in_folders', 'id', moodletxt_escape_string($destinationFolder),
                                    'inbox', moodletxt_escape_string($userinbox->id));

                                if ($checktrash == 0) {

                                    $errorArray['folderDoesNotExist'] = 'The folder ID given does not exist.  Possible form hacking detected.  Knock it off.';

                                    break;

                                }

                            }

                            $selectfrag = "id IN ('" . implode("', '", $inMessageIDs) . "')
                                AND folderid = '" . moodletxt_escape_string($userfolder->id) . "'";

                            $messagestocopy = get_records_select('block_mtxt_in_mess', $selectfrag);

                            foreach($messagestocopy as $msg) {

                                $msg->id = null;
                                $msg->folderid = moodletxt_escape_string($destinationFolder);

                                insert_record('block_mtxt_in_mess', $msg);

                            }

                        }

                        break;

                    }

            }

        }

    }

    // Set up page
    $title = get_string('inboxtitle', 'block_moodletxt');
    $heading = get_string('inboxheader', 'block_moodletxt');
    $blocktitle = get_string('blocktitle', 'block_moodletxt');

    // Get paging information for output table
    $perpage = 30;
    $pagenumber = optional_param('page', -1, PARAM_INT);

    // Check for page number parameter
    if ($pagenumber >= 0) {

        // Save to session
        $SESSION->flextable['blocks-moodletxt-inboxmessages']->page = $pagenumber;

    } else {

        // Load from session
        if (isset($SESSION->flextable['blocks-moodletxt-inboxmessages']->page)) {

            $pagenumber = $SESSION->flextable['blocks-moodletxt-inboxmessages']->page;

        } else {

            $pagenumber = 0;

        }

        $_GET['page'] = $pagenumber;

    }

    // Count number of records available for display
    $messagetotal = count_records('block_mtxt_in_mess', 'folderid', moodletxt_escape_string($userfolder->id));

    // Get whether or not to show phone numbers
    $showInboundNumbers = moodletxt_get_setting('Show_Inbound_Numbers');

    // Create results table
    $table = new flexible_table('blocks-moodletxt-inboxmessages');

    
    if ($showInboundNumbers) {

        // Set structure
        $tablecolumns = array("checkbox", "ticket", "messagetext", "source", "sourcename", "timereceived", "options");

        $tableheaders = array('',
                            get_string('inboxtableheaderticket', 'block_moodletxt'),
                            get_string('inboxtableheadermessage', 'block_moodletxt'),
                            get_string('inboxtableheaderphone', 'block_moodletxt'),
                            get_string('inboxtableheadername', 'block_moodletxt'),
                            get_string('inboxtableheadertime', 'block_moodletxt'),
                            get_string('inboxtableheaderoptions', 'block_moodletxt')
                        ); // ;)

    } else {

        // Set structure
        $tablecolumns = array("checkbox", "ticket", "messagetext", "timereceived", "options");

        $tableheaders = array('',
                            get_string('inboxtableheaderticket', 'block_moodletxt'),
                            get_string('inboxtableheadermessage', 'block_moodletxt'),
                            get_string('inboxtableheadertime', 'block_moodletxt'),
                            get_string('inboxtableheaderoptions', 'block_moodletxt')
                        ); // ;)

    }

    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->column_class('checkbox', 'mdltxt_columnline');
    $table->column_class('ticket', 'mdltxt_columnline');
    $table->column_class('messagetext', 'mdltxt_columnline');
    $table->column_class('source', 'mdltxt_columnline');
    $table->column_class('sourcename', 'mdltxt_columnline');
    $table->column_class('timereceived', 'mdltxt_columnline');

    // Set styling/attributes
    $table->sortable(true, 'timereceived', 'ASC');
    $table->pagesize($perpage, $messagetotal);
    $table->collapsible(true);

    $table->set_attribute('id', 'blocks-moodletxt-inboxmessages');
    $table->set_attribute('class', 'mdltxt_resultlist mdltxt_fullwidth');

    $table->setup();

    /*
      ############################################################
      # GET MESSAGES FROM DB AND FORMAT/PROCESS
      ############################################################
    */

    // Store whether or not to show message controls
    // Have to use boolean because empty ADODB sets do not have count == 0
    $showControls = false;

    // Load sorting data
    $tsort = optional_param('tsort', '', PARAM_ALPHA);
    if ($tsort == 'checkbox' || $tsort == 'options') $_GET['tsort'] = 'ticket';

    $sortby = '';
    $inverseSortBy = ''; //  Getting around MS SQL's complete lack of OFFSET

    $sortbyarr = $SESSION->flextable['blocks-moodletxt-inboxmessages']->sortby;

    if (is_array($sortbyarr)) {

        foreach($sortbyarr as $field => $direction) {

            // Create sort fields
            if ($sortby != '') {
                $sortby .= ', ';
                $inverseSortBy .= ', ';
            }

            $sortby .= 'messages.' . $field . ' ';
            $inverseSortBy .= 'messages.' . $field . ' ';

            $sortby .= ($direction == SORT_DESC) ? 'DESC' : 'ASC';
            $inverseSortBy .= ($direction == SORT_DESC) ? 'ASC' : 'DESC';

        }

    } else {

        $sortby = 'messages.timereceived DESC';

    }

    // Get messages for this folder
    $startrecord = $pagenumber * $perpage;

    $sql = moodletxt_get_sql('inboxgetmessages');
    $sql = sprintf($sql, moodletxt_escape_string($userfolder->id), $sortby, $startrecord, $perpage, ($startrecord + $perpage), $inverseSortBy);

    $usermessages = get_records_sql($sql);

    if (is_array($usermessages)) {

        foreach ($usermessages as $message) {

            // Format table cells
            $timereceived = userdate($message->timereceived, "%H:%M:%S,  %d %B %Y");

            // Create checkbox
            $checkboxfrag = '<input type="checkbox" id="msgchk' . $message->id . '" name="messageids[]" value="' . $message->id . '" />';

            $messagesourcetype = '';
            $messagesourcevalue = '';
            $sourcecell = '';

            // Check type of course contact - Moodle user
            if ($message->userid != '') {

                $sourcecell = $message->userlast . ', ' . $message->userfirst . ' (' . '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $message->userid . '">' . $message->username . '</a>)';
                $messagesourcetype = 'user';
                $messagesourcevalue = $message->userid;

            } else if ($message->contactid != '') {

                // Address book contact
                if ($message->contactlast != '') {
                    $sourcecell = $message->contactlast . ', ' . $message->contactfirst;
                    $messagesourcetype = 'addressbook';
                    $messagesourcevalue = $message->contactid;

                // Additional contact
                } else {
                    $sourcecell = $message->contactcompany;
                    $messagesourcetype = 'additional';
                    $messagesourcevalue = str_replace("+", "%2B", $message->source);
                }

            } else {

                $sourcecell = $message->sourcename;
                $messagesourcetype = 'additional';
                $messagesourcevalue = str_replace("+", "%2B", $message->source);

            }

            $optionsfrag = '<a href="" onclick="javascript:return confirmDelete(' . $message->id . ');">
                                <img src="pix/sms_delete.png" width="16" height="16" alt="Delete" />
                            </a>
                            <a href="sendmessage.php?courseid=' . $courseid . '&instanceid=' . $instanceid . '&replytype=' . $messagesourcetype . '&replyvalue=' . $messagesourcevalue . '">
                                <img src="pix/sms_reply.png" width="16" height="16" alt="Reply" />
                            </a>';

            if ($showInboundNumbers) {

                // Add table row
                $table->add_data(array($checkboxfrag, $message->ticket, $message->messagetext, $message->source,
                                $sourcecell, $timereceived, $optionsfrag));

            } else {

                // Add table row
                $table->add_data(array($checkboxfrag, $message->ticket, $message->messagetext, $timereceived, $optionsfrag));

            }

            $showControls = true;

        }

        // Update messages to be set as read
        $sql = moodletxt_get_sql('inboxmarkfolderread');
        $sql = sprintf($sql, moodletxt_escape_string($userfolder->id));

        execute_sql($sql, false);

    }

    /*
      ############################################################
      # READY CONTENT OUTPUT
      ############################################################
    */

    $selfolderlist = '';
    $selinboxlist = '';
    $folderlinkcid = '';

    if (is_array($folderlist)) {

        foreach($folderlist as $row) {

            $selfolderlist .= '                <option value="' . $row->id . '">' . $row->name . '</option>\n';

        }

    }

    if (is_array($inboxlist)) {

        foreach($inboxlist as $row) {

            $selinboxlist .= '                    <option value="' . $row->id . '">' . $row->lastname . ', ' .
                $row->firstname . ' (' . $row->username . ')</option>\n';

        }

    }

    /*
      ############################################################
      # BEGIN CONTENT OUTPUT
      ############################################################
    */

    // Navigation after Moodle 1.9
    if (function_exists('build_navigation')) {

        $coursefragment = (is_object($course) && $course->id > 0) ? 'courseid=' . $course->id . '&' : '';

        $navigation = build_navigation(array(
            array('name' => $blocktitle, 'link' => 'moodletxt.php?' . $coursefragment, 'type' => 'misc'),
            array('name' => $heading, 'link' => '', 'type' => 'title')
        ));

        print_header_simple(
            $title . ' ' . $USER->username,
            $heading,
            $navigation
        );

    // Navigation before Moodle 1.9
    } else {

        // Print header
        if (is_object($course) && $course->id > 0) {

            print_header($title . ' ' . $USER->username, $heading,
                '<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '">' . $course->shortname . '</a>
                -> <a href="moodletxt.php?courseid=' . $course->id . '">' . $blocktitle . '</a>
                -> ' . $heading, '', '', false, '&nbsp;');

        } else {

            print_header($title . ' ' . $USER->username, $heading,
                '<a href="moodletxt.php">' . $blocktitle . '</a>
                -> ' . $heading, '', '', false, '&nbsp;');

        }

    }

    print_heading($heading);

    // Grab output file
    require_once($CFG->dirroot . '/blocks/moodletxt/inbox_output.php');

    // Grab footer
    print_footer();

?>
