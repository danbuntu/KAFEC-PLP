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
*  (at your ption) any later version.
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
<?php // $Id: share.php,v 1.1 2004/04/30 12:08:28 by guitarsawicki
    require("../../config.php");
    require("{$CFG->dirroot}/blocks/exabis_eportfolio/lib.php");
    require("{$CFG->dirroot}/blocks/exabis_eportfolio/sharelib.php");
    
    $courseid = required_param('courseid', PARAM_INT);
    $save_this = optional_param('save_this', '', PARAM_ALPHA);
    $bookid = optional_param('bookid', 0, PARAM_INT);
    $sharethis = optional_param('sharethis', '', PARAM_RAW); // array of integer, check later with clean_param
    $showall = optional_param('showall', 0, PARAM_INT);
    $shareall = optional_param('shareall', 0, PARAM_INT);
    $externaccess = optional_param('externaccess', 0, PARAM_INT);
    $externcomment = optional_param('externcomment', 0, PARAM_INT);
    $sortkey="date";
    $context = get_context_instance(CONTEXT_SYSTEM);
    
    require_login($courseid);
    require_capability('block/exabis_eportfolio:use', $context);
    require_capability('block/exabis_eportfolio:shareintern', $context);

    if (! $course = get_record("course", "id", $courseid) ) {
        print_error("invalidcourseid","block_exabis_eportfolio");
    }

    // get the bookmark if it is mine.
    $bookmark = get_record_sql("select b.*, bc.name AS cname, bc2.name AS cname_parent, c.fullname As coursename".
                                 " from {$CFG->prefix}block_exabeporbooklink b join {$CFG->prefix}block_exabeporcate bc on b.category = bc.id".
                                 " left join {$CFG->prefix}block_exabeporcate bc2 on bc.pid = bc2.id".
		                         " left join {$CFG->prefix}course c on b.course = c.id".
                                 " where b.userid = '{$USER->id}' and b.id='".$bookid."'");
    
    if(!$bookmark) {
       print_error("bookmarknotfound","block_exabis_eportfolio", 'view.php?courseid=' . $courseid);	 
    }
    
    $strbookmarks = get_string("mybookmarks", "block_exabis_eportfolio");

	$extern_link = get_extern_access($USER->id);
	
	if ($save_this == "ok"){
        if (!confirm_sesskey()) {
            print_error("badsessionkey","block_exabis_eportfolio");
       }
       $bookmark_update = new stdClass();
       $bookmark_update->id=$bookid;
       $bookmark_update->shareall=$shareall;
       
    	if (has_capability('block/exabis_eportfolio:shareextern', $context)) {
	   		$bookmark_update->externaccess=$externaccess;
	   		$bookmark_update->externcomment=$externcomment;
	   	}
	   	else {
	   		$bookmark_update->externaccess=0;
	   		$bookmark_update->externcomment=0;
	   	}
       update_record("block_exabeporbooklink", $bookmark_update);
       
        delete_records("block_exabeporsharlink", "course", $courseid, "bookid", $bookid);
        if (is_array($sharethis)){
            foreach ($sharethis as $share_item) {
            	$share_item = clean_param($share_item, PARAM_INT);
            	$bookmark_shared = new stdClass();
	            $bookmark_shared->bookid=$bookid;	 
	            $bookmark_shared->course=$courseid;
	            $bookmark_shared->userid=$share_item;
	            $bookmark_shared->original=$USER->id;
	            insert_record("block_exabeporsharlink", $bookmark_shared);
            }
        }
	    redirect("view_external_links.php?courseid=$courseid&amp;sortkey=$sortkey&amp;sortorder=name");
    }
	
    if ($courseid == SITEID) {
	    print_header(strip_tags($strbookmarks), $SITE->fullname,
	        "<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view.php?courseid={$courseid}\">" . strip_tags(get_string("mybookmarks", "block_exabis_eportfolio")) . "</a> ->
	        " . strip_tags($strbookmarks), "", "", true, "",
	        navmenu($course),"","");
	}
    else {
	    print_header(strip_tags($strbookmarks), $course->fullname,
	        "<a href=\"{$CFG->wwwroot}/course/view.php?id={$courseid}\">" . $course->shortname . "</a> ->
	        <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view.php?courseid={$courseid}\">" . strip_tags(get_string("mybookmarks", "block_exabis_eportfolio")) . "</a> ->
	        " . strip_tags($strbookmarks), "", "", true, "",
	        navmenu($course),"","");
    }
    

    print_heading("<img src=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/pix/externallinks.png\" width=\"16\" height=\"16\" alt='".get_string("strshare", "block_exabis_eportfolio")."' /> " . $strbookmarks . ": " . get_string("bookmarksexternal", "block_exabis_eportfolio")) ;
    
	
    $currenttab = 'external';
    include("{$CFG->dirroot}/blocks/exabis_eportfolio/tabs.php");
    
    $shareall = $bookmark->shareall;
	if (has_capability('block/exabis_eportfolio:shareextern', $context)) {
   		//$bookmark_update->externaccess=$externaccess;
   		//$bookmark_update->externcomment=$externcomment;
   		$externaccess = $bookmark->externaccess;
   		$externcomment = $bookmark->externcomment;
   	}
   	else {
   		//$bookmark_update->externaccess=0;
   		//$bookmark_update->externcomment=0;
   		$externaccess = 0;
   		$externcomment = 0;
   	}
    
    print_simple_box( text_to_html(get_string("explainingshare", "block_exabis_eportfolio")) , "center");
    $table = new stdClass();
    $table->head  = array (get_string("category","block_exabis_eportfolio"), get_string("name", "block_exabis_eportfolio"), get_string("date","block_exabis_eportfolio"), get_string("course","block_exabis_eportfolio"));
    $table->align = array("LEFT", "LEFT", "CENTER", "CENTER");
    $table->size = array("20%", "34%", "26%","20%");
    $table->width = "85%";
    $table->data[] = array(format_string($bookmark->cname), format_string($bookmark->name), "<div class='block_eportfolio_timemodified'>" . userdate($bookmark->timemodified) . "</div>", $bookmark->coursename);
    print_table($table);

	print_js();

	echo "<form action=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/share.php\" method=\"post\" id=\"listing\">";
    echo '<fieldset><legend>'.get_string("accessoptions", "block_exabis_eportfolio").'</legend>';
    
    echo '<label>' . get_string("internalaccess", "block_exabis_eportfolio") . '</label>';
    
    if($shareall == 0) {
        echo '<p><label><input name="shareall" type="radio" value="1" />'.get_string("shareallexceptthose","block_exabis_eportfolio").'</label><br />';
        echo '<label><input name="shareall" type="radio" value="0" checked="checked" />'.get_string("sharenoneexceptthose","block_exabis_eportfolio").'</label></p>';
    }
    else {
        echo '<p><label><input name="shareall" type="radio" value="1" checked="checked" />'.get_string("shareallexceptthose","block_exabis_eportfolio").'</label><br />';
        echo '<label><input name="shareall" type="radio" value="0" />'.get_string("sharenoneexceptthose","block_exabis_eportfolio").'</label></p>';
    }
    
    
    if (has_capability('block/exabis_eportfolio:shareextern', $context)) {
	    echo '<label>' . get_string("externalaccess", "block_exabis_eportfolio") . '</label>';
	    
	    if($bookmark->externaccess == 0) {
	        echo '<p><label><input type="checkbox" name="externaccess" value="1" />'.get_string("externaccess", "block_exabis_eportfolio").' (<a  onclick="this.target=\'extlink\'; return openpopup(\'/blocks/exabis_eportfolio/'.$extern_link.'\',\'extlink\',\'resizable=1,scrollbars=1,directories=1,location=1,menubar=1,toolbar=1,status=1,width=620,height=450\');" href="'.$CFG->wwwroot.'/blocks/exabis_eportfolio/'.$extern_link.'">'.$CFG->wwwroot.'/blocks/exabis_eportfolio/'.$extern_link.'</a>)</label><br />';
	    }
	    else {
	        echo '<p><label><input type="checkbox" name="externaccess" checked="checked" value="1" />'.get_string("externaccess", "block_exabis_eportfolio").' (<a  onclick="this.target=\'extlink\'; return openpopup(\'/blocks/exabis_eportfolio/'.$extern_link.'\',\'extlink\',\'resizable=1,scrollbars=1,directories=1,location=1,menubar=1,toolbar=1,status=1,width=620,height=450\');" href="'.$CFG->wwwroot.'/blocks/exabis_eportfolio/'.$extern_link.'">'.$CFG->wwwroot.'/blocks/exabis_eportfolio/'.$extern_link.'</a>)</label><br />';
	    }
	    
	    if($bookmark->externcomment == 0) {
	        echo '<label><input type="checkbox" name="externcomment" value="1" />'.get_string("externcomment", "block_exabis_eportfolio").'</label></p>';
	    }
	    else {
	        echo '<label><input type="checkbox" name="externcomment" checked="checked" value="1" />'.get_string("externcomment", "block_exabis_eportfolio").'</label></p>';
	    }
	}
	else {
		echo '<input type="hidden" name="externaccess" value="0" /><input type="hidden" name="externcomment" value="0" />';
	}
	    
	echo '</fieldset>';


	if($showall == 0) {
	    echo '<fieldset><legend>'.$course->fullname.'</legend>';
		print_user_listing($courseid, $bookid, "block_exabeporsharlink",$course);
		echo "</fieldset>";
	}
	else {
	    $courses = get_my_courses($USER->id);
	    foreach($courses as $actcourse)
	    {
		    echo '<fieldset><legend>'.$actcourse->fullname.'</legend>';
		    print_user_listing($actcourse->id, $bookid, "block_exabeporsharlink",$actcourse);
			echo "</fieldset>";
		}
	}
    
            echo "<fieldset>";
            echo "<input type=\"hidden\" name=\"courseid\" value=\"$courseid\" />";
            echo "<input type=\"hidden\" name=\"bookid\" value=\"$bookid\" />";
	        //echo "<input type=\"hidden\" name=\"expand\" value=\"$expand\" />";
	        
	
	
	
        if($showall == 0)
        	echo "<input type=\"submit\" onclick=\"document.getElementById('listing').elements['showall'].value = 1\" value=\"".get_string("showallusers", "block_exabis_eportfolio")."\" />";
        else
            echo "<input type=\"submit\" onclick=\"document.getElementById('listing').elements['showall'].value = 0\" value=\"".get_string("showcourseusers", "block_exabis_eportfolio")."\" />";
            echo "<input type=\"hidden\" name=\"showall\" value=\"0\" />";
            echo "<input type=\"button\" onclick=\"SetAllCheckBoxes('listing', 'sharethis[]', true);\" value=\"".get_string("selectall", "block_exabis_eportfolio")."\" />";
       	    echo "<input type=\"button\" onclick=\"SetAllCheckBoxes('listing', 'sharethis[]', false);\" value=\"".get_string("deselectall", "block_exabis_eportfolio")."\" />";
       	    echo "<input type=\"submit\" onclick=\"document.forms['listing'].elements['save_this'].value = 'ok'\" value=\"".get_string("savechanges")."\" />\n<br /><br />";
       	    echo "<input type=\"hidden\" name=\"sortkey\" value=\"$sortkey\" />";
            echo "<input type=\"hidden\" name=\"save_this\" value=\"notok\" />";
            echo "<input type=\"hidden\" name=\"sesskey\" value=\"" . sesskey() . "\" />";
            echo "<input type=\"hidden\" name=\"selectall\" value=\"\" />";
            echo "</fieldset>";
            echo "</form>";
    
	print_footer($course);
?>
