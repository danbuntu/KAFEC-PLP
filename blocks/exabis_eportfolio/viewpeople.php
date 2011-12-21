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
    
    $context = get_context_instance(CONTEXT_SYSTEM);

    require_login($courseid);
    require_capability('block/exabis_eportfolio:use', $context);

    if (! $course = get_record("course", "id", $courseid) ) {
        error("That's an invalid course id");
    }

	$strbookmarks = get_string("sharedpersons", "block_exabis_eportfolio");
    $strmybookmarks = get_string("mybookmarks", "block_exabis_eportfolio");

    if ($courseid == SITEID) {
	    print_header("$SITE->shortname: $strbookmarks", $SITE->fullname,
	        $strbookmarks,
	                 '', '', true);
	}
    else {
	    print_header("$SITE->shortname: $strbookmarks", $course->fullname,
	        "<a href=\"{$CFG->wwwroot}/course/view.php?id={$courseid}\">" . $course->shortname . "</a> ->
	                 " . $strbookmarks,
	                 '', '', true);
    }
    

    echo "<div class='block_eportfolio_center'>\n";
   
    echo "<br />";

    print_simple_box( text_to_html(get_string("explainingshared","block_exabis_eportfolio")) , "center");

    echo "<br />";
    
    $all_shared_records = get_records_sql(
    " (SELECT bf.id, bf.userid, mu.picture, mu.firstname, mu.lastname FROM {$CFG->prefix}block_exabeporbookfile bf LEFT JOIN {$CFG->prefix}block_exabeporsharfile bfs ON bf.id=bfs.bookid JOIN {$CFG->prefix}user mu ON mu.id=bf.userid JOIN {$CFG->prefix}block_exabeporcate bcat ON bf.category=bcat.id WHERE bf.shareall='0' AND bfs.userid='{$USER->id}')
      UNION
      (SELECT bf.id, bf.userid, mu.picture, mu.firstname, mu.lastname  FROM {$CFG->prefix}block_exabeporbooklink bf LEFT JOIN {$CFG->prefix}block_exabeporsharlink bfs ON bf.id=bfs.bookid JOIN {$CFG->prefix}user mu ON mu.id=bf.userid JOIN {$CFG->prefix}block_exabeporcate bcat ON bf.category=bcat.id WHERE bf.shareall='0' AND bfs.userid='{$USER->id}')
      UNION
      (SELECT bf.id, bf.userid, mu.picture, mu.firstname, mu.lastname  FROM {$CFG->prefix}block_exabepornote bf LEFT JOIN {$CFG->prefix}block_exabeporsharnote bfs ON bf.id=bfs.bookid JOIN {$CFG->prefix}user mu ON mu.id=bf.userid JOIN {$CFG->prefix}block_exabeporcate bcat ON bf.category=bcat.id WHERE bf.shareall='0' AND bfs.userid='{$USER->id}')
      UNION
      (SELECT bf.id, bf.userid, mu.picture, mu.firstname, mu.lastname  FROM {$CFG->prefix}block_exabeporbookfile bf LEFT JOIN ( SELECT * FROM {$CFG->prefix}block_exabeporsharfile WHERE userid='{$USER->id}' ) bfs ON bf.id = bfs.bookid JOIN {$CFG->prefix}block_exabeporcate bcat ON bf.category=bcat.id JOIN {$CFG->prefix}user mu ON mu.id=bf.userid WHERE bf.shareall='1' AND bfs.userid IS NULL)
      UNION
      (SELECT bf.id, bf.userid, mu.picture, mu.firstname, mu.lastname  FROM {$CFG->prefix}block_exabeporbooklink bf LEFT JOIN ( SELECT * FROM {$CFG->prefix}block_exabeporsharlink WHERE userid='{$USER->id}' ) bfs ON bf.id = bfs.bookid JOIN {$CFG->prefix}block_exabeporcate bcat ON bf.category=bcat.id JOIN {$CFG->prefix}user mu ON mu.id=bf.userid WHERE bf.shareall='1' AND bfs.userid IS NULL)
      UNION
      (SELECT bf.id, bf.userid, mu.picture, mu.firstname, mu.lastname  FROM {$CFG->prefix}block_exabepornote bf LEFT JOIN ( SELECT * FROM {$CFG->prefix}block_exabeporsharnote WHERE userid='{$USER->id}' ) bfs ON bf.id = bfs.bookid JOIN {$CFG->prefix}block_exabeporcate bcat ON bf.category=bcat.id JOIN {$CFG->prefix}user mu ON mu.id=bf.userid WHERE bf.shareall='1' AND bfs.userid IS NULL)
      ORDER BY userid; ");
					 
	// Unfortunately tehre is no GROUP BY possible after an UNION statement. So I have to take care that every user is printed only once.
	// That's the reason why I order by userid.
	
    $lastuser = 0;
    if (is_array($all_shared_records)){
    	echo "<table>";
	    foreach($all_shared_records as $student) {
	    	if($student->userid != $lastuser) {
	    		echo "<tr>";
	            echo "<td><a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?courseid=$courseid&amp;original=$student->userid\">";
	            
	            print_user_picture($student->userid, $courseid, $student->picture, 0, false, false);
	            echo "</a>&nbsp;</td>";
	            echo "<td>&nbsp;<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/viewshared.php?courseid=$courseid&amp;original=$student->userid\">".fullname($student, $student->id)."</a></td>";		
	            echo "</tr>";
	    		$lastuser = $student->userid;
	    	}
	    }
	    echo "</table>";
    }
	

    echo "</div>";
    print_footer($course);

?>
