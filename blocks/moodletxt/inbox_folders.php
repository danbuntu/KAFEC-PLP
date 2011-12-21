<?php

    /**
     * User inbox folders page for MoodleTxt
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011030701
     * @since 2007050312
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
    
    // Check that user is allowed to receive messages
    $blockcontext = get_context_instance(CONTEXT_BLOCK, $instanceid);
    require_capability('block/moodletxt:receivemessages', $blockcontext, $USER->id);

    // Check that this user has an inbox record
    $userinbox = get_record('block_mtxt_inbox', 'userid', $USER->id);

    if (! is_object($userinbox)) {

        $inboxid = moodletxt_create_inbox($USER->id);
        $userinbox = get_record('block_mtxt_inbox', 'id', moodletxt_escape_string($inboxid));

    }

    $formid = optional_param('formid', '', PARAM_ALPHA);

    /*
      ############################################################
      # "ADD FOLDER" FORM PROCESSING
      ############################################################
    */

    $foldername = '';
    if (($_SERVER["REQUEST_METHOD"] == "POST") && ($formid == 'addfolder')) {

        // Read in form vars
        $foldername = stripslashes(optional_param('foldername', '', PARAM_TEXT));

        if ($foldername == '') {

            $errorArray['noFolderName'] = get_string('errornofoldername', 'block_moodletxt');

        } else if (strlen($foldername) > 30) {

            $errorArray['folderTooLong'] = get_string('errorfoldernametoolong', 'block_moodletxt');

        }

        // Check that folder name doesn't already exist
        $checkname = count_records('block_mtxt_in_folders', 'inbox', moodletxt_escape_string($userinbox->id),
                        'name', $foldername);

        if ($checkname > 0) {

            $errorArray['folderExists'] = get_string('errorfolderexists', 'block_moodletxt');

        }

        if (count($errorArray) == 0) {

            $insFolder = new stdClass;
            $insFolder->inbox = moodletxt_escape_string($userinbox->id);
            $insFolder->name = moodletxt_escape_string($foldername);
            $insFolder->candelete = 1;

            if (insert_record('block_mtxt_in_folders', $insFolder, false)) {

                $noticeArray['folderAdded'] = get_string('inboxfoldersadded', 'block_moodletxt');
                $foldername = '';

            } else {

                $errorArray['folderAddFailed'] = get_string('errorfolderaddfailed', 'block_moodletxt');

            }

        }

    }

    /*
      ############################################################
      # "DELETE FOLDER" FORM PROCESSING
      ############################################################
    */

    if (($_SERVER["REQUEST_METHOD"] == "POST") && ($formid == 'delfolder')) {

        $messagesinfolder = 0;

        // Read in form vars
        $foldertokill = optional_param('delfolderlist', 0, PARAM_INT);
        $destinationfolder = optional_param('destinationfolder', 0, PARAM_INT);

        // Check for valid folder
        $checkfolder = count_records('block_mtxt_in_folders', 'id', $foldertokill,
                    'inbox', moodletxt_escape_string($userinbox->id), 'candelete', 1);

        if ($checkfolder == 0) {

            $errorArray['invalidDelFolder'] = get_string('errorinvalidfolder', 'block_moodletxt');

        } else {

            // Check for folder content
            $messagesinfolder = count_records('block_mtxt_in_mess', 'folderid', $foldertokill);

            // Check for valid destination folder
            $checkdestination = count_records('block_mtxt_in_folders', 'id', $destinationfolder,
                        'inbox', moodletxt_escape_string($userinbox->id));

            if (($checkdestination == 0) && ($messagesinfolder > 0)) {

                $errorArray['invalidDestinationFolder'] = get_string('errorinvaliddestination', 'block_moodletxt');

            }

        }

        // Check that source and destination folders are not the same
        if (count($errorArray) == 0 && $foldertokill == $destinationfolder)
            $errorArray['destinationFolderSame'] = get_string('errordestfoldersame', 'block_moodletxt');

        if (count($errorArray) == 0) {

            // Move messages into destination folder
            if ($messagesinfolder > 0) {

                $sql = moodletxt_get_sql('inboxmoveallmessages');
                $sql = sprintf($sql, $destinationfolder, $foldertokill);

                execute_sql($sql, false);

            }

            delete_records('block_mtxt_in_folders', 'id', $foldertokill,
                    'inbox', moodletxt_escape_string($userinbox->id), 'candelete', 1);

            $noticeArray['folderDeleted'] = get_string('inboxfoldersdeleted', 'block_moodletxt');

        }

    }

    /*
      ############################################################
      # READY CONTENT OUTPUT
      ############################################################
    */

    // Get user folder list
    $folderlist = get_records('block_mtxt_in_folders', 'inbox', moodletxt_escape_string($userinbox->id), 'candelete, name ASC');

    $folderulout = '';
    $folderlistout = '';
    $folderlist2out = '';
    $courseidpassback = '';
    $courseidlinkback = '';

    if ($courseid > 0) {

        $courseidpassback = '<input type="hidden" name="courseid" value="' . $courseid . '" />';
        $courseidlinkback = '&courseid=' . $courseid;

    }

    if (is_array($folderlist)) {

        foreach($folderlist as $folder) {

            $disablestr = '';

            if ($folder->candelete == 0) {

                $disablestr = ' disabled="disabled"';

            }

            $folderultemp = ($folder->candelete == 0) ? '<i>' . $folder->name . '</i>' : $folder->name;
            $folderulout .= '<li><a href="inbox.php?folder=' . $folder->id . $courseidlinkback . '">' . $folderultemp . '</a></li>';
            $folderlistout .= '<option value="' . $folder->id . '"' . $disablestr . '>' . $folder->name . '</option>';
            $folderlist2out .= '<option value="' . $folder->id . '">' . $folder->name . '</option>';

        }

    }

    // Set up page
    $title = get_string('inboxfolderstitle', 'block_moodletxt');
    $inboxtitle = get_string('inboxheader', 'block_moodletxt');
    $heading = get_string('inboxfoldersheader', 'block_moodletxt');
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
            array('name' => $inboxtitle, 'link' => 'inbox.php?' . $coursefragment, 'type' => 'misc'),
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
                -> <a href="inbox.php?courseid=' . $course->id . '">' . $inboxtitle . '</a>
                -> ' . $heading, '', '', false, '&nbsp;');

        } else {

            print_header($title . ' ' . $USER->username, $heading,
                '<a href="moodletxt.php">' . $blocktitle . '</a>
                -> <a href="inbox.php">' . $inboxtitle . '</a>
                -> ' . $heading, '', '', false, '&nbsp;');

        }

    }

    print_heading($heading);

    // Get output file
    require_once($CFG->dirroot . '/blocks/moodletxt/inbox_folders_output.php');

    // Get footer
    print_footer();

?>
