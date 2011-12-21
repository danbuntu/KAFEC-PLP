<?php

/**
 * jQuery script for send page
 * @author Greg J Preece <support@txttools.co.uk>
 * @version 2009010712
 * @since 2008103012
 */

require_once('../../../config.php');
error_reporting(0);

?>

// Set of variables to hold original cell values while editing
var originalLastName = '';
var originalFirstName = '';
var originalCompany = '';
var originalNumber = '';


/**
 * Function resets the editing table,
 * removing any open records
 */
function resetForm(saved) {

    // Check row ID is set
    var rowid = $('input#rowid').val();

    // If no record is open, return
    if (rowid == '')
        return true;

    // Get the open row and its child cells
    var tableRow = $('tr#' + rowid);

    var lastNameCell  = tableRow.children('td')[1];
    var firstNameCell = tableRow.children('td')[2];
    var companyCell   = tableRow.children('td')[3];
    var numberCell    = tableRow.children('td')[4];

    // If the user has saved this record
    if (saved) {

        // Hallelujah, I've been saved!
        // Grab input from textboxes and enter it into empty cells
        var savedLastName = $($(lastNameCell).children('input')[0]).val();
        var savedFirstName = $($(firstNameCell).children('input')[0]).val();
        var savedCompany = $($(companyCell).children('input')[0]).val();
        var savedNumber = $($(numberCell).children('input')[0]).val();

        $(lastNameCell).empty().text(savedLastName);
        $(firstNameCell).empty().text(savedFirstName);
        $(companyCell).empty().text(savedCompany);
        $(numberCell).empty().text(savedNumber);
        
    } else {

        // Get the text that the cells previously contained and restore it
        $(lastNameCell).empty().text(originalLastName);
        $(firstNameCell).empty().text(originalFirstName);
        $(companyCell).empty().text(originalCompany);
        $(numberCell).empty().text(originalNumber);

    }

    // Reset variables holding form data
    originalFirstName = '';
    originalLastName = '';
    originalCompany = '';
    originalNumber = '';

    $('input#rowid').val('');

}

/*
 * Validate input in phone fields
 */
function validatePhoneInput(event) {

    /*
     * 48 - 57 are numbers
     * 43 is the + sign
     * 8 is backspace
     * 0 is for system keys
     */

    if (event.which == 13) {

        updateContact();

    } else if ((event.which >= 48 && event.which <= 57) ||
        event.which == 43 ||
        event.which == 8 ||
        event.which == 0) {

        return true;

    } else {

        return false;

    }

}


function updateContact() {

    // Check row ID is set
    var rowid = $('input#rowid').val();

    // If there is something to submit, grab the form entries
    if (rowid != '') {

        $('input[type=button]#submit' + rowid).remove();
        $('input[type=button]#cancel' + rowid).remove();

        // Get form details and lock form
        var firstnameInput = $('form#editForm input[name=firstname]');
        var lastnameInput = $('form#editForm input[name=lastname]');
        var companyInput = $('form#editForm input[name=company]');
        var phonenoInput = $('form#editForm input[name=phoneno]');

        var firstname = firstnameInput.val();
        var lastname = lastnameInput.val();
        var company = companyInput.val();
        var phoneno = phonenoInput.val();

        firstnameInput.attr("disabled", "disabled");
        lastnameInput.attr("disabled", "disabled");
        companyInput.attr("disabled", "disabled");
        phonenoInput.attr("disabled", "disabled");

        // Display loading image
        var loadingImage = $('<img src="pix/ajax-loader.gif" width="16" height="16" alt="<?php echo(get_string('loadtoken', 'block_moodletxt')); ?>" title="<?php echo(get_string('loadtoken', 'block_moodletxt')); ?>" />');
        var numberCell = $('tr#' + rowid).children('td')[4];
        $(numberCell).append(loadingImage);

        // POST to processing form
        $.post('addressbook_edit_process.php',
            {rowid : rowid, firstname : firstname, lastname : lastname, company : company, phoneno : phoneno },
            function(data) {

                // If database update succeeded, reset the form
                if ($('Success', data).length > 0) {

                    resetForm(true);

                // If update failed, chuck the error message to screen
                } else {

                    alert($('Error ErrorMessage', data).text());

                }
            });

    }

}

$(document).ready(function() {

    // When a row on the table is double clicked, edit it
    $('#contactsTable tbody tr').dblclick(function() {

        var selectedRowId = $('input#rowid').val();
        var rowid = $(this).attr('id');

        // Check that you're not double-clicking the same row twice
        if (rowid == selectedRowId) {
            
            return true;
            
        } else {
        
            resetForm(false); // Clear any other already opened rows
            $('input#rowid').val(rowid);

            // Get cells
            var lastNameCell  = $(this).children('td')[1];
            var firstNameCell = $(this).children('td')[2];
            var companyCell   = $(this).children('td')[3];
            var numberCell    = $(this).children('td')[4];

            // Grab original contents of cells and populate form
            originalLastName = $(lastNameCell).text();
            var textbox = $('<input id="' + rowid + '_lastname" name="lastname" type="text" size="20" value="' + originalLastName + '" style="text-align:center;" />');
            textbox.keypress(function(event) {
                if (event.which == 13) { updateContact(); }
            });
            $(lastNameCell).empty().append(textbox);

            originalFirstName = $(firstNameCell).text();
            var textbox = $('<input id="' + rowid + '_firstname" name="firstname" type="text" size="20" value="' + originalFirstName + '" style="text-align:center;" />');
            textbox.keypress(function(event) {
                if (event.which == 13) { updateContact(); }
            });
            $(firstNameCell).empty().append(textbox);

            originalCompany = $(companyCell).text();
            var textbox = $('<input id="' + rowid + '_company" name="company" type="text" size="20" value="' + originalCompany + '" style="text-align:center;" />');
            textbox.keypress(function(event) {
                if (event.which == 13) { updateContact(); }
            });
            $(companyCell).empty().append(textbox);

            originalNumber = $(numberCell).text();
            var textbox = $('<input id="' + rowid + '_phoneno" name="phoneno" type="text" size="20" value="' + originalNumber + '" maxlength="20" style="text-align:center;" />');
            textbox.keypress(function(event) {
                return validatePhoneInput(event);
            });
            $(numberCell).empty().append(textbox);

            // Create save button
            var saveButton = $('<input type="button" id="submit' + rowid + '" value="<?php echo(get_string('editbookbutsave', 'block_moodletxt')); ?>" />');
            saveButton.click(function(event) {
                updateContact();
            });
            $(numberCell).append(saveButton);

            // Create cancel button
            var cancelButton = $('<input type="button" id="cancel' + rowid + '" value="<?php echo(get_string('editbookbutcancel', 'block_moodletxt')); ?>" />');
            cancelButton.click(function(event) {
                resetForm();
            });
            $(numberCell).append(cancelButton);

        }

    });

    /**
     * When the form is submitted, do an AJAX POST
     * query to update the record in the DB
     */
    /*
    $('form#editForm').submit(function() {

        // Check row ID is set
        var rowid = $('input#rowid').val();

        // If there is something to submit, grab the form entries
        if (rowid != '') {

            $('button#submit' + rowid).remove();

            // Get form details and lock form
            var firstnameInput = $('form#editForm input[name=firstname]');
            var lastnameInput = $('form#editForm input[name=lastname]');
            var companyInput = $('form#editForm input[name=company]');
            var phonenoInput = $('form#editForm input[name=phoneno]');

            var firstname = firstnameInput.val();
            var lastname = lastnameInput.val();
            var company = companyInput.val();
            var phoneno = phonenoInput.val();

            firstnameInput.attr("disabled", "disabled");
            lastnameInput.attr("disabled", "disabled");
            companyInput.attr("disabled", "disabled");
            phonenoInput.attr("disabled", "disabled");

            // Display loading image
            var loadingImage = $('<img src="pix/ajax-loader.gif" width="16" height="16" alt="<?php echo(get_string('loadtoken', 'block_moodletxt')); ?>" title="<?php echo(get_string('loadtoken', 'block_moodletxt')); ?>" />');
            var numberCell = $('tr#' + rowid).children('td')[4];
            $(numberCell).append(loadingImage);

            // POST to processing form
            $.post('addressbook_edit_process.php',
                {rowid : rowid, firstname : firstname, lastname : lastname, company : company, phoneno : phoneno },
                function(data) {

                    // If database update succeeded, reset the form
                    if ($('Success', data).length > 0) {

                        resetForm(true);

                    // If update failed, chuck the error message to screen
                    } else {

                        alert($('Error ErrorMessage', data).text());

                    }
                });

        }

        return false;
    });
*/

    /* Binds to stop "white page" issue
      Ugly as hell but I haven't got a better way right now */
    $('.tablePagerNext').click(function() {
        resetForm(false);
    });
    $('.tablePagerPrev').click(function() {
        resetForm(false);
    });
    $('.tablePagerFirst').click(function() {
        resetForm(false);
    });
    $('.tablePagerLast').click(function() {
        resetForm(false);
    });
    $('#filterLastName').keyup(function() {
        resetForm(false);
    });
    $('.tablePagerSize').change(function() {
        resetForm(false);
    });

    /**
     * Set up tablesorter, tablesorterPager, tablesorterFilter,
     * and tablesorterWorldPeace2009
     */
    $('#contactsTable')
        .tablesorter({widgets : ['zebra']})
        .tablesorterPager({
            size                : 10,
            container           : $('.tablePager'),
            cssNext             : '.tablePagerNext',
            cssPrev             : '.tablePagerPrev',
            cssFirst            : '.tablePagerFirst',
            cssLast             : '.tablePagerLast',
            cssPageDisplay      : '.tablePagerDisplay',
            cssPageSize         : '.tablePagerSize,',
            positionFixed       : false
        })
        .tablesorterFilter({
            filterContainer     : $('#filterLastName'),
            filterColumns       : [1],
            filterCaseSensitive : false
        });

    /**
     * Set up table selection handlers
     */
    $('a#checkAllBoxes').click(function(event) {
        event.preventDefault();
        $('#contactsTable').find('input[type=checkbox]').attr('checked', 'checked');
    });

    $('a#uncheckAllBoxes').click(function(event) {
        event.preventDefault();
        $('#contactsTable').find('input[type=checkbox]').removeAttr('checked');
    });

    $('a#deleteSelected').click(function(event) {
        event.preventDefault();
        $('input#deleteSwitch').val('deleteSelected');
        $('form#editForm').submit();
    });

    $('a#deleteExceptSelected').click(function(event) {
        event.preventDefault();
        $('input#deleteSwitch').val('deleteExceptSelected');
        $('form#editForm').submit();
    });

});