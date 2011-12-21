<?php

/**
 * jQuery script for send page
 *
 * @todo Oi, Greg.  When you get bored, clean up this mess, would ya?
 * @author Greg J Preece <support@txttools.co.uk>
 * @version 2009110512
 * @since 2008103012
 */

require_once('../../../config.php');
error_reporting(0);

?>

/**
 * Adds a "mail merge" %TAG% to the message text
 */
function addMessageTag(tag) {

    var textbox = $('#messageText');
    textbox.val(textbox.val() + tag);
    textbox.keyup();  // Trigger message box handler
    textbox.focus();

}

function updateScheduleString() {

    $('#schedule2').attr('checked', 'checked');
    $("input[@name='schedule']:checked").val('schedule');
    $('#confirmSchedule').text(buildScheduleString());

}

/**
 * Moves one or more contacts from the list of contacts
 * on the left to the recipients list on the right
 */
function selectMultiple() {

    // Copy options to recipients list
    var source = $('#' + lastSourceBox);
    source.copyOptions('#finalRecipients', 'selected', false, true);
    source.copyOptions('#confirmRecipients', 'selected', false, true);

    // Remove options from original list
    var selected = source.selectedValues();
    $.each(selected, function(intIndex) {
        source.removeOption(this);
    });

    // Sort both lists
    $('#finalRecipients').sortOptions();
    $('#confirmRecipients').sortOptions();

}

/**
 * Generates a new contact based on user input and
 * adds them to the recipients list
 */
function addAdditionalRecipient() {

        // Grab number from form
        var number = $('#addnumber').val();
        var firstName = $('#addfirstname').val();
        var lastName = $('#addlastname').val();

        var errorstring = '';

        // Check that all required fields have been filled
        if (number == '')
            errorstring += '<?php echo(get_string('errornonumber', 'block_moodletxt')); ?>\n';

        if (firstName == '')
            errorstring += '<?php echo(get_string('errornofirstname', 'block_moodletxt')); ?>\n';

        if (lastName == '')
            errorstring += '<?php echo(get_string('errornolastname', 'block_moodletxt')); ?>\n';

        if (errorstring != '') {

            alert('<?php echo(get_string('errorlabel', 'block_moodletxt')); ?>\n' + errorstring);

        } else {

            // Mash additional contact details together to generate a form value
            var numberval = 'add#' + number + '#' + lastName + '#' + firstName;

            // Copy number to recipient lists and reset form
            $('#finalRecipients').addOption(numberval, lastName + ', ' + firstName + ' (' + number + ')');
            $('#confirmRecipients').addOption(numberval, lastName + ', ' + firstName + ' (' + number + ')');

            // Sort both lists
            $('#finalRecipients').sortOptions();
            $('#confirmRecipients').sortOptions();

            $('#addfirstname').val('');
            $('#addlastname').val('');
            $('#addnumber').val('');

        }

}

/**
 * Builds a value string from the <select> boxes used
 * to specify a scheduling date/time
 */
function buildScheduleString() {

    var scheduleString = 'on ';

    scheduleString += zeroPad($('#menuschedule_day').val(), 2) + '/';
    scheduleString += zeroPad($('#menuschedule_month').val(), 2) + '/';
    scheduleString += $('#menuschedule_year').val() + ' (dd/mm/yyyy) at ';
    scheduleString += zeroPad($('#menuschedule_hour').val(), 2) + ':';
    scheduleString += zeroPad($('#menuschedule_minute').val(), 2) + '.';

    return scheduleString;

}

/**
 * Holds the id of the last select box
 * clicked in the left hand pane, so
 * the transfer buttons know which one to use
 */
var lastSourceBox = null;

var currentTab = 0;


/**
 * When document is ready, begin binds!
 */
$(document).ready(function(){

    // Set up wizard interface using the excellent JFlow
    $("#navigator").jFlow({
        slides: "#mySlides",
        controller: ".jFlowControl", // must be class, use . sign
        slideWrapper : "#jFlowSlide", // must be id, use # sign
        menuCurrentClass : "menuCurrent",
        menuDoneClass : "menuDone",
        width: "90%",
        height: "550px",
        duration: 400,
        prev: ".jFlowPrev", // must be class, use . sign
        next: ".jFlowNext" // must be class, use . sign
    });


    /*
     ************************************************************
     **************************SLIDE 1***************************
     ************************************************************
     */

    /**
     * Set up tabbing system for different recipient types
     */
    $('#tabs-nav li').each(function(intIndex) {

        // On load, hide all tabs but the first one
        if (intIndex > 0)
            $('#tab-' + (intIndex + 1)).hide();

        // Create onclick function to show selected tab
        $(this).bind('click', function(event) {
            currentTab = intIndex + 1;
            $('.sendtab').hide();
            $('#tab-' + currentTab).show();
            $(this).parents('ul').find('li').each(function(intIndex2){ $(this).removeClass('selected') });  // Deselect all other tabs
            $(this).addClass('selected');
        });
    });


    /**
     * Set handler for phone number source boxes -
     * when selected, a box should highlight itself
     * and set itself as the box to copy details from
     */
    $('.sourceBox').each(function(intIndex) {
        $(this).bind('click focus', function(event) {

            // Set this select box as the source box
            lastSourceBox = this.id;

            // Enter the recursive dimension!
            // Kill borders on other selects
            $('.sourceBox').each(function(intIndex) {
                $(this).css('border','1px #000000 solid');
            });

            // Set border to show focus
            $(this).css('border','1px #FF0000 solid');

        });
    });

    // Set user recipient box as the initially selected one
    $('#userList').focus();

    // Auto-validation for names and numbers
    $('input#addfirstname').keypress(function(event) {
        return validateNameInput(event);
    });

    $('input#addlastname').keypress(function(event) {
        return validateNameInput(event);
    });

    $('input#addnumber').keypress(function(event) {
        return validatePhoneInput(event);
    });

    /**
     * Handler for adding additional numbers to the recipient list
     */
    $('#select_newnumber').click(function(event) {

    });

    /**
     * Handler for the select button - copies entries
     * from source boxes to recipient list
     */
    $('input#select_multiple').click(function(event) {

        if (currentTab == 3) {
            addAdditionalRecipient();
        } else {
            selectMultiple();
        }

    });

    /**
     * Handler for the deselect button - copies
     * entries back from recipients list to source
     * boxes
     */
    $('input#deselect_multiple').click(function(event) {

        var finalList = $('#finalRecipients'); // Save on lookups
        var confirmList = $('#confirmRecipients');
        var selected = finalList.selectedValues();

        // Declare arrays to hold selected records
        var userArray = new Array();
        var userGroupArray = new Array();
        var abArray = new Array();
        var abGroupArray = new Array();

        /*
         * This section checks the prefix on a recipient
         * to determine which source list to copy it back to
         */
        $.each(selected, function(intIndex) {

            var recipientType = this.split('#')[0];

            switch(recipientType) {

                case 'u':
                    userArray[userArray.length] = this;
                    break;
                case 'ug':
                    userGroupArray[userGroupArray.length] = this;
                    break;
                case 'ab':
                    abArray[abArray.length] = this;
                    break;
                case 'abg':
                    abGroupArray[abGroupArray.length] = this;
                    break;

            }

        });

        // Copy options back to source list
        finalList.copyOptions($('#userList'), userArray, false, true);
        finalList.copyOptions($('#userGroupList'), userGroupArray, false, true);
        finalList.copyOptions($('#abList'), abArray, false, true);
        finalList.copyOptions($('#abGroupList'), abGroupArray, false, true);

        // Remove options from recipient list
        $.each(selected, function(intIndex) {
            finalList.removeOption(this);
            confirmList.removeOption(this);
        });

        // Sort all lists
        $('#userList').sortOptions();
        $('#uerGroupList').sortOptions();
        $('#abList').sortOptions();
        $('#abGroupList').sortOptions();

    });

    /**
     * Basic handler - makes sure that the colour key
     * options cannot be delected by the user
     */
    $('select#colourkey').click(function(event) {
        $(this).deselectAll();
    });
    $('select#colourkey').change(function(event) {
        $(this).deselectAll();
    });

    /*
     ************************************************************
     **************************SLIDE 2***************************
     ************************************************************
     */

    /**
     * Handler for the message box - computes stats and whatnot
     * while user is typing
     */
    $('#messageText').keyup(function(event) {
        var messageLength = $('#messageText').val().length;
        $('#charsUsed').val(messageLength);
        $('#confirmCharsUsed').text(messageLength);
        $('#confirmMessagesUsed').text(Math.ceil(messageLength / 160));
        $('#confirmMessage').val($('#messageText').val());
    });

    /**
     * Message templates handler - when user selects a template,
     * it is copied to the message box
     */
    $('#messageTemplates').change(function(event) {
        var templateID = $('#messageTemplates').val();
        var messageBox = $('#messageText');
        if (templateID > 0) {

            messageBox.val(userTemplates[templateID]);
            messageBox.keyup(); // Trigger message box handler

        } else {

            messageBox.val('');
            messageBox.keyup();

        }
    });

    /**
     * Signature checkbox handler - when selected, signature is
     * added to the message
     */
    $('#addSig').change(function(event) {
        var textbox = $('#messageText');

        // If checked, append sig
        if ($('#addSig').attr('checked')) {
            textbox.val(textbox.val() + userSignature);

        // If unchecked, remove length of sig from end of message
        } else {
            var somestring = textbox.val();
            textbox.val(somestring.substring(0, (somestring.length - userSignature.length)));
        }
        textbox.keyup();  // Trigger message box handler
    });

    /**
     * Tag handler for first name
     */
    $('input#tagFirstName').click(function(event) {
        addMessageTag('%FIRSTNAME%');
    });

    /**
     * Tag handler for surname
     */
    $('input#tagLastName').click(function(event) {
       addMessageTag('%LASTNAME%');
    });

    /**
     *  Tag handler for full name
     */
    $('input#tagFullName').click(function(event) {
       addMessageTag('%FULLNAME%');
    });

    /*
     ************************************************************
     **************************SLIDE 3***************************
     ************************************************************
     */

    /**
     * Set handler for Time-to-live field - copy
     * TTL selection to confirm screen on change
     */
    $('#ttl').change(function(event) {
        $('#confirmTTL').text($(this).val() + ' hours.');
    });

    /**
     * Menu scheduling handler - when schedule selection
     * is changed, create schedule string
     */
    $('select.menuschedule').each(function(intIndex) {

        $(this).change(function(event) {

            if ($("input[@name='schedule']:checked").val() != "now")
                $('#confirmSchedule').text(buildScheduleString());

        });

    });

    $('#menuschedule_day').change(function(event) {
        updateScheduleString();
    });

    $('#menuschedule_month').change(function(event) {
        updateScheduleString();
    });

    $('#menuschedule_year').change(function(event) {
        updateScheduleString();
    });

    $('#menuschedule_hour').change(function(event) {
        updateScheduleString();
    });

    $('#menuschedule_minute').change(function(event) {
        updateScheduleString();
    });

    /**
     * Handler for txttools accounts list - show
     * account description
     */
    $('#txttoolsaccount').change(function(event) {
        var accountid = $('#txttoolsaccount').val();
        $('#accountDescription').text(accountDescriptions[accountid]);
    });

    /**
     * Set handler for scheduling <select> boxes.  When
     * the user selects a scheduling time, copy that
     * time to the confirmation screen
     */
    $("input[@name='schedule']").change(function(event) {

        // Get whether or not user is scheduling
        var value = $("input[@name='schedule']:checked").val();

        // If sending now, say so.  If not, copy datetime to confirmation
        if (value == "now")
            $('#confirmSchedule').text('immediately.');
        else
            $('#confirmSchedule').text(buildScheduleString());

    });

    /*
     ************************************************************
     **************************SLIDE 4***************************
     ************************************************************
     */

    /**
     * Prevent user from selecting recipients on the confirmation page
     */
    $('select#confirmRecipients').click(function(event) {
        $(this).deselectAll();
    });
    $('select#confirmRecipients').change(function(event) {
        $(this).deselectAll();
    });


    /**
     * Form submission handler - when form is submitted,
     * check input to see if it's a bit crap
     */
    $('form#messageForm').submit(function() {

        var errorArray = new Array();

        // Check recipients have been selected
        if ($('#finalRecipients').allValues().length == 0)
            errorArray[errorArray.length] = '<?php echo(get_string('errornorecipientsselected', 'block_moodletxt')); ?>';

        // Check message has been entered
        if ($('#messageText').val().length == 0)
            errorArray[errorArray.length] = '<?php echo(get_string('errornomessage', 'block_moodletxt')); ?>';

        // Echo errors
        if (errorArray.length > 0) {

            var errorString = '<?php echo(get_string('errorlabel', 'block_moodletxt')); ?>\n\n' + errorArray.join('\n');
            alert(errorString);

            return false;

        } else {

            $('#finalRecipients').selectAll();
            return true;

        }

    });

    /**
     * Trigger a whole buncha stuff when the page is first loaded,
     * to make sure everything is set up properly
     */
    $('#messageText').keyup();
    $('#tabs-nav li:first').trigger('click');
    $('#ttl').trigger('change');
    $("input[@name='schedule']:first").trigger('change');
    $('#txttoolsaccount').trigger('change');
    $('select#abList').sortOptions();

});