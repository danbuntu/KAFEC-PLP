/**
 * jQuery script for "add contact" page
 * @author Greg J Preece <support@txttools.co.uk>
 * @version 2008112812
 * @since 2008112812
 */

function shiftOptions(sourceid, destinationid) {

    var sourceObject = $(sourceid);
    var destinationObject = $(destinationid);

    // Copy options to recipients list
    sourceObject.copyOptions(destinationid, 'selected', false, true);

    // Remove options from original list
    var selected = sourceObject.selectedValues();
    $.each(selected, function(intIndex) {
        sourceObject.removeOption(this);
    });

    // Sort both lists
    sourceObject.sortOptions();
    destinationObject.sortOptions();

}

$(document).ready(function() {

    $('#select_multiple').click(function(event) {

        shiftOptions('#potentialGroups', '#selectedGroups');
        
    });

    $('#deselect_multiple').click(function(event) {

        shiftOptions('#selectedGroups', '#potentialGroups');

    });

    $('form#addcontact').submit(function() {

       $('#selectedGroups').selectAll();

    });

    $('input[name=phoneno]').keypress(function(event) {

        /*
         * 48 - 57 are numbers
         * 43 is the + sign
         * 8 is backspace
         * 0 is for system keys
         */

        if ((event.which >= 48 && event.which <= 57) ||
            event.which == 43 || 
            event.which == 8 ||
            event.which == 0) {

            return true;

        } else {

            return false;

        }
       
    });

});