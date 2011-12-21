<!--
//<![CDATA[

/**
 * jQuery script file for moodletxt admin page
 *
 * @author Greg J Preece <support@txttools.co.uk>
 * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
 * @version 20100609
 * @since 2006122012
 */

var FILTER_TYPE_KEYWORD = 'keyword';
var FILTER_TYPE_PHONENO = 'phoneno';

// Define jQuery objects that need to be referenced outside document.ready
var $filterAccountList;
var $existingKeywordFilterList;
var $newKeywordFilterOperand;
var $existingPhoneNumberFilterList;
var $newPhoneNumberFilterOperand;

var $existingKeywordFilterDiv;
var $newKeywordFilterDiv;
var $existingPhoneNumberFilterDiv;
var $newPhoneNumberFilterDiv;

var $loadingUsersOnFilterNotice;
var $usersOnFilterList;
var $userSearcher;
var $removeUsersButton;
var $saveFilterButton;

function validateKeywordInput(event) {

    if ((event.which >= 48 && event.which <= 57) ||
        (event.which >= 97 && event.which <= 122) ||
        (event.which >= 65 && event.which <= 90) ||
        event.which == 8 ||
        event.which == 0) {

        return true;

    } else {

        return false;

    }

}

/**
 * Turn on account controls when an account is selected
 */
function activateAccountControls() {

    var selectBox = $('select#edittxttoolsaccounts');
    var passwordBox = $('input#updatepasswordbox');
    var inboxBoxBoxBox = $('select#updatedefaultinbox');
    var updateButton = $('input#updatepasswordbutton');

    if (selectBox.val() == null) {

        passwordBox.attr("disabled", "disabled");
        updateButton.attr("disabled", "disabled");
        inboxBoxBoxBox.attr("disabled", "disabled");

    } else {

        passwordBox.removeAttr("disabled");
        updateButton.removeAttr("disabled");
        inboxBoxBoxBox.removeAttr("disabled");

        if (inboxarr[selectBox.val()] != null) {

            // Set default list to current default inbox
            inboxBoxBoxBox.val(inboxarr[selectBox.val()]);

        }

    }

}

// Handle data from ajax request
parser = function(data) {

    parsed_data = [];

    $.each(data, function(index, userData) {

        var displayString = buildDisplayString(userData);

        // Oh lookie, undocumented functionality
        parsed_data[parsed_data.length] = {
            data: displayString,
            value: userData.id,
            result: displayString
        };
    });
    
    return parsed_data;
}

formatter = function(row) {
    return row;
}

/**
 * Build a display string for a contact object
 * @param obj The object to build from
 * @return string
 */
function buildDisplayString(obj) {

    var userString =  '';
    
    if (obj.lastname && obj.lastname != '') {
        userString += obj.lastname;
    }

    if (obj.firstname && obj.firstname != '') {
        userString += ', ' + obj.firstname;
    }

    if (obj.username && obj.username != '') {
        userString += ' (' + obj.username + ')';
    }

    return userString;

}

function resetFilterForm($resetSource) {

    // Reset user filter list in all circumstances
    $userSearcher.val(searchUserString).attr('disabled', 'disabled');
    $usersOnFilterList.find('option').remove();
    $usersOnFilterList.attr('disabled', 'disabled');
    $removeUsersButton.attr('disabled', 'disabled');
    $saveFilterButton.attr('disabled', 'disabled');

    switch($resetSource) {

        case $filterAccountList.attr('id'):

            // When clearing out filter lists, we want to leave the first "blanker" result behind
            $existingKeywordFilterList.find('option:not(:first)').remove();
            $existingPhoneNumberFilterList.find('option:not(:first)').remove();

            $newKeywordFilterOperand.val('');
            $newPhoneNumberFilterOperand.val('');

            $newKeywordFilterDiv.hide();
            $newPhoneNumberFilterDiv.hide();
            $existingKeywordFilterDiv.show();
            $existingPhoneNumberFilterDiv.show();

            break;

        case $existingKeywordFilterList.attr('id'):

            $existingPhoneNumberFilterList.selectOptions('', true);
            $newKeywordFilterOperand.val('');
            $newPhoneNumberFilterOperand.val('');

            break;

        case $existingPhoneNumberFilterList.attr('id'):

            $existingKeywordFilterList.selectOptions('', true);
            $newKeywordFilterOperand.val('');
            $newPhoneNumberFilterOperand.val('');

            break;

        case $newKeywordFilterOperand.attr('id'):

            $existingKeywordFilterList.selectOptions('', true);
            $existingPhoneNumberFilterList.selectOptions('', true);
            $newPhoneNumberFilterOperand.val('');
            lockUnlockLowerFilterForm(false);

            break;

        case $newPhoneNumberFilterOperand.attr('id'):

            $existingKeywordFilterList.selectOptions('', true);
            $existingPhoneNumberFilterList.selectOptions('', true);
            $newKeywordFilterOperand.val('');
            lockUnlockLowerFilterForm(false);

            break;

    }

}

function lockUnlockUpperFilterForm(lock) {
    if (lock) {
        $existingKeywordFilterList.attr('disabled', 'disabled');
        $existingPhoneNumberFilterList.attr('disabled', 'disabled');
        $newKeywordFilterOperand.attr('disabled', 'disabled');
        $newPhoneNumberFilterOperand.attr('disabled', 'disabled');
    } else {
        $existingKeywordFilterList.removeAttr('disabled');
        $existingPhoneNumberFilterList.removeAttr('disabled');
        $newKeywordFilterOperand.removeAttr('disabled');
        $newPhoneNumberFilterOperand.removeAttr('disabled');
    }
}

function lockUnlockLowerFilterForm(lock) {

    if (lock) {
        $userSearcher.attr('disabled', 'disabled');
        $usersOnFilterList.attr('disabled', 'disabled');
        $removeUsersButton.attr('disabled', 'disabled');
        $saveFilterButton.attr('disabled', 'disabled');
    } else {
        $userSearcher.removeAttr('disabled');
        $usersOnFilterList.removeAttr('disabled');
        $removeUsersButton.removeAttr('disabled');
        $saveFilterButton.removeAttr('disabled');
    }

}

function getUsersOnFilter(filterId) {

    var requestJSON = $.toJSON({
        mode        :   'getAllUsersOnFilter',
        filterId    :   filterId
    });

    $loadingUsersOnFilterNotice.text(loadingNotice);

    $.getJSON('getfilters.php',
        {json : requestJSON},
        function(json) {

            // Clear and enable user list
            $loadingUsersOnFilterNotice.text(' ');
            lockUnlockLowerFilterForm(false);

            $.each(json, function(index, userData) {
                var $pageElement = $('<option>').val(userData.id).text(buildDisplayString(userData));

                $usersOnFilterList.append($pageElement);
            });

        }

    );

}

/**
 * Binds event handling functions when the page loads
 */
$(document).ready(function() {

    var $txttoolsAccountsBox            = $('select#edittxttoolsaccounts');

    $filterAccountList                  = $('select[name=filterAccountList]');

    var $loadingFiltersNotice           = $('span#loadingFilters');
    $existingKeywordFilterList          = $('select[name=existingKeywordFilterList]');
    $existingKeywordFilterDiv           = $('div#existingKeywordFilterDiv');

    $newKeywordFilterLink               = $('a#createNewKeywordFilter');
    $newKeywordFilterOperand            = $('input[name=newKeywordFilter]');
    $newKeywordFilterDiv                = $('div#newKeywordFilterDiv');

    $existingPhoneNumberFilterList      = $('select[name=existingPhoneNumberFilterList]');
    $existingPhoneNumberFilterDiv       = $('div#existingPhoneNumberFilterDiv');

    $newPhoneNumberFilterLink           = $('a#createNewPhoneNumberFilter');
    $newPhoneNumberFilterOperand        = $('input[name=newPhoneNumberFilter]');
    $newPhoneNumberFilterDiv            = $('div#newPhoneNumberFilterDiv');

    $loadingUsersOnFilterNotice         = $('span#loadingUsersOnFilter');
    $userSearcher                       = $('input[name=textSearcher]');
    $usersOnFilterList                  = $('select[name=usersOnFilter[]]');
    $removeUsersButton                  = $('button#removeUsersFromFilter');
    $saveFilterButton                   = $('button#saveFilterButton');

    // Clear all currently selected options
    $txttoolsAccountsBox.selectOptions('', true);

    // Bind click and change handlers to enable account controls
    $txttoolsAccountsBox.click(function(event) {
       activateAccountControls();            
    });

    $txttoolsAccountsBox.change(function(event) {
       activateAccountControls();
    });

    // New/cancel new filter handlers
    $newKeywordFilterLink.click(function(event) {
        event.preventDefault();
        $existingKeywordFilterDiv.hide();
        $newKeywordFilterDiv.show();
    });

    $('a#cancelNewKeywordFilter').click(function(event) {
        event.preventDefault();
        $newKeywordFilterDiv.hide();
        $existingKeywordFilterDiv.show();
    });

    $newPhoneNumberFilterLink.click(function(event) {
        event.preventDefault();
        $existingPhoneNumberFilterDiv.hide();
        $newPhoneNumberFilterDiv.show();
    });

    $('a#cancelNewPhoneNumberFilter').click(function(event) {
        event.preventDefault();
        $newPhoneNumberFilterDiv.hide();
        $existingPhoneNumberFilterDiv.show();
    });


    // Ensure only phone numbers can be added into the number filter field
    $newPhoneNumberFilterOperand.keypress(function(event) {return validatePhoneInput(event);});

    $filterAccountList.change(function(event) {

        resetFilterForm($(this).attr('id'));

        if ($(this).val() == '') {
            lockUnlockUpperFilterForm(true);
        }
        
        if ($(this).val() != '') {

            $loadingFiltersNotice.text(loadingNotice);

            var requestJSON = $.toJSON({
                mode        :   'getAllFiltersOnAccount',
                accountId   :   $(this).val()
            });

            $.getJSON('getfilters.php',
                {json : requestJSON},
                function(json) {

                    $loadingFiltersNotice.text(' ');
                    lockUnlockUpperFilterForm(false);

                    // Chuck returned filters into appropriate filter lists
                    $.each(json, function(index, filter) {
                        var $pageElement = $('<option>').val(filter.id).text(filter.value);

                        if (filter.type == 'phoneno') {
                            $existingPhoneNumberFilterList.append($pageElement);
                        } else {
                            $existingKeywordFilterList.append($pageElement);
                        }
                    });

                }

            );

        }

    });

    // When a filter is selected, get the users already on it
    $existingKeywordFilterList.change(function(event) {
        resetFilterForm($(this).attr('id'));
        if ($(this).val() != '') {
            getUsersOnFilter($(this).val());
        }
    });

    $existingPhoneNumberFilterList.change(function(event) {
        resetFilterForm($(this).attr('id'));
        if ($(this).val() != '') {
            getUsersOnFilter($(this).val());
        }
    });

    $newKeywordFilterOperand.focus(function(event) {
        if ($(this).val() == '') {
            resetFilterForm($(this).attr('id'));
        }
    });

    $newPhoneNumberFilterOperand.focus(function(event) {
        if ($(this).val() == '') {
            resetFilterForm($(this).attr('id'));
        }
    });

    $newKeywordFilterOperand.keypress(function(event) {
        return validateKeywordInput(event);
    });

    $newKeywordFilterOperand.keyup(function(event) {
        $(this).val($(this).val().toUpperCase());
    })

    $newKeywordFilterOperand.keypress(function(event) {
        return validateKeywordInput(event);
    });

    $newKeywordFilterOperand.keyup(function(event) {
        $(this).val($(this).val().toUpperCase());
    })

    /**
     * Autocompleter - query database for contacts
     * when user enters name text
     */
    $userSearcher.autocomplete(
        "getusers.php",
        {
            autoFill            : false,
            extraParams         : {
                json            : function() {return $.toJSON({
                                    mode            : 'searchUsersByCriteria',
                                    searchFragment  : $userSearcher.val()
                                  });}
            },
            formatItem          : formatter,
            minChars            : 2,
            multiple            : false,
            mustMatch           : false,
            parse               : parser,
            width               : '200px'
        }
    ).result(function(event, selectedUser, selectedValue) {

        /**
         * If a valid contact/set has been selected by the user,
         * add it to the recipients list
         */
        if (typeof(selectedUser) == 'string') {

            // Move selection into recipient box
            $usersOnFilterList.selectOptions("novalue", true);
            $usersOnFilterList.addOption(selectedValue, selectedUser);
            $userSearcher.val('');

        }

    });

    /**
     * Bind prevents return key from submitting
     * the form when the text searcher returns
     * no results
     */
    $userSearcher.keypress(function(event) {
        if (event.which == 13) return false;
    });

    $userSearcher.focus(function(event) {
        $(this).val('');
    });

    $userSearcher.blur(function(event) {
        $(this).val(searchUserString);
    });

    $removeUsersButton.click(function(event) {
        event.preventDefault();
        $usersOnFilterList.removeOption(/./, true);
    });

    $saveFilterButton.click(function(event) {
        $usersOnFilterList.selectOptions(/./, true);
    });

    /**
     * Special onload handlers for form re-population.
     * If an account is already selected, grab filters.
     * If a new filter has been given a value, populate it.
     */
    $filterAccountList.change();

    if ($newKeywordFilterOperand.val() != '') {
        $newKeywordFilterLink.click();
    }

    if ($newPhoneNumberFilterOperand.val() != '') {
        $newPhoneNumberFilterLink.click();
    }

});

//]]>
//-->