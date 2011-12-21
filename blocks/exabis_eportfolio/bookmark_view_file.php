<?php 
/***************************************************************
*  Copyright notice
*
*  (c) 2006 exabis internet solutions <info@exabis.at>
*  All rights reserved
*
*  You can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  This module is based on the Collaborative Moodle Modules from
*  NCSA Education Division (http://www.ncsa.uiuc.edu)
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
?>
<?php

    require_once("../../config.php");
    require_once("{$CFG->dirroot}/blocks/exabis_eportfolio/lib.php");

	$courseid = optional_param('courseid', 0, PARAM_INT);
	$bookid = optional_param('bookid', 0, PARAM_INT);
	$action = optional_param('action', '', PARAM_ALPHA);
	$commentid = optional_param('commentid', 0, PARAM_INT);
	$deletecomment = optional_param('deletecomment', 0, PARAM_INT);
    $original = optional_param('original', 0, PARAM_INT);

    $context = get_context_instance(CONTEXT_SYSTEM);
    
    require_login($courseid);
    require_capability('block/exabis_eportfolio:use', $context);
    
    if (isset($USER->realuser)) {
        print_error("loginasmode","block_exabis_eportfolio");

    }
    
    if (! $course = get_record("course", "id", $courseid) ) {
        print_error("invalidcourseid","block_exabis_eportfolio");
    }
    
    // either it is your own bookmark:
    $bookmark = get_record('block_exabeporbookfile', 'id', $bookid, 'userid', $USER->id);
    if (!$bookmark) {
    	// it can also be shared to noone except to the userid of the current user
        $bookmark = get_record_sql("SELECT bf.*
                                    FROM {$CFG->prefix}block_exabeporbookfile bf
                                    LEFT JOIN {$CFG->prefix}block_exabeporsharfile bfs ON bf.id=bfs.bookid
                                    WHERE bf.shareall='0'
                                    AND bf.id='$bookid'
                                    AND bfs.userid='{$USER->id}'");
        
        if(!$bookmark) {
            // last chance: it can be shared to all and i am no exception:
            $bookmark = get_record_sql("SELECT bf.*
                                        FROM {$CFG->prefix}block_exabeporbookfile bf
                                        LEFT JOIN (
                                          SELECT * FROM {$CFG->prefix}block_exabeporsharfile WHERE userid='{$USER->id}'
                                        ) bfs ON bf.id = bfs.bookid
                                        WHERE bf.shareall='1'
                                        AND bf.id='$bookid'
                                        AND bfs.userid IS NULL");
            if(!$bookmark) {
               // in this case this bookmark doesn't belong to me and no one shared it to me.
               print_error("bookmarknotfound","block_exabis_eportfolio");	 
            }
        }
    }
    
    
    if($deletecomment == 1) {
        if (!confirm_sesskey()) {
            print_error("badsessionkey","block_exabis_eportfolio");	                
        }
    	if(count_records("block_exabeporcommfile", "id", $commentid, "userid", $USER->id, "bookmarkid", $bookid) == 1) {
    		delete_records("block_exabeporcommfile", "id", $commentid, "userid", $USER->id, "bookmarkid", $bookid);
    	}
    	else {
            print_error("commentnotfound","block_exabis_eportfolio");	    	
    	}
    }
    
	require_once("{$CFG->dirroot}/blocks/exabis_eportfolio/bookmark_edit_form.php");
	$commentseditform = new comment_edit_form();
	
	
	if ($commentseditform->is_cancelled());
	else if ($commentseditform->no_submit_button_pressed());
	else if ($fromform = $commentseditform->get_data()){
	    switch ($action) {
	        case 'add':
	            do_add($fromform, $commentseditform, "bookmark_view_file.php?courseid=$courseid&amp;bookid=$bookid", $courseid);
	            redirect("bookmark_view_file.php?courseid=$courseid&amp;bookid=$bookid&amp;original=$original");
	        break;
	    }
	}
	$newcomment = new stdClass();
	$newcomment->action = 'add';
	$newcomment->courseid = $courseid;
	$newcomment->timemodified = time();
	$newcomment->bookid = $bookid;
	$newcomment->userid = $USER->id;
	$newcomment->original = $original;
    
    /// printing the heading
    
	$strbookmarks = get_string("mybookmarks", "block_exabis_eportfolio");
	$strfiles = get_string("bookmarksfiles", "block_exabis_eportfolio");
    $strAction = get_string("view");
    
    if ($courseid == SITEID) {
	    if($original==0) {
			print_header("$SITE->shortname: $strbookmarks", $SITE->fullname /*$SITE->fullname*/,
			             "<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view.php?courseid={$courseid}\">" . strip_tags($strbookmarks) . "</a> ->
			             <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view_files.php?courseid={$courseid}\">" . $strfiles . "</a> ->
			             {$strAction}",
			             '', '', true);
	    }
	    else {
		    print_header(strip_tags($strbookmarks), $SITE->fullname,
		       "<a href=\"{$CFG->wwwroot}/course/view.php?id={$courseid}\">" . $course->shortname . "</a> ->
		        <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/viewpeople.php?courseid={$courseid}\">" . get_string("sharedpersons", "block_exabis_eportfolio") . "</a> ->
		        <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?courseid=$courseid?&amp;original=$original\">" . get_string("sharedbookmarks", "block_exabis_eportfolio") . "</a> ->
		        " . format_string($bookmark->name), "", "", true, "",
		        navmenu($course),"","");
	    }
	}
    else {
	    if($original==0) {
			print_header("$SITE->shortname: $strbookmarks", $course->fullname /*$SITE->fullname*/,
			             "<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view.php?courseid={$courseid}\">" . strip_tags($strbookmarks) . "</a> ->
			             <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view_files.php?courseid={$courseid}\">" . $strfiles . "</a> ->
			             {$strAction}",
			             '', '', true);
	    }
	    else {
		    print_header(strip_tags($strbookmarks), $course->fullname,
		       "<a href=\"{$CFG->wwwroot}/course/view.php?id={$courseid}\">" . $course->shortname . "</a> ->
		        <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/viewpeople.php?courseid={$courseid}\">" . get_string("sharedpersons", "block_exabis_eportfolio") . "</a> ->
		        <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?courseid=$courseid?&amp;original=$original\">" . get_string("sharedbookmarks", "block_exabis_eportfolio") . "</a> ->
		        " . format_string($bookmark->name), "", "", true, "",
		        navmenu($course),"","");
	    }
    }
    
    echo "<div class='block_eportfolio_center'>\n";
    
	show_file($bookmark);
	
	show_comments($bookid,$courseid, $bookmark);
	
	$commentseditform->set_data($newcomment);
	$commentseditform->display();
    
    if($original==0) {
    	// in this case the owner of the bookmark is viewing his own bookmark:
    	echo "<br /><a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/view_files.php?courseid=$courseid'>".get_string("back","block_exabis_eportfolio")."</a><br /><br />";
    }
    else {
    	echo "<br /><a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?courseid=$courseid&amp;original=$original'>".get_string("back","block_exabis_eportfolio")."</a><br /><br />";
    }
    
    echo "</div>";
    
    print_footer($course);
    die;
    
    function show_file($bookmark) {
		global $CFG;
    	print_heading(format_string($bookmark->name));
        if ( $bookmark->attachment ) {
            $filearea = block_exabis_eportfolio_file_area_name($bookmark);
            
            if ($CFG->slasharguments) {
                $ffurl = "{$CFG->wwwroot}/blocks/exabis_eportfolio/portfoliofile.php/$filearea/$bookmark->attachment";
            } else {
                $ffurl = "{$CFG->wwwroot}/blocks/exabis_eportfolio/portfoliofile.php?file=/$filearea/$bookmark->attachment";
            }

			print_box(block_exabis_eportfolio_print_file($ffurl, $bookmark->attachment, $bookmark->name) . format_text(nl2br($bookmark->intro)));
        }
        else {
    		print_box(format_text(nl2br($bookmark->intro)));
        }
    }
        
    function show_comments($bookmarkid, $courseid, $bookmark) {
    	$comments = get_records("block_exabeporcommfile", "bookmarkid", $bookmarkid, 'timemodified DESC');
    	if($comments) {
    		foreach ($comments as $comment) {
    			block_exabis_eportfolio_print_comment($comment, 'bookmark_view_file.php',$courseid, $bookmark);
    		}
    	}
    }
	    
	function do_add($post, $blogeditform, $courseid) {
	    global $CFG, $USER;
	
	    $post->userid       = $USER->id;
	    $post->timemodified = time();
	    $post->course = $courseid;
		$post->bookmarkid = $post->bookid;
		
	    // Insert the new blog entry.
	    if (insert_record('block_exabeporcommfile', $post)) {
	        add_to_log(SITEID, 'exabis_eportfolio', 'add', 'bookmark_view_file.php', $post->entry);
	
	    } else {
	        error('There was an error adding this post in the database');
	    }
	}
?>