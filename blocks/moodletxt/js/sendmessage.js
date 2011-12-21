var $messageText;
var $finalRecipients;
var $confirmRecipients;

var $additionalContactNumber;
var $additionalContactFirstName;
var $additionalContactLastName;

/**
 * Holds the id of the last select box
 * clicked in the left hand pane, so
 * the transfer buttons know which one to use
 */
var $lastSourceBox = null;

var currentTab = 0;
var currentSlide = 1;

var glowSlides = new Array();
var fadeColour = '#FF0000';

/**
 * Adds a "mail merge" %TAG% to the message text
 */
function addMessageTag(tag) {
    
    $messageText.insertAtCaret(tag).keyup().focus();

}

            function glowErrors() {

                jQuery.each(glowSlides, function() {
                    var originalBG = $('#nav' + this).css('background-color');
                    $('#nav' + this).animate({backgroundColor:fadeColour}, 750, 'linear');
                    $('#nav' + this).animate({backgroundColor:originalBG}, 750, 'linear', glowErrors);
                });

            }

function updateScheduleString() {

    $('#schedule2').attr('checked', 'checked');
    $("input[name=schedule]:checked").val('schedule');
    $('#confirmSchedule').text(buildScheduleString());

}

/**
 * Moves one or more contacts from the list of contacts
 * on the left to the recipients list on the right
 */
function selectMultiple() {

    // Copy options to recipients list
    $lastSourceBox.copyOptions($finalRecipients, 'selected', false, true);
    $lastSourceBox.copyOptions($confirmRecipients, 'selected', false, true);

    // Remove options from original list
    var selected = $lastSourceBox.selectedValues();
    $.each(selected, function(intIndex) {
        $lastSourceBox.removeOption(this);
    });

    // Sort both lists
    $finalRecipients.sortOptions();
    $confirmRecipients.sortOptions();

}

/**
 * Generates a new contact based on user input and
 * adds them to the recipients list
 */
function addAdditionalRecipient() {

        // Grab number from form
        var number = $additionalContactNumber.val();
        var firstName = $additionalContactFirstName.val();
        var lastName = $additionalContactLastName.val();

        var errorstring = '';

        // Check that all required fields have been filled
        if (number == '')
            errorstring += language['errornonumber'] + '\n';

        if (firstName == '')
            errorstring += language['errornofirstname'] + '\n';

        if (lastName == '')
            errorstring += language['errornolastname'] + '\n';

        if (errorstring != '') {

            alert(language['errorlabel'] + '\n' + errorstring);

        } else {

            // Mash additional contact details together to generate a form value
            var numberval = 'add#' + number + '#' + lastName + '#' + firstName;

            // Copy number to recipient lists and reset form
            $finalRecipients.addOption(numberval, lastName + ', ' + firstName + ' (' + number + ')');
            $confirmRecipients.addOption(numberval, lastName + ', ' + firstName + ' (' + number + ')');

            // Sort both lists
            $finalRecipients.sortOptions();
            $confirmRecipients.sortOptions();

            $additionalContactFirstName.val('');
            $additionalContactLastName.val('');
            $additionalContactNumber.val('');

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

function animateToSlide(slideNumber) {

    // Animate slider
    var percentage = -100 * (parseInt(slideNumber) - 1);
    $('#slidePlate').animate({left:percentage + '%'});
    
    currentSlide = slideNumber;

    // Update navigator
    $('ul#navigator li').each(function(index) {
        $(this).removeClass('menuCurrent');
    });

    $('ul#navigator li:nth-child(' + currentSlide + ')').addClass('menuCurrent');

}

/**
 * When document is ready, begin binds!
 */
$(document).ready(function(){

    $messageText = $('textarea#messageText');
    $finalRecipients = $('select#finalRecipients');
    $confirmRecipients = $('select#confirmRecipients');

    $additionalContactNumber = $('#addnumber');
    $additionalContactFirstName = $('#addfirstname');
    $additionalContactLastName = $('#addlastname');

    /*
     ************************************************************
     ************************NAVIGATION**************************
     ************************************************************
     */

    $('ul#navigator li').each(function(index) {
        $(this).click(function(event) {
            animateToSlide(index + 1);
        });
    });

    $('span.nextButton').click(function(event) {
        animateToSlide(currentSlide + 1);
    });

    $('span.prevButton').click(function(event) {
        animateToSlide(currentSlide - 1);
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
            $lastSourceBox = $(this);

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

        var selected = $finalRecipients.selectedValues();

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
        $finalRecipients.copyOptions($('#userList'), userArray, false, true);
        $finalRecipients.copyOptions($('#userGroupList'), userGroupArray, false, true);
        $finalRecipients.copyOptions($('#abList'), abArray, false, true);
        $finalRecipients.copyOptions($('#abGroupList'), abGroupArray, false, true);

        // Remove options from recipient list
        $.each(selected, function(intIndex) {
            $finalRecipients.removeOption(this);
            $confirmRecipients.removeOption(this);
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
    $('select#colourkey').bind('click focus blur change', function(event) {
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
    $messageText.keyup(function(event) {
        updateMessageBox(this);
        $('#confirmMessage').val($(this).val());
    });

    /**
     * Message templates handler - when user selects a template,
     * it is copied to the message box
     */
    $('#messageTemplates').change(function(event) {
        var templateID = $('#messageTemplates').val();

        if (templateID > 0) {
            $messageText.val(userTemplates[templateID]);
        } else {
            $messageText.val('');
        }

        $messageText.keyup(); // Trigger message box handler

    });

    /**
     * Signature checkbox handler - when selected, signature is
     * added to the message
     */
    $('#addSig').change(function(event) {

        // If checked, append sig
        if ($('#addSig').attr('checked')) {
            $messageText.val($messageText.val() + userSignature);

        // If unchecked, remove length of sig from end of message
        } else {
            var somestring = $messageText.val();
            $messageText.val(somestring.substring(0, (somestring.length - userSignature.length)));
        }
        $messageText.keyup();  // Trigger message box handler
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
     * Menu scheduling handler - when schedule selection
     * is changed, create schedule string
     */
    $('select.menuschedule').each(function(intIndex) {

        $(this).change(function(event) {

            if ($("input[name=schedule]:checked").val() != "now")
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
    $("input[name=schedule]").change(function(event) {

        // Get whether or not user is scheduling
        var value = $("input[name=schedule]:checked").val();

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
    $confirmRecipients.click(function(event) {
        $(this).deselectAll();
    });
    $confirmRecipients.change(function(event) {
        $(this).deselectAll();
    });


    /**
     * Form submission handler - when form is submitted,
     * check input to see if it's a bit crap
     */
    $('form#messageForm').submit(function() {

        var errorArray = new Array();

        // Check recipients have been selected
        if ($finalRecipients.allValues().length == 0)
            errorArray[errorArray.length] = language['errornorecipientsselected'] + '\n';

        // Check message has been entered
        if ($messageText.val().length == 0)
            errorArray[errorArray.length] = language['errornomessage'] + '\n';

        // Echo errors
        if (errorArray.length > 0) {

            var errorString = language['errorlabel'] + '\n\n' + errorArray.join('\n');
            alert(errorString);

            return false;

        } else {

            $('#sendMessage').attr('disabled', 'disabled');
            $finalRecipients.selectAll();
            return true;

        }

    });

    /**
     * Trigger a whole buncha stuff when the page is first loaded,
     * to make sure everything is set up properly
     */
    $messageText.keyup();
    $('#tabs-nav li:first').trigger('click');
    $("input[name=schedule]:first").trigger('change');
    $('#txttoolsaccount').trigger('change');
    $('select#abList').sortOptions();
    glowErrors();

});