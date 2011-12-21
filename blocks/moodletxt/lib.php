<?php

    /**
     * Library of functions for use with MoodleTxt
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2006 Onwards, txttools Ltd unless otherwise specified. All rights reserved.
     * @version 2011030101
     * @since 2006091412
     */

    /*
     * Yes, that is the DDL library
     * I'm only using it to check that the config table exists
     * before grabbing some data from it, as this library is used during installation
     * The DDL library should not be used for anything else!
     */
    require_once($CFG->libdir . '/ddllib.php');

    $table = new XMLDBTable('block_mtxt_config');
    $NATIONAL_PREFIX = table_exists($table) ? moodletxt_get_setting('National_Prefix') : 'mdl_';
    $PHONE_SOURCE = table_exists($table) ? moodletxt_get_setting('Phone_Number_Source') : 'mdl_';

    /**
     * Function to escape strings to be re-inserted into the database.
     * Created to manage different DB's escape methods
     *
     * @param $instring string The string to be escaped
     * @version 2010123001
     * @since 2007101512
     */
    function moodletxt_escape_string($instring) {

        global $CFG;

        switch($CFG->dbtype) {

            case "mysql":
            case "mysqli":
            case "postgres7":
            case "oci8po":

                $instring = addslashes($instring);
                break;

            case 'mssql':
            case 'mssql_n':
            case "odbc_mssql":

                $instring = str_replace("'", "''", $instring);
                break;

        }

        return $instring;

    }

    /**
     * Function to determine whether a string represents an integer.
     * Based on a suggestion in the PHP manual by "mark at codedesigner dot nl" 2008-06-26.
     *
     * @param mixed $invar The variableto be checked.
     * @return bool Whether or not the string represents an integer value.
     * @version 2010062112
     * @since 2006091412
     */
    function moodletxt_is_intval($invar) {
        
        return is_int($invar) || preg_match('@^[-]?[0-9]+$@',$invar) === 1;

    }

    /**
     * Function to recursively search a multi-dimensional array
     * for a given value. Does not use in_array, to ensure all
     * searches are done on a strict type basis.
     *
     * @param mixed $needle The value to search for
     * @param array $haystack The array to search in
     * @return bool Whether or not the needle was found
     * @version 200710512
     * @since 2006091412
     */
    function moodletxt_in_array_deep($needle, &$haystack) {

        // Loop through values in array
        foreach ($haystack as $item) {

           // Check value against needle
            if (! is_array($item)) {

                if ($needle === $item) {

                    return true;

                }

            } else {

                /* If the item is an array, check its key,
                as the key could be the the name of an element
                that contains sub elements. */
                if (moodletxt_in_array_deep($needle, $item)) {

                    return true;

                }


            }

        }

        // If we have still not found the needle, admit defeat
        return false;

    }

    /**
     * Function to get the selected errors from an array of all errors
     * and display them in an unordered list.
     *
     * @param $needles array(string) An array of the selected errors to display
     * @param $haystack array(string) The full error array to search
     * @param $notice bool New param - allows the function to be used to display notices also
     * @return string XHTML <ul> containing the errors selected
     * @version 2006102412
     * @since 2006092612
     */
    function moodletxt_vomit_errors($needles, $haystack, $notice=false) {

        $listString = '';

        foreach ($needles as $needle) {

            if ((array_key_exists($needle, $haystack))  && ($haystack[$needle] != '')) {

                $listString .= '<li>' . $haystack[$needle] . '</li>
';

            }

        }

        if ($listString != '') {

            if ($notice) {

                $color = 'green';

            } else {

                $color = 'red';

            }

            $listString = '<ul style="color:' . $color . ';">
' . $listString . '
</ul>';
        }

        return $listString;

    }

    /**
     * Function to clean whitespace, dashes and brackets from phone numbers.
     *
     * @param $phoneno string The phone number to be cleaned.
     * @return string The cleaned phone number
     * @version 2006092812
     * @since 2006092812
     */
    function moodletxt_clean_mobile_number($phoneno) {

        return str_replace(array('(', ')', ' ', '-'), '', $phoneno);

    }

    /**
     * Function to take a user object and decide which of the two
     * phone numbers stored to use.  Simplified since v2.2 to get
     * preferred phone number irrespective, and format it internationally.
     *
     * @param $user object The user object to select numbers from
     * @return string The phone number selected
     * @version 2008012412
     * @since 2006092812
     */
    function moodletxt_get_mobile_number(&$user) {

        global $NATIONAL_PREFIX, $PHONE_SOURCE;

        if ($PHONE_SOURCE == 'phone1') {

            $userphone = moodletxt_clean_mobile_number($user->phone1);

        } else {

            $userphone = moodletxt_clean_mobile_number($user->phone2);

        }

        if ($userphone == '') {

            return '';

        }

        if (substr($userphone, 0, 1) == $NATIONAL_PREFIX) {

            $prefix = moodletxt_get_setting('Default_International_Prefix');

            $userphone = $prefix . substr($userphone, 1);

        }

        return $userphone;

    }

    /**
     * Function to retrieve objects of a specific type from a larger set
     *
     * @param $objectArray array(object) The array to pull objects from
     * @return array(object) An array of the objects collected
     * @version 2009062612
     * @since 2007050412
     */
    function moodletxt_get_objects_by_type($objectArray, $objecttype) {

        $retArray = array();

        foreach ($objectArray as $obj) {

            if (is_array($obj)) {

                $retArray = array_merge($retArray, moodletxt_get_objects_by_type($obj, $objecttype));

            } else {

                if (get_class($obj) == $objecttype) {

                    array_push($retArray, $obj);

                }

            }

        }

        return $retArray;

    }

    
    function moodletxt_get_xml_errors($objectArray) {

        $retArray = array();

        foreach ($objectArray as $obj) {

            if (is_array($obj)) {

                $retArray = array_merge($retArray, moodletxt_get_xml_errors($obj));

            } else {

                if (get_class($obj) == 'moodletxt_connector_error') {

                    if ($obj->getErrorCode() != null) {

                        $retArray[$obj->getErrorCode()] = $obj->getErrorString();

                    } else {

                        array_push($retArray, $obj->getErrorString());

                    }

                }

            }

        }

        return $retArray;

    }

    /**
     * Function to write more than 1 data object to the
     * database at once.  Takes an array of data objects
     * and writes each one in turn.  Can also be flagged to
     * return an array of inserted IDs
     *
     * @param $objectarray The array of objects to write
     * @return mixed Success, or the array of inserted IDs if requested
     * @version 2010062212
     * @since 2006092912
     */
    function moodletxt_write_objects($objectarray, $returnids = false) {

        $idarray = array();

        $latestInboundMessageTime = moodletxt_get_setting('Inbound_Last_Update');

        for ($x = 0; $x < count($objectarray); $x++) {

            $objectclass = get_class($objectarray[$x]);

            $writetable = '';

            switch ($objectclass) {

                // Special case - inbound messages may need to be written more than once
                 case 'moodletxt_inbound_message':

                    $inboundmessage = $objectarray[$x];

                    // Check that destination fodlers exist for this message
                    if ($inboundmessage->get_folder_count() == 0) {
                        continue;
                    } else {

                        $sourcecontactid = 0;
                        $sourceuserid = 0;
                        $sourcename = '';

                        // I know, I know, I should be using child objects like I do with recipients
                        // but this whole thing is getting re-done soon!
                        $sourceUser = get_record('user', moodletxt_get_setting('Phone_Number_Source'), moodletxt_escape_string($inboundmessage->get_source()));

                        // Check phone number against Moodle users
                        if (is_object($sourceUser)) {

                            $sourceuserid = $sourceUser->id;
                            $inboundmessage->set_source_name($sourceUser->lastname . ', ' . $sourceUser->firstname . ' (' . $sourceUser->username . ')');

                        } else {

                            // Check number against address book contacts
                            $sourceContact = get_record('block_mtxt_ab_entry', 'phoneno', moodletxt_escape_string($inboundmessage->get_source()));

                            if (is_object($sourceContact)) {

                                $sourcecontactid = $sourceContact->id;

                                if ($sourceContact->lastname == '' && $sourceContact->firstname == '')
                                    $inboundmessage->set_source_name($sourceContact->company);
                                else
                                    $inboundmessage->set_source_name($sourceContact->lastname . ', ' . $sourceContact->firstname);

                            }


                        }

                        // Get writeable message objects
                        $writeableobjects = $inboundmessage->get_writeable_objects();

                        // Write objects to the DB
                        foreach($writeableobjects as $writeobj) {

                            unset($writeobj->id);

                            if ($writeobj->timereceived > $latestInboundMessageTime)
                                $latestInboundMessageTime = $writeobj->timereceived;

                            $insertid = insert_record('block_mtxt_in_mess', $writeobj, true);

                            // Create source links if required
                            if ($sourcecontactid > 0) {

                                $linkObj = new stdClass;
                                $linkObj->contact = moodletxt_escape_string($sourcecontactid);
                                $linkObj->receivedmessage = moodletxt_escape_string($insertid);

                                insert_record('block_mtxt_in_ab', $linkObj);

                            }

                            if ($sourceuserid > 0) {

                                $linkObj = new stdClass;
                                $linkObj->userid = moodletxt_escape_string($sourceuserid);
                                $linkObj->receivedmessage = moodletxt_escape_string($insertid);

                                insert_record('block_mtxt_in_user', $linkObj);

                            }

                            // Store ID of inserted message
                            if ($returnids)
                                array_push($idarray, array($writetable, $insertid));

                        }


                    }


                    continue;

                case 'moodletxt_sentmessage':
                
                    // Write sent message record.
                    // If record inserted, write link record too
                    $recipient = $objectarray[$x]->getRecipientObject();

                    $insObj = new stdClass;
                    $insObj->messageid = $objectarray[$x]->getMessageId();
                    $insObj->ticketnumber = $objectarray[$x]->getTicketNumber();
                    $insObj->destination = $objectarray[$x]->getDestination();
                    $insObj->sendname = ($recipient != null) ? addslashes($recipient->getFullNameForDisplay()) : '';

                    $insertid = insert_record('block_mtxt_sent', $insObj, true);
                    
                    if ($returnids)
                        array_push($idarray, array($writetable, $insertid));
                    
                    $recipientClass = get_class($recipient);

                    switch($recipientClass) {

                        case 'moodletxt_UserRecipient':

                            $linkObj = new stdClass;
                            $linkObj->userid = $recipient->getId();
                            $linkObj->sentmessage = $insertid;

                            insert_record('block_mtxt_sent_user', $linkObj, false);

                            break;

                        case 'moodletxt_ABRecipient':

                            $linkObj = new stdClass;
                            $linkObj->contact = $recipient->getId();
                            $linkObj->sentmessage = $insertid;

                            insert_record('block_mtxt_sent_ab', $linkObj, false);

                            break;

                    }
                    
                    continue;

                case 'moodletxt_message':

                    $writetable = 'block_mtxt_outbox';

                    break;

                case 'moodletxt_message_status':

                    // Check that this status level has not already been received
                    $checkexistence = count_records('block_mtxt_status', 'ticketnumber',
                        $objectarray[$x]->get_ticket(), 'status', $objectarray[$x]->get_status());

                    if ($checkexistence)
                        continue;

                    $writetable = 'block_mtxt_status';

                    break;

                case 'moodletxt_rss_item':

                    $writetable = 'block_mtxt_rss';

                    break;


                default:

                    continue;

            }

            $insertid = insert_record($writetable, $objectarray[$x], $returnids);

            if ($returnids)
                array_push($idarray, array($writetable, $insertid));

        }

        moodletxt_set_setting('Inbound_Last_Update', $latestInboundMessageTime);

        if ($returnids) {

            return $idarray;

        } else {

            return true;

        }

    }

    /**
     * Updates the account info on a given account
     * with data synchronised from txttools
     * @param moodletxt_ParseAccountDetails $accountDetails Details of account to update
     * @return boolean Whether update was successful
     * @version 2010021101
     * @since 2010062812
     */
    function moodletxt_update_account_info($accountDetails, $returnAccount = true) {

        if (!is_object($accountDetails) || get_class($accountDetails) != 'moodletxt_ParseAccountDetails')
            return false;

        // Go get account record
        $account = get_record('block_mtxt_accounts', 'id', addslashes($accountDetails->get_id()));

        if (! is_object($account))
            return false;

        $account->creditsused = addslashes($accountDetails->get_creditsused());
        $account->creditsremaining = addslashes($accountDetails->get_creditsremaining());
        $account->accounttype = addslashes($accountDetails->get_accounttype());
        $account->lastupdate = addslashes($accountDetails->get_updatetime());

        // Re-slash DB-sourced data for update
        $account->username = addslashes($account->username);
        $account->password = addslashes($account->password);
        $account->description = addslashes($account->description);

        $updateSuccess = update_record('block_mtxt_accounts', $account);
        return ($returnAccount) ? $account : $updateSuccess;
    }

    /**
     * Function to trim the length of a given string for
     * display in list and combo boxes
     *
     * @param $str The string to trim
     * @param $length The number of characters to which this string should be cut
     * @return string The new restricted string
     * @version 2007010512
     * @since 2006091412
     */

    function moodletxt_restrict_length($str, $length) {

        if (strlen($str) > $length) {

             return substr($str, 0, $length - 3) . '...';

        } else {

            return $str;

        }

    }

    /**
     * Function to more securely MD5 a password, or indeed, a sucker
     *
     * @param $sucker The string to be MD5'd
     * @return string The MD5'd string
     * @version 2007010512
     * @since 2006121312
     */

    function moodletxt_md5_this_sucker($sucker) {

        $suckersalt = "";

        $arrayofsuckerpieces = str_split($sucker);

        foreach ($arrayofsuckerpieces as $minisucker) {

            $suckersalt .= md5($minisucker);

        }

        $tothelastsucker = md5($suckersalt);

        return $tothelastsucker;

    }

    /**
     * Function to get the value of a given
     * MoodleTxt system setting.
     *
     * @param $settingname The name of the setting to retrieve a value for
     * @return mixed The current value of the setting
     * @version 2010122901
     * @since 2006121312
     */

    function moodletxt_get_setting($settingname) {

        // Get setting from DB
        $settingobject = get_record('block_mtxt_config', 'setting', moodletxt_escape_string($settingname));

        if (is_object($settingobject)) {

            return trim($settingobject->value);

        } else {

            return false;

        }

    }

    /**
     * Function to set the value of a given
     * MoodleTxt system setting.
     *
     * NOTE: As of version 2009102912 values must be escaped BEFORE
     * being put into this function. Moodle automatically escapes form
     * input, so this actually makes a lot more sense.
     *
     * @param $settingname The name of the setting to set a value for
     * @param $settingvalue The new value of the setting
     * @return bool Success
     * @version 2009102912
     * @since 2006121312
     */

    function moodletxt_set_setting($settingname, $settingvalue) {

        // Get setting from DB
        $settingobject = get_record('block_mtxt_config', 'setting', moodletxt_escape_string($settingname));

        if (is_object($settingobject)) {

            if ($settingobject->value != $settingvalue) {

                $settingobject->value = $settingvalue;

                return update_record('block_mtxt_config', $settingobject);

            } else {

                return true;

            }

        } else {

            return false;

        }

    }

    /**
     * Function to get the value of a given
     * MoodleTxt system setting.
     *
     * @param $settingname The name of the setting to retrieve a value for
     * @param $userid The ID of the user this setting belongs to
     * @return mixed The current value of the setting
     * @version 2010032212
     * @since 2010032212
     */

    function moodletxt_get_user_setting($settingname, $userid = null) {

        if ($userid == null)
            $userid = $USER->id;

        // Get setting from DB
        $settingobject = get_record('block_mtxt_uconfig', 
                'userid', moodletxt_escape_string($userid),
                'setting', moodletxt_escape_string($settingname));

        if (is_object($settingobject))
            return $settingobject->value;
        else
            return false;

    }

    /**
     * Function to set the value of a given
     * MoodleTxt system setting.
     *
     * @param $settingname The name of the setting to set a value for
     * @param $settingvalue The new value of the setting
     * @param $userid The ID of the user this setting belongs to
     * @return bool Success
     * @version 2010032212
     * @since 2010032212
     */

    function moodletxt_set_user_setting($settingname, $settingvalue, $userid = null) {

        if ($userid == null)
            $userid = $USER->id;

        // Get setting from DB
        $settingobject = get_record('block_mtxt_uconfig',
                'userid', moodletxt_escape_string($userid),
                'setting', moodletxt_escape_string($settingname));

        if (is_object($settingobject)) {

            if ($settingobject->value != $settingvalue) {

                $settingobject->value = $settingvalue;
                return update_record('block_mtxt_uconfig', $settingobject);

            } else {

                return true;

            }

        } else {

            return false;

        }

    }

    /**
     * Function to create an inbox for a given user
     *
     * @param $userid The ID of the user needing an inbox
     * @return int The integer ID of the new inbox record
     * @version 2007051112
     * @since 2007051112
     */

     function moodletxt_create_inbox($userid) {

        $inboxobj = new stdClass;
        $inboxobj->userid = moodletxt_escape_string($userid);

        $inboxid = insert_record('block_mtxt_inbox', $inboxobj);

        // Create main folders
        $inboxfolder = new stdClass;
        $inboxfolder->inbox = moodletxt_escape_string($inboxid);
        $inboxfolder->name = 'Inbox';
        $inboxfolder->candelete = 0;

        $trashfolder = new stdClass;
        $trashfolder->inbox = moodletxt_escape_string($inboxid);
        $trashfolder->name = 'Trash';
        $trashfolder->candelete = 0;

        insert_record('block_mtxt_in_folders', $inboxfolder);
        insert_record('block_mtxt_in_folders', $trashfolder);

        return $inboxid;

     }

     /**
      * Function replaces the deprecated get_course_users function
      * that wasn't replaced in the Moodle API prior to 1.9.1
      *
      * Note: the only table it's really safe to set fields from is "u",
      * but that's fine for our purposes. SQL provided by a handy man on
      * the Moodle.org forums.
      *
      * @param $courseID int The course to search on. Duh.
      * @param $sort string The field to sort by, along with ASC or DESC
      * @param $exceptions array(int) IDs of records to leave out
      * @param $fields string A list of fields to get from the DB
      * @version 2010061712
      * @since 2007080112
      */

     function moodletxt_get_course_users($courseid, $sort = 'u.lastname ASC, u.firstname ASC',
            $exceptions = '', $fields = 'u.*') {

        $exceptionstr = '';
        $sortstr = '';

        if (is_array($exceptions) && count($exceptions) > 0) {

            $exceptionstr .= ' AND u.id NOT IN (';

            foreach($exceptions as $exception)
                $exceptionstr .= (is_int($exception)) ? $exception . ', ' : '';

            $exceptionstr = substr($exceptionstr, 0, strlen($exeptionstr) - 1) . ')';

        }

        if (!empty($sort))
            $sortstr = ' ORDER by ' . $sort;

        $contextid = get_context_instance(CONTEXT_COURSE, $courseid);

        $sql = moodletxt_get_sql('libgetcourseusers');
        $sql = sprintf($sql, $fields, $contextid->id);
        $sql = $sql . $exceptionstr . $sortstr;

        $courseUsers = get_records_sql($sql);

        return $courseUsers;

    }

    /**
     * Function to update today's outbound stats for the given link
     *
     * @param $txttoolsaccount The account this update relates to
     * @param $userid The user that is sending messages
     * @param $numbersent The number of messages sent
     * @version 2011030301
     * @since 2006091412
     */
    function moodletxt_update_outbound_stats($txttoolsaccount, $userid, $numbersent) {

        global $CFG;

        // Oracle 10.x doesn't like times on the end of its datetime fields. Go figure.
        $statsDate = ($CFG->dbtype == 'oci8po') ? date('Y-m-d') : date('Y-m-d') . ' 00:00:00';

        // Update daily user stats
        $getTodaysStats = get_record('block_mtxt_stats', 'txttoolsaccount', $txttoolsaccount, 'userid', $userid, 'date_entered', $statsDate);

        if (is_object($getTodaysStats)) {

            $getTodaysStats->numbersent += $numbersent;

            update_record('block_mtxt_stats', $getTodaysStats);

        } else {

            $todaysStats = new stdClass;
            $todaysStats->txttoolsaccount = addslashes($txttoolsaccount);
            $todaysStats->userid = addslashes($userid);
            $todaysStats->date_entered = $statsDate;
            $todaysStats->numbersent = $numbersent;

            insert_record('block_mtxt_stats', $todaysStats);

        }

    }

    /**
     * The very handy Carlos Reche submitted to php.net this emulated version
     * of str_split for servers running PHP < 5.
     *
     * @author carlosreche
     * @version 2007090412
     * @since 2007090412
     */
    if (!function_exists("str_split")) {

        function str_split($string, $length = 1) {

            if ($length <= 0) {

                trigger_error(__FUNCTION__."(): The the length of each segment must be greater then zero:", E_USER_WARNING);

                return false;

            }

            $splitted  = array();
            $str_length = strlen($string);
            $i = 0;
            if ($length == 1) {

                while ($str_length--) {

                    $splitted[$i] = $string[$i++];

                }

            } else {

                $j = $i;

                while ($str_length > 0) {

                    $splitted[$j++] = substr($string, $i, $length);
                    $str_length -= $length;
                    $i += $length;

                }

            }

            return $splitted;

        }

    }

    /**
     * Function gets sent messages for the "Sent Messages" page
     * and makes appropriate re-calculations for newly added/more fiddly DBs.
     * Easier to do it here than in the page itself.
     *
     * @version 2010123001
     * @since 2007091712
     */
    function moodletxt_get_sent_messages($selectcriteria, $sortarray, $pagesize, $offset) {

        global $CFG;

        $finalsort = '';

        // Get SQL from library
        $sql = moodletxt_get_sql('sentselectmessages');

        switch ($CFG->dbtype) {

            case "mysql":
            case "mysqli":
            case "postgres7":
            case "oci8po":

                foreach ($sortarray as $deargodimtired) {

                    //zzzzzzzzzzzzzzzzzzz.........
                    if ($finalsort != '') $finalsort .= ', ';
                    $finalsort .= $deargodimtired;

                }

                $sql = sprintf($sql, $selectcriteria, $finalsort, $pagesize, $offset);

                break;

            case "mssql":
            case "mssql_n":
            case "odbc_mssql":

                // Calculate new relevant values
                $endrecord = $offset + $pagesize; // End record, not limit

                // Create modded sorting parameters for multi-level query
                $sortelements = explode(" ", $sortarray[0]);
                $alternatesortdir = ($sortelements[1] == 'ASC') ? 'DESC' : 'ASC';

                $outersortfieldarr = explode('.', $sortelements[0]);
                $outersortfield = $outersortfieldarr[(count($outersortfieldarr) - 1)];

                $sort1 = $sortarray[0];
                $sort2 = $outersortfield . ' ' . $alternatesortdir;
                $sort3 = $outersortfield . ' ' . $sortelements[1];

                $sql = sprintf($sql, moodletxt_escape_string($pagesize), moodletxt_escape_string($endrecord), moodletxt_escape_string($selectcriteria), moodletxt_escape_string($sort1), moodletxt_escape_string($sort2), moodletxt_escape_string($sort3));

                break;

        }

        // Get message details and return them to the calling script
        $sentmessages = get_records_sql($sql);

        return $sentmessages;

    }

    /**
     * Function gets details of text messages sent for the "View Message" page
     * and makes appropriate re-calculations/changes to the query for newly added/more fiddly DBs.
     * Libraried off to make future updates easier - should really do this with the rest, too
     *
     * @version 2010123001
     * @since 2007092012
     */
     function moodletxt_get_message_recipients($messageid, $sortarray, $pagesize, $offset) {

        global $CFG;

        // Get SQL from library
        $sql = moodletxt_get_sql('viewgetrecipients');

        // Check for any DB compatability work that needs doing
        switch ($CFG->dbtype) {

            case "mysql":
            case "mysqli":
            case "postgres7":
            case "oci8po":

                $finalsort = '';

                foreach ($sortarray as $deargodimtired) {

                    //zzzzzzzzzzzzzzzzzzz.........
                    if ($finalsort != '') $finalsort .= ', ';
                    $finalsort .= $deargodimtired;

                }

                $sql = sprintf($sql, moodletxt_escape_string($messageid), moodletxt_escape_string($finalsort), moodletxt_escape_string($pagesize), moodletxt_escape_string($offset));

                break;

            case "mssql":
            case "mssql_n":
            case "odbc_mssql":

                // Calculate new relevant values
                $endrecord = $offset + $pagesize; // End record, not limit

                $sortelements = explode(" ", $sortarray[0]);
                $alternatesortdir = ($sortelements[1] == 'ASC') ? 'DESC' : 'ASC';

                // Create modded sorting parameters for multi-level query
                $outersortfieldarr = explode('.', $sortelements[0]);
                $outersortfield = $outersortfieldarr[(count($outersortfieldarr) - 1)];

                $sort1 = $sortarray[0];
                $sort2 = $outersortfield . ' ' . $alternatesortdir;
                $sort3 = $outersortfield . ' ' . $sortelements[1];

                $sql = sprintf($sql, moodletxt_escape_string($pagesize), moodletxt_escape_string($endrecord), moodletxt_escape_string($messageid), moodletxt_escape_string($sort1), moodletxt_escape_string($sort2), moodletxt_escape_string($sort3));

                break;

        }

        // Get message recipient details and return them to the calling script
        $recipients = get_records_sql($sql);

        return $recipients;

    }
    
    /**
     * Function to merge multiple ADODB record sets,
     * overwriting any duplicate record IDs.
     * This function is required because array_merge()
     * will append duplicate numerical keys in arrays,
     * rather than overwrite them, and as the keys in an ADODB
     * record set are the record IDs, this is a bad thing.
     * 
     * @return array
     * @version 2008090912
     * @since 2008082512
     */
    
    function moodletxt_merge_recordsets() {
        
        $returnArray = array();
        
        for ($x = 0; $x < func_num_args(); $x++) {
            
            $currentset = func_get_arg($x);
            
            if (is_array($currentset) && count($currentset) > 0) {

                // Merge arrays
                // (array_merge() leaves numerical indices)
                foreach($currentset as $record) {

                    if (! array_key_exists($record->id, $returnArray)) {

                        $returnArray[$record->id] = $record;

                    }

                }

            }   
                
        }
        
        return $returnArray;            
        
    }

    /**
     * Creates JSON object notation from a given array
     * Needed because PHP < 5.2 does not support JSON natively
     * @param array $arr
     * @return string
     * @author Binny A V
     * @link http://www.bin-co.com/php/scripts/array2json/
     */
    function array2json($arr) {
    
        if (! is_array($arr)) $arr = array();
        if(function_exists('json_encode')) return json_encode($arr); // PHP >= 5.2 already has this functionality.

        $parts = array();
        $is_list = false;

        //Find out if the given array is a numerical array
        $keys = array_keys($arr);
        $max_length = count($arr)-1;

        if(($keys[0] == 0) and ($keys[$max_length] == $max_length)) {//See if the first key is 0 and last key is length - 1

            $is_list = true;

            for($i=0; $i<count($keys); $i++) { //See if each key correspondes to its position
                if($i != $keys[$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }

        }

        foreach($arr as $key=>$value) {

            if(is_array($value)) { //Custom handling for arrays

                if($is_list) $parts[] = array2json($value); /* :RECURSION: */
                else $parts[] = '"' . $key . '":' . array2json($value); /* :RECURSION: */

            } else {

                $str = '';
                if(!$is_list) $str = '"' . $key . '":';

                //Custom handling for multiple data types
                if(is_numeric($value)) $str .= $value; //Numbers
                elseif($value === false) $str .= 'false'; //The booleans
                elseif($value === true) $str .= 'true';
                else $str .= '"' . addslashes($value) . '"'; //All other things
                // :TODO: Is there any more datatype we should be in the lookout for? (Object?)

                $parts[] = $str;
            }
        }

        $json = implode(',',$parts);

        if($is_list) return '[' . $json . ']';//Return numerical JSON
        return '{' . $json . '}';//Return associative JSON

    }

    /**
     * Emulates PHP 5.2.x JSON functionality in lower versions
     * using the PEAR library.  Function emulation shamelessly
     * stolen from Jamie Tibbetts
     * @link http://www.epigroove.com/posts/97/how_to_use_json_in_php_4_or_php_51x
     */
    if (!function_exists('json_decode')) {
        function json_decode($content, $assoc=false) {
            require_once($CFG->dirroot . '/blocks/moodletxt/lib/json/JSON.php');
            if ($assoc) {
                $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
            } else {
                $json = new Services_JSON;
            }
            return $json->decode($content);
        }
    }

    /**
     * Emulates PHP 5.2.x JSON functionality in lower versions
     * using the PEAR library.  Function emulation shamelessly
     * stolen from Jamie Tibbetts
     * @link http://www.epigroove.com/posts/97/how_to_use_json_in_php_4_or_php_51x
     */
    if (!function_exists('json_encode')) {
        function json_encode($content) {
            require_once($CFG->dirroot . '/blocks/moodletxt/lib/json/JSON.php');
            $json = new Services_JSON;
            return $json->encode($content);
        }
    }
    

?>