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

    $errorArray = array();
    $noticeArray = array();

    $addressbookid = required_param('ab', PARAM_INT);

    // Check address book ID against database and get details for use
    if (! $addressbook = get_record('block_mtxt_ab', 'id', moodletxt_escape_string($addressbookid),
        'owner', moodletxt_escape_string($USER->id))) {

        error(get_string('errorbadbookid', 'block_moodletxt'));

    }

    $firstname = '';
    $lastname = '';
    $company = '';
    $phoneno = '';
    $groups = array();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $addButton      = stripslashes(optional_param('addButton', '', PARAM_ALPHA));
        $addButtonRet   = stripslashes(optional_param('addButtonReturn', '', PARAM_ALPHA));
        $firstname      = stripslashes(optional_param('firstname', '', PARAM_TEXT));
        $lastname       = stripslashes(optional_param('lastname', '', PARAM_TEXT));
        $company        = stripslashes(optional_param('company', '', PARAM_TEXT));
        $phoneno        = stripslashes(optional_param('phoneno', '', PARAM_TEXT));
        $groups         = optional_param('selectedGroups', '', PARAM_RAW);

        if (! is_array($groups))
            $groups = array($groups);

        if ($lastname == '' && $company == '')
            $errorArray['noName'] = get_string('errornonameorcompany', 'block_moodletxt');


        if ($phoneno == '') {

            $errorArray['noNumber'] = get_string('errornonumber', 'block_moodletxt');

        } else if (! preg_match('/^\+{0,1}\d{10,13}$/', $phoneno)) {

            $errorArray['invalidNumber'] = get_string('errorinvalidnumber', 'block_moodletxt');

        }

        if (count($errorArray) == 0) {

            $insObj = new stdClass;
            $insObj->addressbook = moodletxt_escape_string($addressbook->id);
            $insObj->firstname = moodletxt_escape_string($firstname);
            $insObj->lastname = moodletxt_escape_string($lastname);
            $insObj->company = moodletxt_escape_string($company);
            $insObj->phoneno = moodletxt_escape_string($phoneno);

            if (! $contactid = insert_record('block_mtxt_ab_entry', $insObj)) {

                $errorArray['addFailed'] = get_string('erroraddcontactfailed', 'block_moodletxt');

            } else {

                if ($groups != '') {

                    for ($x = 0; $x < count($groups); $x++) {

                        if (! moodletxt_is_intval($groups[$x]))
                            continue;

                        $checkgroup = count_records('block_mtxt_ab_groups', 'addressbook', moodletxt_escape_string($addressbook->id), 'id', $groups[$x]);

                        if (! $checkgroup)
                            continue;

                        $insObj = new stdClass;
                        $insObj->contact = $contactid;
                        $insObj->groupid = $groups[$x];

                        insert_record('block_mtxt_ab_grpmem', $insObj);

                    }

                }

                if ($addButtonRet != '') {

                    $courselink = ($courseid > 0) ? '&courseid=' . $courseid : '';
                    header('Location: addressbook_edit.php?ab=' . $addressbookid . $courselink);
                    exit();

                }

                $firstname = '';
                $lastname = '';
                $company = '';
                $phoneno = '';
                $groups = array();

                $noticeArray['contactAdded'] = get_string('addcontactsuccessful', 'block_moodletxt');

            }

        }

    }

    /*
      ############################################################
      # READY CONTENT OUTPUT
      ############################################################
    */

    // Get group list
    $groupList = get_records('block_mtxt_ab_groups', 'addressbook', moodletxt_escape_string($addressbook->id), 'name ASC');

    $groupListString = '';
    $selectedGroupListString = '';

    if (is_array($groupList)) {

        foreach($groupList as $group) {

            if (is_array($groups) && in_array($group->id, $groups)) {

                $selectedGroupListString .= '
            <option value="' . $group->id . '">' . $group->name . '</option>';

            } else {

                $groupListString .= '
            <option value="' . $group->id . '">' . $group->name . '</option>';

            }

        }

    }

    // Set up page
    $title = get_string('addcontacttitle', 'block_moodletxt');
    $heading = get_string('addcontactheader', 'block_moodletxt');
    $blocktitle = get_string('blocktitle', 'block_moodletxt');
    $parentheader = get_string('addressheader', 'block_moodletxt');
    $parent2header = get_string('editbookheader', 'block_moodletxt');

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
            array('name' => $parent2header, 'link' => $CFG->wwwroot . '/blocks/moodletxt/addressbook_edit.php?' . $coursefragment . 'ab=' . $addressbook->id, 'type' => 'misc'),
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

            print_header($title . ' ' . $addressbook->name, $heading, '<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '">' . $course->shortname . '</a>
                -> <a href="moodletxt.php?courseid=' . $course->id . '">' . $blocktitle . '</a>
                -> <a href="' . $CFG->wwwroot . '/blocks/moodletxt/addressbooks.php?courseid=' . $course->id . '">' . $parentheader . '</a>
                -> <a href="' . $CFG->wwwroot . '/blocks/moodletxt/addressbook_edit.php?ab=' . $addressbook->id . '&courseid=' . $course->id . '">'. $parent2header . '</a>
                -> ' . $heading, '', '', false, '&nbsp;');

        } else {

            print_header($title . ' ' . $addressbook->name, $heading, '<a href="moodletxt.php">' . $blocktitle . '</a>
                -> <a href="' . $CFG->wwwroot . '/blocks/moodletxt/addressbooks.php">' . $parentheader . '</a>
                -> <a href="' . $CFG->wwwroot . '/blocks/moodletxt/addressbook_edit.php?ab=' . $addressbook->id . '">' . $parent2header . '</a>
                -> ' . $heading, '', '', false, '&nbsp;');

        }

    }

    print_heading($heading);

    // Get output file
    require_once($CFG->dirroot . '/blocks/moodletxt/addressbook_add_contact_output.php');

    // Get footer
    print_footer();

?>