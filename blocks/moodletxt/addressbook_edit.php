<?php

    /**
     * Page to view and edit the contacts in an address book
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
    require_once($CFG->libdir.'/tablelib.php');
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

    $seltypeglobal = '';
    $seltypeprivate = '';

    /**
     * POST PROCESSING
     */
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        if ($formid == 'updatename') {

            $bookid   = optional_param('ab', 0, PARAM_INT);
            $bookname = stripslashes(optional_param('bookname', '', PARAM_TEXT));
            $booktype = optional_param('booktype', 'private', PARAM_ALPHA);

            // Get object for updating
            $updatebook = get_record('block_mtxt_ab', 'id', $bookid);

            if (! is_object($updatebook)) {
                
                $errorArray['invalidBookId'] = get_string('errorinvalidbookid', 'block_moodletxt');

            } else {

                // If no book name was entered, bitch
                if ($bookname == '') {

                    $errorArray['noBookName'] = get_string('errornobookname', 'block_moodletxt');

                // If the name was too long, bitch some more
                } else if (strlen($bookname) > 50) {

                    $errorArray['nameTooLong'] = get_string('errorbooknamelength', 'block_moodletxt');

                } else if ($bookname != $updatebook->name) {

                    // Check that the name doesn't already exist
                    $bookcount = count_records('block_mtxt_ab', 'name', moodletxt_escape_string($bookname), 'owner', $USER->id);

                    if ($bookcount > 0)
                        $errorArray['nameExists'] = get_string('errorbooknameexists', 'block_moodletxt');

                }

                // Check that the user isn't messing with the type box
                if (! in_array($booktype, array('private', 'global'))) {

                    $errorArray['invalidBookType'] = get_string('errorinvalidbooktype', 'block_moodletxt');

                } else if ($booktype == 'global' && ! $canHaveGlobalAddressBooks) {

                    $errorArray['globalNotAllowed'] = get_string('errorglobalbooknotallowed');
                    
                }



            }

            // If no errors have been found, update the address book
            if (count($errorArray) == 0) {

                $updatebook->name = moodletxt_escape_string($bookname);
                $updatebook->type = moodletxt_escape_string($booktype);

                if (update_record('block_mtxt_ab', $updatebook))
                    $noticeArray['bookUpdated'] = get_string('addressbookupdated', 'block_moodletxt');

            }

        }

        if ($formid == 'deleteContacts') {

            $deleteSwitch = optional_param('deleteSwitch', '', PARAM_ALPHA);
            $contactids = optional_param('deleteContacts', 0, PARAM_INT);

            if (! is_array($contactids))
                $contactids = array($contactids);

            $selectedSwitch = '';

            if ($deleteSwitch == 'deleteExceptSelected')
                $selectedSwitch = 'NOT ';

            // Delete contact records
            $sql = 'id ' . $selectedSwitch . "IN ('" . implode("', '", $contactids) . "')";
            delete_records_select('block_mtxt_ab_entry', $sql);

            // Delete group links
            $sql = 'contact ' . $selectedSwitch . "IN ('" . implode("', '", $contactids) . "')";
            delete_records_select('block_mtxt_ab_grpmem', $sql);

        }

    }
    
    /*
      ############################################################
      # READY CONTENT OUTPUT
      ############################################################
    */

    $addressbookid = required_param('ab', PARAM_INT);

    // Check address book ID against database and get details for use
    if (! $addressbook = get_record('block_mtxt_ab', 'id', moodletxt_escape_string($addressbookid),
        'owner', moodletxt_escape_string($USER->id))) {

        error(get_string('errorbadbookid', 'block_moodletxt'));

    }

    // Auto-select options in form
    if ($addressbook->type == 'global')
        $seltypeglobal = ' selected="selected"';
    else
        $seltypeprivate = ' selected="selected"';


    // Get contact list
    $contactlist = get_records('block_mtxt_ab_entry', 'addressbook', moodletxt_escape_string($addressbookid), 'lastname ASC, firstname ASC');

    // Set up page
    $title = get_string('editbooktitle', 'block_moodletxt');
    $heading = get_string('editbookheader', 'block_moodletxt');
    $blocktitle = get_string('blocktitle', 'block_moodletxt');
    $parentheader = get_string('addressheader', 'block_moodletxt');
    $addlinkstring = '?ab=' . $addressbook->id;
    $addlinkgroups = '?ab=' . $addressbook->id;
    $addlinkmerge  = '?ab=' . $addressbook->id;

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
            array('name' => $parentheader, 'link' => $CFG->wwwroot . '/blocks/moodletxt/addressbooks.php?' . $coursefragment, 'type' => 'misc'),
            array('name' => $heading, 'link' => '', 'type' => 'title')
        ));

        print_header_simple(
            $title . ' ' . $addressbook->name,
            $heading,
            $navigation
        );

    // Navigation before Moodle 1.9
    } else {

        // Print header
        if (is_object($course) && $course->id > 0) {

            print_header($title . ' ' . $addressbook->name, $heading,
                '<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '">' . $course->shortname . '</a>
                -> <a href="moodletxt.php?courseid=' . $course->id . '">' . $blocktitle . '</a>
                -> <a href="' . $CFG->wwwroot . '/blocks/moodletxt/addressbooks.php?courseid=' . $course->id . '">' . $parentheader . '</a>
                -> ' . $heading, '', '', false, '&nbsp;');

        } else {

            print_header($title . ' ' . $addressbook->name, $heading, '<a href="moodletxt.php">' . $blocktitle . '</a> 
                -> <a href="' . $CFG->wwwroot . '/blocks/moodletxt/addressbooks.php">' . $parentheader . '</a>
                -> ' . $heading, '', '', false, '&nbsp;');

        }

    }

    print_heading($heading);

    // Get output file
    require_once($CFG->dirroot . '/blocks/moodletxt/addressbook_edit_output.php');

    // Get footer
    print_footer();

?>