<?php

    /**
     * "New installation" output file for admin page
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011030101
     * @since 2007082312
     */

?>
    <?php if (moodletxt_get_setting('jQuery_Include_Enabled') == 1) { ?><script type="text/javascript" src="js/jquery.js"></script><?php } ?>
    <script type="text/javascript" src="js/admin_output_new.php"></script>
    <div class="mdltxt_half_centred">
    <h2><?php echo(get_string('adminintroheader1', 'block_moodletxt')); ?></h2>
    <p>
        <?php echo(get_string('adminintropara1', 'block_moodletxt')); ?>
    </p>
    <h3 style="margin-top:2em;"><?php echo(get_string('adminintroheader2', 'block_moodletxt')); ?></h3>
    <p>
        <?php echo(get_string('adminintropara2', 'block_moodletxt')); ?>
    </p>
    <p>
        <?php echo(get_string('adminintropara3', 'block_moodletxt')); ?>
    </p>
    <h3 style="margin-top:2em;"><?php echo(get_string('adminintroheader3', 'block_moodletxt')); ?></h3>
    <p>
        <?php echo(get_string('adminintropara4', 'block_moodletxt')); ?>
    </p>
    <p>
        <?php echo(get_string('adminintropara5', 'block_moodletxt')); ?>
    </p>
    <?php echo($connErrorString); ?>
    <h2 style="margin-top:2em;">Account Details</h2>
        <form action="<?php echo($ME); ?>" method="post">
            <input type="hidden" name="formid" value="newinstall" />
            <fieldset>
                <legend><?php echo(get_string('adminlabeladdaccount', 'block_moodletxt')); ?></legend>
                <?php echo(moodletxt_vomit_errors(array('addNoAccountName', 'addNoPassword', 'addNoMatch', 'addExists', '401', '403', '404', '500', '503', '601', '602', '603', '604', '605', '606', 'invalidaccount', 'addNotInserted'), $errorArray)); ?>
                <label for="addaccountname" class="mdltxt_align_form">
                    <?php echo(get_string('adminlabelaccusername', 'block_moodletxt')); ?>
                </label>
                <input type="text" id="addaccountname" name="accountname" size="20" maxlength="20"<?php echo($addAccountName); ?> /><br />
                <label for="addaccountpass" class="mdltxt_align_form">
                    <?php echo(get_string('adminlabelaccpassword', 'block_moodletxt')); ?>
                </label>
                <input id="addaccountpass" type="password" name="password1" size="20" maxlength="20" /><br />
                <label for="addaccountpass2" class="mdltxt_align_form">
                    <?php echo(get_string('adminlabelaccpassword2', 'block_moodletxt')); ?>
                </label>
                <input id="addaccountpass2" type="password" name="password2" size="20" maxlength="20" /><br />
                <label for="addaccountdesc" class="mdltxt_align_form">
                    <?php echo(get_string('adminlabelaccdesc', 'block_moodletxt')); ?>
                </label>
                <input id="addaccountdesc" type="text" name="accountdescription" size="20" maxlength="255"<?php echo(stripslashes($addDescription)); ?> /><br />
                <label for="addaccountinbox" class="mdltxt_align_form">
                    <?php echo(get_string('adminlabelaccinbox', 'block_moodletxt')); ?>
                </label>
                <select id="addaccountinbox" name="defaultinbox" size="1">
                    <?php echo($defaultinboxlist); ?>
                </select><br /><br />
                <span style="float:right;">
                    <input type="submit" value="<?php echo(get_string('adminbutaddaccount', 'block_moodletxt')); ?>" />
                </span>
            </fieldset>
            <input id="showProxySettings" type="button" value="<?php echo(get_string('adminlabelshowproxy', 'block_moodletxt')); ?>" />
            <fieldset id="proxySettings">
                <legend><?php echo(get_string('adminlabelproxysettings', 'block_moodletxt')); ?></legend>
                <label for="proxyAddress" class="mdltxt_align_form">
                    <?php echo(get_string('adminlabelproxyaddress', 'block_moodletxt')); ?>
                </label>
                <input id="proxyAddress" type="text" size="15" name="Proxy_Host" value="<?php echo($settings['Proxy_Host']->value); ?>" /><br />
                <label for="proxyPort" class="mdltxt_align_form">
                    <?php echo(get_string('adminlabelproxyport', 'block_moodletxt')); ?>
                </label>
                <input id="proxyPort" type="text" size="06" name="Proxy_Port" value="<?php echo($settings['Proxy_Port']->value); ?>" /><br />
                <label for="proxyUsername" class="mdltxt_align_form">
                    <?php echo(get_string('adminlabelproxyusername', 'block_moodletxt')); ?>
                </label>
                <input id="proxyUsername" type="text" size="15" name="Proxy_Username" value="<?php echo($settings['Proxy_Username']->value); ?>" /><br />
                <label for="proxyPassword" class="mdltxt_align_form">
                    <?php echo(get_string('adminlabelproxypassword', 'block_moodletxt')); ?>
                </label>
                <input id="proxyPassword" type="password" size="15" name="Proxy_Password" value="<?php echo($settings['Proxy_Password']->value); ?>" /><br />
                <span style="float:right;">
                    <input type="submit" value="<?php echo(get_string('adminbutaddaccount', 'block_moodletxt')); ?>" />
                </span>
            </fieldset>
        </form>
    </div>