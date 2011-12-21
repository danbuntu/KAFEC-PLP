<?php // $Id$

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodl.com                                           //
//                                                                       //
// Copyright (C) 2001-3001 Martin Dougiamas        http://dougiamas.com  //
//           (C) 2001-3001 Eloy Lafuente (stronk7) http://contiento.com  //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

 /***
     * This page will deploy an IMS Content Package from repository.
     * Arguments:
     *   - file   directory containing CP to deploy
     *   - all    if not set, will deploy 1 package
     *            if = true, will recursively deploy all packages
     *             found in directory file.
     *            if = force, same as above but will redeploy too.
     */

/// Required stuff
    require_once('../../../../config.php');
    require_once('../../lib.php');
    require_once('resource.class.php');
    require_once('../../../../backup/lib.php');
    require_once('../../../../lib/filelib.php');
    require_once('../../../../lib/xmlize.php');
    require_once('lib.php');

    /// Security - Admin Only  
    require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM, SITEID));

    $file       = required_param ('file', PARAM_PATH);
    $all        = optional_param ('all', '', PARAM_ALPHA);
    
    if ($all == '') {
        print_header();
        ims_deploy_file($file);
        print_footer();
    }
    else {
        print_header();
        ims_deploy_folder($file, $all);
        print_footer();
    }
	
?>
