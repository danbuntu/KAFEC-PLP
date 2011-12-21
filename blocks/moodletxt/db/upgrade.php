<?php

/**
 * XMLDB upgrade file for moodletxt
 * @author Greg J Preece <support@txttools.co.uk>
 * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
 * @version 2011032901
 * @since 2008081212
 */

require_once($CFG->dirroot . '/blocks/moodletxt/db/sqllib.php');
require_once($CFG->dirroot . '/blocks/moodletxt/lib.php');

function xmldb_block_moodletxt_upgrade($oldversion=0) {

    global $CFG, $DB;

    $result = true;

    /*
     * Release 2007021412 is version 1.1 -
     * RSS capability, better authentication, stylesheets, bugfixes, user stats
     */

    if ($result && $oldversion < 2007021412) {

        // Create table to hold RSS updates
        $table = new XMLDBTable('moodletxt_rss_archive');

        if (! table_exists($table)) {

            $table->comment = 'Holds previously downloaded RSS updates';

            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null, null);

            $table->addFieldInfo('title', XMLDB_TYPE_CHAR, '255', null,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('link', XMLDB_TYPE_CHAR, '255', null,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('pubtime', XMLDB_TYPE_INTEGER, '12', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('description', XMLDB_TYPE_TEXT, 'medium', null,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('expirytime', XMLDB_TYPE_INTEGER, '12', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

            $result = $result && create_table($table);

        }

        // Add in RSS settings
        $ins = new stdClass;
        $ins->setting = 'RSS_Last_Update';
        $ins->value = '0';

        $result = $result && insert_record('moodletxt_settings', $ins);

        // Add in RSS settings
        $ins = new stdClass;
        $ins->setting = 'RSS_Update_Interval';
        $ins->value = '14400';

        $result = $result && insert_record('moodletxt_settings', $ins);

        // Add in RSS settings
        $ins = new stdClass;
        $ins->setting = 'RSS_Expiry_Length';
        $ins->value = '604800';

        $result = $result && insert_record('moodletxt_settings', $ins);

    }

    /*
     * Release 2007051112 is Version 2.0
     * New features include inbound messaging and new admin interface
     */
    if ($result && $oldversion < 2007051112) {

        // Rename outbound message table
        $table = new XMLDBTable('moodletxt_messages');
        if (table_exists($table))
            $result = $result && rename_table($table, 'moodletxt_outbox');

        // Add field to hold id of default inbox
        $table = new XMLDBTable('moodletxt_txttools_accounts');
        $field = new XMLDBField('defaultinbox');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, null, null, '0', null);

        $result = $result && add_field($table, $field);


        // Create table to store records of users' inboxes
        $table = new XMLDBTable('moodletxt_inbox');

        if (! table_exists($table)) {

            $table->comment = 'Links Moodle users to inbound messages.';

            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null, null);

            $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            // Add table keys
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('fk-userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

            $result = $result && create_table($table);

        }


        // Create table to store inbox folders
        $table = new XMLDBTable('moodletxt_inbox_folders');

        if (! table_exists($table)) {

            $table->comment = 'Stores inbox folders for organising inbound messages';

            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null, null);

            $table->addFieldInfo('inbox', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '30', null,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('candelete', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('fk-inbox', XMLDB_KEY_FOREIGN, array('inbox'), 'moodletxt_inbox', array('id'));

            $result = $result && create_table($table);

        }


        // Create table to store inbox messages
        $table = new XMLDBTable('moodletxt_inbox_messages');

        if (! table_exists($table)) {

            $table->comment = 'Stores details of text messages received.';

            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null, null);

            $table->addFieldInfo('folderid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('ticket', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('messagetext', XMLDB_TYPE_CHAR, '160', null,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('source', XMLDB_TYPE_CHAR, '20', null,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('timereceived', XMLDB_TYPE_INTEGER, '12', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('hasbeenread', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('fk-folder', XMLDB_KEY_FOREIGN, array('folderid'), 'moodletxt_inbox_folders', array('id'));

            $result = $result && create_table($table);

        }


        // Create table to store inbound filters
        $table = new XMLDBTable('moodletxt_filter');

        if (! table_exists($table)) {

            $table->comment = 'Holds filters for filtering inbound messages to inboxes.';

            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null, null);

            $table->addFieldInfo('account', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('type', XMLDB_TYPE_CHAR, '10', null,
                XMLDB_NOTNULL, null, XMLDB_ENUM, array('PHONENO', 'KEYWORD'), 'KEYWORD', null);

            $table->addFieldInfo('value', XMLDB_TYPE_CHAR, '50', null,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('fk-account', XMLDB_KEY_FOREIGN, array('account'), 'moodletxt_accounts', array('id'));

            $result = $result && create_table($table);

        }


        // Create table linking filters to inboxes
        $table = new XMLDBTable('moodletxt_inbox_filter');

        if (! table_exists($table)) {

            $table->comment = 'Links inbound filters to inboxes.';

            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null, null);

            $table->addFieldInfo('inbox', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('filter', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('fk-inbox', XMLDB_KEY_FOREIGN, array('inbox'), 'moodletxt_inbox', array('id'));
            $table->addKeyInfo('fk-filter', XMLDB_KEY_FOREIGN, array('filter'), 'moodletxt_filter', array('id'));

            $result = $result && create_table($table);

        }


        // Add settings for inbound messaging
        $ins = new stdClass;
        $ins->setting = 'Get_Inbound_On_View';
        $ins->value = '1';

        $result = $result && insert_record('moodletxt_settings', $ins);

        $firstadmin = get_admin();

        // Set all current accounts to use this poor bugger as the default inbox
        $inboxid = moodletxt_create_inbox($firstadmin->id);

        $result = $result && set_field('moodletxt_txttools_accounts', 'defaultinbox', addslashes($inboxid), 'defaultinbox', '0');

    }

    /*
     * Release 2007080112 is Version 2.0.3
     * Bugfixing CDATA and filter creation bugs on admin panel
     * (I forgot to update the global version number in previous bugfix
     * releases, because I'm a very naughty person.)
     */
    if ($result && $oldversion < 2007080112) {

        // Do nothing - tagged for reference and any required bugfixes

    }

    /*
     * Release 2007082812 is Version 2.1
     * Features added as requested by users.  Table sorting and paging
     * fully implemented.  TTL added.  Couple of bugfixes, including
     * case sensitivity on keywords.  AJAX form submission on user admin panel.
     * Content output separated off into output files. Multiple messages now also supported.
     * XML builder has also been rebuilt to be much more sexy - parser is next.
     */
    if ($result && $oldversion < 2007082812) {

        // Change outbox table to allow for longer messages and TTL
        $table = new XMLDBTable('moodletxt_outbox');

        $field = new XMLDBField('messagetext');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'medium', null,
            XMLDB_NOTNULL, null, null, null, null, null);

        $result = $result && change_field_precision($table, $field);

        $field = new XMLDBField('ttl');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '2', null,
            XMLDB_NOTNULL, null, XMLDB_ENUM, array('24', '36', '48', '60', '72'), '72', 'scheduledfor');

        $result = $result && add_field($table, $field);

    }

    /*
     * Release 2007101512 is Version 2.2-dev
     * No new features added as of yet, but the system now runs on PostgreSQL and MS SQL Server >= 2005.
     * ### THIS IS A SPECIAL RELEASE INTENDED ONLY FOR ALISTAIR HOLE AT WORTECH ###
     */
    if ($result && $oldversion < 2007101512) {

        // Allow templates to be over 160 characters
        $table = new XMLDBTable('moodletxt_templates');
        $field = new XMLDBField('template');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'medium', null,
            XMLDB_NOTNULL, null, null, null, null, null);

        $result = $result && change_field_precision($table, $field);

        // Remove "date" as field name - reserved word in other DBs
        $table = new XMLDBTable('moodletxt_userstats');
        $field = new XMLDBField('date');
        $field->setAttributes(XMLDB_TYPE_DATETIME, null, null,
            XMLDB_NOTNULL, null, null, null, null, null);

        $result = $result && rename_field($table, $field, 'date_entered');

        // Rename table names to make them shorter - inter-DB compliancy
        $table = new XMLDBTable('moodletxt_txttools_accounts');
        if (table_exists($table))
            $result = $result && rename_table($table, 'moodletxt_accounts');

        $table = new XMLDBTable('moodletxt_txttools_accounts_user');
        if (table_exists($table))
            $result = $result && rename_table($table, 'moodletxt_accounts_user');

        // Rename foreign keys to remove reserved word "user" for other DBs
        $table = new XMLDBTable('moodletxt_templates');
        $field = new XMLDBField('user');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, null, null, null, null);

        $result = $result && rename_field($table, $field, 'userid');

        $table = new XMLDBTable('moodletxt_signatures');

        $result = $result && rename_field($table, $field, 'userid');

    }

    /*
     * Release 2008011112 is Version 2.2 RC - later modded before release
     * Same as 2.2-dev, but also works internationally now
     */
    if ($result && $oldversion < 2008011112) {

        if ($CFG->dbtype == 'postgres7') {

            // Update settings table sequence - early installation scripts
            // made a mess of it
            $sql = 'ALTER SEQUENCE ' . $CFG->prefix . 'moodletxt_settings_id_seq INCREMENT BY 15';

            $result = $result && execute_sql(trim($sql));

        }

        // Add in default international prefix
        $ins = new stdClass;
        $ins->setting = 'Default_International_Prefix';
        $ins->value = '+44';

        $result = $result && insert_record('moodletxt_settings', $ins);

        // Add in default national prefix
        $ins = new stdClass;
        $ins->setting = 'National_Prefix';
        $ins->value = '0';

        $result = $result && insert_record('moodletxt_settings', $ins);


    }

    /*
     * Release 2008012412 is Version 2.2 release
     * Modded before release to allow users to select which
     * phone field to extract phone numbers from
     */
    if ($result && $oldversion < 2008012412) {

        // Add phone source
        $ins = new stdClass;
        $ins->setting = 'Phone_Number_Source';
        $ins->value = 'phone2';

        $result = $result && insert_record('moodletxt_settings', $ins);

    }

    /*
     * Release 2008081112 is Version 2.2.1 release
     * Basically consisting of bugfix to PostgreSQL install file
     */
    if ($result && $oldversion < 2008081112) {

        // Nothing to do

    }

    /*
     * Release 2009031312 is Version 2.3 release
     * Main improvements are user address books, change from
     * DOMIT! parser to SAXY, and many many bug fixes
     */
    if ($result && $oldversion < 2009031312) {

        $table = new XMLDBTable('moodletxt');
        if (table_exists($table))
            $result = $result && drop_table($table);


        // Change userstats table to use DATETIME
        $table = new XMLDBTable('moodletxt_userstats');
        $field = new XMLDBField('date_entered');
        $field->setAttributes(XMLDB_TYPE_DATETIME, null, null,
            XMLDB_NOTNULL, null, null, null, null, null);

        $result = $result && change_field_type($table, $field);


        // Change text lengths inbound/outbound to allow for "unlimited" length
        $table  = new XMLDBTable('moodletxt_inbox_messages');
        $table2 = new XMLDBTable('moodletxt_outbox');

        $field = new XMLDBField('messagetext');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'medium', null,
            XMLDB_NOTNULL, null, null, null, null, null);

        $result = $result && change_field_type($table, $field);
        $result = $result && change_field_type($table2, $field);


        // Creating table to store details of contact address books
        $table = new XMLDBTable('moodletxt_addressbook');

        if (! table_exists($table)) {

            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null, null);

            $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '50', null,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('type', XMLDB_TYPE_CHAR, '7', null,
                XMLDB_NOTNULL, null, XMLDB_ENUM, array('global', 'private'), 'global', null);

            $table->addFieldInfo('owner', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('fk-owner-user', XMLDB_KEY_FOREIGN, array('owner'), 'user', array('id'));

            $result = $result && create_table($table);

        }


        // Creating table to store contact details
        $table = new XMLDBTable('moodletxt_ab_contacts');

        if (! table_exists($table)) {

            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null, null);

            $table->addFieldInfo('addressbook', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('lastname', XMLDB_TYPE_CHAR, '50', null,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('firstname', XMLDB_TYPE_CHAR, '50', null,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('company', XMLDB_TYPE_CHAR, '100', null,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('phoneno', XMLDB_TYPE_CHAR, '20', null,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('fk-parent-addressbook', XMLDB_KEY_FOREIGN, array('addressbook'), 'moodletxt_addressbook', array('id'));

            $result = $result && create_table($table);

        }

        // Creating table to link Moodle users to private address books
        $table = new XMLDBTable('moodletxt_ab_users');

        if (! table_exists($table)) {

            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL,
                XMLDB_SEQUENCE, null, null, null, null);

            $table->addFieldInfo('addressbook', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('fk-addressbook', XMLDB_KEY_FOREIGN, array('addressbook'), 'moodletxt_addressbook', array('id'));
            $table->addKeyInfo('fk-user', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

            $result = $result && create_table($table);

        }


        // Creating table to hold details of contact groups
        $table = new XMLDBTable('moodletxt_ab_groups');

        if (! table_exists($table)) {

            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL,
                XMLDB_SEQUENCE, null, null, null, null);

            $table->addFieldInfo('addressbook', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '100', null,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('description', XMLDB_TYPE_TEXT, 'small', null,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('fk-addressbook-parent', XMLDB_KEY_FOREIGN, array('addressbook'), 'moodletxt_addressbook', array('id'));

            $result = $result && create_table($table);

        }


        // Creating table to link address book contacts to groups
        $table = new XMLDBTable('moodletxt_ab_group_link');

        if (! table_exists($table)) {

            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null, null);

            $table->addFieldInfo('contact', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('groupid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('fk-contact', XMLDB_KEY_FOREIGN, array('contact'), 'moodletxt_ab_contacts', array('id'));
            $table->addKeyInfo('fk-groupid', XMLDB_KEY_FOREIGN, array('groupid'), 'moodletxt_ab_groups', array('id'));

            $result = $result && create_table($table);

        }


        // Creating table to link received messages to address book contacts
        $table = new XMLDBTable('moodletxt_inbox_contacts');

        if (! table_exists($table)) {

            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null, null);

            $table->addFieldInfo('contact', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('receivedmessage', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('fk-contact', XMLDB_KEY_FOREIGN, array('contact'), 'moodletxt_ab_contacts', array('id'));
            $table->addKeyInfo('fk-receivedmessage', XMLDB_KEY_FOREIGN, array('receivedmessage'), 'moodletxt_inbox_messages', array('id'));

            $result = $result && create_table($table);

        }


        // Creating table to link received messages to Moodle users
        $table = new XMLDBTable('moodletxt_inbox_users');

        if (! table_exists($table)) {

            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null, null);

            $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('receivedmessage', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('fk-userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
            $table->addKeyInfo('fk-receivedmessage', XMLDB_KEY_FOREIGN, array('receivedmessage'), 'moodletxt_inbox_messages', array('id'));

            $result = $result && create_table($table);

        }


        // Creating table to link sent messages and address book contacts
        $table = new XMLDBTable('moodletxt_sent_contacts');

        if (! table_exists($table)) {

            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null, null);

            $table->addFieldInfo('contact', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('sentmessage', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('fk-contact', XMLDB_KEY_FOREIGN, array('contact'), 'moodletxt_ab_contacts', array('id'));
            $table->addKeyInfo('fk-sentmessage', XMLDB_KEY_FOREIGN, array('sentmessage'), 'moodletxt_sent', array('id'));

            $result = $result && create_table($table);

        }


        // Creating table to link sent messages and Moodle users
        $table = new XMLDBTable('moodletxt_sent_users');

        if (! table_exists($table)) {

            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null, null);

            $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('sentmessage', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('fk-userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
            $table->addKeyInfo('fk-sentmessage', XMLDB_KEY_FOREIGN, array('sentmessage'), 'moodletxt_sent', array('id'));

            $result = $result && create_table($table);

        }


        /*
         * Prepare to remove the old foreign key linking sent
         * messages and Moodle users. Grab all the old data
         * and put it in the new structure
         */
        $sql = 'SELECT id, userid FROM ' . $CFG->prefix . 'moodletxt_sent WHERE userid > 0';

        $links = get_records_sql($sql);

        if (is_array($links)) {

            foreach ($links as $link) {

                $ins = new stdClass;
                $ins->userid = $link->userid;
                $ins->sentmessage = $link->id;

                $result = $result && insert_record('moodletxt_sent_users', $ins);

            }

        }

        // Remove old field
        $table = new XMLDBTable('moodletxt_sent');
        $field = new XMLDBField('userid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, null, null, null, null);

        $result = $result && drop_field($table, $field);

        // Add new name fields - store name on send
        $field = new XMLDBField('sendname');
        $field->setAttributes(XMLDB_TYPE_CHAR, '100', null,
                XMLDB_NOTNULL, null, null, null, null, null);

        $result = $result && add_field($table, $field);

        // Add new name fields - store when received
        $table  = new XMLDBTable('moodletxt_inbox_messages');

        $field = new XMLDBField('sourcename');
        $field->setAttributes(XMLDB_TYPE_CHAR, '100', null,
            XMLDB_NOTNULL, null, null, null, null, null);

        $result = $result && add_field($table, $field);

        // Add default recipient name
        $ins = new stdClass;
        $ins->setting = 'Default_Recipient_Name';
        $ins->value = get_string('configdefaultrecipient', 'block_moodletxt');

        $result = $result && insert_record('moodletxt_settings', $ins);

        // Add option to hide inbound numbers
        $ins = new stdClass;
        $ins->setting = 'Show_Inbound_Numbers';
        $ins->value = '1';  // Users will expect inbound numbers to remain

        $result = $result && insert_record('moodletxt_settings', $ins);

        // Update all templates and remove newlines
        $templates = get_records('moodletxt_templates');

        if (is_array($templates)) {

            foreach($templates as $template) {

                $template->template = preg_replace("(\r\n|\n|\r|\t)", " ", $template->template);

                // Doesn't matter if this fails
                update_record('moodletxt_templates', $template);

            }

        }

    }

    /**
     * Release 2009070212 is version 2.3.1
     * Bugfixes over original 2.3 release
     */
    if ($result && $oldversion < 2009070212) {

        // Nothing to do

    }
    
    /**
     * Release 2009092512 is version 2.3.2
     * Introduces support for proxies
     */
     if ($result && $oldversion < 2009092512) {

        // Insert proxy details
        $ins = new stdClass;
        $ins->setting = 'Proxy_Host';
        $ins->value = '';

        $result = $result && insert_record('moodletxt_settings', $ins);

        $ins = new stdClass;
        $ins->setting = 'Proxy_Port';
        $ins->value = '';

        $result = $result && insert_record('moodletxt_settings', $ins);

        $ins = new stdClass;
        $ins->setting = 'Proxy_Username';
        $ins->value = '';

        $result = $result && insert_record('moodletxt_settings', $ins);

        $ins = new stdClass;
        $ins->setting = 'Proxy_Password';
        $ins->value = '';

        $result = $result && insert_record('moodletxt_settings', $ins);

        // Bring in support for XML connector 1.1 - store time of last update
        $ins = new stdClass;
        $ins->setting = 'Inbound_Last_Update';
        $ins->value = time();

        $result = $result && insert_record('moodletxt_settings', $ins);
        
     }

    /**
     * Release 2009102912 is version 2.3.3 pre-release for Tony Butler
     * General bugfixes including filtering system
     */
    if ($result && $oldversion < 2009102912) {

        // Enable include of main jQuery script
        // (Disable include if Moodle theme already includes jQuery)
        $ins = new stdClass;
        $ins->setting = 'jQuery_Include_Enabled';
        $ins->value = '1';

        $result = $result && insert_record('moodletxt_settings', $ins);

    }

    /**
     * Release 2009111612 is version 2.3.3 final release
     * More bugfixes to filtering and jQuery conversions
     */
    if ($result && $oldversion < 2009111612) {

        // No database changes since beta

    }

    /**
     * Release 2009121812 is version 2.3.3.1 release
     * Fixes problems with apostrophes in contact names and inbound messages
     */
    if ($result && $oldversion < 2009121812) {

        // No database changes

    }

    /**
     * Release 2010012612 is version 2.3.3.2 release
     * Fixes admin page bug with granting course-level access
     */
    if ($result && $oldversion < 2010012612) {

        // No database changes

    }

    /**
     * moodletxt 2.4 beta 1
     * Ditching old authentication, readying for Moodle 2.0
     */
    if ($result && $oldversion < 2010091301) {

        /*
         * Renaming tables to fit updated coding standards
         * (See ticket #570)
         */
        $table = new XMLDBTable('moodletxt_inbox');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_inbox');

        $table = new XMLDBTable('moodletxt_inbox_folders');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_in_folders');

        $table = new XMLDBTable('moodletxt_inbox_messages');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_in_mess');

        $table = new XMLDBTable('moodletxt_accounts');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_accounts');

        $table = new XMLDBTable('moodletxt_accounts_user');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_acc_user');

        $table = new XMLDBTable('moodletxt_userstats');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_stats');

        $table = new XMLDBTable('moodletxt_outbox');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_outbox');

        $table = new XMLDBTable('moodletxt_sent');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_sent');

        $table = new XMLDBTable('moodletxt_status');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_status');

        $table = new XMLDBTable('moodletxt_templates');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_templates');

        $table = new XMLDBTable('moodletxt_signatures');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_sig');

        $table = new XMLDBTable('moodletxt_rss_archive');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_rss');

        $table = new XMLDBTable('moodletxt_settings');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_config');

        $table = new XMLDBTable('moodletxt_filter');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_filter');

        $table = new XMLDBTable('moodletxt_inbox_filter');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_in_filter');

        $table = new XMLDBTable('moodletxt_addressbook');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_ab');

        $table = new XMLDBTable('moodletxt_ab_contacts');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_ab_entry');

        $table = new XMLDBTable('moodletxt_ab_users');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_ab_users');

        $table = new XMLDBTable('moodletxt_ab_groups');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_ab_groups');

        $table = new XMLDBTable('moodletxt_ab_group_link');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_ab_grpmem');

        $table = new XMLDBTable('moodletxt_inbox_contacts');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_in_ab');

        $table = new XMLDBTable('moodletxt_inbox_users');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_in_user');

        $table = new XMLDBTable('moodletxt_sent_contacts');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_sent_ab');

        $table = new XMLDBTable('moodletxt_sent_users');
        if (table_exists($table))
            $result = $result && rename_table($table, 'block_mtxt_sent_user');


        if (!$result) return $result;

        /**
         * Create table to hold user settings
         */
        $table = new XMLDBTable('block_mtxt_uconfig');

        if (! table_exists($table)) {

            $table->comment = 'Holds user settings for moodletxt';

            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null, null);

            $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('setting', XMLDB_TYPE_CHAR, '50', null,
                XMLDB_NOTNULL, null, null, null, null, null);

            $table->addFieldInfo('value', XMLDB_TYPE_CHAR, '255', null,
                XMLDB_NOTNULL, null, null, null, null, null);

            // Add table keys
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('fk-userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

            $result = $result && create_table($table);

        }

        /**
         * Move all user signatures to the new user config table
         */
        $table = new XMLDBTable('block_mtxt_sig');

        if (table_exists($table)) {

            $userSignatures = get_records('block_mtxt_sig');

            if (is_array($userSignatures)) {

                foreach($userSignatures as $signature) {

                    if ($result) {

                        $insertSig = new stdClass;
                        $insertSig->userid = $signature->userid;
                        $insertSig->setting = 'SIGNATURE';
                        $insertSig->value = $signature->signature;

                        $result = $result && insert_record('block_mtxt_uconfig', $insertSig);

                    }

                }

            }

            /**
             * Drop redundant signature table
             */
            $result = $result && drop_table($table);

        }


        if (!$result) return $result;


        /**
         * Upgrading sent messages to comply with new authentication
         * structure - dropping user/course/account link table
         * and adding relevant data to the outbox table
         */
        $outboxtable = new XMLDBTable('block_mtxt_outbox');
        $statstable = new XMLDBTable('block_mtxt_stats');
        $accountLinkTable = new XMLDBTable('block_mtxt_acc_user');

        $field = new XMLDBField('userid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, null, null, '0', 'id');

        $result = $result && add_field($outboxtable, $field);
        $result = $result && add_field($statstable, $field);


        $field = new XMLDBField('txttoolsaccount');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, null, null, '0', 'userid');

        $result = $result && add_field($outboxtable, $field);
        $result = $result && add_field($statstable, $field);


        // Grab all existing user/account/course links and use them to update messages
        $field = new XMLDBField('useraccount');

        if (table_exists($accountLinkTable) &&
            field_exists($outboxtable, $field) &&
            field_exists($statstable, $field)) {

            $existingLinks = get_records('block_mtxt_acc_user');

            if (is_array($existingLinks)) {

                foreach($existingLinks as $link) {

                    $sql = moodletxt_get_sql('upgrade24sentmessages');
                    $sql = sprintf($sql, addslashes($link->moodleuser), addslashes($link->txttoolsaccount), addslashes($link->id));

                    $result = $result && execute_sql($sql);

                    $sql = moodletxt_get_sql('upgrade24userstats');
                    $sql = sprintf($sql, addslashes($link->moodleuser), addslashes($link->txttoolsaccount), addslashes($link->id));

                    $result = $result && execute_sql($sql);

                }

            }

        }


        // Drop MSSQL indexes/constraints if created
        // Ugly method - better one should be found if possible.
        if ($CFG->dbtype == 'mssql' || $CFG->dbtype == 'mssql_n') {

            $sql = 'ALTER TABLE ' . $CFG->prefix . 'block_mtxt_outbox DROP CONSTRAINT ' . $CFG->prefix . 'blocmtxtoutb_ttl_ck';
            $result = $result && execute_sql($sql);

            $sql = 'DROP INDEX ' . $CFG->prefix . 'block_mtxt_stats.' . $CFG->prefix . 'mooduser_use_ix';
            $result = $result && execute_sql($sql);

            $sql = 'DROP INDEX ' . $CFG->prefix . 'block_mtxt_outbox.' . $CFG->prefix . 'moodoutb_use_ix';
            $result = $result && execute_sql($sql);

        }

        // Drop previously used link fields
        $field = new XMLDBField('useraccount');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
        XMLDB_NOTNULL, null, null, null, null, null);

        $result = $result && drop_field($outboxtable, $field);
        $result = $result && drop_field($statstable, $field);

        // Drop redundant table
        if (table_exists($accountLinkTable))
            $result = $result && drop_table($accountLinkTable);

        /**
         * Drop time-to-live field from system
         * This option is now deprecated in the XML connector
         */
        $field = new XMLDBField('ttl');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '2', null,
            XMLDB_NOTNULL, null, XMLDB_ENUM, array('24', '36', '48', '60', '72'), '72', 'scheduledfor');

        $result = $result && drop_field($outboxtable, $field);

        /**
         * Add support for unicode suppression in XML connector 1.2
         */
        $outboxtable = new XMLDBTable('block_mtxt_outbox');

        $field = new XMLDBField('suppresunicode');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, null, null, null, null);

        $result = $result && add_field($outboxtable, $field);

        /**
         * Now we don't have the user-account link,
         * we need some way of disabling outbound access via
         * accounts. In keeping with the new theme of fine-grained
         * control, we're going to have inbound *and* outbound
         * access controls
         */
        $accountstable = new XMLDBTable('block_mtxt_accounts');

        $field = new XMLDBField('outboundenabled');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, null, null, 1, null);

        $result = $result && add_field($accountstable, $field);

        $field = new XMLDBField('inboundenabled');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, null, null, 1, null);

        $result = $result && add_field($accountstable, $field);

        /**
         * Add fields to support credit checking on accounts
         */
        $field = new XMLDBField('creditsused');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, 0, null);

        $result = $result && add_field($accountstable, $field);

        $field = new XMLDBField('creditsremaining');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, 0, null);

        $result = $result && add_field($accountstable, $field);

        $field = new XMLDBField('lastupdate');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '12', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null, null, 0, null);

        $result = $result && add_field($accountstable, $field);

        // Duplicate existing jQuery include switch
        // for jQuery UI library
        $ins = new stdClass;
        $ins->setting = 'jQuery_UI_Include_Enabled';
        $ins->value = '1';

        $result = $result && insert_record('block_mtxt_config', $ins);

        // Manually insert cron update interval on upgrade.
        // Gets around bug in Moodle system
        // Check it out: http://tracker.moodle.org/browse/MDL-10281
        $blockrecord = get_record('block', 'name', 'moodletxt');
        $blockrecord->name = addslashes($blockrecord->name);
        $blockrecord->cron = 300;

        update_record('block', $blockrecord);

    }

    /**
     * moodletxt 2.4 beta 2
     * Ditching old authentication, readying for Moodle 2.0
     */
    if ($result && $oldversion < 2011011101) {

        // Nothing to do

    }

    /**
     * Placeholder for 2.4 final
     * Version number will change at release
     */
    if ($result && $oldversion < 2011032901) {

        /// Define field accounttype to be added to block_mtxt_accounts
        $table = new XMLDBTable('block_mtxt_accounts');
        $field = new XMLDBField('accounttype');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'inboundenabled');

        /// Launch add field accounttype
        $result = $result && add_field($table, $field);

    }

    return $result;

}

?>