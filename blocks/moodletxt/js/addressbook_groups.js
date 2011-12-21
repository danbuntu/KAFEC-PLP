/**
 * jQuery script for "manage groups" page
 * @author Greg J Preece <support@txttools.co.uk>
 * @version 2008120312
 * @since 2008120312
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

        shiftOptions('#potentialContacts', '#selectedContacts');

    });

    $('#deselect_multiple').click(function(event) {

        shiftOptions('#selectedContacts', '#potentialContacts');

    });

    $('form#updategroup').submit(function() {

       $('#selectedContacts').selectAll();

    });

    $('select#group').change(function(event) {

        var potential = $('#potentialContacts');
        var selected = $('#selectedContacts');

        // First, reset form
        selected.copyOptions(potential, 'all', false, true);

        var killList = selected.allValues();
        $.each(killList, function(intIndex) {
            selected.removeOption(this);
        });

        if ($(this).val() > 0 && groupMembers[$(this).val()].length > 0) {

            potential.copyOptions(selected, groupMembers[$(this).val()], false, true);

            $.each(groupMembers[$(this).val()], function(intIndex) {
                potential.removeOption(this);
            })

        }

        potential.sortOptions();
        selected.sortOptions();
        
    });

});