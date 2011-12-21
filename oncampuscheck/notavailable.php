<?php // $Id: notavailable.php,v 1.0 2007/08/30 by Red Morris Exp $

    require_once("../config.php");
    $onoroffcampus = "oncampus";

    //initialize variables
    $errormsg = '';

/// Check for timed out sessions
    if (!empty($SESSION->has_timed_out)) {
        $session_has_timed_out = true;
        $SESSION->has_timed_out = false;
    } else {
        $session_has_timed_out = false;
    }

/// Define variables used in page
    if (!$site = get_site()) {
        error("No site found!");
    }

    if (empty($CFG->langmenu)) {
        $langmenu = "";
    } else {
        $currlang = current_language();
        $langs    = get_list_of_languages();
        $langmenu = popup_form ("$CFG->httpswwwroot/login/index.php?lang=", $langs, "chooselang", $currlang, "", "", "", true);
    }

    $loginsite = get_string("loginsite");

/// Generate the page
    print_header("$site->fullname: Unable to access", $site->fullname, "Unable to access", "", 
                 '', true, '<div class="langmenu" align="right">'.$langmenu.'</div>'); 

    include("notavailable.html");

    print_footer();


?>
