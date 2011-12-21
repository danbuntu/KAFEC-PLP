<?php

/* 
 * File returns filter info in JSON form to
 * the admin panel, as and when requested
 * @author Greg J Preece <support@txttools.co.uk>
 * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
 * @version 2010061812
 * @since 2010061812
 */

// Get config and required libraries
require_once('../../config.php');
require_once($CFG->libdir.'/datalib.php');
require_once($CFG->dirroot . '/blocks/moodletxt/lib.php');
require_once($CFG->dirroot . '/blocks/moodletxt/db/sqllib.php');

/**
 * Get all filters associated with a txttools account
 * @param int $accountId
 * @return string
 */
function getAllFilters($accountId) {

    $filters = get_records('block_mtxt_filter', 'account', addslashes($accountId));
    if (is_array($filters) && count($filters) > 0) {
        return json_encode($filters);
    } else {
        return '{}';
    }

}

/**
 * Get all users associated with a given filter
 * @param int $filterId
 * @return string
 */
function getAllUsersOnFilter($filterId) {

    $sql = moodletxt_get_sql('admingetusersonfilter');
    $sql = sprintf($sql, addslashes($filterId));

    $filters = get_records_sql($sql);

    if (is_array($filters) && count($filters) > 0) {
        return json_encode($filters);
    } else {
        return '{}';
    }

}



// Suppress error reporting
error_reporting(0);

// Security checks!

// Create site context
$sitecontext = get_context_instance(CONTEXT_SYSTEM, SITEID);

// Check for admin
if (! has_capability('block/moodletxt:adminusers', $sitecontext, $USER->id) &&
    ! has_capability('block/moodletxt:adminsettings', $sitecontext, $USER->id))
    die();


// Now that that's out the way, grab the JSON request and let's rock!
$json = optional_param('json', '', PARAM_RAW);
$decodedJson = json_decode(stripslashes($json));

header('Content-Type: application/json;');

if (is_object($decodedJson)) {

    switch($decodedJson->mode) {

        case 'getAllFiltersOnAccount':
            echo(getAllFilters($decodedJson->accountId));
            break;

        case 'getAllUsersOnFilter':
            echo(getAllUsersOnFilter($decodedJson->filterId));
            break;

        default:
            // Go away!
            die();

    }

}

?>