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
    require_once("{$CFG->dirroot}/blocks/exabis_eportfolio/sharelib.php");
    require_once("{$CFG->dirroot}/blocks/exabis_eportfolio/information_edit_form.php");
    
    $userid = optional_param('userid', 0, PARAM_INT);
    $courseid = optional_param('courseid', 0, PARAM_INT);
    $edit = optional_param('edit', 0, PARAM_BOOL);
    
    require_login($courseid);
    
    $context = get_context_instance(CONTEXT_SYSTEM);
    
    require_capability('block/exabis_eportfolio:use', $context);        
    
    if (! $course = get_record("course", "id", $courseid) ) {
         print_error("invalidinstance","block_exabis_eportfolio");
    }
    
    $strbookmarks = get_string("personal","block_exabis_eportfolio");
    $strmybookmarks = get_string("mybookmarks", "block_exabis_eportfolio");
    if ($courseid == SITEID) {
	    print_header("$SITE->shortname: $strbookmarks", $course->fullname,
	        "<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view.php?courseid={$courseid}\">" . strip_tags($strmybookmarks) . "</a> ->
	                 " . $strbookmarks,
	                 '', '', true);
	}
    else {
	    print_header("$SITE->shortname: $strbookmarks", $course->fullname,
	        "<a href=\"{$CFG->wwwroot}/course/view.php?id={$courseid}\">" . $course->shortname . "</a> ->
	                 <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view.php?courseid={$courseid}\">" . strip_tags($strmybookmarks) . "</a> ->
	                 " . $strbookmarks,
	                 '', '', true);
    }
    
    print_heading("<img src=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/pix/personalinfo.png\" width=\"16\" height=\"16\" alt=\"personalinfo\"/> " . $strbookmarks) ;

    $currenttab = 'personal';
    include("{$CFG->dirroot}/blocks/exabis_eportfolio/tabs.php");
        
    if (isset($USER->realuser)) {
        print_error("loginasmode","block_exabis_eportfolio");
    }
    
    if (has_capability('block/exabis_eportfolio:shareextern', $context)) {
        $extern_link = get_extern_access($USER->id);
        print_simple_box( get_string("externaccess", "block_exabis_eportfolio") . ': <a  onclick="this.target=\'extlink\'; return openpopup(\'/blocks/exabis_eportfolio/'.$extern_link.'\',\'extlink\',\'resizable=1,scrollbars=1,directories=1,location=1,menubar=1,toolbar=1,status=1,width=620,height=450\');" href="'.$CFG->wwwroot.'/blocks/exabis_eportfolio/'.$extern_link.'">'.$CFG->wwwroot.'/blocks/exabis_eportfolio/'.$extern_link.'</a>','center');
    }

    echo "<br />";
    
    $descid = 0;
    $cataction = 'new';
    $description = '';
    $show_information = true;
    
    if($userdescription = get_record_select('block_exabeporpers', 'userid = \''.$USER->id.'\'', 'id, description')) {
        $cataction = 'save';
        $descid = $userdescription->id;
        $description = $userdescription->description;
    }

    echo "<div class='block_eportfolio_center'>";

    print_simple_box( text_to_html(get_string("explainpersonal","block_exabis_eportfolio")) , 'center');
    
    echo "</div>";
        
    if($edit) {
        if (!confirm_sesskey()) {
            print_error("badsessionkey","block_exabis_eportfolio");   
        }
        $informationform = new eportfolio_personal_information_form();
                
        if($informationform->is_cancelled()) {
        }
        else if($fromform = $informationform->get_data()) {
            $newentry = new stdClass();
            $newentry->description = $fromform->description;
            $newentry->timemodified = time();
            
            trusttext_after_edit($newentry->description, $context);
            
            // took the $cataction from selection because it can't be wrong.
            switch ($cataction) {
                case 'new':  $newentry->userid = $USER->id;
                             if (! $newentry->id = insert_record("block_exabeporpers", $newentry)) {
                                print_error("couldntinsertdesc","block_exabis_eportfolio");   
                             }
                             else {
                                add_to_log($courseid, "bookmark", "add description", "", $newentry->id);
                                $message = get_string("descriptionsaved","block_exabis_eportfolio");
                             }
                             break;
                case 'save': $newentry->id = $descid;
                             if (! update_record("block_exabeporpers", $newentry)) {
                                 print_error("couldntupdatedesc","block_exabis_eportfolio");   
                             } else {
                                 add_to_log($courseid, "bookmark", "update description", "", $newentry->id);
                                 $message = get_string("descriptionsaved","block_exabis_eportfolio");
                             }
                             break;
            }
            // read new data from the database
            if($userdescription = get_record_select('block_exabeporpers', 'userid = \''.$USER->id.'\'', 'id, description')) {
                $cataction = 'save';
                $descid = $userdescription->id;
                $description = $userdescription->description;
            }
            print_simple_box($message, 'center', '40%', '#ccffbb');
        }
        else {
            $show_information = false;
            $informationform->set_data(array('courseid' => $courseid,
                                         'description' => $description,
                                         'cataction' => $cataction,
                                         'descid' => $descid,
                                         'edit' => 1 ) );
            
            $informationform->display();
        }
    }
    
    if($show_information) {
    	
        echo '<table cellspacing="0" class="forumpost blogpost blog" width="100%">';
        
        echo '<tr class="header"><td class="picture left">';
        print_user_picture($USER->id, $courseid, $USER->picture);
        echo '</td>';
        
        echo '<td class="topic starter"><div class="author">';
        $by =  '<a href="'.$CFG->wwwroot.'/user/view.php?id='.
                    $USER->id.'&amp;course='.$courseid.'">'.fullname($USER, $USER->id).'</a>';
        print_string('byname', 'moodle', $by);
        echo '</div></td></tr>';

        echo '<tr><td class="left side">';

        echo '</td><td class="content">'."\n";
        
        echo format_text($description, FORMAT_HTML);
        
        echo '</td></tr></table>'."\n\n";
        
        echo '<div class="block_eportfolio_center">';

        echo '<form method="post" action="'.$CFG->wwwroot.'/blocks/exabis_eportfolio/view.php?courseid='.$courseid.'">';
        echo '<fieldset class="hidden">';
        echo '<input type="hidden" name="edit" value="1" />';
        echo '<input type="submit" value="' . get_string("edit") . '" />';
        echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
        echo '</fieldset>';
        echo '</form>';
        echo '</div>';
    }
    
    echo "<div class=\"block_eportfolio_bmukk\">project supported by<br /> <img src=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/pix/bmukk.png\" width=\"63\" height=\"24\" alt=\"bmukk\" /></div>";
    echo "<div class=\"block_eportfolio_exabis\">programmed by<br /><a href=\"http://www.exabis.at/\"><img src=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/pix/exabis.png\" width=\"89\" height=\"40\" alt=\"exabis\"/></a></div>";
    echo "<div class=\"block_eportfolio_clear\" />";
    
    print_footer($course);
?>