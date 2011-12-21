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
    function get_user_from_hash($hash) {
		if (! $hashrecord = get_record("block_exabeporexte", "user_hash", $hash) )
	        return false;
    	else
    	    return get_record("user", "id", $hashrecord->user_id);
    }


function print_extcomment($comment) {
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
        $by->name = $fullname;
        $by->date = userdate($comment->timemodified);
        print_string('bynameondate', 'forum', $by);
        
        echo '</div></td></tr>';

        echo '<tr><td class="left side">';

        echo '</td><td class="content">'."\n";
        
        echo format_text($comment->entry);
        
        echo '</td></tr></table>'."\n\n";

}

?>
