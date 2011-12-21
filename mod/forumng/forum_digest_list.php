<?php
require_once(dirname(__FILE__).'/forum.php');

/**
 * Manages a list (based on a database recordset, so not all stored in memory)
 * of posts which need to be included in digests sent to users.
 *
 * The list only includes posts which are due to be included in digests. The
 * same caveats apply as to forum_mail_list.
 */
class forum_digest_list extends forum_mail_list {
    /** Config flag used to prevent sending mails twice */
    const PENDING_MARK_DIGESTED = 'pending_mark_digested';

    function __construct($tracetimes) {
        parent::__construct($tracetimes);
    }

    protected function get_pending_flag_name() {
        return self::PENDING_MARK_DIGESTED;
    }

    protected function get_target_mail_state() {
        return forum::MAILSTATE_DIGESTED;
    }

    protected function get_safety_net($time) {
        // The digest safety net is 24 hours earlier because digest posts may
        // be delayed by 24 hours.
        return parent::get_safety_net($time) - 24 * 3600;
    }
    
    protected function get_query_where($time) {
        global $CFG;

        // In case cron has not run for a while
        $safetynet = $this->get_safety_net($time);

        global $CFG;
        return " 
WHERE
    -- Post must be waiting for digest
    fp.mailstate = " . forum::MAILSTATE_MAILED . "

    -- Don't mail out really old posts (unless they were previously hidden)
    AND (fp.created > $safetynet OR fd.timestart > $safetynet)

    -- Post and discussion must not have been deleted and we're only looking
    -- at original posts not edited old ones
    AND fp.deleted = 0
    AND fd.deleted = 0
    AND fp.oldversion = 0

    -- Course-module and context limitations
    AND m.name='forumng'
    AND x.contextlevel = 70";
    }
}
?>