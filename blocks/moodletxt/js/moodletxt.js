<!--
//<![CDATA[

/**
 * jQuery scripting for the user control panel
 *
 * @author Greg J Preece <support@txttools.co.uk>
 * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
 * @version 2010123101
 * @since 2006102512
 */

// Holds jQuery object for main template box
var $templateText;


/**
 * Adds a "mail merge" %TAG% to the message text
 * @param string tag Tag to add to message
 * @version 2010062412
 * @since 2010062412
 */
function addMessageTag(tag) {

    $templateText.insertAtCaret(tag).keyup().focus();

}

$(document).ready(function() {

    // Man, some of the IDs on this page are confusing.
    // Oh well, let's abstract them!
    var $templateList           = $('select#currenttemplates');
    var $templateFormSwitch     = $('input#templateEditFormID');
    var $templateID             = $('input#editTemplateID');
    $templateText               = $('textarea#templateEdit');

    var $templateEditButton     = $('input#editTemplateButton');
    var $templateEditLegend     = $('legend#templateEditLegend');
    var $templateSubmitButton   = $('input#templateEditSubmit');

    /**
     * When a user presses the edit button,
     * load the selected template into the editor
     * for updating
     */
    $templateEditButton.click(function(event) {

        var selectedTemplate = $templateList.val();

        if (selectedTemplate < 1) {
            alert(language['settingsnotemplateselected']);
            return false;
        }

        // Set form elements
        $templateFormSwitch.val('edittemplate');
        $templateID.val(selectedTemplate);
        $templateText.val(templateArray[selectedTemplate]).keyup();

        // Update GUI
        $templateEditLegend.text(language['settingslegendedittemplate']);
        $templateSubmitButton.val(language['settingsedittemplate']);

    });

    /**
     * Handler for the message box - computes stats and whatnot
     * while user is typing
     */
    $templateText.keyup(function(event) {
        updateMessageBox(this);
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

    // Update signature length counter
    // (Using the full fat unicode-checking badassery
    // here would be a bit overkill)
    $('input#signature').keyup(function(event) {
        $('input#sigCharsLeft').val(25 - $(this).val().length);
    });
    
    $('input#signature').keyup();
    $templateText.keyup();

});

//]]>
//-->