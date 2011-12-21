<?php

    /**
     * User statistics page for MoodleTxt
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011030701
     * @since 2007021412
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

    // Check for course ID
    $courseid = optional_param('courseid', 0, PARAM_INT);
    $instanceid = $_SESSION['moodletxt_last_instance'];

    $course = get_record('course', 'id', $courseid);

    if (empty($instanceid) || ! is_int($instanceid))
        error(get_string('errorbadinstanceid', 'block_moodletxt'));

    // User MUST be logged in
    require_login($courseid, false);

    if (count_records('block_mtxt_accounts') == 0)
        error(get_string('errornoaccountspresent', 'block_moodletxt'));

    // Check that user is allowed to use moodletxt
    $blockcontext = get_context_instance(CONTEXT_BLOCK, $instanceid);
    require_capability('block/moodletxt:viewstats', $blockcontext, $USER->id);

    // Check for update flag
    $updateStatus = optional_param('update', 0, PARAM_BOOL);

    // Check for mode
    $searchMode = optional_param('mode', 'allusers', PARAM_ALPHA);

    $p = $CFG->prefix;

    // Create results table
    $table = new flexible_table('blocks-moodletxt-statstable');

    $totalmessages = count_records('block_mtxt_outbox');
    $totalreceived = count_records('block_mtxt_status', 'status', 5);
    $totalfailed = count_records('block_mtxt_status', 'status', 2);

    $rowsarray = array();

    $statstotal = 0;

    // Check for "start at" and "per page" parameters
    $startint = optional_param("start", 0, PARAM_INT);
    $displayno = optional_param("display", 0, PARAM_INT);

    // Check parameters for validity
    if ($startint < 0) {

        $startint = 0;

    }

    if ($displayno <= 0) {

        $displayno = 30;

    }

    $selectedString1 = '';
    $selectedString2 = '';

    switch ($searchMode) {

        /* Display number of messages sent for each user */
        case 'allusers':

            $selectedString1 = ' selected="selected"';

            $sql = moodletxt_get_sql('statsgetallusers');
            $sql = sprintf($sql, $displayno, $startint, ($displayno + $startint));

            $userstats = get_records_sql($sql);

            $statstotal = count($userstats);

            $tablecolumns = array('user', 'messages');
            $tableheaders = array(
                                get_string('statstableheaderuser', 'block_moodletxt'),
                                get_string('statstableheadermessages', 'block_moodletxt')
                            ); // ;)

            $table->define_columns($tablecolumns);
            $table->define_headers($tableheaders);
            $table->column_class('user', 'mdltxt_columnline');

            // Enter records
            if (is_array($userstats)) {

                foreach($userstats as $row) {

                    // Style user column
                    $usercolumn = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $row->id . '">' .
                            $row->lastname . ', ' . $row->firstname . ' (' . $row->username . ')</a>';

                    $sentcolumn = '<a href="' . $CFG->wwwroot . '/blocks/moodletxt/sentmessages.php?user=' .
                            $row->id . '">' . $row->totalsent . '</a>';

                    array_push($rowsarray, array($usercolumn, $sentcolumn));

                }

             }

             break;

        /* Display number of messages sent for each date */
        case 'alldates':

            $selectedString2 = ' selected="selected"';

            $sql = moodletxt_get_sql('statsgetalldates');

            $userstats = get_records_sql($sql);

            $statstotal = count($userstats);

            $tablecolumns = array('date', 'messages');
            $tableheaders = array(
                                get_string('statstableheaderdate', 'block_moodletxt'),
                                get_string('statstableheadermessages', 'block_moodletxt')
                            ); // ;)

            $table->define_columns($tablecolumns);
            $table->define_headers($tableheaders);
            $table->column_class('date', 'mdltxt_columnline');

            // Enter records into array
            if (is_array($userstats)) {

                foreach($userstats as $row) {

                    // Style columns
                    $usercolumn = userdate(strtotime($row->date_entered), "%H:%M:%S,  %d %B %Y");

                    $sentcolumn = '<a href="' . $CFG->wwwroot . '/blocks/moodletxt/sentmessages.php?date=' .
                            $row->date_entered . '">' . $row->totalsent . '</a>';

                    array_push($rowsarray, array($usercolumn, $sentcolumn));

                }

            }

            break;

    }

    // Check to see whether "previous" or "next" links need to be displayed
    if ($startint > 0) {

      $showprev = true;

      $prevstart = ($startint - $displayno > 0) ? $startint - $displayno : 0;

      // Build link
      $prevlink  = $ME . '?start=' . $prevstart . '&displayno=' . $displayno;

    } else {

      $showprev = false;

    }

    if (($startint + $displayno) < $statstotal) {

        $shownext = true;

        $nextstart = $startint + $displayno;

        // Build link
        $nextlink  = $ME . '?start=' . $nextstart . '&displayno=' . $displayno;

    } else {

        $shownext = false;

    }

     // Set styling/attributes
    $table->collapsible(false);

    $table->set_attribute('class', 'mdltxt_resultlist mdltxt_halfwidth');

    $table->setup();

    if (count($rowsarray) > 0) {

        // Echo row data into table
        foreach($rowsarray as $row) {

            $table->add_data($row);

        }

    }

    // Set up page
    $title = get_string('statstitle', 'block_moodletxt');
    $heading = get_string('statsheader', 'block_moodletxt');
    $blocktitle = get_string('blocktitle', 'block_moodletxt');

    $stradmin = get_string('administration');
    $strconfiguration = get_string('configuration');
    $strmanageblocks = get_string('manageblocks');

    // Navigation after Moodle 1.9
    if (function_exists('build_navigation')) {

        if (is_object($course) && $course->id > 0) {

            $navarray = array(
                array('name' => $blocktitle, 'link' => 'moodletxt.php?courseid=' . $course->id, 'type' => 'misc'),
                array('name' => $title, 'link' => '', 'type' => 'title')
            );

        } else {

            $navarray = array(
                array('name' => $stradmin, 'link' => $CFG->wwwroot . '/admin/index.php', 'type' => 'activity'),
                array('name' => $strconfiguration, 'link' => '', 'type' => 'misc'),
                array('name' => $strmanageblocks, 'link' => $CFG->wwwroot . '/admin/blocks.php', 'type' => 'category'),
                array('name' => $blocktitle, 'link' => $CFG->wwwroot . '/blocks/moodletxt/admin.php', 'type' => 'misc'),
                array('name' => $title, 'link' => '', 'type' => 'title')
            );

        }

        $navigation = build_navigation($navarray);

        print_header_simple(
            $title,
            $heading,
            $navigation
        );

    // Navigation before Moodle 1.9
    } else {

        if (is_object($course) && $course->id > 0) {

            print_header($title, $heading, '<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '">' . $course->shortname . '</a> ->
                                            <a href="' . $CFG->wwwroot . '/blocks/moodletxt/moodletxt.php?courseid=' . $course->id . '">' . $blocktitle . '</a> ->
                                            ' . $title, '', false, '&nbsp;');

        } else {

            print_header($title, $heading, '<a href="' . $CFG->wwwroot . '/admin/index.php">' . $stradmin . '</a> ->
                                            <a href="' . $CFG->wwwroot . '/admin/configure.php">' . $strconfiguration . '</a> ->
                                            <a href="' . $CFG->wwwroot . '/admin/blocks.php">' . $strmanageblocks . '</a> ->
                                            <a href="' . $CFG->wwwroot . '/blocks/moodletxt/admin.php">' . $blocktitle . '</a> ->
                                            ' . $title, '', false, '&nbsp;');

        }

    }

    print_heading($heading);

?>
    <div>
        <p>
            <?php echo(get_string('statspara1', 'block_moodletxt')); ?>
        </p>
    </div>
    <div class="mdltxt_right" style="text-align:right;">
        <form action="<?php echo($ME); ?>" method="get">
            <?php if(is_object($course)) echo('<input type="hidden" name="courseid" value="' . $course->id . '" />'); ?>
            <?php echo(get_string('statslabelswitchmode', 'block_moodletxt')); ?>
            <select name="mode">
                <?php echo('<option value="allusers"' . $selectedString1 . '>' . get_string('statsmodeallusers', 'block_moodletxt') . '</option>'); ?>
                <?php echo('<option value="alldates"' . $selectedString2 . '>' . get_string('statsmodealldates', 'block_moodletxt') . '</option>'); ?>
            </select>
            <input type="submit" value="<?php echo(get_string('statslabelmodebutton', 'block_moodletxt')); ?>" />
        </form>
    </div>
    <div class="mdltxt_clearer">
    </div>
<?php

    if ($showprev) {

?>
    <div class="mdltxt_prev">
        <a href="<?php echo($prevlink); ?>"><?php echo(get_string('previoustoken', 'block_moodletxt')); ?></a>
    </div>
<?php

    }

    if ($shownext) {

?>
    <div class="mdltxt_next">
        <a href="<?php echo($nextlink); ?>"><?php echo(get_string('nexttoken', 'block_moodletxt')); ?></a>
    </div>
<?php

    }

?>
    <div class="mdltxt_clearer"></div>
<?php

    $table->print_html();

    print_footer();

?>
