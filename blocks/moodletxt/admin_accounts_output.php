<?php

    /**
     * Account listing output file
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011030101
     * @since 2007082312
     */

    $languageStrings = array(
        'adminaccountconfirmupdate',
        'adminaccountfragoutbound',
        'adminaccountfragloading',
        'adminaccountfraginbound',
        'adminaccountfragdenied',
        'adminaccountprocessedfrag',
        'adminaccountupdatefailed',
        'adminaccountupdatesuccess',
        'erroroperationinprogress',
        'acctableaccounttypeinvoiced',
        'acctableaccounttypeprepaid'
    );

?>
    <?php if (moodletxt_get_setting('jQuery_Include_Enabled') == 1) { ?><script type="text/javascript" src="js/jquery.js"></script><?php } ?>
    <?php if (moodletxt_get_setting('jQuery_UI_Include_Enabled') == 1) { ?><script type="text/javascript" src="js/jquery.ui.min.js"></script><?php } ?>
    <script type="text/javascript" src="js/jquery.json.min.js"></script>
    <script type="text/javascript" src="js/admin_accounts.js"></script>
    <script type="text/javascript" src="js/jquery.timers.js"></script>
    <script type="text/javascript" src="js/jquery.colour.js"></script>
    <script type="text/javascript" src="js/lib.js"></script>
    <script type="text/javascript">
    //<!--
    //<![CDATA[

        /*
          ############################################################
          # Javascript declarations that originate from PHP -
          # must stay in this file!
          ############################################################
        */

        // Declarations from PHP - must stay here, not in library file
        var confirmUpdate = '<?php echo(get_string('adminaccountsconfirmupdate', 'block_moodletxt')); ?>';

        var accountArray = new Array(); <?php echo($javascriptRowArray); ?>

        var language = new Array(<?php echo(count($languageStrings)); ?>);
        <?php for($x = 0; $x < count($languageStrings); $x++) echo ("
            language['" . $languageStrings[$x] . "'] = '" . addslashes(get_string($languageStrings[$x], 'block_moodletxt')) . "';"
        ); ?>

    //]]>
    //-->
    </script>
    <div style="text-align:center;">
        <p>
            <?php echo(get_string('adminaccountintropara1', 'block_moodletxt')); ?>
        </p>
        <p>
            <?php echo(get_string('adminaccountintropara2', 'block_moodletxt')); ?>
        </p>
        <button type="button" id="updateAllAccounts"><?php echo(get_string('adminaccountbutupdateall', 'block_moodletxt')); ?></button>
        <div id="accountProgressBar" class="ui-progressbar">
            <div id="accountProgressTextValue" class="ui-progressbar-textvalue"></div>
        </div>
    </div>