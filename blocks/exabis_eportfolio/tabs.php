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

    $inactive = NULL;
    $activetwo = NULL;
    $toprow = array();
    
    $toprow[] = new tabobject('personal', $CFG->wwwroot.'/blocks/exabis_eportfolio/view.php?courseid='.$courseid, get_string("personal","block_exabis_eportfolio"));
    $toprow[] = new tabobject('categories', $CFG->wwwroot.'/blocks/exabis_eportfolio/viewcategories.php?courseid='.$courseid, get_string("categories","block_exabis_eportfolio"));
    $toprow[] = new tabobject('external', $CFG->wwwroot.'/blocks/exabis_eportfolio/view_external_links.php?courseid='.$courseid, get_string("bookmarksexternal","block_exabis_eportfolio"));
    $toprow[] = new tabobject('files', $CFG->wwwroot.'/blocks/exabis_eportfolio/view_files.php?courseid='.$courseid, get_string("bookmarksfiles","block_exabis_eportfolio"));
    $toprow[] = new tabobject('notes', $CFG->wwwroot.'/blocks/exabis_eportfolio/view_notes.php?courseid='.$courseid, get_string("bookmarksnotes","block_exabis_eportfolio"));
    $toprow[] = new tabobject('exportimport', $CFG->wwwroot.'/blocks/exabis_eportfolio/exportimport.php?courseid='.$courseid, get_string("exportimport","block_exabis_eportfolio"));
 
    print_tabs(array($toprow), $currenttab, $inactive, $activetwo);

?>
