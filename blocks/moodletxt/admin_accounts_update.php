<?php

/*
 * File responds to AJAX requests for
 * txttools account updates, and synchronises
 * those accounts with the main server
 * @author Greg J Preece <support@txttools.co.uk>
 * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
 * @version 2011021101
 * @since 2010062812
 */

// Get config and required libraries
require_once('../../config.php');
require_once($CFG->libdir.'/datalib.php');
require_once($CFG->dirroot . '/blocks/moodletxt/lib.php');
require_once($CFG->dirroot . '/blocks/moodletxt/db/sqllib.php');
require_once($CFG->dirroot . '/blocks/moodletxt/xml/moodletxt_xml_controller.php');

// Default values for response
$responseTemplate = array(
    'accountID' => 0,
    'creditsUsed' => 0,
    'creditsRemaining' => 0,
    'updateTimeString' => '',
    'allowOutbound' => false,
    'allowInbound' => false,
    'accountType' => 0,

    'hasError' => false,
    'errorMessage' => '',
    'makeNoFurtherRequests' => false
);

$xmlcontroller = null;


/**
 * Returns failure to the calling script
 * @param string $errorMessage Error message
 * @param boolean $preventFurtherRequests Whether additional requests should be sent
 * @param int $accountId ID of account that failed update
 * @return string JSON response
 * @version 2010070612
 * @since 2010063012
 */
function returnFailure($errorMessage, $preventFurtherRequests = false, $accountId = 0) {

    $response['hasError'] = true;
    $response['errorMessage'] = $errorMessage;
    $response['makeNoFurtherRequests'] = $preventFurtherRequests;

    if ($accountId > 0) {
        $response['accountID'] = $accountId;
    }

    return json_encode($response);

}

/**
 * Returns success to the calling script
 * @param Object $txttoolsaccount The successfully updated account
 * @return string JSON response
 * @version 2011021101
 * @since 2010063012
 */
function returnSuccess($txttoolsaccount) {

    $response['accountID'] = $txttoolsaccount->id;
    $response['creditsUsed'] = $txttoolsaccount->creditsused;
    $response['creditsRemaining'] = $txttoolsaccount->creditsremaining;
    $response['updateTimeString'] = userdate($txttoolsaccount->lastupdate, "%H:%M:%S, %d %B %Y");
    $response['allowOutbound'] = ($txttoolsaccount->outboundenabled == 1) ? true : false;
    $response['allowInbound'] = ($txttoolsaccount->inboundenabled == 1) ? true : false;
    $response['accountType'] = $txttoolsaccount->accounttype;

    return json_encode($response);

}

/**
 * Gets updated account info for a single account
 * @global array $responseTemplate
 * @global object $xmlcontroller
 * @param int $accountid txttools Account ID
 * @return string JSON response
 * @version 2010063012
 * @since 2010062812
 */
function updateSingleAccount($accountid) {

    global $responseTemplate, $xmlcontroller;
    $response = $responseTemplate;

    // Check that account exists
    $checkaccount = count_records('block_mtxt_accounts', 'id', addslashes($accountid));

    if ($checkaccount != 1)
        return returnFailure(get_string('errorinvalidaccountid', 'block_moodletxt'), true);

    // Call up txttools and request account data
    $accountDetails = $xmlcontroller->get_account_credit_info($accountid);
    $errorarr = moodletxt_get_xml_errors($accountDetails);

    if (count($errorarr) > 0) {
        return returnFailure(array_pop($errorarr), false, $accountid);
    } else {
        $txttoolsaccount = moodletxt_update_account_info($accountDetails[0]);
        return returnSuccess($txttoolsaccount);
    }

}

/**
 * Writes an updated account to the database
 * @param Object $txttoolsaccount The successfully updated account
 * @version 2011021101
 * @since 2010063012
 */
function writeBackAccount($txttoolsaccount) {

    // Write record back to DB (escapify as needed)
    $txttoolsaccount->username = addslashes($txttoolsaccount->username);
    $txttoolsaccount->password = addslashes($txttoolsaccount->password);
    $txttoolsaccount->description = addslashes($txttoolsaccount->description);
    $txttoolsaccount->defaultinbox = addslashes($txttoolsaccount->defaultinbox);
    $txttoolsaccount->creditsused = addslashes($txttoolsaccount->creditsused);
    $txttoolsaccount->creditsremaining = addslashes($txttoolsaccount->creditsremaining);
    $txttoolsaccount->accounttype = addslashes($txttoolsaccount->accounttype);
    $txttoolsaccount->lastupdate = addslashes($txttoolsaccount->lastupdate);
    $txttoolsaccount->inboundenabled = addslashes($txttoolsaccount->inboundenabled);
    $txttoolsaccount->outboundenabled = addslashes($txttoolsaccount->outboundenabled);

    update_record('block_mtxt_accounts', $txttoolsaccount);

}

/**
 * Allow/disallow account access in a given direction
 * @param int $accountId ID of account to update
 * @param string $direction Inbound/Outbound
 * @param boolean $allow Whether access should be allowed in this direction
 * @return string JSON response
 * @version 2010070612
 * @since 2010063012
 */
function setAccountAccess($accountId, $direction = 'outbound', $allow = true) {

    // Get account record from DB
    $txttoolsaccount = get_record('block_mtxt_accounts', 'id', addslashes($accountId));

    if (! is_object($txttoolsaccount)) {
        return returnFailure(get_string('errorinvalidaccountid', 'block_moodletxt'), true);
    }

    if ($direction == 'outbound')
        $txttoolsaccount->outboundenabled = ($allow) ? 1 : 0;
    else if ($direction == 'inbound')
        $txttoolsaccount->inboundenabled = ($allow) ? 1 : 0;

    writeBackAcccount($txttoolsaccount);
    return returnSuccess($txttoolsaccount);

}


/**
 * Toggle account access in a given direction
 * @param int $accountId ID of account to update
 * @param string $direction Inbound/Outbound
 * @return string JSON response
 * @version 2010070612
 * @since 2010063012
 */
function toggleAccountAccess($accountId, $direction = 'outbound') {

    // Get account record from DB
    $txttoolsaccount = get_record('block_mtxt_accounts', 'id', addslashes($accountId));

    if (! is_object($txttoolsaccount)) {
        return returnFailure(get_string('errorinvalidaccountid', 'block_moodletxt'), true);
    }

    if ($direction == 'outbound')
        $txttoolsaccount->outboundenabled = ($txttoolsaccount->outboundenabled == 0) ? 1 : 0;
    else if ($direction == 'inbound')
        $txttoolsaccount->inboundenabled = ($txttoolsaccount->inboundenabled == 0) ? 1 : 0;

    writeBackAccount($txttoolsaccount);
    return returnSuccess($txttoolsaccount);

}




// Suppress error reporting
error_reporting(0);

// Security checks!

// Create site context
$sitecontext = get_context_instance(CONTEXT_SYSTEM, SITEID);

// Check for admin
if (! has_capability('block/moodletxt:adminsettings', $sitecontext, $USER->id))
    die();

$xmlcontroller = new moodletxt_xml_controller();

// Now that that's out the way, grab the JSON request and let's rock!
$json = optional_param('json', '', PARAM_RAW);
$decodedJson = json_decode(stripslashes($json));

header('Content-Type: application/json; charset=utf-8');

if (is_object($decodedJson)) {

    switch($decodedJson->mode) {

        case 'updateSingleAccount':
            echo(updateSingleAccount((int) $decodedJson->accountId));
            break;

        case 'setInboundAccess':
            echo(setAccountAccess((int) $decodedJson->accountId, 'inbound', $decodedJson->allowInbound));
            break;

        case 'setOutboundAccess':
            echo(setAccountAccess((int) $decodedJson->accountId, 'outbound', $decodedJson->allowOutbound));
            break;

        case 'toggleOutboundAccess':
            echo(toggleAccountAccess((int) $decodedJson->accountId, 'outbound'));
            break;

        case 'toggleInboundAccess':
            echo(toggleAccountAccess((int) $decodedJson->accountId, 'inbound'));
            break;

        default:
            // Go away!
            die();

    }

}

?>