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
	$action = optional_param('action', '', PARAM_ALPHA);
	$confirm = optional_param('confirm', '', PARAM_BOOL);
    $post = new stdClass();
	
    
	if(!confirm_sesskey()) {
        print_error("badsessionkey","block_exabis_eportfolio");
	}
	
    $context = get_context_instance(CONTEXT_SYSTEM);
    
    require_login($courseid);
    require_capability('block/exabis_eportfolio:use', $context);
    
	if(! $course = get_record("course", "id", $courseid) ) {
        print_error("invalidcourseid","block_exabis_eportfolio");
	}
			
    if(!block_exabis_eportfolio_has_categories($USER->id)) {
    	print_error("nocategories", "block_exabis_eportfolio", "view.php?courseid=" . $courseid);
    }


	if ($id) {
		if (!$existing = get_record('block_exabeporbookfile', 'id', $id, 'userid', $USER->id)) {
            print_error("wrongfileid","block_exabis_eportfolio");		
		}

		$returnurl = $CFG->wwwroot.'/blocks/exabis_eportfolio/view_files.php?courseid='.$courseid; //?userid='.$existing->userid;
	} else {
		$existing  = false;
		$returnurl = $CFG->wwwroot.'/blocks/exabis_eportfolio/view_files.php?courseid='.$courseid; //'index.php?userid='.$USER->id;
	}

	if ($action=='delete'){
		if (!$existing) {
            print_error("wrongfilepostid","block_exabis_eportfolio");			
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
			notice_yesno(get_string("deletefileconfirm", "block_exabis_eportfolio"), 'add_file.php', 'view_files.php', $optionsyes, $optionsno, 'post', 'get');
			print_footer();
			die;
		}
	}

	require_once("{$CFG->dirroot}/blocks/exabis_eportfolio/bookmark_edit_form.php");
	
	// gui setup
	switch ($action) {
		case 'add':
			$exteditform = new file_edit_form_new(null, compact('existing'));
			break;
		case 'edit':
			$exteditform = new file_edit_form(null, compact('existing'));
			break;
		default :
            print_error("unknownaction","block_exabis_eportfolio");			
	}

	if ($exteditform->is_cancelled()){
		redirect($returnurl);
	} else if ($exteditform->no_submit_button_pressed()) {
		die("nosubmitbutton");
		//no_submit_button_actions($exteditform, $sitecontext);
	} else if ($fromform = $exteditform->get_data()){
		switch ($action) {
			case 'add':
				do_add($fromform, $exteditform, $returnurl, $courseid);
			break;

			case 'edit':
				if (!$existing) {
                    print_error("wrongfileid","block_exabis_eportfolio");					
				}
				do_edit($fromform, $exteditform,$returnurl, $courseid);
			break;
			default :
                    print_error("unknownaction","block_exabis_eportfolio");					
		}
		redirect($returnurl);
	}

	$strAction = '';
	$content = '';
	// gui setup
	switch ($action) {
		case 'add':
			$post->action       = $action;
			$post->courseid     = $courseid;
			$strAction = get_string('new');
			break;
		case 'edit':
			if (!$existing) {
                print_error("wrongfileid","block_exabis_eportfolio");			
			}
			$post->id           = $existing->id;
			$post->name         = $existing->name;
			$post->category     = $existing->category;
			$post->intro        = $existing->intro;
			$post->attachment   = $existing->attachment;
			$post->userid       = $existing->userid;
			$post->action       = $action;
			$post->courseid     = $courseid;

			$strAction = get_string('edit');
	        $filearea = block_exabis_eportfolio_file_area_name($post);
	        
	        $ffurl = '';
	        if ($CFG->slasharguments) {
	            $ffurl = "{$CFG->wwwroot}/blocks/exabis_eportfolio/portfoliofile.php/$filearea/$post->attachment";
	        } else {
	            $ffurl = "{$CFG->wwwroot}/blocks/exabis_eportfolio/portfoliofile.php?file=/$filearea/$post->attachment";
	        }
	        
            $content = "<div class='block_eportfolio_center'>\n";
            $content .= print_box(block_exabis_eportfolio_print_file($ffurl, $post->attachment, $post->name), 'generalbox', '', true);
	        $content .= "</div>";
			break;
		default :
                print_error("unknownaction","block_exabis_eportfolio");			
	}

	$strbookmarks = get_string("mybookmarks", "block_exabis_eportfolio");
	$strext = get_string("bookmarksfiles", "block_exabis_eportfolio");

    if ($courseid == SITEID) {
		print_header("$SITE->shortname: $strbookmarks", $SITE->fullname /*$SITE->fullname*/,
					 "<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view.php?courseid={$courseid}\">" . strip_tags($strbookmarks) . "</a> ->
					 <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view_files.php?courseid={$courseid}\">" . $strext . "</a> ->
					 {$strAction}",
					 '', '', true);
	}
    else {
		print_header("$SITE->shortname: $strbookmarks", $course->fullname /*$SITE->fullname*/,
					 "<a href=\"{$CFG->wwwroot}/course/view.php?id={$courseid}\">" . $course->shortname . "</a> ->
					 <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view.php?courseid={$courseid}\">" . strip_tags($strbookmarks) . "</a> ->
					 <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view_files.php?courseid={$courseid}\">" . $strext . "</a> ->
					 {$strAction}",
					 '', '', true);
    }
    
	print_heading("<img src=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/pix/files.png\" width=\"16\" height=\"16\" alt='".get_string("bookmarksfiles", "block_exabis_eportfolio")."' /> " . $strbookmarks . ": " . $strext) ;

	$currenttab = 'files';
    include("{$CFG->dirroot}/blocks/exabis_eportfolio/tabs.php");
	
	$exteditform->set_data($post);
	echo $content;
	$exteditform->display();

	print_footer($course);
die;

/**
 * Update a file in the database
 */
function do_edit($post, $blogeditform,$returnurl, $courseid) {
	global $CFG, $USER;

	$post->timemodified = time();

	if (update_record('block_exabeporbookfile', $post)) {
		add_to_log(SITEID, 'bookmark', 'update', 'add_file.php?courseid='.$courseid.'&id='.$post->id.'&action=edit', $post->name);
	} else {
		print_error('updateposterror', 'block_exabis_eportfolio', $returnurl);
	}
}

/**
 * Write a new blog entry into database
 */
function do_add($post, $blogeditform, $returnurl,$courseid) {
	global $CFG, $USER;

	$post->userid       = $USER->id;
	$post->timemodified = time();
	$post->course = $courseid;

	// Insert the new blog entry.
	if ($post->id = insert_record('block_exabeporbookfile', $post)) {
		add_to_log(SITEID, 'bookmark', 'add', 'add_file.php?courseid='.$courseid.'&id='.$post->id.'&action=add', $post->name);
		$dir = block_exabis_eportfolio_file_area_name($post);
	        if ($blogeditform->save_files($dir) and $newfilename = $blogeditform->get_new_filename()) {
	            set_field("block_exabeporbookfile", "attachment", $newfilename, "id", $post->id);
	        }

	} else {
		print_error('addposterror', 'block_exabis_eportfolio', $returnurl);
	}
}

/**
 * Delete blog post from database
 */
function do_delete($post,$returnurl, $courseid) {


	$status = delete_records('block_exabeporbookfile', 'id', $post->id);

	add_to_log(SITEID, 'blog', 'delete', 'add_file.php?courseid='.$courseid.'&id='.$post->id.'&action=delete&confirm=1', $post->name);

	if (!$status) {
		print_error('deleteposterror', 'block_exabis_eportfolio', $returnurl);
	}
}

?>
