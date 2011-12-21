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
// Print a list of teachers and students of a specific course
function print_user_listing($courseid, $bookid, $table,$course) {
	global $USER;
    $name=get_string("name");
    $role=get_string("role");
    
	echo "<table width=\"70%\">";
	echo "<tr><th align=\"center\">&nbsp;</th><th align=\"left\">$name</th><th align=\"right\">$role</th></tr>";

    $teachers = get_course_teachers($courseid);
	if ($teachers){
	    foreach ($teachers as $teacher){
		if ($teacher->username != "guest" && $teacher->lastname != "" && $teacher->firstname != ""){
				// && $teacher->id != $USER->id){
	            echo "<tr><td align=\"center\">";
	            $count_this=count_records($table, "bookid", $bookid, "userid", $teacher->id);
	            if ($count_this >= 1){
	                echo "<input type=\"checkbox\" name=\"sharethis[]\" value=\"$teacher->id\" checked=\"checked\" />";
	            }else{
	  	        		echo "<input type=\"checkbox\" name=\"sharethis[]\" value=\"$teacher->id\" />";
	            }
	            echo "</td><td align=\"left\">" . fullname($teacher, $teacher->id) . "</td><td align=\"right\">$course->teacher</td></tr>";
	        } 
	    }
	}
	$students = get_course_students($courseid);
	if ($students){
	    foreach ($students as $student){
			if ($student->username != "guest" && $student->lastname != "" && $student->firstname != ""){
				// && $student->id != $USER->id){
	            echo "<tr><td align=\"center\">";
	            $count_this=count_records($table, "bookid", $bookid, "userid", $student->id);
	            if ($count_this >= 1){
	                echo "<input type=\"checkbox\" name=\"sharethis[]\" value=\"$student->id\" checked=\"checked\" />";
	            }else{
	  	        	echo "<input type=\"checkbox\" name=\"sharethis[]\" value=\"$student->id\" />";
	            }
	            echo "</td><td align=\"left\">" . fullname($student, $student->id) . "</td><td align=\"right\">$course->student</td></tr>";
	    	}
	    }
	}
    echo "</table>";
}

function get_extern_access($userid) {
	global $CFG;
	if (! $access = get_record("block_exabeporexte", "user_id", $userid) ) {
	 $externaccess = new stdClass();	
        $externaccess->user_id = $userid;
        do {
        	$hash = substr(md5(uniqid(rand(), true)), 3, 8);
        } while(record_exists("block_exabeporexte", "user_hash", $hash));
        $externaccess->user_hash = $hash;
	    insert_record("block_exabeporexte", $externaccess);
    	return "extern.php?id={$externaccess->user_hash}";
    }
    else {
    	return "extern.php?id={$access->user_hash}";
    }
}

function print_js() {
    echo "<script type=\"text/javascript\">\n";
    echo "<!--\n";
    echo "function SetAllCheckBoxes(FormName, FieldName, CheckValue)\n";
    echo "{\n";
    echo "	if(!document.getElementById(FormName))\n";
    echo "		return;\n";
    echo "	var objCheckBoxes = document.getElementById(FormName).elements[FieldName];\n";
    echo "	if(!objCheckBoxes)\n";
    echo "		return;\n";
    echo "	var countCheckBoxes = objCheckBoxes.length;\n";
    echo "	if(!countCheckBoxes)\n";
    echo "		objCheckBoxes.checked = CheckValue;\n";
    echo "	else\n";
    echo "		// set the check value for all check boxes\n";
    echo "		for(var i = 0; i < countCheckBoxes; i++)\n";
    echo "			objCheckBoxes[i].checked = CheckValue;\n";
    echo "      if (CheckValue == true)\n";
    echo "              document.getElementById(FormName).selectall.value = \"1\";\n";
    echo "      else\n";
    echo "              document.getElementById(FormName).selectall.value = \"0\";\n";
    echo "}\n";
    echo "// -->\n";
    echo "</script>\n";
}
?>
