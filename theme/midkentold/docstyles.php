<?PHP /*  $Id: docstyles.php,v 1.1 2005/04/01 15:49:35 andreabix Exp $ */

/// We use PHP so we can do value substitutions into the styles

    if (!isset($themename)) {
        $themename = NULL;
    }

    $nomoodlecookie = true;
    require_once("../../config.php");
    $themeurl = style_sheet_setup(filemtime("styles.php"), 300, $themename);

///
/// You can hardcode colours in this file if you
/// don't care about this.

?>
