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

	$id = optional_param('id', 0, PARAM_INT);
	$courseid = optional_param('courseid', 0, PARAM_INT);
	$action = optional_param("action", "", PARAM_ALPHA);
	$confirm = optional_param("confirm", "", PARAM_BOOL);

 	if (!confirm_sesskey()) {
        print_error("badsessionkey","block_exabis_eportfolio");
	}
    
    $context = get_context_instance(CONTEXT_SYSTEM);
    
    require_login($courseid);
    require_capability('block/exabis_eportfolio:use', $context);
    
    if (! $course = get_record("course", "id", $courseid) ) {
        print_error("invalidcourseid","block_exabis_eportfolio");
    }
    
    if(!block_exabis_eportfolio_has_categories($USER->id)) {
    	print_error("nocategories", "block_exabis_eportfolio", "view.php?courseid=" . $courseid);
    }


	if ($id) {
	    if (!$existing = get_record('block_exabeporbooklink', 'id', $id, 'userid', $USER->id)) {
            print_error("wronglinkid","block_exabis_eportfolio");
	    }

	    $returnurl = $CFG->wwwroot.'/blocks/exabis_eportfolio/view_external_links.php?courseid='.$courseid; //?userid='.$existing->userid;
	} else {
	    $existing  = false;
	    $returnurl = $CFG->wwwroot.'/blocks/exabis_eportfolio/view_external_links.php?courseid='.$courseid; //'index.php?userid='.$USER->id;
	}

	if ($action=='delete'){
	    if (!$existing) {
            print_error("incorrectlinksid","block_exabis_eportfolio");
	    }
	    if (data_submitted() and $confirm and confirm_sesskey()) {
	        do_delete($existing,$returnurl, $courseid);
	        redirect($returnurl);
	    } else {
	        $optionsyes = array('id'=>$id, 'action'=>'delete', 'confirm'=>1, 'sesskey'=>sesskey(), 'courseid'=>$courseid);
	        $optionsno = array('userid'=>$existing->userid, 'courseid'=>$courseid);
	        print_header("$SITE->shortname", $SITE->fullname);
	        // ev. noch eintrag anzeigen!!!
	        //blog_print_entry($existing);
	        echo '<br />';
	        notice_yesno(get_string("deleteextconfirm", "block_exabis_eportfolio"), 'add_external.php', 'view_external_links.php', $optionsyes, $optionsno, 'post', 'get');
	        print_footer();
	        die;
	    }
	}
	
	require_once("{$CFG->dirroot}/blocks/exabis_eportfolio/bookmark_edit_form.php");
	$exteditform = new external_edit_form(null, compact('existing'));
	
	if ($exteditform->is_cancelled()){
		redirect($returnurl);
	} else if ($exteditform->no_submit_button_pressed()) {
		die("nosubmitbutton");
	    //no_submit_button_actions($exteditform, $sitecontext);
	} else if ($fromform = $exteditform->get_data()){
	    switch ($action) {
	        case 'add':
	            do_add($fromform, $exteditform,$returnurl, $courseid);
	        break;

	        case 'edit':
	            if (!$existing) {
                    print_error("incorrectnoteid","block_exabis_eportfolio");
	            }
	            do_edit($fromform, $exteditform,$returnurl, $courseid);
	        break;
	        default :
                print_error("unknownaction","block_exabis_eportfolio");
	    }
	    redirect($returnurl);
	}

	$strAction = "";
	// gui setup
	$post = new stdClass();
	switch ($action) {
		case 'add':
		    $post->action       = $action;
	        $post->courseid     = $courseid;
			$strAction = get_string('new');
			break;
	    case 'edit':
	        if (!$existing) {
                print_error("wronglinkid","block_exabis_eportfolio");
	        }
	        $post->id           = $existing->id;
	        $post->name         = $existing->name;
	        $post->category     = $existing->category;
	        $post->intro        = $existing->intro;
	        $post->url          = $existing->url;
	        $post->action       = $action;
	        $post->courseid     = $courseid;

			$strAction = get_string('edit');
	        break;
	    default :
            print_error("unknownaction","block_exabis_eportfolio");
	}

	$strbookmarks = get_string("mybookmarks", "block_exabis_eportfolio");
	$strext = get_string("bookmarksexternal", "block_exabis_eportfolio");

    if ($courseid == SITEID) {
		print_header("$SITE->shortname: $strbookmarks", $SITE->fullname,
		             "<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view.php?courseid={$courseid}\">" . strip_tags($strbookmarks) . "</a> ->
		             <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view_external_links.php?courseid={$courseid}\">" . $strext . "</a> ->
		             {$strAction}",
		             '', '', true);
	}
    else {
		print_header("$SITE->shortname: $strbookmarks", $course->fullname,
		             "<a href=\"{$CFG->wwwroot}/course/view.php?id={$courseid}\">" . $course->shortname . "</a> ->
		             <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view.php?courseid={$courseid}\">" . strip_tags($strbookmarks) . "</a> ->
		             <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view_external_links.php?courseid={$courseid}\">" . $strext . "</a> ->
		             {$strAction}",
		             '', '', true);
    }

    print_heading("<img src=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/pix/externallinks.png\" width=\"16\" height=\"16\" alt='".get_string("externallink", "block_exabis_eportfolio")."' /> " . $strbookmarks . ": " . $strext) ;

    $currenttab = 'external';
    include("{$CFG->dirroot}/blocks/exabis_eportfolio/tabs.php");

	$exteditform->set_data($post);
	$exteditform->display();

	print_footer($course);
die;

/**
 * Update a link in the database
 */
function do_edit($post, $blogeditform,$returnurl, $courseid) {
    global $CFG, $USER;

    $post->timemodified = time();

    if (update_record('block_exabeporbooklink', $post)) {
        add_to_log(SITEID, 'bookmark', 'update', 'add_external.php?courseid='.$courseid.'&id='.$post->id.'&action=edit', $post->name);
    } else {
        print_error('updateposterror','block_exabis_eportfolio', $returnurl);
    }
}

/**
 * Write a new blog entry into database
 */
function do_add($post, $blogeditform,$returnurl, $courseid) {
    global $CFG, $USER;

    $post->userid       = $USER->id;
    $post->timemodified = time();
    $post->course = $courseid;
    // Insert the new blog entry.
    if ($id = insert_record('block_exabeporbooklink', $post)) {
        add_to_log(SITEID, 'bookmark', 'add', 'add_external.php?courseid='.$courseid.'&id='.$id.'&action=add', $post->name);

    } else {
        print_error('updateposterror', 'block_exabis_eportfolio', $returnurl);
    }

}

/**
 * Delete blog post from database
 */
function do_delete($post,$returnurl, $courseid) {

    $status = delete_records('block_exabeporbooklink', 'id', $post->id);
    
    add_to_log(SITEID, 'blog', 'delete', 'add_external.php?courseid='.$courseid.'&id='.$post->id.'&action=delete&confirm=1', $post->name);

    if (!$status) {
        print_error('deleteposterror', 'block_exabis_eportfolio', $returnurl);
    }
}

?>
