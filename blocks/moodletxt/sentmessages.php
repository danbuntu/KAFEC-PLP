<?php

    /**
     * Page to show history of messages sent through MoodleTxt
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011030701
     * @since 2006081012
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

    $columnstofields = array('user' => 'moodleuser',
                             'account' => 'acc.username',
                             'message' => 'o.messagetext',
                             'time' => 'o.timesent');

    // Check to see if status updates were requested
    $updateStatus = optional_param('update', 0, PARAM_BOOL);

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

    // Check that user is allowed to send messages
    $blockcontext = get_context_instance(CONTEXT_BLOCK, $instanceid);
    require_capability('block/moodletxt:sendmessages', $blockcontext, $USER->id);

    $datetoview = '';
    $usertoview = 0;
    $countcriteria = '';
    $selectcriteria = '';

    /*
      ############################################################
      # Check whether the user has specified that message status
      # records are to be updated now
      #
      # Note: this isn't actually used yet, but it's here for if/
      # when that functionality is added
      ############################################################
    */

    if ($updateStatus == 1) {

        // Get XML connector files
        require_once($CFG->dirroot . '/blocks/moodletxt/xml/moodletxt_xml_controller.php');
        require_once($CFG->dirroot . '/blocks/moodletxt/data/moodletxt_message_status.php');
        require_once($CFG->dirroot . '/blocks/moodletxt/encryption.php');

        // Time for some more subquery-free fun!

        $p = $CFG->prefix;

        // Get all tickets that do not need updating
        $sql = moodletxt_get_sql('sentgetfinishedmessages');
        $sql = sprintf($sql, 2, 5);

        $finalrecords = get_records_sql($sql);

        // Get tickets that DO need updating
        $sql = moodletxt_get_sql('sentgetunfinishedmessages1');

        // Bug fix for empty ADODB recordsets
        if ((is_array($finalrecords)) && (count(array_keys($finalrecords)) > 0)) {

            $sql .= moodletxt_get_sql('sentgetunfinishedmessages2');

            foreach ($finalrecords as $ticket) {

                $sql .= $ticket->ticketnumber . "', '";

            }

            $sql = substr($sql, 0, (strlen($sql) - 3)) . ')';

        }

        $ticketstoupdate = get_records_sql($sql);

        // Build array to pass to XML controller
        $tickets = array();

        // Bug fix for empty ADODB recordsets
        if ((is_array($ticketstoupdate)) && (count(array_keys($ticketstoupdate)) > 0)) {

            foreach ($ticketstoupdate as $ticket) {

                array_push($tickets, $ticket->ticketnumber);

            }


            // Now that the messy bit is over, let's go do some updating!

            // Create XML controller object
            $xmlcontroller = new moodletxt_xml_controller();

            foreach($userLinks as $link) {

                $statusobjects = $xmlcontroller->get_status_updates($tickets,
                    $link->id);

                // Write updates to database
                moodletxt_write_objects($statusobjects);

            }

        }

    }

    // Set up page
    $title = get_string('historytitle', 'block_moodletxt');
    $heading = get_string('historyview', 'block_moodletxt');
    $blocktitle = get_string('blocktitle', 'block_moodletxt');

    // Get paging information for output table
    $perpage = 30;
    $pagenumber = optional_param('page', -1, PARAM_INT);

    // Check for page number parameter
    if ($pagenumber >= 0) {

        // Save to session
        $SESSION->flextable['blocks-moodletxt-sentmessages']->page = $pagenumber;

    } else {

        // Load from session
        if (isset($SESSION->flextable['blocks-moodletxt-sentmessages']->page)) {

            $pagenumber = $SESSION->flextable['blocks-moodletxt-sentmessages']->page;

        } else {

            $pagenumber = 0;

        }

        $_GET['page'] = $pagenumber;

    }

    /*
      ############################################################
      # GET MESSAGES FROM DB AND FORMAT/PROCESS
      ############################################################
    */

    $p = $CFG->prefix;

    if (! preg_match('/^[0-9]{4}[-]{1}[0-9]{2}[-]{1}[0-9]{2}$/', $datetoview)) {

        $timetoview = 0;

    } else {

        $timetoview = strtotime($datetoview);

        if ($timetoview == -1 || $timetoview == false) {

            $timetoview = 0;

        } else {

            $countfrag = moodletxt_get_sql('sentcountcriteria');
            $countcriteria = sprintf($countfrag, $timetoview, ($timetoview + 86400));

            $selectfrag = moodletxt_get_sql('sentselectcriteria');
            $selectcriteria = sprintf($selectfrag, $timetoview, ($timetoview + 86400));

        }

    }

    if ($usertoview <= 0) {

        $usertoview = 0;

    }

    // Admins can choose to view specific members - everyone else has to!
    if (! has_capability('block/moodletxt:adminusers', $blockcontext, $USER->id)) {

        $countcriteria .= ($countcriteria == '') ? 'WHERE ' : 'AND ';
        $countcriteria .= 'o.userid = \'' . $USER->id . '\'';
        $selectcriteria .= 'AND o.userid = \'' . $USER->id . '\' ';

    } else {

        if ($usertoview > 0) {

            $countcriteria .= ($countcriteria == '') ? 'WHERE ' : 'AND ';
            $countcriteria .= 'o.userid = \'' . $usertoview . '\'';
            $selectcriteria .= 'AND o.userid = \'' . $usertoview . '\' ';

        }

    }

    // Count number of messages that can be displayed
    $sql = moodletxt_get_sql('sentcountmessages') . ' ' . $countcriteria;
    $messagetotal = count_records_sql($sql);

    // Create results table
    $table = new flexible_table('blocks-moodletxt-sentmessages');

    // Set structure
    $tablecolumns = array("user", "account", "message", "time");
    $tableheaders = array(
                        get_string('senttableheaderuser', 'block_moodletxt'),
                        get_string('senttableheaderacc', 'block_moodletxt'),
                        get_string('senttableheadermessage', 'block_moodletxt'),
                        get_string('senttableheadertime', 'block_moodletxt')
                    ); // ;)

    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->column_class('user', 'mdltxt_columnline');
    $table->column_class('account', 'mdltxt_columnline');
    $table->column_class('message', 'mdltxt_columnline');

    // Set styling/attributes
    $table->sortable(true, 'o.timesent', 'DESC');
    $table->pagesize($perpage, $messagetotal);
    $table->collapsible(true);

    $table->set_attribute('class', 'mdltxt_resultlist mdltxt_fullwidth');

    $table->setup();

    // Load sorting data
    $sortby = array();

    $sortbyarr = $SESSION->flextable['blocks-moodletxt-sentmessages']->sortby;

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

    $startrecord = $pagenumber * $perpage;

    $sentmessages = moodletxt_get_sent_messages($selectcriteria, $sortby, $perpage, $startrecord);

    if (is_array($sentmessages)) {

        foreach ($sentmessages as $message) {

            // Format table cells
            $userlink = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $message->moodleuserid . '">' .
                        $message->moodleuser . '</a>';

            $messagelink  = '<a href="viewmessage.php?messageid=' . $message->id;
            $messagelink .= '">' . moodletxt_restrict_length($message->messagetext, 60) . '</a>';

            $timesent = userdate($message->timesent, "%H:%M:%S,  %d %B %Y");

            // Add table row
            $table->add_data(array($userlink, $message->txttoolsuser, $messagelink, $timesent));


        }

    }

    /*
      ############################################################
      # BEGIN CONTENT OUTPUT
      ############################################################
    */

    // Count number of messages sent from this user
    $numberofmessages = count_records('block_mtxt_outbox', 'userid', $USER->id);

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

            print_header($title . ' ' . $course->fullname, $heading,
                    '<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '">' . $course->shortname . '</a>
                    -> <a href="moodletxt.php?courseid=' . $course->id . '">' . $blocktitle . '</a>
                    -> ' . $heading, '', '', false, '&nbsp;');

        } else {

            print_header($title . ' ' . $course->fullname, $heading,
                    '<a href="moodletxt.php">' . $blocktitle . '</a>
                    -> ' . $heading, '', '', false, '&nbsp;');

        }

    }

    print_heading($heading);

    echo(count_records('block_mtxt_outbox') .  get_string('sentnoticefrag1',  'block_moodletxt') . count_records('block_mtxt_sent') . get_string('sentnoticefrag2', 'block_moodletxt') .  '<br />');
    echo($numberofmessages . get_string('sentnoticefrag3', 'block_moodletxt') . '<br /><br />');

    // Create site context
    if (has_capability('block/moodletxt:adminusers', $blockcontext, $USER->id) && ($usertoview > 0 || $timetoview > 0))
        echo('<a href="userstats.php">' . get_string('sentnoticestatslink', 'block_moodletxt') . '</a>');

?>
    <div class="mdltxt_clearer"></div>
<?php

    // Print out results table
    $table->print_html();

    print_footer();

?>
