<?php

require_once($CFG->libdir  . '/datalib.php');
require_once($CFG->dirroot . '/blocks/moodletxt/lib.php');
require_once($CFG->dirroot . '/blocks/moodletxt/db/sqllib.php');

// Get XML connector classes
require_once($CFG->dirroot . '/blocks/moodletxt/xml/moodletxt_xml_controller.php');
require_once($CFG->dirroot . '/blocks/moodletxt/inbound/InboundFilterManager.php');

require_once($CFG->dirroot . '/blocks/moodletxt/encryption.php');
    
/**
 * Performs maintenance functions for moodletxt.
 * @author Greg J Preece <support@txttools.co.uk>
 * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
 * @version 2010070712
 * @since 2010070712
 */
class MoodletxtCronHandler {

    /**
     * XML controller for connections to txttools
     * @var moodletxt_xml_controller
     */
    var $xmlcontroller;

    /**
     * Takes care of filtering inbound messages
     * @var InboundFilterManager
     */
    var $filtermanager;

    /**
     * Constructor sets up required objects
     * for processing
     * @version 2010070712
     * @since 2010070712
     */
    function MoodletxtCronHandler() {

        // Create XML objects
        $this->xmlcontroller = new moodletxt_xml_controller();
        $this->filtermanager = new InboundFilterManager();
        
    }

    /**
     * Runs the cron tasks required for
     * system mtainenance
     * @return boolean Success
     * @version 2010070712
     * @since 2010070712
     */
    function doCron() {

        // You do cron cron cron, you do cron cron...

        $this->getStatusUpdates();
        $this->getInboundMessages();
        $this->updateAccountDetails();

        /*
         * These dead-link nukers don't need to be run every time,
         * and could take a while to execute, so use rand() to run them
         * approx every 1 in 6 runs (once an hour on default cron)
         */
        if (rand(1, 6) == 1)
            $this->removeDeadAddressbookLinks();

        if (rand(1, 6) == 1)
            $this->removeDeadInboxMessageLinks();

        if (rand(1, 6) == 1)
            $this->removeDeadSentMessageLinks();

        return true;

    }

    /**
     * Updates status info within the system for any sent
     * messages that have not reached a final status
     * @version 2010070712
     * @since 2010070712
     */
    function getStatusUpdates() {

        // Get txttools account links
        $outboundaccounts = get_records('block_mtxt_accounts', 'outboundenabled', '1');
        if (!is_array($outboundaccounts)) $outboundaccounts = array();

        foreach($outboundaccounts as $account) {

            /*
              ############################################################
              # Update the stored statuses for each message found
              ############################################################
            */

            $sql = moodletxt_get_sql('crongetfinishedmessages');
            $sql = sprintf($sql, moodletxt_escape_string($account->id), 2, 5);

            $finalrecords = get_records_sql($sql);

            $ticketnumbers = "";

            // Bug fix for empty ADODB recordsets
            if ((is_array($finalrecords)) && (count(array_keys($finalrecords)) > 0)) {

                // Get tickets that DO need updating
                $sql = moodletxt_get_sql('crongetsentfrag');

                foreach ($finalrecords as $ticket)
                    $sql .= $ticket->ticketnumber . "', '";

                $sql = substr($sql, 0, (strlen($sql) - 3)) . ')';

                $ticketnumbers = get_records_sql($sql);

            } else {

                $ticketnumbers = get_records('block_mtxt_sent', '', '', '', 'id, ticketnumber');

            }

            // Put ticket numbers into sequentially-indexed array
            $ticketarray = array();

            if (is_array($ticketnumbers))
                foreach($ticketnumbers as $number)
                    array_push($ticketarray, $number->ticketnumber);


            // If there are ticket numbers to process, hit it!
            if (count($ticketarray) > 0) {

                $statusobjects = $this->xmlcontroller->get_status_updates($ticketarray, $account->id);
                moodletxt_write_objects($statusobjects);

            }

        }

    }

    /**
     * Retrieves any new inbound messages from
     * the txttools server and filters them
     * @version 2010070712
     * @since 2010070712
     */
    function getInboundMessages() {

        $inboundaccounts = get_records('block_mtxt_accounts', 'inboundenabled', '1');
        if (!is_array($inboundaccounts)) $inboundaccounts = array();

        $inboundmessagesets = $this->xmlcontroller->get_inbound_messages($inboundaccounts);

        // Filter into correct folders
        $accountids = array_keys($inboundmessagesets);

        foreach ($accountids as $accid) {

            $filteredObjects = $this->filtermanager->filterMessages($inboundmessagesets[$accid]);
            moodletxt_write_objects($inboundmessagesets[$accid]);

        }

    }

    /**
     * Retrieves updated account credit information
     * for all txttools accounts stored in the system
     * @version 2010070712
     * @since 2010070712
     */
    function updateAccountDetails() {

        $allaccounts = get_records('block_mtxt_accounts');
        if (!is_array($allaccounts)) $allaccounts = array();

        foreach($allaccounts as $account) {

            $accountDetails = $this->xmlcontroller->get_account_credit_info($account->id);
            moodletxt_update_account_info($accountDetails);

        }

    }

    /**
     * Kills off any redundant entries in the link
     * table connecting address book entries to Moodle users
     * (In case the Moodle user was deleted.)
     * @version 2010070712
     * @since 2010070712
     */
    function removeDeadAddressbookLinks() {

        $sql = moodletxt_get_sql('crondeadaddressbooklinks');
        execute_sql($sql);

    }

    /**
     * Kills of redundant links to Moodle users
     * from messages contained in inboxes.
     * (In case the Moodle user was deleted.)
     * @version 2010070712
     * @since 2010070712
     */
    function removeDeadInboxMessageLinks() {

        $sql = moodletxt_get_sql('crondeadinboxlinks');
        execute_sql($sql);

    }

    /**
     * Kills off redundant links to Moodle users
     * from messages contained in outboxes.
     * (In case the Moodle user was deleted.)
     * @version 2010070712
     * @since 2010070712
     */
    function removeDeadSentMessageLinks() {

        $sql = moodletxt_get_sql('crondeadsentlinks');
        execute_sql($sql);

    }

}

?>