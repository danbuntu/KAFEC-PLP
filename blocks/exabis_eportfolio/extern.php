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
    require_once("{$CFG->dirroot}/blocks/exabis_eportfolio/lib.php");

	$id = optional_param('id', 0, PARAM_ALPHANUM);
	
	require_login(0, true);
	    
    if(!$user = get_user_from_hash($id)) {
    	print_error("nouserforid","block_exabis_eportfolio");

    }
    
    print_header(get_string("externaccess", "block_exabis_eportfolio"), get_string("externaccess", "block_exabis_eportfolio") . " " . fullname($USER, $USER->id));
    
    echo "<br />";

    echo "<div class='block_eportfolio_center'>\n";
    $table = new stdClass();
    $table->head  = array (get_string("name", "block_exabis_eportfolio"), get_string("date","block_exabis_eportfolio"));
    $table->align = array("CENTER","LEFT", "CENTER","CENTER");
    $table->size = array("20%", "37%", "28%","15%");
    $table->width = "85%";
    
    // the uid has to be generated because there can be more than one entry with the same id
    
   $bookmarks = get_records_sql
   ( "(SELECT b.id + 10000000 as uid, b.id, b.url, b.name, b.intro, b.attachment, b.timemodified, CONCAT_WS(' &rArr; ', bc2.name, bc.name) AS cname, 'view_external_link.php' AS linkfile FROM {$CFG->prefix}block_exabeporbooklink b
        JOIN {$CFG->prefix}block_exabeporcate bc ON b.category = bc.id
        LEFT JOIN {$CFG->prefix}block_exabeporcate bc2 on bc.pid = bc2.id
        WHERE b.externaccess='1' AND b.userid='{$user->id}')
        UNION
      (SELECT b.id + 20000000 as uid, b.id, b.url, b.name, b.intro, b.attachment, b.timemodified, CONCAT_WS(' &rArr; ', bc2.name, bc.name) AS cname, 'view_file.php' AS linkfile FROM {$CFG->prefix}block_exabeporbookfile b
        JOIN {$CFG->prefix}block_exabeporcate bc ON b.category = bc.id
        LEFT JOIN {$CFG->prefix}block_exabeporcate bc2 on bc.pid = bc2.id
        WHERE b.externaccess='1' AND b.userid='{$user->id}')
        UNION
      (SELECT b.id + 30000000 as uid, b.id, b.url, b.name, b.intro, b.attachment, b.timemodified, CONCAT_WS(' &rArr; ', bc2.name, bc.name) AS cname, 'view_note.php' AS linkfile FROM {$CFG->prefix}block_exabepornote b
        JOIN {$CFG->prefix}block_exabeporcate bc ON b.category = bc.id
        LEFT JOIN {$CFG->prefix}block_exabeporcate bc2 on bc.pid = bc2.id
        WHERE b.externaccess='1' AND b.userid='{$user->id}')
       ORDER BY cname");
       
    if ( $bookmarks ) {
        $lastcat = "";
        $firstrow = true;
        foreach ($bookmarks as $bookmark) {
	    	if($lastcat != $bookmark->cname) {
    			if($firstrow) {
    				$firstrow = false;
    			}
    			else {
    				print_table($table);
    			}
    			print_heading(format_string($bookmark->cname));
        		$lastcat = $bookmark->cname;
        		unset($table->data);
        	}
            $name = "";
            $name .= "<a href=\"{$bookmark->linkfile}?id=$id&amp;bookid={$bookmark->id}\">" . format_string($bookmark->name) . "</a>";

            if ($bookmark->intro) {
                $name .= "<br /><table width=\"98%\"><tr><td class='block_eportfolio_externalview'>" . format_text($bookmark->intro) . "</td></tr></table>";
        	}

            $date = userdate($bookmark->timemodified) ;

            $table->data[] = array($name,$date);
    	}
    	print_table($table);
    }
    else {
        echo "<div class='block_eportfolio_center'>" . get_string("nobookmarksexternal","block_exabis_eportfolio"). "</div>";
    }

    echo "<br />";

    echo "</div>\n";

    print_footer();
?>
