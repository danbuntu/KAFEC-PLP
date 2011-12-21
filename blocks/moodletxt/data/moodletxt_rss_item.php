<?php

    /**
     * Data class to hold the details of an RSS news update item.
     * These data classes are used for insertion, removal
     * and transportation of data.  I prefer this to
     * creating them on the fly.
     * (Spot the Java monkey!) Rock on.
     *
     * @package datawrappers
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2010062212
     * @since 2007013012
     */
    class moodletxt_rss_item {

        /**
         * Holds whether or not the object instance is valid.
         * This stems from not having a reliable way to prevent
         * object initialisation within the constructor. Roll on PHP 5!
         * @var boolean
         */
        var $valid;

        /**
         * Holds the record ID of the news item if known.
         * @var int
         */
        var $id;

        /**
         * Holds the title of the news item
         * @var string
         */
        var $title;

        /**
         * Holds the link to the news item on the site
         * @var string
         */
        var $link;

        /**
         * Holds the timestamp on which this item was published
         * @var int
         */
        var $pubtime;  // You're damn right it is!

        /**
         * Holds the description of the item
         * @var string
         */
        var $description;

        /**
         * Holds the expiration time for this item to drop off the admin page
         * @var int
         */
        var $expirytime;

        /**
         * Class constructor - takes a set of valid values
         * and initialises the data object.
         *
         * @param string $title The title of this RSS item.
         * @param string $link The link to this RSS item on the Mtxt website.
         * @param int $pubtime The timestamp on which this item was published.
         * @param string $description The description of this RSS item
         * @param int $expirytime The time at which this will drop off the admin page.
         * @param int $id The record ID of this item if known. (Optional)
         * @version 2010062212
         * @since 2007013012
         */
        function moodletxt_rss_item($title, $link, $pubtime, $description, $expirytime, $id = 0) {

            $isvalid = true;

            // Cast required integers to that type
            $pubtime = (int) $pubtime;
            $id = (int) $id;
            $expirytime = (int) $expirytime;

            /* Check that the parameters passed are valid */

            // Check integers are in valid range
            if (($pubtime <= 0) || ($expirytime < 0) || ($id < 0))
                $isvalid = false;


            // If the object is invalid, set it as such
            if (! $isvalid) {

                $this->valid = false;

            // Otherwise, populate fields
            } else {

                if ($id > 0) {

                    $this->set_id($id);

                }

                $this->set_title($title);
                $this->set_link($link);
                $this->set_pubtime($pubtime);
                $this->set_description($description);
                $this->set_expirytime($expirytime);

            }

        }

        /**
         * Returns the DB record ID of this RSS item
         * @return int DB record ID
         * @version 2010062212
         * @since 2007013012
         */
        function get_id() {
            return $this->id;
        }

        /**
         * Returns the title of this news item
         * @return string Item title
         * @version 2010062212
         * @since 2007013012
         */
        function get_title() {
            return $this->title;
        }

        /**
         * Returns the hyperlink to this news item on the web
         * @return string Hyperlink
         * @version 2010062212
         * @since 2007013012
         */
        function get_link() {
            return $this->link;
        }

        /**
         * Returns the time at which this news item was published
         * @return int Unix timestamp
         * @version 2010062212
         * @since 2007013012
         */
        function get_pubtime() {
            return $this->pubtime;
        }

        /**
         * Returns a description of this news item
         * @return string Item description
         * @version 2010062212
         * @since 2007013012
         */
        function get_description() {
            return $this->description;
        }

        /**
         * Returns the time at which this news
         * item will drop off the admin page
         * @return int Unix timestamp
         * @version 2010062212
         * @since 2010062212
         */
        function get_expirytime() {
            return $this->expirytime;
        }

        /**
         * Sets the DB record ID of this RSS item
         * @param int $id DB record ID
         * @version 2010062212
         * @since 2010062212
         */
        function set_id($id) {
            $this->id = $id;
        }

        /**
         * Returns the title of this news item
         * @param string $title Item title
         * @version 2010062212
         * @since 2010062212
         */
        function set_title($title) {
            $this->title = $title;
        }

        /**
         * Sets the link to this news item on the web
         * @param string $link Hyperlink
         * @version 2010062212
         * @since 2010062212
         */
        function set_link($link) {
            $this->link = $link;
        }

        /**
         * Sets the time at which this news item was published
         * @param int $pubtime  Unix timestamp
         * @version 2010062212
         * @since 2010062212
         */
        function set_pubtime($pubtime) {
            $this->pubtime = $pubtime;
        }

        /**
         * Sets a description of this news item
         * @param string $description Item description
         * @version 2010062212
         * @since 2010062212
         */
        function set_description($description) {
            $this->description = $description;
        }

        /**
         * Sets the time at which this news item
         * will expire and fall off the admin page
         * @param int $expirytime Unix timestamp
         * @version 2010062212
         * @since 2010062212
         */
        function set_expirytime($expirytime) {
            $this->expirytime = $expirytime;
        }

    }

?>
