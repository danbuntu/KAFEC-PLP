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
    require_once("{$CFG->dirroot}/blocks/exabis_eportfolio/externlib.php");
    
	$id = optional_param('id', 0, PARAM_ALPHANUM);
	$bookid = optional_param('bookid', 0, PARAM_INT);
		
	require_login(0, true);
	
    if(!$user = get_user_from_hash($id)) {
    	error("No user for this id.");
    }
    
    $bookmark = get_record("block_exabeporbooklink", "id", $bookid, "userid", $user->id, "externaccess", "1");
    if (!$bookmark) {
	    error("Bookmark not found!");
    }

    print_header(get_string("externaccess", "block_exabis_eportfolio"), get_string("externaccess", "block_exabis_eportfolio") . fullname($user, $user->id));
    
    echo "<div class='block_eportfolio_center'>\n";

	show_ext($bookmark);

	if($bookmark->externcomment) {
		show_comments($bookmark->id);
	}

    echo "<br /><a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/extern.php?id=$id\">".get_string("back","block_exabis_eportfolio")."</a><br /><br />";

    echo "</div>";
    print_footer();
    die;

    function show_ext($bookmark) {
    	print_heading(format_string($bookmark->name));
    	$link = clean_param($bookmark->url, PARAM_URL);
    	$link_js = str_replace('http://', '', $link);
    	//convert_urls_into_links($link);
    	
        if ($bookmark->url) {
    		print_box('<p><a href="#" onclick="window.open(\'http://' . addslashes_js($link_js) . '\',\'validate\',\'width=620,height=450,scrollbars=yes,status=yes,resizable=yes,menubar=yes,location=yes\');return true;">' . $link . '</a></p>' . format_text(nl2br($bookmark->intro)));
	    }
	    else {
    		print_box(format_text($bookmark->intro, FORMAT_HTML));
    	}
    }

    function show_comments($bookmarkid) {
    	$comments = get_records("block_exabeporcommlink", "bookmarkid", $bookmarkid, 'timemodified DESC');
    	if($comments) {
    		foreach ($comments as $comment) {
    			print_extcomment($comment);
    		}
    	}
    }
?>