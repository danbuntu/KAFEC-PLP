<?php

    /**
     * User settings page output file for MoodleTxt.
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2008082112
     * @since 2007082312
     */

    $languageStrings = array(
        'settingslegendedittemplate',
        'settingsnotemplateselected',
        'settingsedittemplate',
        'warnunicode'
    );

?>
    <?php echo($accessiblelinks); ?>
    <?php if (moodletxt_get_setting('jQuery_Include_Enabled') == 1) { ?><script type="text/javascript" src="js/jquery.js"></script><?php } ?>
    <script type="text/javascript" src="js/lib.js"></script>
    <script type="text/javascript" src="js/jquery.json.min.js"></script>
    <script type="text/javascript" src="js/jquery.timers.js"></script>
    <script type="text/javascript" src="js/jquery.selectboxes.js"></script>
    <script type="text/javascript" src="js/moodletxt.js"></script>
    <script type="text/javascript">
    <!--
    //<![CDATA[

        // Declarations from PHP - must stay here, not in library file

        var templateArray = new Array();
        <?php echo($jsarray); ?>

        var language = new Array(<?php echo(count($languageStrings)); ?>);
        <?php for($x = 0; $x < count($languageStrings); $x++) echo ("
            language['" . $languageStrings[$x] . "'] = '" . addslashes(get_string($languageStrings[$x], 'block_moodletxt')) . "';"
        ); ?>

    //]]>
    //-->
    </script>
    <!--
        EXISTING TEMPLATES LIST
    -->
    <div class="mdltxt_left">
        <h2><?php echo(get_string('settingstemplateheader', 'block_moodletxt')); ?></h2>
        <fieldset class="mdltxt_userset">
            <legend><?php echo(get_string('settingstemplateeditleg', 'block_moodletxt')); ?></legend>
            <?php echo(moodletxt_vomit_errors(array('noTemplate', 'invalidTemplateID', 'templateDeleteFailed'), $errorArray)); ?>
            <?php echo(moodletxt_vomit_errors(array('templateDeleted'), $noticeArray, true)); ?>
            <form action="<?php echo($ME); ?>" method="post">
                <input type="hidden" name="courseid" value="<?php echo($courseid); ?>" />
                <input type="hidden" name="formid" value="deletetemplate" />
                <select id="currenttemplates" name="currenttemplates" size="5" <?php if ($disableTemplateForm) echo('disabled="disabled" '); ?>style="float:left;width:50%;margin-right:1em;">
                    <?php echo($templatelist); ?>
                </select>
                <input type="button" id ="editTemplateButton" value="<?php echo(get_string('settingsbuttemplateedit', 'block_moodletxt')); ?>" <?php if ($disableTemplateForm) echo('disabled="disabled" '); ?>/><br /><br />
                <input type="submit" value="<?php echo(get_string('settingsbuttemplatedel', 'block_moodletxt')); ?>" <?php if ($disableTemplateForm) echo('disabled="disabled" '); ?>/>
            </form>
        </fieldset>
        <!--
            ADD NEW TEMPLATE FORM
        -->
        <fieldset class="mdltxt_userset">
            <legend id="templateEditLegend"><?php echo(get_string('settingstemplateaddleg', 'block_moodletxt')); ?></legend>
            <?php echo(moodletxt_vomit_errors(array('templateTooLong', 'templateInsertFailed', 'templateUpdateFailed'), $errorArray)); ?>
            <?php echo(moodletxt_vomit_errors(array('templateAdded', 'templateUpdated'), $noticeArray, true)); ?>
            <form id="editTemplateForm" action="<?php echo($ME); ?>" method="post">
                <input type="hidden" name="courseid" value="<?php echo($courseid); ?>" />
                <input id="templateEditFormID" type="hidden" name="formid" value="newtemplate" />
                <input id="editTemplateID" type="hidden" name="editTemplateID" value="0" />
                <?php echo(get_string('sendlabelcharsused', 'block_moodletxt')); ?>
                <input type="text" id="charsUsed" name="notimportant" size="4" />
                Characters per message: <span id="charactersPerMessage"></span><br />
                <textarea rows="4" name="templateEdit" id="templateEdit" style="width:100%;" ><?php if (isset($inNewTemplate)) echo($inNewTemplate); ?></textarea><br />
                <span style="padding-left:2pca"><?php echo(get_string('sendlabelnametags', 'block_moodletxt')); ?></span><br /><br />
                <span style="padding-left:2pca;">
                    <input type="button" id="tagFirstName" value="<?php echo(get_string('sendbuttag1', 'block_moodletxt')); ?>" />
                    <input type="button" id="tagLastName" value="<?php echo(get_string('sendbuttag2', 'block_moodletxt')); ?>" />
                    <input type="button" id="tagFullName" value="<?php echo(get_string('sendbuttag3', 'block_moodletxt')); ?>" />
                </span>
                <p id="unicodeMessage" style="margin-top:2em;"></p>
                <input type="submit" id="templateEditSubmit" value="<?php echo(get_string('settingssubmittemplate', 'block_moodletxt')); ?>" style="float:right;" />
            </form>
        </fieldset>
    </div>
    <!--
        SIGNATURE EDIT FORM
    -->
    <div class="mdltxt_right">
        <h2><?php echo(get_string('settingssigheader', 'block_moodletxt')); ?></h2>
        <?php echo(moodletxt_vomit_errors(array('sigTooLong', 'sigUpdateFailed'), $errorArray)); ?>
        <?php echo(moodletxt_vomit_errors(array('sigUpdated'), $noticeArray, true)); ?>
        <form name="changesig" action="<?php echo($ME); ?>" method="post">
            <input type="hidden" name="courseid" value="<?php echo($courseid); ?>" />
            <input type="hidden" name="formid" value="signature" />
            <?php echo(get_string('settingscharsremain', 'block_moodletxt')); ?>
            <input type="text" name="sigCharsLeft" id="sigCharsLeft" size="2" maxlength="2" /><br />
            <input type="text" name="signature" id="signature" size="30" maxlength="25" value="<?php echo($inSignature); ?>" onkeydown="javascript:updateCharsRemaining(this, sigCharsLeftBox, 25);" onkeyup="javascript:updateCharsRemaining(this, sigCharsLeftBox, 25);" />
            <input type="submit" name="sigSubmit" value="<?php echo(get_string('settingssubmitsignature', 'block_moodletxt')); ?>" />
        </form>
    </div>
    <div class="clearer">
    </div>
    <script type="text/javascript">
    </script>
