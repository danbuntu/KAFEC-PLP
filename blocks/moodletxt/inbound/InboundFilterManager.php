<?php

/**
 * Class designed to abstract the inbound filtering process into a single code set.
 * Pulls filters from the database and matches them against message objects handed to it.
 * @author Greg J Preece <support@txttools.co.uk>
 * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
 * @version 2010062812
 * @since 2009110112
 */

class InboundFilterManager {

    /**
     * Holds filters indexed by account ID and username (via internal reference)
     *
     * Indexed like-a so:
     *
     * Account username
     *     ↓
     * Account ID
     *     ↓
     *     → Default Inbox
     *     ↓
     *     → Keywords → Filters → Destination Folders
     *     ↓
     *     → Source Phone Numbers → Filters → Destination Folders
     */
    var $filterSet;

    /**
     * String reference for default inbox routing
     * @var string
     */
    var $FILTER_TYPE_DEFAULT = "defaultInbox";

    /**
     * String reference for keyword filtering
     * @var string
     */
    var $FILTER_TYPE_KEYWORD = "keyword";

    /**
     * String reference for source phone number filtering
     * @var string
     */
    var $FILTER_TYPE_PHONENO = "phoneno";

    /**
     * Initialises the filter manager
     * @version 2009110112
     * @since 2009110112
     */
    function InboundFilterManager() {
        
        $this->filterSet = array();

        $this->setupFilters();
        
    }

    /**
     * Sets up the inbound filter set ready for use
     * @version 2010062812
     * @since 2009110112
     */
    function setupFilters() {
        
        $accounts = get_records('block_mtxt_accounts', 'inboundenabled', '1');
        
        foreach($accounts as $account) {

            // Set up placeholders in filter set array
            $this->filterSet[$account->id] = array();
            $this->filterSet[strtolower($account->username)] = &$this->filterSet[$account->id];

            $this->filterSet[$account->id][$this->FILTER_TYPE_DEFAULT] = 0;
            $this->filterSet[$account->id][$this->FILTER_TYPE_KEYWORD] = array();
            $this->filterSet[$account->id][$this->FILTER_TYPE_PHONENO] = array();

            // Get default inbox folder
            $sql = moodletxt_get_sql('inboxgetdefaultinbox');
            $sql = sprintf($sql, moodletxt_escape_string($account->id));

            $defaultinboxobj = get_record_sql($sql);
            $this->filterSet[$account->id][$this->FILTER_TYPE_DEFAULT] = $defaultinboxobj->id;

            // Get filters
            $sql = moodletxt_get_sql('inboxgetinboxfilters');
            $sql = sprintf($sql, moodletxt_escape_string($account->id));

            $filters = get_records_sql($sql);

            if (is_array($filters)) {

                foreach ($filters as $filter) {

                    // Fix for older installations still running with filter
                    // types stored in upper-case, pre PostgreSQL fix
                    $filter->type = strtolower($filter->type);

                    $filter->value = strtolower($filter->value);

                    if (! isset($this->filterSet[$account->id][$filter->type][$filter->value]))
                        $this->filterSet[$account->id][$filter->type][$filter->value] = array();

                    array_push($this->filterSet[$account->id][$filter->type][$filter->value], $filter->folderid);

                }

            }
            
        }
        
    }

    /**
     * Takes an array of messages and applies inbound filters to them
     * @param array $messageset
     * @return array
     * @version 2009110212
     * @since 2009110112
     */
    function filterMessages($messageset) {

        if (! is_array($messageset))
            $messageset = array($messageset);

        // Iterate over messages and filter
        foreach ($messageset as $message) {

            if (! $message instanceof moodletxt_inbound_message)
                continue;

            $keywordex = explode(' ', trim($message->get_message_text()));
            $keyword = strtolower($keywordex[0]);  // Case insensitive filtering, ta
            $message->setDestinationAccountUsername(strtolower($message->getDestinationAccountUsername()));

            // Get ID/username of the txttools account this message came in on
            $accountIdent = ($message->getDestinationAccountID() > 0)
                ? $message->getDestinationAccountID()
                : $message->getDestinationAccountUsername();

            // Do keyword filtering
            if (isset($this->filterSet[$accountIdent][$this->FILTER_TYPE_KEYWORD][$keyword]))
                $message->add_folders($this->filterSet[$accountIdent][$this->FILTER_TYPE_KEYWORD][$keyword]);

            // Do source number filtering
            if (isset($this->filterSet[$accountIdent][$this->FILTER_TYPE_PHONENO][$message->get_source()]))
                $message->add_folders($this->filterSet[$accountIdent][$this->FILTER_TYPE_PHONENO][$message->get_source()]);

            // If no filters have been matched, send to default inbox
            if ($message->get_folder_count() == 0)
                $message->add_folder($this->filterSet[$accountIdent][$this->FILTER_TYPE_DEFAULT]);

        }

        return $messageset;

    }

}

?>