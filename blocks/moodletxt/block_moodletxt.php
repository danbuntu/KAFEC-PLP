<?php

    require_once($CFG->dirroot . '/blocks/moodletxt/db/MoodletxtCronHandler.php');

    /**
     * Main moodletxt block class for display on course pages
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011032901
     * @since 2006081012
     */
    class block_moodletxt extends block_base {

        /**
         * Class initialiser to set up the display block
         *
         * @version 2011032901
         * @since 2006081012
         */
        function init() {

            $this->cron = 300; // 5 minutes
            $this->title = get_string('blocktitle', 'block_moodletxt');
            $this->blocktitle = get_string('blocktitle', 'block_moodletxt');
            $this->version = 2011032901;
        }

        /**
         * Fetches content for the block when displayed
         * @global Object $CFG Moodle config object
         * @global Object $USER Moodle user object
         * @return string Block content
         * @version 2011030701
         * @since 2006081012
         */
        function get_content() {

            global $CFG, $USER;


            // If content has already been created, return that
            if ($this->content !== NULL)
                return $this->content;

            // Initialise content class
            $this->content = new stdClass;

            // Check that instance object is available
            if (empty($this->instance))
                return $this->content;

            // Check that specialization has been done
            if (empty($this->course))
                $this->specialization();

            $userisadmin = false;
            $usercanreceive = false;

            // Check for the existence of txttools accounts
            // If none are available, most of the links should not be displayed!
            $numberOfAccounts = count_records('block_mtxt_accounts');


            
            // Capability checks...

            // Create site context
            $blockcontext = get_context_instance(CONTEXT_BLOCK, $this->instance->id);

            // Check for admin
            $userisadmin = (has_capability('block/moodletxt:adminsettings', $blockcontext, $USER->id) ||
                            has_capability('block/moodletxt:adminusers', $blockcontext, $USER->id));

            // Check that user has send access
            $checksend = has_capability('block/moodletxt:sendmessages', $blockcontext, $USER->id);

            // Check that user can create address books
            $checkCreateAddressbooks = has_capability('block/moodletxt:addressbooks', $blockcontext, $USER->id);

            // Check that user can view stats page
            $canViewStats = has_capability('block/moodletxt:viewstats', $blockcontext, $USER->id);



            // Check inbound capability and inbox contents...
            $unreadfrag = '';

            if (has_capability('block/moodletxt:receivemessages', $blockcontext, $USER->id)) {
                $usercanreceive = true;

                // Check to see if user has an inbox
                $userinbox = get_record('block_mtxt_inbox', 'userid', $USER->id);

                if (is_object($userinbox)) {

                    // If user has an inbox, get the number of unread messages in it
                    $inboxfolder = get_record('block_mtxt_in_folders', 'inbox', $userinbox->id, 'name',
                            'Inbox', 'candelete', 0);

                    $unreadmessages = count_records('block_mtxt_in_mess', 'folderid', $inboxfolder->id,
                            'hasbeenread', 0);

                    if ($unreadmessages > 0)
                        $unreadfrag = ' <b>(' . $unreadmessages . ')</b>';

                }

            }



            // Output content!
            $this->content->title = '';
            $this->content->text = '';
            $this->content->footer = '';

            $this->content->text .= ($numberOfAccounts > 0 && $checksend) ? '<a href="' . $CFG->wwwroot . '/blocks/moodletxt/sendmessage.php">' . get_string('blocklink1', 'block_moodletxt') . '</a><br />' : '';
            $this->content->text .= ($numberOfAccounts > 0 && $checksend) ? '<a href="' . $CFG->wwwroot . '/blocks/moodletxt/sentmessages.php">' . get_string('blocklink2', 'block_moodletxt') . '</a><br />' : '';
            $this->content->text .= ($checksend && $checkCreateAddressbooks) ? '<a href="' . $CFG->wwwroot . '/blocks/moodletxt/addressbooks.php">' . get_string('blocklink3', 'block_moodletxt') . '</a><br />' : '';
            $this->content->text .= ($numberOfAccounts > 0 && $usercanreceive) ? '<a href="' . $CFG->wwwroot . '/blocks/moodletxt/inbox.php">' . get_string('blocklink4', 'block_moodletxt') . '</a>' . $unreadfrag . '<br />' : '';
            $this->content->text .= ($checksend) ? '<a href="' . $CFG->wwwroot . '/blocks/moodletxt/moodletxt.php">' . get_string('blocklink5', 'block_moodletxt') . '</a><br />' : '';
            $this->content->text .= ($numberOfAccounts > 0 && $canViewStats) ? '<a href="' . $CFG->wwwroot . '/blocks/moodletxt/userstats.php?courseid=' . $this->course->id . '">' . get_string('blocklink6', 'block_moodletxt') . '</a><br />' : '';
            $this->content->text .= ($userisadmin) ? '<a href="' . $CFG->wwwroot . '/blocks/moodletxt/admin.php">' . get_string('blocklink7', 'block_moodletxt') . '</a>' : '';


            // If some form of content has been added, set up block
            if ($this->content->text != '') {

                $this->content->footer = get_string('blockfooter', 'block_moodletxt');

                // Check whether config info has been previously defined
                if ((!isset($this->config)) || (empty($this->config))) {

                    // Set up default configuration
                    $this->content->title = 'moodletxt';

                } else {

                    // Use user configuration
                    $this->content->title = $this->config->title;

                }

            }

            return $this->content;

        }

        /**
         * Returns whether instances of the block
         * should have their own config.
         * @return boolean Config allowed?
         * @version 2010070712
         * @since 2006081012
         */
        function instance_allow_config() {

            return true;

        }

        /**
         * Returns whether multiple moodletxt blocks
         * are allowed within the same course.
         * In the case of moodletxt, no.
         * @return boolean Multiple blocks?
         * @version 2010070712
         * @since 2006081012
         */
        function instance_allow_multiple() {

            return false;

        }

        /**
         * Returns whether the block has a global config
         * file or not. moodletxt has one.
         * @return boolean Has global config?
         * @version 2010070712
         * @since 2006081012
         */
        function has_config() {

            return true;

        }

        /**
         * Returns whether or not the block's
         * header should be hidden. In this case, no.
         * @return boolean Hide header?
         * @version 2010070712
         * @since 2006081012
         */
        function hide_header() {

            return false;

        }

        /**
         * Performs any specialist initialisation
         * required by the block. moodletxt requires
         * session variables to be initialised
         * @version 2010070712
         * @since 2006081012
         */
        function specialization() {

            $this->course = get_record('course', 'id', $this->instance->pageid);

            if ($this->course)
                $_SESSION['moodletxt_last_course'] = $this->course->id;

            $_SESSION['moodletxt_last_instance'] = $this->instance->id;
            
        }

        /**
         * Returns a list of formats, and whether the block
         * should be displayed within them. moodletxt should
         * only be displayed within courses.
         * @return array(string => boolean) List of formats
         * @version 2010070712
         * @since 2010070712
         */
        function applicable_formats() {
            return array(
                'course-view' => true,
                'all' => false
            );
        }

        /**
         * Responds to calls from the Moodle cron
         * maintenance script. Used in moodletxt for
         * automatic fetching of data from txttools
         * and any necessary database cleanup.
         * @return boolean Success
         * @version 2010070712
         * @since 2010070712
         */
        function cron() {

            $cronhandler = new MoodletxtCronHandler();
            return $cronhandler->doCron();

        }

        /**
         * Performs a set of PHP-based procedures immediately
         * after installation. This allows the block to take
         * care of tasks that cannot be handled within the
         * install.xml file
         * @return boolean Success
         * @version 2010070712
         * @since 2008081212
         */
        function after_install() {
            
            // Add "encryption key" - ha!
            // Basically used to stop passwords being human-readable
            $ins = new stdClass;
            $ins->setting = 'EK';
            $ins->value = time();

            if (! insert_record('block_mtxt_config', $ins)) {

                return false;

            }

            // Add default recipient name            
            $ins = new stdClass;
            $ins->setting = 'Default_Recipient_Name';
            $ins->value = get_string('configdefaultrecipient', 'block_moodletxt');

            if (! insert_record('block_mtxt_config', $ins)) {

                return false;

            }

            // Set time of last inbox update to current time
            $ins = new stdClass;
            $ins->setting = 'Inbound_Last_Update';
            $ins->value = time();

            if (! insert_record('block_mtxt_config', $ins)) {

                return false;

            }
            

        }

    }

?>