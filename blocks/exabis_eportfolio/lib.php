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
require_once($CFG->libdir.'/filelib.php');

function block_exabis_eportfolio_file_area_name($entry) {
//  Creates a directory file name, suitable for make_upload_directory()
    global $CFG;
    //return "$entry->course/$CFG->moddata/bookmark/$entry->userid/$entry->id";
    return 'exabis_eportfolio/files/' . $entry->userid . '/' . $entry->id;
}

function block_exabis_eportfolio_file_area($entry) {
    return make_upload_directory( block_exabis_eportfolio_file_area_name($entry) );
}

// Deletes all the user files in the attachments area for a entry
// EXCEPT for any file named $exception
function block_exabis_eportfolio_delete_old_attachments($id, $entry, $exception="") {

    if ($basedir = block_exabis_eportfolio_file_area($entry)) {
        if ($files = get_directory_list($basedir)) {
            foreach ($files as $file) {
                if ($file != $exception) {
                    unlink("$basedir/$file");
                }
            }
        }
        if (!$exception) {  // Delete directory as well, if empty
            @rmdir("$basedir");
        }
    }
}

// not needed at all - at least at the moment
//function block_exabis_eportfolio_empty_directory($basedir) {
//	if ($files = get_directory_list($basedir)) {
//        foreach ($files as $file) {
//            unlink("$basedir/$file");
//        }
//    }
//}
 
function block_exabis_eportfolio_copy_attachments($entry, $newentry) {
/// Given a entry object that is being copied to bookmarkid,
/// this function checks that entry
/// for attachments, and if any are found, these are
/// copied to the new bookmark directory.

    global $CFG;

    $return = true;

    if ($entries = get_records_select("bookmark", "id = '{$entry->id}' AND attachment <> ''")) {
        foreach ($entries as $curentry) {
            $oldentry = new stdClass();
            $oldentry->id = $entry->id;
            $oldentry->userid = $entry->userid;
            $oldentry->name = $entry->name;
            $oldentry->category = $curentry->category;
            $oldentry->intro = $entry->intro;
            $oldentry->url = $entry->url;
            $oldentrydir = "$CFG->dataroot/".block_exabis_eportfolio_file_area_name($oldentry);
            if (is_dir($oldentrydir)) {

                $newentrydir = block_exabis_eportfolio_file_area($newentry);
                if (! copy("$oldentrydir/$newentry->attachment", "$newentrydir/$newentry->attachment")) {
                    $return = false;
                }
            }
        }
     }
    return $return;
}

function  block_exabis_eportfolio_move_attachments($entry, $bookmarkid, $id) {
/// Given a entry object that is being moved to bookmarkid,
/// this function checks that entry
/// for attachments, and if any are found, these are
/// moved to the new bookmark directory.

    global $CFG;

    $return = true;

    if ($entries = get_records_select("bookmark", "id = '$entry->id' AND attachment <> ''")) {
        foreach ($entries as $entry) {
            $oldentry = new stdClass();
            $newentry = new stdClass();
            $oldentry->id = $entry->id;
            $oldentry->name = $entry->name;
            $oldentry->userid = $entry->userid;
            $oldentry->category = $curentry->category;
            $oldentry->intro = $entry->intro;
            $oldentry->url = $entry->url;
            $oldentrydir = "$CFG->dataroot/".block_exabis_eportfolio_file_area_name($oldentry);
            if (is_dir($oldentrydir)) {
                $newentry = $oldentry;
                $newentry->bookmarkid = $bookmarkid;
                $newentrydir = "$CFG->dataroot/".block_exabis_eportfolio_file_area_name($newentry);
                if (! @rename($oldentrydir, $newentrydir)) {
                    $return = false;
                }
            }
        }
    }
    return $return;
}

function block_exabis_eportfolio_add_attachment($entry, $newfile, $id) {
// $entry is a full entry record, including course and bookmark
// $newfile is a full upload array from $_FILES
// If successful, this function returns the name of the file

    global $CFG;

    if (empty($newfile['name'])) {
        return "";
    }

    $newfile_name = clean_filename($newfile['name']);

    if (valid_uploaded_file($newfile)) {
        if (! $newfile_name) {
            notify("This file had a wierd filename and couldn't be uploaded");

        } else if (! $dir = block_exabis_eportfolio_file_area($entry)) {
            notify("Attachment could not be stored");
            $newfile_name = "";

        } else {
            if (move_uploaded_file($newfile['tmp_name'], "$dir/$newfile_name")) {
                chmod("$dir/$newfile_name", $CFG->directorypermissions);
                block_exabis_eportfolio_delete_old_attachments($entry, $newfile_name);
            } else {
                notify("An error happened while saving the file on the server");
                $newfile_name = "";
            }
        }
    } else {
        $newfile_name = "";
    }

    return $newfile_name;
}

function block_exabis_eportfolio_print_attachments($id, $entry, $return=NULL, $align="left") {
// if return=html, then return a html string.
// if return=text, then return a text-only string.
// otherwise, print HTML for non-images, and return image HTML
//     if attachment is an image, $align set its aligment.
    global $CFG;

    $newentry = $entry;

    $filearea = block_exabis_eportfolio_file_area_name($newentry);

    $imagereturn = "";
    $output = "";

    if ($basedir = block_exabis_eportfolio_file_area($newentry)) {
        if ($files = get_directory_list($basedir)) {
            $strattachment = get_string("attachment", "block_exabis_eportfolio");
            $strpopupwindow = get_string("popupwindow");
            foreach ($files as $file) {
                $icon = mimeinfo("icon", $file);
                if ($CFG->slasharguments) {
                    $ffurl = "file.php/$filearea/$file";
                } else {
                    $ffurl = "file.php?file=/$filearea/$file";
                }
                $image = "<img border=0 src=\"$CFG->wwwroot/files/pix/$icon\" height=16 width=16 alt=\"$strpopupwindow\">";

                if ($return == "html") {
                    $output .= "<a target=_image href=\"$CFG->wwwroot/$ffurl\">$image</a> ";
                    $output .= "<a target=_image href=\"$CFG->wwwroot/$ffurl\">$file</a><br />";
                } else if ($return == "text") {
                    $output .= "$strattachment $file:\n$CFG->wwwroot/$ffurl\n";

                } else {
                    if ($icon == "image.gif") {    // Image attachments don't get printed as links
                        $imagereturn .= "<br /><img src=\"$CFG->wwwroot/$ffurl\" align=$align>";
                    } else {
                        link_to_popup_window("/$ffurl", "attachment", $image, 500, 500, $strattachment);
                        echo "<a target=_image href=\"$CFG->wwwroot/$ffurl\">$file</a>";
                        echo "<br />";
                    }
                }
            }
        }
    }

    if ($return) {
        return $output;
    }

    return $imagereturn;
}

function block_exabis_eportfolio_print_comment($comment, $filename, $courseid, $bookmark) {

        global $USER, $CFG;
        
        $stredit = get_string('edit');
        $strdelete = get_string('delete');

        $user = get_record('user','id',$comment->userid);

        echo '<table cellspacing="0" class="forumpost blogpost blog" width="100%">';

        echo '<tr class="header"><td class="picture left">';
        print_user_picture($comment->userid, SITEID, $user->picture);
        echo '</td>';

        echo '<td class="topic starter"><div class="author">';
        $fullname = fullname($user, $comment->userid);
        $by = new object();
        $by->name =  '<a href="'.$CFG->wwwroot.'/user/view.php?id='.
                    $user->id.'&amp;course='.$courseid.'">'.$fullname.'</a>';
        $by->date = userdate($comment->timemodified);
        print_string('bynameondate', 'forum', $by);
        if($comment->userid == $USER->id) {
	        echo ' - <a href="'.$CFG->wwwroot.'/blocks/exabis_eportfolio/'.$filename.'?courseid='.$courseid.'&amp;bookid='.$bookmark->id.'&amp;commentid='.$comment->id.'&amp;deletecomment=1&amp;sesskey='.sesskey().'">' . get_string('delete') . '</a>';
	    }
        echo '</div></td></tr>';

        echo '<tr><td class="left side">';

        echo '</td><td class="content">'."\n";
        
        echo format_text($comment->entry);
        
        echo '</td></tr></table>'."\n\n";

}

function block_exabis_eportfolio_has_categories($userid) {
	global $CFG;
	if(count_records_sql("SELECT COUNT(*) FROM {$CFG->prefix}block_exabeporcate WHERE userid='$userid' AND pid=0") > 0) {
		return true;
	}
	else {
		return false;
	}
}

function block_exabis_eportfolio_moodleimport_file_area_name($userid, $assignmentid, $courseid) {
    global $CFG;

    return $courseid.'/'.$CFG->moddata.'/assignment/'.$assignmentid.'/'.$userid;
}

    
function block_exabis_eportfolio_print_file($url, $filename, $alttext) {
	global $CFG;
	$icon = mimeinfo('icon', $filename);
    $type = mimeinfo('type', $filename);
    if (in_array($type, array('image/gif', 'image/jpeg', 'image/png'))) {    // Image attachments don't get printed as links
        return "<img src=\"$url\" alt=\"" . format_string($alttext) . "\" />";
    } else {
    	return '<p><img src="'.$CFG->pixpath.'/f/'.$icon.'" class="icon" alt="'.$icon.'" />&nbsp;' . link_to_popup_window($url, 'popup', $filename, $height=400, $width=500, $alttext, 'none', true) . "</p>";
    }
}
?>