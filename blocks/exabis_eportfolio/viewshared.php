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

    $courseid = required_param('courseid', PARAM_INT);
    $original = optional_param('original', 0, PARAM_INT);
	$sortkey = optional_param('sortkey', "default", PARAM_ALPHA);
	$sortorder = optional_param('sortorder', "asc", PARAM_ALPHA);

    $sortorder = strtolower($sortorder);

    $strbookmarks = get_string("sharedbookmarks", "block_exabis_eportfolio");
    $orig=get_record("user", "id", $original);
    
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
    //$order=$sortorder;
    
    if ($courseid == SITEID) {
	    print_header(strip_tags($strbookmarks), $SITE->fullname,
	       "<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/viewpeople.php?courseid={$courseid}\">" . get_string("sharedpersons", "block_exabis_eportfolio") . "</a> ->
	        " . strip_tags($strbookmarks), "", "", true, "",
	        navmenu($course),"","");
	}
    else {
	    print_header(strip_tags($strbookmarks), $course->fullname,
	       "<a href=\"{$CFG->wwwroot}/course/view.php?id={$courseid}\">" . $course->shortname . "</a> ->
	        <a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/viewpeople.php?courseid={$courseid}\">" . get_string("sharedpersons", "block_exabis_eportfolio") . "</a> ->
	        " . strip_tags($strbookmarks), "", "", true, "",
	        navmenu($course),"","");
    }

    print_heading($strbookmarks." (" . fullname($orig, $orig->id) . ") " . " <img src=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/pix/publishedportfolios.png\" width=\"16\" height=\"16\" alt='".get_string("publishedportfolios", "block_exabis_eportfolio")."' />") ;

    echo "<div class='block_eportfolio_center'>\n";
    print_simple_box( text_to_html(get_string("explainingexternal","block_exabis_eportfolio")) , "center");

    echo "<br />";

 
    $asc_icon = "asc.gif";
    $desc_icon = "desc.gif";

    if ( $sortorder == "desc") {
        $icon = $desc_icon;
        $neworder = "asc";
    } else {
        $icon = $asc_icon;
        $neworder = "desc";
    }
    
    $name_header = '';
    $date_header = '';
    $cat_header = '';
    
    switch ( $sortkey ) {
        case "name":
            $name_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=name&amp;sortorder=$neworder'>" . get_string("name", "block_exabis_eportfolio");
            $date_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=date'>" . get_string("date","block_exabis_eportfolio");
            $cat_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=category'>" . get_string("category","block_exabis_eportfolio");

            $name_header .= "<img src=\"pix/$icon\" alt='".get_string("updownarrow", "block_exabis_eportfolio")."' />";
        break;
        case "date":
            $name_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=name'>" . get_string("name", "block_exabis_eportfolio");
            $date_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=date&amp;sortorder=$neworder'>" . get_string("date","block_exabis_eportfolio");
            $cat_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=category'>" . get_string("category","block_exabis_eportfolio");

            $date_header .= "<img src=\"pix/$icon\"  alt='".get_string("updownarrow", "block_exabis_eportfolio")."' />";
        break;
        case "category":
            $name_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=name'>" . get_string("name", "block_exabis_eportfolio");
            $date_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=date'>" . get_string("date","block_exabis_eportfolio");
            $cat_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=category&amp;sortorder=$neworder'>" . get_string("category","block_exabis_eportfolio");

            $cat_header .= "<img src=\"pix/$icon\" alt='".get_string("updownarrow", "block_exabis_eportfolio")."' />";
        break;
        default:
	        $name_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=name&amp;sortorder=desc'>" . get_string("name", "block_exabis_eportfolio");
	        $date_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=date'>" . get_string("date","block_exabis_eportfolio");
	        $cat_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=category'>" . get_string("category","block_exabis_eportfolio");
	        $date_header .= "<img src=\"pix/desc.gif\" alt='".get_string("updownarrow", "block_exabis_eportfolio")."' />";
	
	        $sortkey = "timemodified";
	        $sortorder = "desc";
        break;
    }
    $name_header .= "</a>";
    $date_header .= "</a>";
    $cat_header  .= "</a>";
	    
    $table = new stdClass();
    $table->head  = array ($cat_header, $name_header, $date_header);
    $table->align = array("CENTER","LEFT", "CENTER");
    $table->size = array("20%", "47%", "33%");
    $table->width = "85%";
    
    $secondorder = "";
    if ( $sortkey ==  "date" ) {
        $sortkey = "timemodified";
    } elseif ($sortkey == "category") {
        $sortkey = "cname";
        $secondorder = ", timemodified";
    }

   $bookmarks = get_records_sql(
   "(SELECT b.*, bc.name AS cname FROM {$CFG->prefix}block_exabeporbooklink b
       JOIN {$CFG->prefix}block_exabeporcate bc ON b.category = bc.id
       JOIN {$CFG->prefix}block_exabeporsharlink bfs ON b.id=bfs.bookid
       WHERE b.shareall='0' AND b.userid='$original' AND bfs.userid='{$USER->id}')
    UNION
    (SELECT b.*, bc.name AS cname  FROM {$CFG->prefix}block_exabeporbooklink b
       JOIN {$CFG->prefix}block_exabeporcate bc ON b.category = bc.id
       LEFT JOIN ( SELECT * FROM {$CFG->prefix}block_exabeporsharlink WHERE userid='{$USER->id}' ) bfs ON b.id = bfs.bookid
       WHERE b.shareall='1' AND b.userid='$original' AND bfs.userid IS NULL)
    ORDER BY $sortkey $sortorder $secondorder");    
    
    if ( $bookmarks ) {
        $lastcat = "";
        foreach ($bookmarks as $bookmark) {
	        $options = "menubar=0,location=0,scrollbars,resizable,width=800,height=600,top=60,left=40";
	            $title = get_string("go","block_exabis_eportfolio");
	
	            $name = "";
	            /*if ( $bookmark->url ) {
            $name .= "<a title=\"$title\" href=\"$bookmark->url\" ".
                     "onclick=\"window.open('$bookmark->url','Moodle','$options'); return false;\">" . format_string($bookmark->name) . "</a>";
            }*/
            	$name .= "<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/bookmark_view_external_link.php?courseid=$courseid&amp;original=$original&amp;bookid=$bookmark->id\">" . format_string($bookmark->name) . "</a>";

                if ($bookmark->intro) {
                    $bookmark->intro = "<div class='block_eportfolio_italic'>" . format_text($bookmark->intro, FORMAT_PLAIN, $options=NULL) . "</div>";
                    $name .= "<br /><table width=\"98%\"><tr><td>".format_text($bookmark->intro, FORMAT_HTML)."</td></tr></table>";
                }

               /* if ( $bookmark->attachment ) {
                    $filearea = block_exabis_eportfolio_file_area_name($bookmark);
                    if ($CFG->slasharguments) {
                        $ffurl = "file.php/$filearea/$bookmark->attachment";
                    } else {
                        $ffurl = "file.php?file=/$filearea/$bookmark->attachment";
                    }

                    $name .= "<a target=_image href=\"$CFG->wwwroot/$ffurl\"><img src=\"".$CFG->wwwroot.""/pix/i/clip.gif\"  alt=\"clip\"/></a> ";

                }*/

                $date = "" . userdate($bookmark->timemodified) . "";
                $icons = "";
                $category = format_string($bookmark->cname);
                if ( $lastcat ==  $category and $sortkey == "cname") {
                    $category = "";
                } else {
                    $lastcat = $category;
                }
                $table->data[] = array($category, $name,$date, $icons);
    	}
    	print_table($table);
    } else {
        echo "" . get_string("nobookmarksexternal","block_exabis_eportfolio"). "";
    }
    echo "";

    unset($table);

    echo "<br />";

    print_simple_box( text_to_html(get_string("explainingfile","block_exabis_eportfolio")) , "center");

 
    echo "<br />";

    $asc_icon = "asc.gif";
    $desc_icon = "desc.gif";

    if ( $sortorder == "desc") {
        $icon = $desc_icon;
        $neworder = "asc";
    } else {
        $icon = $asc_icon;
        $neworder = "desc";
    }
	
    $name_header = '';
    $date_header = '';
    $cat_header = '';
    
    switch ( $sortkey ) {
        case "name":
            $name_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=name&amp;sortorder=$neworder'>" . get_string("name", "block_exabis_eportfolio");
            $date_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=date'>" . get_string("date","block_exabis_eportfolio");
            $cat_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=category'>" . get_string("category","block_exabis_eportfolio");

            $name_header .= "<img src=\"pix/$icon\" alt='".get_string("updownarrow", "block_exabis_eportfolio")."' />";
        break;
        case "date":
            $name_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=name'>" . get_string("name", "block_exabis_eportfolio");
            $date_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=date&amp;sortorder=$neworder'>" . get_string("date","block_exabis_eportfolio");
            $cat_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=category'>" . get_string("category","block_exabis_eportfolio");

            $date_header .= "<img  src=\"pix/$icon\"  alt='".get_string("updownarrow", "block_exabis_eportfolio")."' />";
        break;
        case "category":
            $name_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=name'>" . get_string("name", "block_exabis_eportfolio");
            $date_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=date'>" . get_string("date","block_exabis_eportfolio");
            $cat_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=category&amp;sortorder=$neworder'>" . get_string("category","block_exabis_eportfolio");

            $cat_header .= "<img  src=\"pix/$icon\"  alt='".get_string("updownarrow", "block_exabis_eportfolio")."' />";
        break;
        default:
            $name_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=name&amp;sortorder=desc'>" . get_string("name", "block_exabis_eportfolio");
	        $date_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=date'>" . get_string("date","block_exabis_eportfolio");
	        $cat_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=category'>" . get_string("category","block_exabis_eportfolio");
	        $date_header .= "<img src=\"pix/desc.gif\"  alt='".get_string("updownarrow", "block_exabis_eportfolio")."' />";
	
	        $sortkey = "timemodified";
	        $sortorder = "desc";
	   break;        
    }
    $name_header .= "</a>";
    $date_header .= "</a>";
    $cat_header  .= "</a>";

    $table = new stdClass();
    $table->head  = array ($cat_header, $name_header, $date_header);
    $table->align = array("CENTER","LEFT", "CENTER");
    $table->size = array("20%", "47%", "33%");
    $table->width = "85%";

    $secondorder = "";
    if ( $sortkey ==  "date" ) {
        $sortkey = "timemodified";
    } elseif ($sortkey == "category") {
        $sortkey = "cname";
        $secondorder = ", timemodified";
    }

    
    $bookmarks = get_records_sql(
   "(SELECT b.*, bc.name AS cname FROM {$CFG->prefix}block_exabeporbookfile b
       JOIN {$CFG->prefix}block_exabeporcate bc ON b.category = bc.id
       JOIN {$CFG->prefix}block_exabeporsharfile bfs ON b.id=bfs.bookid
       WHERE b.shareall='0' AND b.userid='$original' AND bfs.userid='{$USER->id}')
    UNION
    (SELECT b.*, bc.name AS cname  FROM {$CFG->prefix}block_exabeporbookfile b
       JOIN {$CFG->prefix}block_exabeporcate bc ON b.category = bc.id
       LEFT JOIN ( SELECT * FROM {$CFG->prefix}block_exabeporsharfile WHERE userid='{$USER->id}' ) bfs ON b.id = bfs.bookid
       WHERE b.shareall='1' AND b.userid='$original' AND bfs.userid IS NULL)
    ORDER BY $sortkey $sortorder $secondorder");
                                 
    if ( $bookmarks ) {
        $lastcat = "";
        foreach ($bookmarks as $bookmark) {
            $name = "";
            	$name .= "<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/bookmark_view_file.php?courseid=$courseid&amp;original=$original&amp;bookid=$bookmark->id\">" . format_string($bookmark->name) . "</a>";
            	
            if ($bookmark->intro) {
                $bookmark->intro = "<div class='block_eportfolio_italic'>" . format_text($bookmark->intro, FORMAT_PLAIN, $options=NULL) . "</div>";
                $name .= "<br /><table  width=\"98%\"><tr><td>".format_text($bookmark->intro, FORMAT_HTML)."</td></tr></table>";
            }

            $date = "" . userdate($bookmark->timemodified) . "";
            $icons = "";
            $category = format_string($bookmark->cname);
            if ( $lastcat ==  $category and $sortkey == "cname") {
                $category = "";
            } else {
                $lastcat = $category;
            }
            $table->data[] = array($category, $name,$date, $icons);
    	}
    	print_table($table);
    } else {
        echo "" . get_string("nobookmarksfile","block_exabis_eportfolio"). "";
    }
    echo "";

    unset($table);

    echo "<br />";

    print_simple_box( text_to_html(get_string("explainingnote","block_exabis_eportfolio")) , "center");


    echo "<br />";

    $asc_icon = "asc.gif";
    $desc_icon = "desc.gif";

    if ( $sortorder == "desc") {
        $icon = $desc_icon;
        $neworder = "asc";
    } else {
        $icon = $asc_icon;
        $neworder = "desc";
    }
    
    $name_header = '';
    $date_header = '';
    $cat_header = '';
	
    switch ( $sortkey ) {
        case "name":
            $name_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=name&amp;sortorder=$neworder'>" . get_string("name", "block_exabis_eportfolio");
            $date_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=date'>" . get_string("date","block_exabis_eportfolio");
            $cat_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=category'>" . get_string("category","block_exabis_eportfolio");

            $name_header .= "<img src=\"pix/$icon\"  alt='".get_string("updownarrow", "block_exabis_eportfolio")."' />";
        break;
        case "date":
            $name_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=name'>" . get_string("name", "block_exabis_eportfolio");
            $date_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=date&amp;sortorder=$neworder'>" . get_string("date","block_exabis_eportfolio");
            $cat_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=category'>" . get_string("category","block_exabis_eportfolio");

            $date_header .= "<img src=\"pix/$icon\" alt='".get_string("updownarrow", "block_exabis_eportfolio")."' />";
        break;
        case "category":
            $name_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=name'>" . get_string("name", "block_exabis_eportfolio");
            $date_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=date'>" . get_string("date","block_exabis_eportfolio");
            $cat_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=category&amp;sortorder=$neworder'>" . get_string("category","block_exabis_eportfolio");

            $cat_header .= "<img  src=\"pix/$icon\"  alt='".get_string("updownarrow", "block_exabis_eportfolio")."' />";
        break;
        default:
            $name_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=name&amp;sortorder=desc'>" . get_string("name", "block_exabis_eportfolio");
	        $date_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=date'>" . get_string("date","block_exabis_eportfolio");
	        $cat_header = "<a href='{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?original=$original&amp;courseid=$courseid&amp;sortkey=category'>" . get_string("category","block_exabis_eportfolio");
	        $date_header .= "<img src=\"pix/desc.gif\" alt='".get_string("updownarrow", "block_exabis_eportfolio")."' />";
	
	        $sortkey = "timemodified";
	        $sortorder = "desc";
	   break;
    }
    $name_header .= "</a>";
    $date_header .= "</a>";
    $cat_header  .= "</a>";

    
    $table = new stdClass();
    $table->head  = array ($cat_header, $name_header, $date_header);
    $table->align = array("CENTER","LEFT", "CENTER");
    $table->size = array("20%", "47%", "33%");
    $table->width = "85%";

    $secondorder = "";
    if ( $sortkey ==  "date" ) {
        $sortkey = "timemodified";
    } elseif ($sortkey == "category") {
        $sortkey = "cname";
        $secondorder = ", timemodified";
    }

    $bookmarks = get_records_sql(
   "(SELECT b.*, bc.name AS cname FROM {$CFG->prefix}block_exabepornote b
       JOIN {$CFG->prefix}block_exabeporcate bc ON b.category = bc.id
       JOIN {$CFG->prefix}block_exabeporsharnote bfs ON b.id=bfs.bookid
       WHERE b.shareall='0' AND b.userid='$original' AND bfs.userid='{$USER->id}')
    UNION
    (SELECT b.*, bc.name AS cname  FROM {$CFG->prefix}block_exabepornote b
       JOIN {$CFG->prefix}block_exabeporcate bc ON b.category = bc.id
       LEFT JOIN ( SELECT * FROM {$CFG->prefix}block_exabeporsharnote WHERE userid='{$USER->id}' ) bfs ON b.id = bfs.bookid
       WHERE b.shareall='1' AND b.userid='$original' AND bfs.userid IS NULL)
    ORDER BY $sortkey $sortorder $secondorder");      
    
    if ( $bookmarks ) {
        $lastcat = "";
        foreach ($bookmarks as $bookmark) {

            $name = "";
            $name .= "<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/bookmark_view_note.php?courseid=$courseid&amp;original=$original&amp;bookid=$bookmark->id\">" . format_string($bookmark->name) . "</a>";

            if ($bookmark->intro) {
                $bookmark->intro = "<div class='block_eportfolio_italic'>" . format_text($bookmark->intro, FORMAT_PLAIN, $options=NULL) . "</div>";
                $name .= "<br /><table width=\"98%\"><tr><td>".format_text($bookmark->intro, FORMAT_HTML)."</td></tr></table>";
            }

            $date = userdate($bookmark->timemodified);
            $icons = "";
            $category = format_string($bookmark->cname);
            if ( $lastcat ==  $category and $sortkey == "cname") {
                $category = "";
            } else {
                $lastcat = $category;
            }
            $table->data[] = array($category, $name,$date, $icons);
    	}
    	print_table($table);
    } else {
        echo "" . get_string("nobookmarksnote","block_exabis_eportfolio"). "";
    }

    echo "<br /><br />";
    
    echo "</div>";

    print_footer($course);

?>
