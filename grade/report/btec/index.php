<?php // $Id: index.php,v 1.65.2.15 2009/05/05 11:52:03 skodak Exp $

///////////////////////////////////////////////////////////////////////////
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.org                                            //
//                                                                       //
// Copyright (C) 1999 onwards  Martin Dougiamas  http://moodle.com       //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////


require_once '../../../config.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once 'lib.php';

require_js(array('yui_yahoo', 'yui_dom', 'yui_event', 'yui_container', 'yui_connection', 'yui_dragdrop', 'yui_element'));


$courseid      = required_param('id', PARAM_INT);        // course id
$page          = optional_param('page', 0, PARAM_INT);   // active page
$perpageurl    = optional_param('perpage', 0, PARAM_INT);
$groupid       = optional_param('group', 0, PARAM_INT);

$sortitemid    = optional_param('sortitemid', 'lastname', PARAM_ALPHANUM); // sort by which grade item
$sortorder     = optional_param('sortorder', 0, PARAM_INT); // sortorder - 0 = ascending
$action        = optional_param('action', 0, PARAM_ALPHAEXT);
$move          = optional_param('move', 0, PARAM_INT);
$type          = optional_param('type', 0, PARAM_ALPHA);
$target        = optional_param('target', 0, PARAM_ALPHANUM);
$toggle        = optional_param('toggle', NULL, PARAM_INT);
$toggle_type   = optional_param('toggle_type', 0, PARAM_ALPHANUM);

/// basic access checks
if (!$course = get_record('course', 'id', $courseid)) {
    $print_error('nocourseid','', $CFG->wwwroot.'/course/view.php?id='.$courseid);
}
require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $course->id);

require_capability('gradereport/btec:view', $context);

$reporterror = '';
if ($CFG->enableoutcomes != 2) {  // Outcomes must be enabled for this report to have data
    $reporterror = 'outcomesdisabled';
}

// return tracking object
$gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'btec', 'courseid'=>$courseid, 'page'=>$page));

/// last selected report session tracking
if (!isset($USER->grade_last_report)) {
    $USER->grade_last_report = array();
}
if($reporterror == '') {
    // Remember to come back to this report if it didn't create an error
    $USER->grade_last_report[$course->id] = 'btec';
}

//first make sure we have proper final grades - this must be done before constructing of the grade tree
grade_regrade_final_grades($courseid);

// Perform actions
if (!empty($target) && !empty($action) && confirm_sesskey()) {
    grade_report_btec::process_action($target, $action);
}

$reportname = get_string('modulename', 'gradereport_btec');
// Initialise the btec report object
$report = new grade_report_btec($courseid, $gpr, $context, $page, $sortitemid);

// make sure separate group does not prevent view
if ($report->currentgroup == -2) {
    print_grade_page_head($COURSE->id, 'report', 'btec', $reportname, false, null, $buttons);
    print_heading(get_string("notingroup"));
    print_footer($course);
    exit;
}

/// processing posted grades & feedback here
if ($data = data_submitted() and confirm_sesskey() and has_capability('moodle/grade:edit', $context)) {
    $warnings = $report->process_data($data);
} else {
    $warnings = array();
}

// Override perpage if set in URL
if ($perpageurl) {
    $report->user_prefs['studentsperpage'] = $perpageurl;
}

$report->groupid = $groupid;
$report->sortitemid = $sortitemid;
$report->sortorder = $sortorder;

// final grades MUST be loaded after the processing
$numusers = $report->get_numusers();

// Print header
print_grade_page_head($COURSE->id, 'report', 'btec', $reportname, false, null);

// Print Groups menu
if (has_capability('moodle/grade:viewall', $context)) {
    groups_print_course_menu($course, $gpr->get_return_url('index.php?id='.$courseid, array('userid'=>0)));
}
echo '<div class="clearer"></div>';
// echo $report->get_toggles_html();

//show warnings if any
foreach($warnings as $warning) {
    notify($warning);
}

$studentsperpage = $report->get_pref('studentsperpage');
// Don't use paging if studentsperpage is empty or 0 at course AND site levels
if (!empty($studentsperpage)) {
    print_paging_bar($numusers, $report->page, $studentsperpage, $report->pbarurl);
}

if ($reporterror == '') {
    $reporthtml = '<script src="functions.js" type="text/javascript"></script>';
    if (has_capability('moodle/grade:viewall', $context)) {
        // Teacher view
        $reporthtml .= $report->get_html_for_all_users();
    } else {
        // Show student view
        $reporthtml .= $report->get_html_for_this_user();
    }
    $reporthtml .= $report->get_closing_html();
} else {
    $reporthtml = '<div class="box errorbox errorboxcontent"><p class="errormessage">'.get_string($reporterror,'gradereport_btec').'</p>
                    <p class="errorcode"><a href="http://docs.moodle.org/en/error/gradereport_btec/'.$reporterror.'">'.get_string('moreinformation').'</a></p></div>
                    <div class="continuebutton"><div class="singlebutton"><form action="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'" method="get"><div><input type="hidden" name="id" value="'.$courseid.'" /><input type="submit" value="'.get_string('continue').'" /></div></form></div></div>';
}

echo $report->keyhtml;
echo $reporthtml;

// prints paging bar at bottom for large pages
if (!empty($studentsperpage) && $studentsperpage >= 20) {
    print_paging_bar($numusers, $report->page, $studentsperpage, $report->pbarurl);
}


echo '<div id="hiddentooltiproot">tooltip panel</div>';
// Print YUI tooltip code
?>
<script type="text/javascript">
//<![CDATA[

YAHOO.namespace("btecreport");

function init() {
    // attach event listener to the table for mouseover and mouseout
    var table = document.getElementById('user-grades');
    YAHOO.util.Event.on(table, 'mouseover', YAHOO.btecreport.mouseoverHandler);
    YAHOO.util.Event.on(table, 'mouseout', YAHOO.btecreport.mouseoutHandler);

    // Make single panel that can be dynamically re-rendered with the right data
    YAHOO.btecreport.panelEl = new YAHOO.widget.Panel("tooltipPanel", {

        draggable: false,
        visible: false,
        close: false,
        preventcontextoverlap: true

    });

    YAHOO.btecreport.panelEl.render(table);

    document.body.className += ' yui-skin-sam';

}

YAHOO.btecreport.mouseoverHandler = function (e) {

    var tempNode = '';
    var searchString = '';
    var tooltipNode = '';

    // get the element that we just moved the mouse over
    var elTarget = YAHOO.util.Event.getTarget(e);


    // if it was part of the yui panel, we don't want to redraw yet
    searchString = /fullname|itemname|feedback/;
    if (elTarget.className.search(searchString) > -1) {
        return false;
    }

    // move up until we are in the actual cell, not any other child div or span
    while (elTarget.id != 'user-grades') {
        if(elTarget.nodeName.toUpperCase() == "TD") {
            break;
        } else {
            elTarget = elTarget.parentNode;
        }
    }

    // only make a tooltip for cells with grades
    if (elTarget.className.search('grade cell') > -1) {

        // each time we go over a new cell, we need to put it's tooltip into a div to stop it from
        // popping up on top of the panel.

        // don't do anything if we have already made the tooltip div
        var makeTooltip = true
        for (var k=0; k < elTarget.childNodes.length; k++) {
            if (typeof(elTarget.childNodes[k].className) != 'undefined') {
                if (elTarget.childNodes[k].className.search('tooltipDiv') > -1) {
                    makeTooltip =  false;
                }
            }
        }

        // if need to, make the tooltip div and append it to the cell
        if (makeTooltip) {
            tempNode = document.createElement("div");
            tempNode.className = "tooltipDiv";
            tempNode.innerHTML = elTarget.title;
            elTarget.appendChild(tempNode);
            elTarget.title = null;
        }

        // Get the tooltip div
        elChildren = elTarget.childNodes;
        for (var m=0; m < elChildren.length; m++) {
            if (typeof(elChildren[m].className) != 'undefined') {
                if (elChildren[m].className.search('tooltipDiv') > -1) {
                    tooltipNode = elChildren[m];
                    break;
                }
            }
        }
        //build and show the tooltip
        YAHOO.btecreport.panelEl.setBody(tooltipNode.innerHTML);
        YAHOO.btecreport.panelEl.render(elTarget);
        YAHOO.btecreport.panelEl.show()
    }
}

// only hide the overlay if the mouse has not moved over it
YAHOO.btecreport.mouseoutHandler = function (e) {

    var classVar = '';
    var searchString = '';
    var newTargetClass = '';
    var newTarget = YAHOO.util.Event.getRelatedTarget(e);

    // deals with an error if the mouseout event is over the lower scrollbar
    try {
        classVar = newTarget.className;
    } catch (err) {
        YAHOO.btecreport.panelEl.hide()
        return false;
    }

    // if we are over any part of the panel, do not hide
    // do this by walking up the DOM till we reach table level, looking for panel tag
    while ((typeof(newTarget.id) == 'undefined') || (newTarget.id != 'user-grades')) {

        try {
            newTargetClass = newTarget.className;
        } catch (err) {
            // we've gone over the scrollbar again
            YAHOO.btecreport.panelEl.hide()
            return false;
        }
        searchString = /yui-panel|grade cell/;
        if (newTargetClass.search(searchString) > -1) {
            // we're in the panel so don't hide it
            return false;
        }

        if (newTarget.nodeName.toUpperCase() == "HTML") {
            // we missed the user-grades table altogether by moving down off screen to read a long one
            YAHOO.btecreport.panelEl.hide()
            break;
        }

        newTarget = newTarget.parentNode;
    }

    // no panel so far and we went up to the
    YAHOO.btecreport.panelEl.hide()

}


YAHOO.util.Event.onDOMReady(init);
//]]>
</script>
<?php

print_footer($course);

?>
