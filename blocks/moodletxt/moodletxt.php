<?php

    /**
     * User settings page for MoodleTxt.
     *
     * I've split the form processing by form, rather than by
     * POST/GET.  Makes for a few more conditional statements,
     * but it's easier to keep track of on a page like this.
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
    require_once($CFG->libdir.'/datalib.php');
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

    // Check that user is allowed to use moodletxt
    $blockcontext = get_context_instance(CONTEXT_BLOCK, $instanceid);
    require_capability('block/moodletxt:personalsettings', $blockcontext, $USER->id);

    // Check for form ID
    $formid = optional_param('formid', '', PARAM_ALPHA);

   /*
      ############################################################
      # "ADD TEMPLATE" FORM PROCESSING
      ############################################################
    */

    if (($_SERVER["REQUEST_METHOD"] == "POST") && ($formid == "newtemplate")) {

        // Read in new template
        $inNewTemplate = required_param('templateEdit', PARAM_NOTAGS);

        // Check that the length of the template is acceptable

        if (strlen($inNewTemplate) == 0) {

            $errorArray['noTemplate'] = get_string('errornotemplate', 'block_moodletxt');

        }

        if (strlen($inNewTemplate) > 1600) {

            $errorArray['templateTooLong'] = get_string('errortemplatetoolong', 'block_moodletxt');

        }

        if (count($errorArray) == 0) {

            // Add template to DB
            $insTemplate = new stdClass;
            $insTemplate->userid = $USER->id;
            $insTemplate->template = preg_replace("(\r\n|\n|\r|\t)", " ", $inNewTemplate);

            if (! insert_record('block_mtxt_templates', $insTemplate)) {

                $errorArray['templateInsertFailed'] = get_string('errortemplateinsertfail', 'block_moodletxt');

            } else {

                $noticeArray['templateAdded'] = get_string('settingstemplateadded', 'block_moodletxt');
                $inNewTemplate = "";

            }

        }

    }

    /*
      ############################################################
      # "EDIT TEMPLATE" FORM PROCESSING
      ############################################################
    */

    if (($_SERVER["REQUEST_METHOD"] == "POST") && ($formid == "edittemplate")) {

        // Read in form vars
        $inTemplateID = required_param('editTemplateID', PARAM_INT);
        $inTemplateEdit = required_param('templateEdit', PARAM_NOTAGS);

        // Get the existing template from the DB
        // (Checks for existence, and provides an object for write-back)
        if ($inTemplateID < 1 ||
            (! $currentTemplate = get_record('block_mtxt_templates', 'id', $inTemplateID, 'userid', $USER->id)) ||
            (! is_object($currentTemplate))) {

            $errorArray['invalidTemplateID'] = get_string('errorformhacktemplate', 'block_moodletxt');

        }

        if (count($errorArray) == 0) {

            // Attempt update
            $currentTemplate->template = preg_replace("(\r\n|\n|\r|\t)", " ", $inTemplateEdit);

            if (update_record('block_mtxt_templates', $currentTemplate)) {

                $noticeArray['templateUpdated'] = get_string('settingstemplateupdated', 'block_moodletxt');

            } else {

                $errorArray['templateUpdateFailed'] = get_string('errortemplateupdatefail', 'block_moodletxt');

            }

        }

    }

    /*
      ############################################################
      # "DELETE TEMPLATE" FORM PROCESSING
      ############################################################
    */

    if (($_SERVER["REQUEST_METHOD"] == "POST") && ($formid == "deletetemplate")) {

        // Read in ID of template to delete
        $delTemplate = required_param('currenttemplates', PARAM_INT);

        // Check that the ID exists
        if (count_records('block_mtxt_templates', 'id', $delTemplate, 'userid', $USER->id) == 0) {

            $errorArray['invalidTemplateID'] = get_string('errorformhacktemplate', 'block_moodletxt');

        } else {

            if (! delete_records('block_mtxt_templates', 'id', $delTemplate, 'userid', $USER->id)) {

                $errorArray['templateDeleteFailed'] = get_string('errortemplatedeletefail', 'block_moodletxt');

            } else {

                $noticeArray['templateDeleted'] = get_string('settingstemplatedeleted', 'block_moodletxt');

            }

        }

    }

    /*
      ############################################################
      # SIGNATURE FORM PROCESSING
      ############################################################
    */

    // Get current signature
    // (Also provides object for update writeback if necessary)
    $userSignature = get_record('block_mtxt_uconfig', 'userid', $USER->id, 'setting', 'SIGNATURE');

    $inSignature = (is_object($userSignature)) ? $userSignature->value : '';

    if (($_SERVER["REQUEST_METHOD"] == "POST") && ($formid == "signature")) {

        // Read in signature
        $inSignature = required_param('signature', PARAM_NOTAGS);

        // Check that the signature entered is 25 chars or less
        if (strlen($inSignature) > 25) {

            $errorArray['sigTooLong'] = get_string('errorsigtoolong', 'block_moodletxt');

        } else {

            if (is_object($userSignature)) {

                $userSignature->value = $inSignature;

                if (update_record('block_mtxt_uconfig', $userSignature)) {

                    $noticeArray['sigUpdated'] = get_string('settingssigupdated', 'block_moodletxt');

                } else {

                    $errorArray['sigUpdateFailed'] = get_string('errorsigupdatefail', 'block_moodletxt');

                }

            } else {

                $userSignature = new stdClass;
                $userSignature->userid = $USER->id;
                $userSignature->setting = 'SIGNATURE';
                $userSignature->value = $inSignature;

                if (insert_record('block_mtxt_uconfig', $userSignature)) {

                    $noticeArray['sigUpdated'] = get_string('settingssigupdated', 'block_moodletxt');

                } else {

                    $errorArray['sigUpdateFailed'] = get_string('errorsigupdatefail', 'block_moodletxt');

                }

            }

        }

        // Un-escape signature ready for display
        $inSignature = stripslashes($inSignature);

    }


    /*
      ############################################################
      # SET UP THE PAGE
      ############################################################
    */

    $accessiblelinks = '';
    $jsarray = '';
    $templatelist = '';

    $userTemplates = get_records('block_mtxt_templates', 'userid', $USER->id);

    // Check whether the template form should be disabled
    if ((is_array($userTemplates)) && (count($userTemplates) > 0)) {
        $disableTemplateForm = false;
    } else {
        $disableTemplateForm = true;
    }

    // Figure out whether to display course info in the page title
    if (is_object($course)) {
        $title = get_string('settingstitlein', 'block_moodletxt') . ' ' . $course->fullname;
    } else {
        $title = get_string('settingstitle', 'block_moodletxt');
    }

    // Build paragraph to hold accessible links
    $checksend = has_capability('block/moodletxt:sendmessages', $blockcontext, $USER->id);

    if ($checksend) {

        $accessiblelinks .= '
    <p>
        <a href="' . $CFG->wwwroot . '/blocks/moodletxt/sendmessage.php">' . get_string('settingssendtextlink', 'block_moodletxt') . ' ' . $course->shortname . '</a><br />
        <a href="' . $CFG->wwwroot . '/blocks/moodletxt/sentmessages.php">' . get_string('settingssentmessageslink', 'block_moodletxt') . '</a><br />
        <a href="' . $CFG->wwwroot . '/blocks/moodletxt/addressbooks.php">' . get_string('settingsaddressbooklink', 'block_moodletxt') . '</a><br />
    </p>';

    }

    $checkreceive = (has_capability('block/moodletxt:receivemessages', $blockcontext, $USER->id));

    if ($checkreceive) {

        $accessiblelinks .= '
    <p>
        <a href="' . $CFG->wwwroot . '/blocks/moodletxt/inbox.php">' . get_string('settingsinboxlink', 'block_moodletxt') . '</a>
    </p>';

    }

    // Not expecting users to have lots of templates, so just echo those into JS
    if (! $disableTemplateForm) {

        foreach($userTemplates as $template) {

            $jsarray .= "
        templateArray[" . $template->id . "] = '" . addslashes(preg_replace("(\r\n|\n|\r|\t)", " ", $template->template)) . "';";

            $templatelist .= '
                    <option value="' . $template->id . '">' . moodletxt_restrict_length($template->template, 30) . '</option>';

        }

    }


    $heading = get_string('settingsheading', 'block_moodletxt');
    $blocktitle = get_string('blocktitle', 'block_moodletxt');

    // Navigation after Moodle 1.9
    if (function_exists('build_navigation')) {

        $navigation = build_navigation(array(
            array('name' => $blocktitle, 'link' => '', 'type' => 'title')
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

            print_header($title, $heading,
                '<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '">' . $course->shortname . '</a>
                -> ' . $blocktitle, '', false, '&nbsp;');

        } else {

            print_header($title, $heading, $blocktitle, '', false, '&nbsp;');

        }

    }

    print_heading($heading);

    // Get output file
    require_once($CFG->dirroot . '/blocks/moodletxt/moodletxt_output.php');

    // Get footer
    print_footer();

?>
