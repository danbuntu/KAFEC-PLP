/**
 * jQuery include file for the account listing page
 * @author Greg J Preece <support@txttools.co.uk>
 * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
 * @version 2011021101
 * @since 2010062812
 */

// Constants - not declared as const to mollycoddle IE
var ACCOUNT_TYPE_INVOICED = 0;
var ACCOUNT_TYPE_PREPAID = 1;

// Variables to handle AJAX requests
var activeRequests = new Array(); // Log of current requests
var numberOfAccounts; // Number of accounts displayed on page
var numberProcessed = 0; // Number of accounts in chain that have been processed so far
var ceaseRequests = false; // Whether or not to send any further requests after an error has been encountered

// jQuery page references
var $updateAllButton;
var $progressBar;
var $progressBarTextValue;
var $accountsTable;

// Images used in account processing
var $WARNING_ICON;
var $IMAGE_LOADING;
var $IMAGE_UPDATE_SUCCESSFUL;
var $IMAGE_OUTBOUND;
var $IMAGE_INBOUND;
var $IMAGE_ACCESS_DENIED;

/**
 * Sets up the images that are used in
 * page processing/animation
 * @version 2010070612
 * @since 2010070612
 */
function setUpImages() {
    
    $WARNING_ICON = $('<img />')
        .attr('src', 'pix/warning.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', language['adminaccountupdatefailed'])
        .attr('title', language['adminaccountupdatefailed'])
        .css('float', 'left');

    $IMAGE_LOADING = $('<img />')
        .attr('src', 'pix/ajax-loader.gif')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', language['adminaccountfragloading'])
        .attr('title', language['adminaccountfragloading']);

    $IMAGE_UPDATE_SUCCESSFUL = $('<img />')
        .attr('src', 'pix/ok.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', language['adminaccountupdatesuccess'])
        .attr('title', language['adminaccountupdatesuccess'])
        .css('float', 'left');

    $IMAGE_OUTBOUND = $('<img />')
        .attr('src', 'pix/allow_outbound.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', language['adminaccountfragoutbound'])
        .attr('title', language['adminaccountfragoutbound'])
        .click(toggleOutboundAccess);
        
    $IMAGE_INBOUND = $('<img />')
        .attr('src', 'pix/allow_inbound.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', language['adminaccountfraginbound'])
        .attr('title', language['adminaccountfraginbound'])
        .click(toggleInboundAccess);

    $IMAGE_ACCESS_DENIED = $('<img />')
        .attr('src', 'pix/access_denied.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', language['adminaccountfragdenied'])
        .attr('title', language['adminaccountfragdenied'])
    
}

/**
 * Event handler for turning outbound access
 * on/off for a given account
 * @param Event event The click() event
 * @version 2010070612
 * @since 2010070612
 */
function toggleOutboundAccess(event) {
    toggleAccountAccess(event, this, 'toggleOutboundAccess');
}

/**
 * Event handler for turning inbound access
 * on/off for a given account
 * @param Event event The click() event
 * @version 2010070612
 * @since 2010070612
 */
function toggleInboundAccess(event) {
    toggleAccountAccess(event, this, 'toggleInboundAccess');
}


/**
 * Toggles inbound/outbound access for given accounts
 * (Abstraction of event handlers)
 * @param Event event The click() event
 * @param Object obj The element clicked on
 * @param string mode Whether to affect inbound/outbound
 * @see toggleOutboundAccess, toggleInboundAccess
 * @version 2010070612
 * @since 2010070612
 */
function toggleAccountAccess(event, obj, mode) {

    var $parentRow = $(obj).parent().parent();
    var accountId = $parentRow.attr('id');
    var rowNumber = $parentRow.parent().children().index($parentRow) + 1;

    // If this account is already being updated, wait
    if (activeRequests[accountId] != null) {
        alert(language['erroroperationinprogress']);
        return;
    }

    // Chuck in loading image
    $(obj).parent().append($IMAGE_LOADING.clone());
    $(obj).remove();

    // Requests are asynchronous, so store details
    // of the active request.
    activeRequests[accountId] = rowNumber;

    // Build JSON string to request data with
    var requestJSON = $.toJSON({
        mode : mode,
        accountId : accountId
    });

    // Make request and update accounts
    $.getJSON('admin_accounts_update.php',
        { json : requestJSON },
        handleAccountAccess
    );
}

/**
 * Callback function to handle JSON responses
 * for updating account access
 * @param Object json JSON response
 * @see toggleAccountAccess
 * @version 2010070612
 * @since 2010070612
 */
function handleAccountAccess(json) {

    // If this transaction is not recognised, discard
    if (! json.accountID || activeRequests[json.accountID] == null) {
        return;
    }

    // Grab table references
    var tableRow = activeRequests[json.accountID];
    var $accountRow = $accountsTable.find('tr:nth-child(' + tableRow + ')');
    var $outboundCell = $accountRow.find('td:nth-child(4)');
    var $inboundCell = $accountRow.find('td:nth-child(5)');

    // Update table with outbound status
    if (json.allowOutbound) {
        $outboundCell.children().remove();
        $outboundCell.append($IMAGE_OUTBOUND.clone(true));
    } else {
        $outboundCell.children().remove();
        $outboundCell.append($IMAGE_ACCESS_DENIED.clone(true).click(toggleOutboundAccess));
    }

    // Update table with inbound status
    if (json.allowInbound) {
        $inboundCell.children().remove();
        $inboundCell.append($IMAGE_INBOUND.clone(true));
    } else {
        $inboundCell.children().remove()
        $inboundCell.append($IMAGE_ACCESS_DENIED.clone(true).click(toggleInboundAccess));
    }
    
    activeRequests[json.accountID] = null;  //I'd use splice(), but it re-indexes the array

}

/**
 * Makes a series of calls to the txttools server
 * to update account credit information
 * @param Event event Button click event
 * @version 2010070612
 * @since 2010070612
 */
function updateAllAccounts(event) {

    // Make sure the user wants to update if there are many accounts
    if (numberOfAccounts > 5) {
        if (! confirm(language['adminaccountconfirmupdate'])) {
            return;
        }
    }

    // Stop user double-tapping
    $updateAllButton.attr('disabled', 'disabled');

    // Set up progress bar
    $progressBar.slideDown().progressbar({ value : 0 });
    $progressBarTextValue.text(language['adminaccountprocessedfrag'] + ': 0/' + numberOfAccounts)

    numberProcessed = 0;

    // Iterate over accounts defined
    $accountsTable.find('tr:not(:first-child)').each(function(index) {

        // If one of the previous requests returned a fatal error,
        // get the hell out of here, she's gonna blow!
        if (ceaseRequests) {
            $progressBar.progressbar("option", "value", 100);
            $updateAllButton.removeAttr('disabled');
            return false; // Break $.each()
        }

        // Instantiate vars to make the code more readable
        var tableRow = index + 2;
        var accountId = $(this).attr('id');
        var $firstCell = $(this).children('td:first');

        // Chuck in loading image
        $firstCell.children('img').remove();
        $firstCell.append($IMAGE_LOADING.clone().css('float', 'left'));

        // Requests are asynchronous, so store details
        // of the active request. (Reversed for lookup
        // in the other direction on the return journey.)
        activeRequests[accountId] = tableRow;

        // Build JSON string to request data with
        var requestJSON = $.toJSON({
            mode : 'updateSingleAccount',
            accountId : accountId
        });

        // Make request and pass result to handler
        $.getJSON('admin_accounts_update.php',
            { json : requestJSON },
            handleAccountInfoUpdate
        );

    });
}

/**
 * Response handler for account credit info
 * @param string json JSON response
 * @see updateAllAccounts
 * @version 2011021101
 * @since 2010070612
 */
function handleAccountInfoUpdate(json) {

    // If this transaction is not recognised, throw it in the trash
    if (! json.accountID || activeRequests[json.accountID] == null) {
        return;
    }

    var tableRow = activeRequests[json.accountID];
    var $accountRow = $accountsTable.find('tr:nth-child(' + tableRow + ')');
    var $firstCell = $accountRow.find('td:first');

    // Remove loading image
    $firstCell.children('img').remove();

    // If the response indicates errors...
    if (json.hasError) {

        $accountRow.everyTime(999, function() {
            $accountRow.animate({ backgroundColor : '#FC686A' }, 2000).animate({ backgroundColor : '#FCF0F0' }, 2000);
        });

        // Create warning icon and make error message its title
        $firstCell.prepend($WARNING_ICON.clone().attr('title', json.errorMessage));

        if (json.makeNoFurtherRequests) {
            breakOut = true;
        }

    } else {
        // Hide negative remaining numbers on invoiced accounts
        if (json.accountType == ACCOUNT_TYPE_INVOICED) {
            json.creditsRemaining = '\u221e';
        }

        if (json.accountType == ACCOUNT_TYPE_INVOICED) {
            json.accountType = language['acctableaccounttypeinvoiced'];
        } else {
            json.accountType = language['acctableaccounttypeprepaid'];
        }

        // Hey, hey, it's OK
        $firstCell.prepend($IMAGE_UPDATE_SUCCESSFUL.clone());


        // Update row content and highlight as updated
        $accountRow.animate({ backgroundColor : '#5CFF8D' }, 2000);
        $accountRow.find('td:nth-child(6)').text(json.creditsUsed).css('font-weight','bold');
        $accountRow.find('td:nth-child(7)').text(json.creditsRemaining).css('font-weight', 'bold');
        $accountRow.find('td:nth-child(8)').text(json.accountType).css('font-weight', 'bold');
        $accountRow.find('td:nth-child(9)').text(json.updateTimeString).css('font-weight', 'bold');
    }

    // Update progress bar
    var currentValue = Math.ceil(100 / numberOfAccounts * ++numberProcessed);
    $progressBar.progressbar("option", "value", currentValue);
    $progressBarTextValue.text(language['adminaccountprocessedfrag'] + ': ' + numberProcessed + '/' + numberOfAccounts);

    // Re-enable update button if processing complete
    if (numberOfAccounts == numberProcessed) {

        // Hide progress bar after 3 seconds
        setTimeout(function() {
            $progressBar.slideUp();
            $updateAllButton.removeAttr('disabled');
        }, 3000);
    }

    activeRequests[json.accountID] = null;  //I'd use splice(), but it re-indexes the array

}



// Page load!
$(document).ready(function() {

    // Set up images used
    setUpImages();

    // Instantiate progress bar reference
    $accountsTable = $('table#accountListTable');
    $updateAllButton = $('button#updateAllAccounts');
    $progressBar = $('div#accountProgressBar');
    $progressBarTextValue = $('div#accountProgressTextValue');

    numberOfAccounts = $accountsTable.find('tr:not(:first-child)').length;

    // Get credit info when button is clicked
    $updateAllButton.click(updateAllAccounts);

    // Toggle outbound access when clicked
    $accountsTable.find('td:nth-child(4) img').each(function(index) {
        $(this).click(toggleOutboundAccess);
    });

    // Toggle inbound access when clicked
    $accountsTable.find('td:nth-child(5) img').each(function(index) {
        $(this).click(toggleInboundAccess);
    });

    // Iterate through on load and set
    // account IDs as row attributes - makes
    // it far easier to associate them with
    // child elemenets
    for (var x = 2; x < accountArray.length; x++) {
        $accountsTable.find('tr:nth-child(' + x + ')').attr('id', accountArray[x]);
    }

    // Hide the progress bar on load
    $progressBar.hide();

});