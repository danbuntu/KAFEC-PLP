<?php

    /**
     * Page to view and edit the contacts in an address book
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011030701
     * @since 2008120312
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
    $formid = optional_param('formid', '', PARAM_ALPHA);

    // Check address book ID against database and get details for use
    if (! $addressbook = get_record('block_mtxt_ab', 'id', moodletxt_escape_string($addressbookid),
        'owner', moodletxt_escape_string($USER->id))) {

        error(get_string('errorbadbookid', 'block_moodletxt'));

    }

    $deleteGroupListString = '';
    $contactDestListString = '';
    $newgroupname = '';
    $newgroupdescription = '';

    /**
     * POST PROCESSING ON UPDATE GROUP FORM
     */

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        // If user is adding a group...
        if ($formid == 'addgroup') {

            // Read in form params
            $groupName = stripslashes(optional_param('groupname', '', PARAM_TEXT));
            $groupDesc = stripslashes(optional_param('description', '', PARAM_TEXT));

            if ($groupName == '')
                $errorArray['newNoName'] = get_string('errornogroupname', 'block_moodletxt');

            // If no errors are found in form validation,
            // shove the group into the DB
            if (count($errorArray) == 0) {

                $insObj = new stdClass;
                $insObj->addressbook = moodletxt_escape_string($addressbook->id);
                $insObj->name = moodletxt_escape_string($groupName);
                $insObj->description = moodletxt_escape_string($groupDesc);

                if (insert_record('block_mtxt_ab_groups', $insObj))
                    $noticeArray['newGroupAdded'] = get_string('mangroupsadded', 'block_moodletxt');
                else
                    $errorArray['newGroupNotAdded'] = get_string('errorgroupnotadded', 'block_moodletxt');

            }

        // If the user is updating the members of a group...
        } else if ($formid == 'updategroup') {

            // Get form details
            $groupid = optional_param('group', 0, PARAM_INT);
            $newlist = optional_param('selectedContacts', '', PARAM_RAW);

            if (! is_array($newlist))
                $newlist = array($newlist);

            // Check group ID
            $groupcheck = count_records('block_mtxt_ab_groups', 'addressbook',
                moodletxt_escape_string($addressbook->id), 'id', moodletxt_escape_string($groupid));

            if ($groupcheck == 0) {
                $errorArray['badGroup'] = get_string('errorinvalidgroupid', 'block_moodletxt');
            } else {

                $previousMembers = array();

                // Get group members
                $groupsql = moodletxt_get_sql('sendgetabgroupmembers');
                $groupsql = sprintf($groupsql, moodletxt_escape_string($groupid));

                $groupMemberArr = get_records_sql($groupsql);

                if (is_array($groupMemberArr))
                    $previousMembers = array_keys($groupMemberArr);

                // Find which members are new/deleted
                $newGroupMembers = array_diff($newlist, $previousMembers);
                $deletedGroupMembers = array_diff($previousMembers, $newlist);

                // Add new members to group
                foreach($newGroupMembers as $newMember) {

                    // Create new member link
                    $insObj = new stdClass;
                    $insObj->contact = moodletxt_escape_string($newMember);
                    $insObj->groupid = moodletxt_escape_string($groupid);

                    insert_record('block_mtxt_ab_grpmem', $insObj);

                }

                // Delete removed members from group
                foreach($deletedGroupMembers as $deletedMember) {

                    // Remove old member links
                    delete_records('block_mtxt_ab_grpmem', 'contact',
                        moodletxt_escape_string($deletedMember), 'groupid', moodletxt_escape_string($groupid));

                }

                // Drop completed notice into array
                $noticeArray['groupUpdated'] = get_string('mangroupsupdated', 'block_moodletxt');

            }

        } else if ($formid == 'deletegroup') {

            $groupid = optional_param('groupid', 0, PARAM_INT);
            $choice = optional_param('contactchoice', 'donothing', PARAM_ALPHA);
            $contactDest = optional_param('contactdest', 0, PARAM_INT);

            // Check group ID
            $groupcheck = count_records('block_mtxt_ab_groups', 'addressbook',
                moodletxt_escape_string($addressbook->id), 'id', moodletxt_escape_string($groupid));

            if ($groupcheck == 0) {
                $errorArray['deleteBadGroup'] = get_string('errorinvalidgroupid', 'block_moodletxt');
            } else {

                // Check that choice is a valid one
                if (! in_array($choice, array('donothing', 'delete', 'merge'))) {

                    $errorArray['deleteBadChoice'] = get_string('errorinvalidchoice', 'block_moodletxt');

                    // Do you think deleteBadChoice was the error the Terminator got
                    // after taking down the wrong Sarah Connor?
                    
                } else {
                    
                    // If the user is wanting to merge contacts into another group,
                    // check that the destination group is valid
                    if ($choice == 'merge') {

                        // Check that destination is not the same as source
                        if ($groupid == $contactDest) {

                            $errorArray['deleteDestSame'] = get_string('errordestgroupsame', 'block_moodletxt');

                        } else {

                            // Check that destination group is valid
                            $checkdest = count_records('block_mtxt_ab_groups', 'addressbook',
                                moodletxt_escape_string($addressbook->id), 'id', moodletxt_escape_string($contactDest));

                            if ($checkdest == 0)
                                $errorArray['deleteDestInvalid'] = get_string('errordestgroupinvalid', 'block_moodletxt');

                        }

                    }
                    
                }

            }

            // Check that the user has chosen
            // what to do with contacts in the group
            if ($choice == '')
                $errorArray['deleteNoChoiceMade'] = get_string('errorgroupsmakechoice', 'block_moodletxt');

            // If no errors were found, go for it
            if (count($errorArray) == 0) {

                if (delete_records('block_mtxt_ab_groups', 'id', moodletxt_escape_string($groupid))) {

                    switch($choice) {
                    
                        case 'merge':

                            // Simply update existing group links to new group
                            $sql = moodletxt_get_sql('groupsmergelinks');
                            $sql = sprintf($sql, moodletxt_escape_string($contactDest), moodletxt_escape_string($groupid));

                            execute_sql($sql, false);

                            $noticeArray['groupsMerged'] = get_string('mangroupsmerged', 'block_moodletxt');

                            break;

                        case 'delete':

                            // Get contact IDs
                            $sql = moodletxt_get_sql('groupsgetlinkedcontacts');
                            $sql = sprintf($sql, moodletxt_escape_string($groupid));

                            $contacts = get_records_sql($sql, false);
                            $contactids = array_keys($contacts);

                            // Collect contact IDs into handy dandy SQL string
                            $idfrag = "IN('" . implode("', '", $contactids) . "')";

                            // Kill the contacts off
                            $sqlfrag = 'id ' . $idfrag;
                            delete_records_select('block_mtxt_ab_entry', $sqlfrag);

                            // Now that that lot are gone, we can delete the link entries
                            delete_records('block_mtxt_ab_grpmem', 'groupid', moodletxt_escape_string($groupid));

                            $sqlfrag = 'contact ' . $idfrag;
                            
                            delete_records_select('block_mtxt_in_ab', $sqlfrag);
                            delete_records_select('block_mtxt_sent_ab', $sqlfrag);

                            $noticeArray['groupAndContactsDeleted'] = get_string('mangroupscontactsdeleted', 'block_moodletxt');

                            break;

                        case 'donothing':

                            // Kill off link entries, do naff all else
                            delete_records('block_mtxt_ab_grpmem', 'groupid', moodletxt_escape_string($groupid));

                            $noticeArray['groupDeleted'] = get_string('mangroupsdeleted', 'block_moodletxt');
                            break;

                        default:

                            delete_records('block_mtxt_ab_grpmem', 'groupid', moodletxt_escape_string($groupid));
                            break;

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

    // Get contact list
    $contactList = get_records('block_mtxt_ab_entry', 'addressbook', moodletxt_escape_string($addressbook->id), 'lastname ASC');

    $contactListString = '';

    if (is_array($contactList)) {

        foreach($contactList as $contact) {

            $contactString = '';

            if ($contact->lastname == '' && $contact->company != '')
                $contactString = $contact->company;
            else
                $contactString = $contact->lastname . ', ' . $contact->firstname;

            $contactListString .= '
            <option value="' . $contact->id . '">' . $contactString . '</option>';

        }

    }

    // Get group list
    $groupMemberArray = array();

    $groupList = get_records('block_mtxt_ab_groups', 'addressbook', moodletxt_escape_string($addressbook->id), 'name ASC');

    $groupIDArr = array();
    $groupListString = '';

    if (is_array($groupList)) {

        foreach($groupList as $group) {

            $groupMemberArray[$group->id] = array();

            $groupListString .= '
            <option value="' . $group->id . '">' . $group->name . '</option>';

            $deleteGroupListString .= '
            <option value="' . $group->id . '">' . $group->name . '</option>';

            $contactDestListString .= '
            <option value="' . $group->id . '">' . $group->name . '</option>';

            array_push($groupIDArr, $group->id);

        }

    }

    // Get group members for JS
    if (count($groupIDArr) > 0) {
        $groupMembersSQL = moodletxt_get_sql('groupsgetmembers');
        $groupMembersSQL = sprintf($groupMembersSQL, "('" . implode("', '", $groupIDArr) . "')");

        $groupMembers = get_records_sql($groupMembersSQL);

        if (is_array($groupMembers)) {

            foreach($groupMembers as $member) {

                array_push($groupMemberArray[$member->groupid], $member->contact);

            }

        }

    }

    $javascriptGroupString = '';

    $keys = array_keys($groupMemberArray);

    foreach($keys as $key) {

        if (count($groupMemberArray[$key]) == 1) {

            $javascriptGroupString .= '
        groupMembers[' . $key . '] = new Array();
        groupMembers[' . $key . '].push(' . array_pop($groupMemberArray[$key]) . ');';

        } else {

            $javascriptGroupString .= '
        groupMembers[' . $key . '] = new Array(' . implode(',', $groupMemberArray[$key]) . ');';

        }

    }

    // Set up page
    $title = get_string('mangroupstitle', 'block_moodletxt');
    $heading = get_string('mangroupsheader', 'block_moodletxt');
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

            print_header($title . ' ' . $addressbook->name, $heading,
                '<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '">' . $course->shortname . '</a>
                -> <a href="moodletxt.php?courseid=' . $course->id . '">' . $blocktitle . '</a>
                -> <a href="' . $CFG->wwwroot . '/blocks/moodletxt/addressbooks.php?courseid=' . $course->id . '">' . $parentheader . '</a>
                -> <a href="' . $CFG->wwwroot . '/blocks/moodletxt/addressbook_edit.php?courseid=' . $course->id . '&ab=' . $addressbook->id . '">'. $parent2header . '</a>
                -> ' . $heading, '', '', false, '&nbsp;');

        } else {

            print_header($title . ' ' . $addressbook->name, $heading,
                '<a href="moodletxt.php">' . $blocktitle . '</a>
                -> <a href="' . $CFG->wwwroot . '/blocks/moodletxt/addressbooks.php">' . $parentheader . '</a>
                -> <a href="' . $CFG->wwwroot . '/blocks/moodletxt/addressbook_edit.php?ab=' . $addressbook->id . '">' . $parent2header . '</a>
                -> ' . $heading, '', '', false, '&nbsp;');

        }

    }

    print_heading($heading);

    // Get output file
    require_once($CFG->dirroot . '/blocks/moodletxt/addressbook_groups_output.php');

    // Get footer
    print_footer();

?>