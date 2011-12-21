<?php

    /**
     * Data class to hold details of a link between
     * a Moodle user and a txttools user account.
     * This link determines whether a user can use txttools,
     * and which account they will use.
     *
     * @package datawrappers
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2006 Onwards, Cy-nap Ltd. All rights reserved.
     * @version 2006101312
     * @since 2006082912
     */

    class moodletxt_user_account_link {

        /**
         * Holds the id of the DB record if known.
         * NOTE: This is not an account ID.
         */

        var $id;

        /**
         * Holds the moodle account ID of the user
         */

        var $moodleuser;

        /**
         * Holds the ID of the course that this link relates to
         */

        var $courseid;

        /**
         * Holds the ID of the txttools account to use.
         */

        var $txttoolsaccount;

        /**
         * Holds whether or not the link is active.
         */

        var $active;

        /**
         * Class constructor - takes a set of valid parameters
         * and initialises the object
         *
         * @version 2006101312
         * @since 2006082912
         * @param id The record ID of the link if known (optional).
         * @param moodleuser The Moodle user's account ID.
         * @param courseid The course that this link is valid on
         * @param txttoolsaccount The ID of the txttools account used.
         * @param active Whether or not the account link is active.
         */

        function moodletxt_user_account_link($moodleuser, $courseid, $txttoolsaccount, $active, $id = '') {

            if ($id != '') {

                $this->id = $id;

            }

            $this->moodleuser = $moodleuser;
            $this->courseid = $courseid;
            $this->txttoolsaccount = $txttoolsaccount;
            $this->active = $active;

        }

    }

?>

