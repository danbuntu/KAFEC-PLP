<?php

    /**
     * Accounts page - allows admins to view
     * all details of the txttools accounts registered
     * within moodletxt, and manually sync their account info
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011030401
     * @since 2010062812
     */

    // Get config and required libraries
    require_once('../../config.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/lib.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/db/sqllib.php');

    $ACCOUNT_TYPE_INVOICED = 0;
    $ACCOUNT_TYPE_PREPAID = 1;

    require_login(0, false);
    
    // Create site context
    $sitecontext = get_context_instance(CONTEXT_SYSTEM);

    // Check for admin
    if (! has_capability('block/moodletxt:adminsettings', $sitecontext, $USER->id))
        error(get_string('errornopermission', 'block_moodletxt'));

    $errorArray = array();
    $noticeArray = array();

    // Get accounts list for output
    $sql = moodletxt_get_sql('admingetaccountlist');
    $accountList = get_records_sql($sql);


    // Page header
    $title = get_string('adminaccountstitle', 'block_moodletxt');
    $heading = get_string('adminaccountsheading', 'block_moodletxt');
    $blocktitle = get_string('blocktitle', 'block_moodletxt');

    $stradmin = get_string('administration');
    $strconfiguration = get_string('configuration');
    $strmanageblocks = get_string('manageblocks');

    // Navigation after Moodle 1.9
    if (function_exists('build_navigation')) {

        $navigation = build_navigation(array(
            array('name' => $stradmin, 'link' => $CFG->wwwroot . '/admin/index.php', 'type' => 'activity'),
            array('name' => $strconfiguration, 'link' => '', 'type' => 'misc'),
            array('name' => $strmanageblocks, 'link' => $CFG->wwwroot . '/admin/blocks.php', 'type' => 'category'),
            array('name' => $blocktitle, 'link' => $CFG->wwwroot . '/blocks/moodletxt/admin.php', 'type' => 'misc'),
            array('name' => $heading, 'link' => '', 'type' => 'title')
        ));

        print_header_simple(
            $title,
            $heading,
            $navigation
        );

    // Navigation before Moodle 1.9
    } else {

        print_header($title, $heading, '<a href="' . $CFG->wwwroot . '/admin/index.php">' . $stradmin . '</a> ->
                                        ' . $strconfiguration . ' ->
                                        <a href="' . $CFG->wwwroot . '/admin/blocks.php">' . $strmanageblocks . '</a> ->
                                        <a href="' . $CFG->wwwroot . '/blocks/moodletxt/admin.php">' . $blocktitle . '</a> ->
                                        ' . $heading, '', false, '&nbsp;');

    }

    print_heading($heading);

    // Create results table
    $table = new flexible_table('blocks-moodletxt-accountlist');
    $table->set_attribute('id', 'accountListTable');
    $table->set_attribute('class', 'mdltxt_resultlist mdltxt_fullwidth');
    $table->collapsible(true);

    // Set structure
    $tablecolumns = array("username", "description", "messagessent", "allowoutbound", 
        "allowinbound", "creditsused", "creditsremaining", "accounttype", "lastupdate");
    
    $tableheaders = array(
        get_string('acctableheaderusername',        'block_moodletxt'),
        get_string('acctableheaderdescription',     'block_moodletxt'),
        get_string('acctableheadermessages',        'block_moodletxt'),
        get_string('acctableheaderallowoutbound',   'block_moodletxt'),
        get_string('acctableheaderallowinbound',    'block_moodletxt'),
        get_string('acctableheadercreditsused',     'block_moodletxt'),
        get_string('acctableheadercreditsleft',     'block_moodletxt'),
        get_string('acctableheaderaccounttype',     'block_moodletxt'),
        get_string('acctableheaderlastupdate',      'block_moodletxt')
    );

    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    
    $table->column_class('username',        'mdltxt_columnline');
    $table->column_class('description',     'mdltxt_columnline');
    $table->column_class('messagessent',    'mdltxt_columnline');
    $table->column_class('allowoutbound',   'mdltxt_columnline');
    $table->column_class('allowinbound',    'mdltxt_columnline');
    $table->column_class('creditsused',     'mdltxt_columnline');
    $table->column_class('creditsremaining','mdltxt_columnline');
    $table->column_class('accounttype',     'mdltxt_columnline');

    $table->setup();

    $javascriptRowArray = '';

    if (is_array($accountList)) {

        // Start row counter at 2 to bypass table
        // header row
        $rowNumber = 2;

        // Image tags
        $deniedImage = '<img src="pix/access_denied.png" width="16" height="16" alt="' . get_string('adminaccountfragdenied', 'block_moodletxt') . '" title="' . get_string('adminaccountfragdenied', 'block_moodletxt') . '" />';
        $outboundImage = '<img src="pix/allow_outbound.png" width="16" height="16" alt="' . get_string('adminaccountfragoutbound', 'block_moodletxt') . '" title="' . get_string('adminaccountfragoutbound', 'block_moodletxt') . '" />';
        $inboundImage = '<img src="pix/allow_inbound.png" width="16" height="16" alt="' . get_string('adminaccountfraginbound', 'block_moodletxt') . '" title="' . get_string('adminaccountfraginbound', 'block_moodletxt') . '" />';

        foreach($accountList as $account) {

            if ($account->lastupdate > 0)
                $lastupdate = userdate($account->lastupdate, "%H:%M:%S,  %d %B %Y");
            else
                $lastupdate = "Never updated";

            $outboundEnabled = ($account->outboundenabled == 1) ? $outboundImage : $deniedImage;
            $inboundEnabled = ($account->inboundenabled == 1) ? $inboundImage : $deniedImage;

            $account->creditsremaining = ($account->accounttype == $ACCOUNT_TYPE_INVOICED) ?
                    "&infin;" :
                    $account->creditsremaining;

            $accountTypeString = ($account->accounttype == 1) ?
                    get_string('acctableaccounttypeprepaid', 'block_moodletxt') :
                    get_string('acctableaccounttypeinvoiced', 'block_moodletxt');

            $table->add_data(array(
                $account->username,
                $account->description,
                $account->messagecount,
                $outboundEnabled,
                $inboundEnabled,
                $account->creditsused,
                $account->creditsremaining,
                $accountTypeString,
                $lastupdate
            ));

            $javascriptRowArray .= '
        accountArray[' . ($rowNumber++) . '] = ' . $account->id . ';';

        }

    }

    require_once('admin_accounts_output.php');

    // Print out results table
    $table->print_html();

    print_footer();

?>