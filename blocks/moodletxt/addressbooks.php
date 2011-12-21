<?php

    /**
     * Address book management page for moodletxt
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011030701
     * @since 2008112112
     */

    /*
      ############################################################
      # SET UP
      ############################################################
    */

    // Get config and required libraries
    require_once('../../config.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/lib.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/db/sqllib.php');

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

    // Check that user is allowed to use the address book interface
    $blockcontext = get_context_instance(CONTEXT_BLOCK, $instanceid);
    require_capability('block/moodletxt:addressbooks', $blockcontext, $USER->id);

    $canHaveGlobalAddressBooks = has_capability('block/moodletxt:globaladdressbooks', $blockcontext, $USER->id);

    $errorArray = array();
    $noticeArray = array();

    $formid = optional_param('formid', '', PARAM_ALPHA);

    // Declare processing vars here to prevent warnings
    $delbook = '';
    $delbookdest = '';
    $delbooklistout = '';
    $delbookdestlistout = '';
    $seltypeglobal = '';
    $seltypeprivate = '';
    $bookname = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        /**
         * ADD FORM PROCESSING
        */

        if ($formid == 'addbook') {

            // Grab POSTed parameters
            $bookname = stripslashes(optional_param('bookname', '', PARAM_TEXT));
            $booktype = optional_param('booktype', 'private', PARAM_ALPHA);
            $seltypeprivate = '';
            $seltypeglobal = '';

            // If no book name was entered, bitch
            if ($bookname == '') {

                $errorArray['noBookName'] = get_string('errornobookname', 'block_moodletxt');

            // If the name was too long, bitch some more
            } else if (strlen($bookname) > 50) {

                $errorArray['nameTooLong'] = get_string('errorbooknamelength', 'block_moodletxt');

            } else {

                // Check that the name doesn't already exist
                $bookcount = count_records('block_mtxt_ab', 'name', moodletxt_escape_string($bookname), 'owner', $USER->id);

                if ($bookcount > 0)
                    $errorArray['nameExists'] = get_string('errorbooknameexists', 'block_moodletxt');

            }

            // Check that the user isn't messing with the type box
            if (! in_array($booktype, array('private', 'global'))) {
                
                $errorArray['invalidBookType'] = get_string('errorinvalidbooktype', 'block_moodletxt');
                
            } else {

                // Auto-select options in form
                if ($booktype == 'global')
                    $seltypeglobal = ' selected="selected"';
                else
                    $seltypeprivate = ' selected="selected"';

                if ($booktype == 'global' && ! $canHaveGlobalAddressBooks)
                    $errorArray['globalNotAllowed'] = get_string('errorglobalbooknotallowed');
                    
            }

            // If no errors have been found, add the address book
            if (count($errorArray) == 0) {

                // Build data object for insertion
                $insObj = new stdClass;
                $insObj->name = moodletxt_escape_string($bookname);
                $insObj->type = moodletxt_escape_string($booktype);
                $insObj->owner = moodletxt_escape_string($USER->id);

                if (insert_record('block_mtxt_ab', $insObj)) {

                    $noticeArray['bookAdded'] = get_string('addressbookadded', 'block_moodletxt');

                    // Kill off form auto-population
                    $bookname = '';
                    $seltypeglobal = '';
                    $seltypeprivate = '';

                } else {

                    $errorArray['addFailed'] = get_string('errorbookaddfailed', 'block_moodletxt');

                }

            }

        }

        /**
         * DELETE FORM PROCESSING
         */

        if ($formid == 'delbook') {

            // Grab POSTed parameters
            $delbook = optional_param('delbooklist', 0, PARAM_INT);
            $delbookdest = optional_param('delbookdestination', 0, PARAM_INT);

            // Check that this user owns the record they're trying to delete
            $recordcount = count_records('block_mtxt_ab',
                'id', moodletxt_escape_string($delbook),
                'owner', moodletxt_escape_string($USER->id)
            );

            // They don't??  The cheeky sods!
            if ($recordcount == 0)
                $errorArray['notOwner'] = get_string('errorbooknotowned', 'block_moodletxt');

            // If a destination book has been selected for a merge, check it out
            if ($delbookdest > 0) {

                $recordcount = count_records('block_mtxt_ab',
                    'id', moodletxt_escape_string($delbookdest),
                    'owner', moodletxt_escape_string($USER->id)
                );

                if ($recordcount == 0)
                    $errorArray['notDestOwner'] = get_string('errordestbooknotowned', 'block_moodletxt');

                if ($delbook == $delbookdest)
                    $errorArray['destSame'] = get_string('errordestbooksame', 'block_moodletxt');
                    
            }

            if (count($errorArray) == 0) {

                // Merge contacts and groups into new book
                if ($delbookdest > 0) {

                    // Shift contacts
                    $sql = moodletxt_get_sql('addressmovecontacts');
                    $sql = sprintf($sql, moodletxt_escape_string($delbookdest), moodletxt_escape_string($delbook));

                    if (! execute_sql($sql, false)) {
                            
                        $errorArray['moveContactsFailed'] = get_string('errormovecontactsfailed', 'block_moodletxt');
                            
                    } else {

                        // Shift groups
                        $sql = moodletxt_get_sql('addressmovegroups');
                        $sql = sprintf($sql, moodletxt_escape_string($delbookdest), moodletxt_escape_string($delbook));

                        if (! execute_sql($sql, false)) {

                            $errorArray['moveGroupsFailed'] = get_string('errormovegroupsfailed', 'block_moodletxt');

                        }

                    }

                }

                // If no errors were found updating contacts, delete book
                if (count($errorArray) == 0) {

                    if (delete_records('block_mtxt_ab', 'id', moodletxt_escape_string($delbook))) {

                        $noticeArray['bookDeleted'] = get_string('addressbookdeleted', 'block_moodletxt');

                        // If contacts are still in the book, wipe them out
                        if ($delbookdest <= 0) {

                            $contactrecords = get_records('block_mtxt_ab_entry', 'addressbook', moodletxt_escape_string($delbook));

                            $delarray = array();

                            if (is_array($contactrecords))
                                foreach($contactrecords as $delcontact)
                                    array_push($delarray, $delcontact->id);

                            $delstring = "contact IN('" . implode("', '", $delarray) . "')";
                            
                            delete_records_select('block_mtxt_ab_grpmem', $delstring);
                            delete_records('block_mtxt_ab_entry', 'addressbook', moodletxt_escape_string($delbook));
                            delete_records('block_mtxt_ab_groups', 'addressbook', moodletxt_escape_string($delbook));

                        }

                    }  else {

                        $errorArray['bookNotDeleted'] = get_string('errorbooknotdeleted');

                    }

                }

            }

        }

    }


    /*
      ############################################################
      # READY CONTENT OUTPUT
      ############################################################
    */

    // Get user folder list
    $ablist = get_records('block_mtxt_ab', 'owner', moodletxt_escape_string($USER->id), 'type DESC, name ASC');

    $abulout = '';
    $ablistout = '';
    $courseidpassback = '';
    $courseidlinkback = '';

    if ($courseid > 0) {

        $courseidpassback = '<input type="hidden" name="courseid" value="' . $courseid . '" />';
        $courseidlinkback = '&courseid=' . $courseid;

    }

    if (is_array($ablist)) {

        foreach($ablist as $ab) {

            $classstring = (strtolower($ab->type) == 'private') ? 'private' : 'global';

            $selectedString1 = ($ab->id == $delbook) ? ' selected="selected"' : '';
            $selectedString2 = ($ab->id == $delbookdest) ? ' selected="selected"' : '';

            $abulout .= '<li class="' . $classstring . '"><a href="addressbook_edit.php?ab=' . $ab->id . $courseidlinkback . '">' . $ab->name . '</a></li>';
            $delbooklistout .= '<option value="' . $ab->id . '"' . $selectedString1 . '>' . $ab->name . '</option>';
            $delbookdestlistout .= '<option value="' . $ab->id . '"' . $selectedString2 . '>' . $ab->name . '</option>';

        }

    }

    // Set up page
    $title = get_string('addresstitle', 'block_moodletxt');
    $heading = get_string('addressheader', 'block_moodletxt');
    $blocktitle = get_string('blocktitle', 'block_moodletxt');

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
            $title,
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

    // Get output file
    require_once($CFG->dirroot . '/blocks/moodletxt/addressbooks_output.php');

    // Get footer
    print_footer();

?>
