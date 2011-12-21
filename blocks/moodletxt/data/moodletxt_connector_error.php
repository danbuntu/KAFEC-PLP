<?php

    /**
     * Data class to hold details of an error generated
     * or received by the XML connector, such as "Invalid XML", etc
     *
     * @package datawrappers
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2009062612
     * @since 2006111312
     */

    class moodletxt_connector_error {

        /**
         * Holds the HTTP/XML connector code for this error (if one exists)
         * @var string
         */
        var $errorcode;

        /**
         * Holds the error message string
         * @var string
         */
        var $errorstring;

        /**
         * Holds the time at which this error was recorded
         * @var int
         */
        var $timeoferror;

        /**
         * Class constructor - takes a set of valid parameters
         * and initialises the object
         *
         * @param string $error The error string for this error
         * @param int $time The time at which this error was encountered
         * @param string $code The HTTP/XML error for this error
         * @version 2010062112
         * @since 2006111312
         */
        function moodletxt_connector_error($error, $time = 0, $code = '') {

            $this->errorstring = $error;
            $this->timeoferror = ($time > 0) ? $time : time();

            if ($code != '') {

                $this->errorcode = $code;

            }

        }

        /**
         * Returns the HTTP/XML connector code for this error
         * @return string HTTP/XML error code
         * @version 2010062112
         * @since 2009070212
         */
        function getErrorCode() {

            return $this->errorcode;

        }

        /**
         * Returns a textual description of the error
         * @return string Error string
         * @version 2010062112
         * @since 2009070212
         */
        function getErrorString() {

            return $this->errorstring;

        }

        /**
         * Returns the unix timestamp at which this error occurred
         * @return int Error timestamp
         * @version 2010062112
         * @since 2009070212
         */
        function getTimeOfError() {

            return $this->timeoferror;

        }

    }

?>
