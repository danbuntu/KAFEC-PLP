<?php

    /**
     * User access admin page for MoodleTxt.  Allows admins to configure
     * send/receive permissions for all Moodle users
     *
     * I've split the form processing by form, rather than by
     * POST/GET.  Makes for a few more conditional statements,
     * but it's easier to keep track of on a page like this.
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2006 Onwards, Cy-nap Ltd. All rights reserved.
     * @version 2008111112
     * @since 2007041612
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

    require_login();

    // Create site context
    $sitecontext = get_context_instance(CONTEXT_SYSTEM, SITEID);

    // Check for admin
    if (! has_capability('moodle/site:doanything', $sitecontext, $USER->id)) {

        error(get_string('errornopermission', 'block_moodletxt'));

    }

    if (! isset($SESSION->moodletxt) || ! is_object($SESSION->moodletxt))
        $SESSION->moodletxt = new stdClass;

    $SESSION->moodletxt->vkey = mt_rand();

    $errorArray = array();
    $noticeArray = array();

    // Check for form ID
    $formid = optional_param('formid', '', PARAM_ALPHA);

    /*
      ############################################################
      # SET UP THE PAGE
      ############################################################
    */

    $title = get_string('admintitle', 'block_moodletxt');

    // Get txttols accounts
    $accounts = get_records('moodletxt_accounts');

    // Get course category list
    $categories = get_categories(0, 'name ASC');

    $title = get_string('adminuserpaneltitle', 'block_moodletxt');
    $heading = get_string('adminheading', 'block_moodletxt');
    $blocktitle = get_string('blocktitle', 'block_moodletxt');

    $stradmin = get_string('administration');
    $strconfiguration = get_string('configuration');
    $strmanageblocks = get_string('manageblocks');

    $categorylist = '';

    foreach($categories as $cat) {

        $categorylist .= '        <li id="categorycontainer' . $cat->id . '">
            <a href="javascript:expandNode(document.getElementById(\'categorycontainer' . $cat->id . '\'), \'category\', \'' . $cat->id . '\');">
                <img id="categorycontainerexpand' . $cat->id . '" class="mdltxt_usertree_expand" src="pix/select_expand.gif" width="15" height="15" alt="Expand node" title="Expand node" />
            </a>' . $cat->name . '
        </li>
';

    }

    $accountlist = '';

    if (! is_array($accounts) || count($accounts) == 0) {

        $accountlist = '                    <option value="">' . get_string('adminnoaccount', 'block_moodletxt') . '</option>
';

    } else {

        foreach ($accounts as $account) {

            $accountlist .= '                    <option value="' . $account->id . '">' . $account->username . '</option>
';

        }

    }

    // Get header file
    print_header($title, $heading, '<a href="' . $CFG->wwwroot . '/admin/index.php">' . $stradmin . '</a> ->
                                    ' . $strconfiguration . ' ->
                                    <a href="' . $CFG->wwwroot . '/admin/blocks.php">' . $strmanageblocks . '</a> ->
                                    <a href="' . $CFG->wwwroot . '/blocks/moodletxt/admin.php">' . $blocktitle . '</a> ->
                                    ' . $title, '', false, '&nbsp;');

    print_heading($heading);

    // Get output template
    require_once($CFG->dirroot . '/blocks/moodletxt/admin_user_access_output.php');

    // Get footer
    print_footer();

?>
