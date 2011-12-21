<?php

    /**
     * Page to show individual recipients and status information
     * for a given message
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011030702
     * @since 2006081012
     */

    /*
      ############################################################
      # SET UP
      ############################################################
    */

    // Get config and required libraries
    require_once('../../config.php');
    require_once($CFG->libdir  . '/datalib.php');
    require_once($CFG->libdir  . '/tablelib.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/lib.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/db/sqllib.php');

    $columnstofields = array('user' => 'user.username',
                             'phone' => 'sent.destination',
                             'time' => 'latestupdate',
                             'status' => 'latestupdate');

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

    // Check that user is allowed to use moodletxt
    $blockcontext = get_context_instance(CONTEXT_BLOCK, $instanceid);
    require_capability('block/moodletxt:sendmessages', $blockcontext, $USER->id);


    // Get ID of message to view
    $messageid = required_param('messageid', PARAM_INT);
    
    // Get XML connector classes
    require_once($CFG->dirroot . '/blocks/moodletxt/xml/moodletxt_xml_controller.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/encryption.php');

    
    /*
      ############################################################
      # Check that user is authorised to be here
      ############################################################
    */

    // Check that message exists
    $thismessage = get_record('block_mtxt_outbox', 'id', $messageid);

    if (! $thismessage) {

        error(get_string('errorbadmessageid', 'block_moodletxt'));

    } else {

        // Check that the user is authorised to view this message
        if ($thismessage->userid != $USER->id && ! has_capability('block/moodletxt:adminusers', $blockcontext, $USER->id))
            error(get_string('errornopermissionmessage', 'block_moodletxt'));

    }

    $p = $CFG->prefix;

    // Check to see whether the status of this message should be updated
    $setGetStatus = moodletxt_get_setting('Get_Status_On_View');

    $inUpdateStatus = optional_param('updatestatus', 0, PARAM_INT);

    if (($setGetStatus == 1) || ($inUpdateStatus == 1)) {

        /*
          ############################################################
          # Update the stored status for this message
          ############################################################
        */

        $sql = moodletxt_get_sql('sentgetfinishedmessages');

        $finalrecords = get_records_sql($sql);

        // Get tickets that DO need updating
        $sql = moodletxt_get_sql('sentgetunfinishedmessages1');

        // Bug fix for empty ADODB recordsets
        if ((is_array($finalrecords)) && (count(array_keys($finalrecords)) > 0)) {

        $sql .= ' ' . moodletxt_get_sql('sentgetunfinishedmessages2');

            foreach ($finalrecords as $ticket) {

                $sql .= $ticket->ticketnumber . "', '";

            }

            $sql = substr($sql, 0, (strlen($sql) - 3)) . ')';

        }

        $ticketnumbers = get_records_sql($sql);

        // Put ticket numbers into sequentially-indexed array
        $ticketarray = array();

        if (is_array($ticketnumbers)) {

            foreach($ticketnumbers as $number) {

                array_push($ticketarray, $number->ticketnumber);

            }

        }

        if (count($ticketarray) > 0) {

            // Get status updates from txttools and write to DB
            $xmlcontroller = new moodletxt_xml_controller();

            $statusobjects = $xmlcontroller->get_status_updates($ticketarray,
                            $thismessage->txttoolsaccount);

            moodletxt_write_objects($statusobjects);

        }

    }

    /*
      ############################################################
      # Everything's updated - let's grab the info for display!
      ############################################################
    */

    $sql = moodletxt_get_sql('viewgetmessagedetails');
    $sql = sprintf($sql, moodletxt_escape_string($messageid));

    $thismessage = get_record_sql($sql);

    // Count number of recipients
    $recipientcount = count_records('block_mtxt_sent', 'messageid', $thismessage->id);

    // Get paging information for output table
    $perpage = 30;
    $pagenumber = optional_param('page', -1, PARAM_INT);

    // Check for page number parameter
    if ($pagenumber >= 0) {

        // Save to session
        $SESSION->flextable['blocks-moodletxt-messagestatusreports']->page = $pagenumber;

    } else {

        // Load from session
        if (isset($SESSION->flextable['blocks-moodletxt-messagestatusreports']->page)) {

            $pagenumber = $SESSION->flextable['blocks-moodletxt-messagestatusreports']->page;

        } else {

            $pagenumber = 0;

        }

        $_GET['page'] = $pagenumber;

    }

     // Create results table
    $table = new flexible_table('blocks-moodletxt-messagestatusreports');

    // Set structure
    $tablecolumns = array('user', 'phone', 'time', 'status');
    $tableheaders = array(
                        get_string('viewtableheaderuser', 'block_moodletxt'),
                        get_string('viewtableheaderphone', 'block_moodletxt'),
                        get_string('viewtableheadertime', 'block_moodletxt'),
                        get_string('viewtableheaderstatus', 'block_moodletxt'),
                    ); // ;)

    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);

    $table->sortable(true, 'latestupdate', 'DESC');
    $table->pagesize($perpage, $recipientcount);
    $table->collapsible(true);

    // Set styling/attributes
    $table->set_attribute('align', 'center');
    $table->set_attribute('width', '75%');
    $table->column_style_all('text-align', 'center');

    $table->setup();
    // Load sorting data
    $sortby = array();

    $sortbyarr = $SESSION->flextable['blocks-moodletxt-messagestatusreports']->sortby;

    if ((is_array($sortbyarr)) && (count($sortbyarr) > 0)) {

        foreach($sortbyarr as $field => $direction) {

            // Create sort fields
            $thissortby = $columnstofields[$field] . ' ';
            $thissortby .= ($direction == SORT_DESC) ? 'DESC' : 'ASC';
            array_push($sortby, $thissortby);

        }

    } else {

        array_push($sortby, $columnstofields['time'] . ' DESC');

    }

    // Get final result set
    $startrecord = $perpage * $pagenumber;

    $getsentmessages = moodletxt_get_message_recipients($thismessage->id, $sortby, $perpage, $startrecord);

    $sentmessageids = (is_array($getsentmessages)) ? array_keys($getsentmessages) : array();
    $idstring = "('" . implode("', '", $sentmessageids) . "')";
    
    $sql = moodletxt_get_sql('viewgetrecipientusers');
    $sql = sprintf($sql, moodletxt_escape_string($thismessage->id), $idstring);
    $recipientusers = get_records_sql($sql);
    
    $sql = moodletxt_get_sql('viewgetrecipientcontacts');
    $sql = sprintf($sql, moodletxt_escape_string($thismessage->id), $idstring);
    $recipientcontacts = get_records_sql($sql);

    $criteria = array();

    if (is_array($getsentmessages) && count($getsentmessages) > 0) {

        foreach ($getsentmessages as $sentmessage) {

            array_push($criteria, array($sentmessage->ticketnumber, $sentmessage->latestupdate));

        }

        // Get latest status updates for each message from the database
        // (Required because sub-queries are not supported in MySQL below 4.1)
        // GAH! If anyone knows a more efficient way, contact me, PLEASE!
        $sql = '';

        for ($x = 0; $x < count($criteria); $x++) {

            $sql .= '(ticketnumber = \'' . $criteria[$x][0] . '\' ';
            $sql .= 'AND updatetime = \'' . $criteria[$x][1] . '\')';

            if ($x < (count($criteria) - 1)) {

                $sql .= ' OR ';

            }

        }

        // Get the little beggars
        $statusupdates = get_records_select('block_mtxt_status', $sql, '', 'ticketnumber, status, statusmessage');
        $defaultRecipientName = moodletxt_get_setting('Default_Recipient_Name');

        // Populate table
        foreach ($getsentmessages as $sentmessage) {

            // Get current status record
            $curstatus = $statusupdates[$sentmessage->ticketnumber];

            if (is_object($recipientusers[$sentmessage->id])) {

                $recipient = $recipientusers[$sentmessage->id];
                $userlink = $recipient->lastname . ', ' . $recipient->firstname . ' (<a href="' . $CFG->wwwroot . 
                    '/user/view.php?id=' . $recipient->userid . '">' . $recipient->username . '</a>)';

            } else if (is_object($recipientcontacts[$sentmessage->id])) {
                
                $recipient = $recipientcontacts[$sentmessage->id];

                if ($recipient->lastname == '' && $recipient->firstname == '')
                    $userlink = $recipient->company;
                else
                    $userlink = $recipient->lastname . ', ' . $recipient->firstname;
                
            } else if ($sentmessage->sendname != '') {

                $userlink = $sentmessage->sendname;

            } else {

                $userlink = $defaultRecipientName;

            }

            $statusimage = "";
            $alttext = "";
            $titletext = "";

            // Decide which status flag to use
            switch($curstatus->status) {

                // WIN or network failed
                case 2:

                    $statusimage = "status_red.gif";
                    $alttext = get_string('statuskeyfailedalt', 'block_moodletxt');
                    $titletext = get_string('statuskeyfailedtitle', 'block_moodletxt');

                    break;

                // Message is on its way
                case 1:
                case 3:
                case 4:

                    $statusimage = "status_yellow.gif";
                    $alttext = get_string('statuskeysentalt', 'block_moodletxt');
                    $titletext = get_string('statuskeysenttitle', 'block_moodletxt');

                    break;

                // Message has arrived. Ra!
                case 5:

                    $statusimage = "status_green.gif";
                    $alttext = get_string('statuskeyreceivedalt', 'block_moodletxt');
                    $titletext = get_string('statuskeyreceivedtitle', 'block_moodletxt');

                    break;

                // It should never get here, but just in case...
                default:

                    $statusimage = "status_yellow.gif";

            }

            // Create table row

            $updatetime = userdate($sentmessage->latestupdate, "%H:%M:%S,  %d %B %Y");

            $statuscell = '<img src="pix/' . $statusimage . '" width="16" height="17" alt="' . $alttext . '" title="' . $titletext . '" />';

            $table->add_data(array($userlink, $sentmessage->destination, $updatetime, $statuscell));

        }

    }



    // Set up page
    $title = get_string('messageviewtitle', 'block_moodletxt');
    $heading = get_string('messageview', 'block_moodletxt');
    $blocktitle = get_string('blocktitle', 'block_moodletxt');

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

    // Print header
    print_heading($heading);

    // Print results table
    require_once($CFG->dirroot . '/blocks/moodletxt/viewmessage_output.php');

    // Get footer
    print_footer();

?>
