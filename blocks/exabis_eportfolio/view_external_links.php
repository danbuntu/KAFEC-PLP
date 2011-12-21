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
	$sortkey = optional_param('sortkey', "default", PARAM_ALPHA);
	$sortorder = optional_param('sortorder', "asc", PARAM_ALPHA);
    $sortorder = strtolower($sortorder);

    $strbookmarks = get_string("mybookmarks", "block_exabis_eportfolio");
	$strext = get_string("bookmarksexternal", "block_exabis_eportfolio");
	
    $context = get_context_instance(CONTEXT_SYSTEM);
    
    require_login($courseid);
    require_capability('block/exabis_eportfolio:use', $context);
    
    if (! $course = get_record("course", "id", $courseid) ) {
        error("That's an invalid course id");
    }

    if ($sortkey == "name"){
        $sortkey="name";
    }
    if ($sortkey == "timemodified"){
        $sortkey="date";
    }
    if ($sortkey == "cname"){
        $sortkey="category";
    }
    $order=$sortorder;
  
    if ($courseid == SITEID) {
		print_header("$SITE->shortname: $strbookmarks", $SITE->fullname,
		             "<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view.php?courseid={$courseid}\">" . strip_tags($strbookmarks) . "</a> ->
		             " . $strext,
		             '', '', true);
	}
    else {
		print_header("$SITE->shortname: $strbookmarks", $course->fullname,
		             "<a href=\"{$CFG->wwwroot}/course/view.php?id={$courseid}\">" . $course->shortname . "</a> ->
		             <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view.php?courseid={$courseid}\">" . strip_tags($strbookmarks) . "</a> ->
		             " . $strext,
		             '', '', true);
    }
        
    print_heading("<img src=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/pix/externallinks.png\" width=\"16\" height=\"16\" alt='".get_string("bookmarksexternal", "block_exabis_eportfolio")."' /> " . $strbookmarks . ": " . $strext) ;
    
    $currenttab = 'external';
    include("{$CFG->dirroot}/blocks/exabis_eportfolio/tabs.php");
        
    if (isset($USER->realuser)) {
        error("You can't access portfolios in 'Login As'-Mode.");
    }


    echo "<br />";
    echo "<div class='block_eportfolio_center'>";
    print_simple_box( text_to_html(get_string("explainingexternal","block_exabis_eportfolio")) , "center");

    echo "</div>";
    echo "<br />";

    $asc_icon = "asc.gif";
    $desc_icon = "desc.gif";

    if ( $sortorder == "desc") {
        $icon = $desc_icon;
        $neworder = "asc";
    } else {
    	$sortorder = "asc";
        $icon = $asc_icon;
        $neworder = "desc";
    }

    if ( $sortkey != "default") {
        switch ( $sortkey ) {
            case "name":
                $name_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/view_external_links.php?courseid=$courseid&amp;sortkey=name&amp;sortorder=$neworder'>" . get_string("name", "block_exabis_eportfolio");
                $date_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/view_external_links.php?courseid=$courseid&amp;sortkey=date'>" . get_string("date","block_exabis_eportfolio");
        		$course_header = get_string("course","block_exabis_eportfolio");
                $cat_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/view_external_links.php?courseid=$courseid&amp;sortkey=category'>" . get_string("category","block_exabis_eportfolio");
        		$comments_header = get_string("comments","block_exabis_eportfolio");
                $name_header .= "<img src=\"pix/$icon\" alt='".get_string("updownarrow", "block_exabis_eportfolio")."' />";
            break;
            case "date":
                $name_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/view_external_links.php?courseid=$courseid&amp;sortkey=name'>" . get_string("name", "block_exabis_eportfolio");
                $date_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/view_external_links.php?courseid=$courseid&amp;sortkey=date&amp;sortorder=$neworder'>" . get_string("date","block_exabis_eportfolio");
        		$course_header = get_string("course","block_exabis_eportfolio");
                $cat_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/view_external_links.php?courseid=$courseid&amp;sortkey=category'>" . get_string("category","block_exabis_eportfolio");
        		$comments_header = get_string("comments","block_exabis_eportfolio");
                $date_header .= "<img src=\"pix/$icon\" alt='".get_string("updownarrow", "block_exabis_eportfolio")."' />";
            break;
            case "category":
                $name_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/view_external_links.php?courseid=$courseid&amp;sortkey=name'>" . get_string("name", "block_exabis_eportfolio");
                $date_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/view_external_links.php?courseid=$courseid&amp;sortkey=date'>" . get_string("date","block_exabis_eportfolio");
        		$course_header = get_string("course","block_exabis_eportfolio");
                $cat_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/view_external_links.php?courseid=$courseid&amp;sortkey=category&amp;sortorder=$neworder'>" . get_string("category","block_exabis_eportfolio");
        		$comments_header = get_string("comments","block_exabis_eportfolio");
                $cat_header .= "<img src=\"pix/$icon\" alt='".get_string("updownarrow", "block_exabis_eportfolio")."' />";
            break;
        }
	 	
    } else {
        $name_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/view_external_links.php?courseid=$courseid&amp;sortkey=name&amp;sortorder=desc'>" . get_string("name", "block_exabis_eportfolio");
        $date_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/view_external_links.php?courseid=$courseid&amp;sortkey=date'>" . get_string("date","block_exabis_eportfolio");
        $course_header = get_string("course","block_exabis_eportfolio");
        $cat_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/view_external_links.php?courseid=$courseid&amp;sortkey=category'>" . get_string("category","block_exabis_eportfolio");
        $date_header .= "<img src=\"pix/desc.gif\" alt='".get_string("updownarrow", "block_exabis_eportfolio")."' />";
        $comments_header = get_string("comments","block_exabis_eportfolio");

        $sortkey = "timemodified";
        $sortorder = "desc";
    }
   $key=$sortkey;
    $name_header .= "</a>";
    $date_header .= "</a>";
    $course_header .= "";
    $cat_header  .= "</a>";
    $table = new stdClass();
    $table->head  = array ($cat_header, $name_header, $date_header, $course_header, $comments_header, get_string("action"));
    $table->align = array("LEFT","LEFT", "CENTER", "CENTER","RIGHT","LEFT");
    $table->size = array("14%", "30%", "20%","14%","8%","14%");
    $table->width = "85%";

    $secondorder = "";
    if ( $sortkey ==  "date" ) {
        $sortkey = "timemodified";
    } elseif ($sortkey == "category") {
        $sortkey = "cname";
        $secondorder = ", timemodified";
    }

    $bookmarks = get_records_sql("select b.id,b.intro,b.timemodified,b.shareall,b.externaccess, b.name, bc.name AS cname, bc2.name AS cname_parent, c.fullname AS coursename, COUNT(com.id) AS comments".
                                 " from {$CFG->prefix}block_exabeporbooklink b join {$CFG->prefix}block_exabeporcate bc on b.category = bc.id".
                                 " left join {$CFG->prefix}block_exabeporcate bc2 on bc.pid = bc2.id".
		                         " left join {$CFG->prefix}course c on b.course = c.id".
		                         " left join {$CFG->prefix}block_exabeporcommlink com on com.bookmarkid = b.id".
                                 " where b.userid = $USER->id group by b.id,b.intro,b.timemodified,b.shareall,b.externaccess, b.name,cname,cname_parent,coursename order by $sortkey $sortorder $secondorder");
    
    if ( $bookmarks ) {
        $lastcat = "";
        foreach ($bookmarks as $bookmark) {

	    $options = "menubar=0,location=0,scrollbars,resizable,width=800,height=600,top=60,left=40";
    	    $title = get_string("go","block_exabis_eportfolio");

            $name = "";
            $name .= "<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/bookmark_view_external_link.php?courseid=$courseid&amp;bookid=$bookmark->id\">" . format_string($bookmark->name) . "</a>";
            
            if ($bookmark->intro) {
                //$bookmark->intro = "<i>" . format_text($bookmark->intro, FORMAT_PLAIN, $options=NULL) . "</i>";
                $name .= "<br /><table width=\"98%\"><tr><td>" . format_text($bookmark->intro, FORMAT_HTML) . "</td></tr></table>";
	        }

            $date = userdate($bookmark->timemodified);
            $icons = "";
                        
            $icons .= " <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/add_external.php?courseid=$courseid&amp;id=$bookmark->id&amp;sesskey=".sesskey()."&amp;action=edit\"><img src=\"".$CFG->pixpath."/t/edit.gif\" width=\"11\" height=\"11\" alt=\"" . get_string("edit"). "\"/></a>";
            $icons .= "   ";
            $icons .= " <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/add_external.php?courseid=$courseid&amp;id=$bookmark->id&amp;sesskey=".sesskey()."&amp;action=delete&amp;confirm=1\"><img src=\"".$CFG->pixpath."/t/delete.gif\" width=\"11\" height=\"11\" alt=\"" . get_string("delete"). "\"/></a>";

		    if (has_capability('block/exabis_eportfolio:shareintern', $context)) {	        
		        if( ($bookmark->shareall == 1) ||
	                ($bookmark->externaccess == 1) ||
	               (($bookmark->shareall == 0) && (count_records('block_exabeporsharlink', 'bookid', $bookmark->id, 'original', $USER->id) > 0))) {
			    	$icons .= " <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/share.php?courseid=$courseid&amp;bookid=$bookmark->id&amp;sortkey=$key&amp;sortorder=$order\">".get_string("strunshare", "block_exabis_eportfolio")."</a>";
	            }
	            else {
			    	$icons .= " <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/share.php?courseid=$courseid&amp;bookid=$bookmark->id&amp;sortkey=$key&amp;sortorder=$order\">".get_string("strshare", "block_exabis_eportfolio")."</a>";
			    }
			}
	        	        
	    	if(is_null($bookmark->cname_parent)) {
            	$category = format_string($bookmark->cname);
	    	}
	    	else {
	            $category = format_string($bookmark->cname_parent) . " &rArr; " . format_string($bookmark->cname);
	        }
            if ( $lastcat ==  $category and $sortkey == "cname") {
                $category = "";
            } else {
                $lastcat = $category;
            }
            $table->data[] = array($category, $name, $date, $bookmark->coursename, $bookmark->comments, $icons);
        }
        print_table($table);
    } else {
        echo "<br />" . get_string("nobookmarksexternal","block_exabis_eportfolio"). "";
    }

    echo "<div class='block_eportfolio_center'>";

    echo "<br />
          <form action=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/add_external.php\" method=\"post\">
            <fieldset>
              <input type=\"hidden\" name=\"action\" value=\"add\"/>
              <input type=\"hidden\" name=\"courseid\" value=\"$courseid\"/>
              <input type=\"hidden\" name=\"sesskey\" value=\"" . sesskey() . "\" />
              <input type=\"submit\" value=\"" . get_string("newexternal","block_exabis_eportfolio"). "\"/>
            </fieldset>
          </form>";

    echo "<br /><br />";

    echo "</div>";
    print_footer($course);

?>
