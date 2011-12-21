<?php // $Id: index.php,v 1.7 2007/09/03 12:23:36 jamiesensei Exp $
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: index.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: This page lists all the instances of easyvoter in a particular course
////////////////////////////////////////////////////////////////////////////

    require_once("../../config.php");
    require_once("lib.php");

    $id = required_param('id', PARAM_INT);   // course

    if (! $course = get_record("course", "id", $id)) {
        print_error('invalidrequest');
    }

    require_login($course->id);

    add_to_log($course->id, "easyvoter", "view all", "index.php?id=$course->id", "");


/// Get all required stringseasyvoter

    $streasyvoters = get_string("modulenameplural", "easyvoter");
    $streasyvoter  = get_string("modulename", "easyvoter");


/// Print the header

	$navlinks = array();
	$navlinks[] = array('name' => $streasyvoters, 'link' => '', 'type' => 'activity');
	$navigation = build_navigation($navlinks);
	
	print_header_simple("$streasyvoters", "", $navigation, "", "", true, "", navmenu($course));

/// Get all the appropriate data

    if (! $easyvoters = get_all_instances_in_course("easyvoter", $course)) {
        notice("There are no easyvoters", "../../course/view.php?id=$course->id");
        die;
    }

/// Print the list of instances (your module will probably extend this)

    $timenow = time();
    $strname  = get_string("name");
    $strweek  = get_string("week");
    $strtopic  = get_string("topic");

    if ($course->format == "weeks") {
        $table->head  = array ($strweek, $strname);
        $table->align = array ("center", "left");
    } else if ($course->format == "topics") {
        $table->head  = array ($strtopic, $strname);
        $table->align = array ("center", "left", "left", "left");
    } else {
        $table->head  = array ($strname);
        $table->align = array ("left", "left", "left");
    }

    foreach ($easyvoters as $easyvoter) {
        if (!$easyvoter->visible) {
            //Show dimmed if the mod is hidden
            $link = "<a class=\"dimmed\" href=\"view.php?id=$easyvoter->coursemodule\">$easyvoter->name</a>";
        } else {
            //Show normal if the mod is visible
            $link = "<a href=\"view.php?id=$easyvoter->coursemodule\">$easyvoter->name</a>";
        }

        if ($course->format == "weeks" or $course->format == "topics") {
            $table->data[] = array ($easyvoter->section, $link);
        } else {
            $table->data[] = array ($link);
        }
    }

    echo "<br />";

    print_table($table);

/// Finish the page

    print_footer($course);

?>
