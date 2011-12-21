<?php

    /**
     * Represents a txttools account within the system
     *
     * @package datawrappers
     * @author Greg J Preece, <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2010062512
     * @since 2010062512
     */
    class moodletxt_Account {
    
        /**
         * DB Record ID for the account
         * @var int
         */
        var $id;

        /**
         * Username on txttools
         * @var string
         */
        var $username;

        /**
         * Encrypted password string
         * @var string
         */
        var $password;

        /**
         * Short description of the account
         * @var string
         */
        var $description;

        /**
         * ID of the default inbox for inbound messages
         * @var int
         */
        var $defaultinbox;

        /**
         * Number of credits used on account
         * @var int
         */
        var $creditsused;

        /**
         * Number of credits remaining on account
         * @var int
         */
        var $creditsremaining;

        /**
         * Unix timestamp representing last sync with txttools
         * @var int
         */
        var $lastupdate;

        /**
         * Returns the DB record ID for this account
         * @return int DB record ID
         * @version 2010062512
         * @since 2010062512
         */
        function get_id() {
            return $this->id;
        }

        /**
         * Set the DB record ID for this account
         * @param int $id DB record ID
         * @version 2010062512
         * @since 2010062512
         */
        function set_id($id) {
            if ($id > 0)
                $this->id = $id;
        }

        /**
         * Returns the username of this account
         * @return string Txttools account username
         * @version 2010062512
         * @since 2010062512
         */
        function get_username() {
            return $this->username;
        }

        /**
         * Set the txttools username for this account
         * @param string $username Txttools account username
         * @version 2010062512
         * @since 2010062512
         */
        function set_username($username) {
            $this->username = $username;
        }

        /**
         * Returns the password for this account in encrypted form
         * @return string Txttools account password (encrypted)
         * @version 2010062512
         * @since 2010062512
         */
        function get_password() {
            return $this->password;
        }

        /**
         * Sets the password for this account in encrypted form
         * @param string $password Txttools account password (unencrypted)
         * @version 2010062512
         * @since 2010062512
         */
        function set_password($password) {
            $this->password = $password;
        }

        /**
         * Returns a short description of the account
         * @return string Account description
         * @version 2010062512
         * @since 2010062512
         */
        function get_description() {
            return $this->description;
        }

        /**
         * Sets a short description of the account
         * @param string $description  Account description
         * @version 2010062512
         * @since 2010062512
         */
        function set_description($description) {
            $this->description = $description;
        }

        /**
         * Returns the ID of the default inbox for
         * inbound messages on this account
         * @return int DB ID of default inbox
         * @version 2010062512
         * @since 2010062512
         */
        function get_defaultinbox() {
            return $this->defaultinbox;
        }

        /**
         * Sets the ID of the default inbox for
         * inbound messages on this account
         * @param int $defaultinbox  DB ID of default inbox
         * @version 2010062512
         * @since 2010062512
         */
        function set_defaultinbox($defaultinbox) {
            $this->defaultinbox = $defaultinbox;
        }

        /**
         * Returns the number of message credits
         * used via this account
         * @return int Number of credits used
         * @version 2010062512
         * @since 2010062512
         */
        function get_creditsused() {
            return $this->creditsused;
        }

        /**
         * Sets the number of message credits
         * used via this account
         * @param int $creditsused  Number of credits used
         * @version 2010062512
         * @since 2010062512
         */
        function set_creditsused($creditsused) {
            $this->creditsused = $creditsused;
        }

        /**
         * Returns the number of message credits
         * remaining on this account
         * @return int Number of credits remaining
         * @version 2010062512
         * @since 2010062512
         */
        function get_creditsremaining() {
            return $this->creditsremaining;
        }

        /**
         * Sets the number of message credits
         * remaining on this account
         * @param int $creditsremaining Number of credits remaining
         * @version 2010062512
         * @since 2010062512
         */
        function set_creditsremaining($creditsremaining) {
            $this->creditsremaining = $creditsremaining;
        }

        /**
         * Returns the time of the last sync
         * with txttools
         * @return int Unix timestamp
         * @version 2010062512
         * @since 2010062512
         */
        function get_lastupdate() {
            return $this->lastupdate;
        }

        /**
         * Sets the time of the last sync
         * with txttools
         * @param int $lastupdate Unix timestamp
         * @version 2010062512
         * @since 2010062512
         */
        function set_lastupdate($lastupdate) {
            $this->lastupdate = $lastupdate;
        }

    }

?>