<?php

require_once('../../../config.php');
error_reporting(0);

?>

<!--
//<![CDATA[

    /**
     * Javascript file for the "new installation" page
     *
     * @version 2009102312
     * @since 2009102312
     */

    var openState = false;

    var closedText = "<?php echo(get_string('adminlabelshowproxy', 'block_moodletxt')); ?>";
    var openText = "<?php echo(get_string('adminlabelhideproxy', 'block_moodletxt')); ?>";

    $(document).ready(function() {

        $('fieldset#proxySettings').hide();

        var showHideButton = $('input#showProxySettings');

        showHideButton.click(function(event) {
            if (openState) {
                $('fieldset#proxySettings').hide();
                showHideButton.val(closedText);
            } else if (!openState) {
                $('fieldset#proxySettings').show();
                showHideButton.val(openText);
            }
            openState = !openState;
        });

    });

// ]]>
-->