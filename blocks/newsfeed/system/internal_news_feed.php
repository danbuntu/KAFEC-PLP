<?php
require_once('news_feed.php');
require_once('feed_role_user.php');

/**
 * Object representing data held for an internal news feed
 * (one that is created within the system).
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 */
class internal_news_feed extends news_feed {

    const ROLE_POSTER='poster',ROLE_APPROVER='approver';

    private $authids=null,$roles=null;

    /**
     * Obtain the list of optional authids that may be selected when
     * posting messages to this newsfeed. The default authid is not
     * included in this list, but should also be allowed.
     *
     * This call may involve a database request.
     * @return array Array of authid strings
     */
    public function get_optional_authids() {
        // Get list from database if it hasn't already been cached
        if(is_null($this->authids)) {
            $rs=db_do('SELECT authid FROM prefix_newsfeed_authids WHERE newsfeedid='.$this->get_id());
            $this->authids=array();
            while(!$rs->EOF) {
                $this->authids[]=$rs->fields['authid'];
                $rs->MoveNext();
            }
        }
        return $this->authids;
    }

    /**
     * Adds an authid to the list.
     * @param string $authid New authid to add
     * @throws Exception If the authid already exists.
     */
    public function add_optional_authid($authid) {
        if(empty($authid)) {
            throw new Exception("Optional authids may not be blank/null",
                EXN_NEWSFEED_AUTHIDINVALID);
        }
        $this->get_optional_authids();
        if(in_array($authid,$this->authids) || $this->get_default_authid()==$authid) {
            throw new Exception("Feed already allows authid $authid",
                EXN_NEWSFEED_ALREADYGOTAUTHID);
        }

        db_do('INSERT INTO prefix_newsfeed_authids(newsfeedid,authid) VALUES('.
            $this->get_id().','.db_q($authid).')');
        $this->authids[]=$authid;
    }

    /**
     * Removes an authid from the list.
     * @param string $authid Authid to remove
     * @throws Exception If the authid isn't there.
     */
    public function remove_optional_authid($authid) {
        $this->get_optional_authids();
        if(($key=array_search($authid,$this->authids))===false) {
            throw new Exception("Feed does not contain authid $authid",
                EXN_NEWSFEED_NOTGOTAUTHID);
        }

        db_do('DELETE FROM prefix_newsfeed_authids WHERE newsfeedid='.
            $this->get_id().' AND authid='.db_q($authid));
        unset($this->authids[$key]);
    }

    /**
     * Updates the list of authids as necessary to match
     * that specified.
     * Note that this does not call save_changes(), so you
     * need to call that afterwards.
     * @param string $defaultauthid Default authid, or null if none
     * @param array $optionalauthids Array of optional authids
     */
    public function set_authids($defaultauthid,$optionalauthids) {
        // Clear default one so it doesn't interfere with the list
        // (otherwise adding authid would throw exception when 'moving'
        // an authid from default to optional).
        if($this->get_default_authid()!=$defaultauthid) {
            $resetdefault=$this->get_default_authid();
            $this->set_default_authid(null);
        }

        // Update optional list
        $tw=new transaction_wrapper();
        try {
            $existing=$this->get_optional_authids();

            foreach($existing as $existingauthid) {
                if(!in_array($existingauthid,$optionalauthids)) {
                    $this->remove_optional_authid($existingauthid);
                }
            }

            foreach($optionalauthids as $new) {
                if(!in_array($new,$existing)) {
                    $this->add_optional_authid($new);
                }
            }

            $tw->commit();
        } catch(Exception $e) {
            $tw->rollback();
            if(isset($resetdefault)) {
                $this->set_default_authid($resetdefault);
            }
            throw $e;
        }


        // OK now set default one
        if($this->get_default_authid()!=$defaultauthid) {
            $this->set_default_authid($defaultauthid);
        }
    }

    public function get_poster_usernames() {
        return $this->get_role_usernames(self::ROLE_POSTER);
    }
    public function get_approver_usernames() {
        return $this->get_role_usernames(self::ROLE_APPROVER);
    }

    private function get_role_usernames($rolename) {
        $roles=$this->get_feed_users();
        $usernames=array();
        if(array_key_exists($rolename,$roles)) {
            foreach($roles[$rolename] as $info) {
                $usernames[]=$info->get_user_name();
            }
        }
        return $usernames;
    }

    /**
     * Get the list of people associated with each role on this newsfeed.
     *
     * This call may involve a database request.
     * @return array Associative array of 'rolename' => array of feed_role_user
     */
    public function get_feed_users() {
        // Get list from database if it hasn't already been cached
        if(is_null($this->roles)) {
            $id=$this->get_id();
            $nfcontext = get_context_instance(CONTEXT_BLOCK, $this->get_blockinstance());
            $this->roles=array();
            $fields = 'u.id AS userid, u.username, u.firstname, u.lastname, u.email';

            // Add newsfeed posters to roles array
            $capability = 'block/newsfeed:post';
            $usersbycap = get_users_by_capability($nfcontext, $capability, $fields, '', '', '', '', '', false);
            $rolename = self::ROLE_POSTER;
            if (!empty($usersbycap)) {
                foreach ($usersbycap as $userbycap) {
                    $userbycap->rolename = $rolename;
                    $user=new feed_role_user($userbycap);

                    if(array_key_exists($rolename, $this->roles)) {
                        $this->roles[$rolename][] = $user;
                    } else {
                        $this->roles[$rolename] = array($user);
                    }
                }
            }

            // Add newsfeed approvers to roles array
            $capability = 'block/newsfeed:approve';
            $usersbycap = get_users_by_capability($nfcontext, $capability, $fields, '', '', '', '', '', false);
            $rolename = self::ROLE_APPROVER;
            if (!empty($usersbycap)) {
                foreach ($usersbycap as $userbycap) {
                    $userbycap->rolename = $rolename;
                    $user=new feed_role_user($userbycap);

                    if(array_key_exists($rolename, $this->roles)) {
                        $this->roles[$rolename][] = $user;
                    } else {
                        $this->roles[$rolename] = array($user);
                    }
                }
            }
        }
        return $this->roles;
    }

    /**
     * Clears the roles cache. If you make changes to roles, these will not
     * be reflected in the various methods that check roles until you call this
     * to refresh them (which causes another database query next time).
     */
    public function refresh_roles() {
        $this->roles=null;
    }

    /** Array of included feeds, or null if not obtained */
    private $includedfeeds=null;

    /**
     * Obtains an array of included feeds.
     * @param bool $usecache If true (default), uses information from any previous calls
     * @return array Array of news_feed objects
     */
    public function get_included_feeds($usecache=true) {
        if($this->includedfeeds===null || !$usecache) {
            $this->includedfeeds=
                feed_system::$inst->get_included_feeds($this->get_id());
        }
        return $this->includedfeeds;
    }

    /**
     * Adds an included feed.
     * @param mixed $newsfeed ID of feed to include or feed object
     * @throws Exception If there's a database error (such as adding a feed that's already added)
     */
    public function add_included_feed($newsfeed) {
        if(is_object($newsfeed)) {
            $newsfeed=$newsfeed->get_id();
        }

        db_do('INSERT INTO prefix_newsfeed_includes(parentnewsfeedid,childnewsfeedid) VALUES('.
            sql_int($this->get_id()).','.sql_int($newsfeed).')');

        // Clear cache
        $this->includedfeeds=null;

        // Clear feed cache
        feed_system::$inst->clear_feed_cache($this->get_id());
    }

    /**
     * Removes an included feed.
     * @param mixed $newsfeed ID of feed to include or feed object
     */
    public function remove_included_feed($newsfeed) {
        if(is_object($newsfeed)) {
            $newsfeed=$newsfeed->get_id();
        }

        db_do('DELETE FROM prefix_newsfeed_includes WHERE parentnewsfeedid='.
            sql_int($this->get_id()).' AND childnewsfeedid='.sql_int($newsfeed));

        // Clear cache
        $this->includedfeeds=null;

        // Clear feed cache
        feed_system::$inst->clear_feed_cache($this->get_id());
    }

    public function roll_forward($newshortname, $startdate, $block_instance_id, $startdateoffset) {
       $tw=new transaction_wrapper();

        // Save copy, rolling forward dates
        // Passing on any existing block_instance id as a parameter
        try {
            $this->save_as_new($startdate, $block_instance_id, $startdateoffset);
        } catch(Exception $e) {
            $tw->rollback();
            throw $e;
        }

        // Get previous presentation
        $oldpres = $this->get_pres();

        // Get new presentation
        $newpres = '';
        if(class_exists('ouflags') && ($result = get_course_code_pres($newshortname))) {
            $newpres = $result[2];
        }

        // Update included feeds if we can
        if($newpres && $oldpres && ($newpres != $oldpres)) {
            $includes=$this->get_included_feeds();
            foreach($includes as $include) {

                // Get included presentation
                $incpres = '';
                if(class_exists('ouflags') && ($result = get_course_code_pres($include->get_courseshortname()))) {
                    $incpres = $result[2];
                }

                // Leave as is if no presentation on included feed
                if ($incpres != '') {

                    // Remove included feed
                    $this->remove_included_feed($include);

                    // Check if included feed pres matches previous pres
                    if($incpres == $oldpres) {
    
                        // Search for existing new one
                        $alreadygot = feed_system::$inst->find_feed_for_course_shortname($result[1].'-'.$newpres, $include->get_name());
                        if($alreadygot) {
                            $this->add_included_feed($alreadygot);
                        }
                    }
                }
            }
        }
        $tw->commit();
    }

    /**
     * Saves this feed as a new one (leaving all the old stuff there too).
     * Duplicates all entries and files etc. Includes only current approved
     * versions of messages. Includes are copied, but included-ins are not.
     * @param int $changedate Date (seconds since epoch) to make the 'start time';
     *   if this parameter is set, message times are adjusted accordingly, and
     *   non-rollforward messages are discarded.
     * @param int $block_instance_id
     *   if this parameter is set, the newsfeed is added to this block instance
     *   and the calling function is responsible for updating the configdata
     *   field for this block instance with the returned new newsfeed id
     * @throws Exception
     */
    public function save_as_new($changedate, $block_instance_id, $startdateoffset) {
        $tw=new transaction_wrapper();

        $entries=$this->get_own_entries(true);

        // Call base class to do the actual news feed copy
        $oldid = $this->get_id();
        $newid = parent::save_as_new($changedate, $block_instance_id, $startdateoffset);

        if($changedate) {
            // Get midnight, and the offset since midnight, of start date before...
            $oldstartmidnight=strtotime(date('Y-m-d',($changedate - $startdateoffset)));
            $oldstartseconds=($changedate - $startdateoffset)-$oldstartmidnight;

            // ...and after changing it
            $newstartmidnight=strtotime(date('Y-m-d',$changedate));
            $newstartseconds=$changedate-$newstartmidnight;
        }

        // includes
        db_do("
INSERT INTO prefix_newsfeed_includes(parentnewsfeedid,childnewsfeedid)
SELECT $newid,childnewsfeedid FROM prefix_newsfeed_includes WHERE parentnewsfeedid=$oldid
            ");

        // Copy approved entries
        foreach($entries as $entry) {
            if($changedate && (!$entry->should_roll_forward() || $entry->is_deleted())) {
                continue;
            }
            $approver=$entry->get_approver_userid();
            $approvetime=$entry->get_time_approved();
            if($changedate) {
                $before=$entry->get_date();

                // Get offset in days since feed start (rounded to avoid DST effects
                $postmidnight=strtotime(date('Y-m-d',$before));
                $offsetdays=round(($postmidnight-$oldstartmidnight) / (3600*24));
                // And time on that day
                $offsetseconds=$before-$postmidnight-$oldstartseconds;

                // Now add the same values to the new feed start
                $entry->set_date(
                    strtotime(date('Y-m-d',$newstartmidnight).' +'.$offsetdays.' days')+
                    $newstartseconds+
                    $offsetseconds);
            }
            try {
                $entry->save_new(true,$newid,false);
                $entry->approve($approver,$approvetime,false);
            } catch(Exception $e) {
                $tw->rollback();
                throw $e;
            }
        }

        $this->id=$newid;

        $tw->commit();
        return $newid;
    }

    /**
     * Sends email to the people who approve messages, telling them
     * there are new changes to approve, unless:
     * - Current user (who posted message) also has approve permission
     * - Such an email has already been sent
     * - Email is not sent to current user
     */
    public function notify_new_version() {
        global $CFG,$USER;
        // Check there are some approvers
        $roles=$this->get_feed_users();
        if(!isset($roles[self::ROLE_APPROVER]) || count($roles[self::ROLE_APPROVER]==0)) {
            return; // No approvers!
        }
        // If current user can approve then don't send mail
        $nfcontext = get_context_instance(CONTEXT_BLOCK, $this->get_blockinstance());
        if(has_capability('block/newsfeed:approve', $nfcontext)) {
            return;
        }

        $id=$this->get_id();
        $rs=db_do("SELECT newsfeedid FROM prefix_newsfeed_approverequests WHERE newsfeedid=$id");
        if(!$rs->EOF) {
            // Already sent; do nothing
            return;
        }
        $now=time();
        db_do("INSERT INTO prefix_newsfeed_approverequests(newsfeedid,sendtime) VALUES($id,$now)");

        // Send actual emails
        $subject=get_string('approvalrequestsubject','block_newsfeed',$this->get_full_name());
        $a=new stdClass;
        $a->feedname=$this->get_full_name();
        $recipients='';
        foreach($roles[self::ROLE_APPROVER] as $approver) {
            $approverid=$approver->get_user_id();
            if($USER->id==$approverid) {
                continue;
            }
            $recipients.=$approver->get_display_version()."\n";
        }
        $a->another=count($roles[self::ROLE_APPROVER])==1 ? '' : get_string('approvalrequestoranother','block_newsfeed');
        if($a->another) {
            $a->recipients=get_string('approvalrequestrecipients','block_newsfeed')."\n".$recipients;
        } else {
            $a->recipients='';
        }
        $a->otherconfirm=$a->another ? get_string('approvalrequestother','block_newsfeed') : '';
        $a->url=$CFG->wwwroot."/blocks/newsfeed/ui/viewfeed.php?newsfeedid=$id&aim=approve";
        $message=get_string('approvalrequestmessage','block_newsfeed',$a);
        $this->send_approver_emails($subject,$message);
    }

    private function send_approver_emails($subject,$message) {
        global $USER;

        // Send emails to each approver except current
        $roles=$this->get_feed_users();
        if(!isset($roles[self::ROLE_APPROVER])) {
            return; // No approvers!
        }

        $emailfailed='';
        foreach($roles[self::ROLE_APPROVER] as $approver) {
            $approverid=$approver->get_user_id();
            if($USER->id==$approverid) {
                // Don't mail this user
                continue;
            }
            if(!$target=get_record('user','id',$approverid)) {
                throw new Exception("Failed to email approvers: unknown user ",
                    EXN_NEWSFEED_EMAILBADUSER);
            }
            if(!defined('UNITTEST')) { // Don't send emails while testing
                if(!email_to_user($target,
                    get_string('emailsendername','block_newsfeed'),$subject,$message)) {
                    $emailfailed.=$approver->get_display_version()."\n";
                } else {
                    // Store information about the email sent
                    add_to_log(SITEID,'newsfeed','update','ui/viewfeed.php?newsfeedid='.$this->get_id(),
                        "emailsent {$target->id} ({$target->username}): $subject");
                }
            }
        }

        if($emailfailed) {
            throw new Exception("Failed to email some or all approvers:\n$emailfailed",
                EXN_NEWSFEED_EMAILFAIL);
        }
    }

    /**
     * Sends email to the people who approve messages, telling them
     * that all changes have been approved, unless:
     * - Email is not outstanding
     * - Excludes current user
     */
    public function notify_approve() {
        $id=$this->get_id();

        // Check there are no other messages left to approve
        $entries=$this->get_own_entries(false,true);
        foreach($entries as $entry) {
            if(!$entry->is_approved()) {
                // Not everything's approved yet, so don't send emails
                return;
            }
        }

        $rs=db_do("SELECT newsfeedid FROM prefix_newsfeed_approverequests WHERE newsfeedid=$id");
        if($rs->EOF) {
            // No email was sent in the first place, so don't bother
            return;
        }
        db_do("DELETE FROM prefix_newsfeed_approverequests WHERE newsfeedid=$id");

        // Send actual emails
        global $CFG;
        $subject=get_string('informapprovedsubject','block_newsfeed',$this->get_full_name());
        $a=new stdClass;
        $a->feedname=$this->get_full_name;
        $a->url=$CFG->wwwroot."/blocks/newsfeed/ui/viewfeed.php?newsfeedid=$id&aim=view";
        $message=get_string('informapprovedmessage','block_newsfeed',$a);
        $this->send_approver_emails($subject,$message);
    }
}
?>