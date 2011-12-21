<?php

    /**
     * Output file for user address book management
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2009102912
     * @since 2008112112
     */
?>
    <?php if (moodletxt_get_setting('jQuery_Include_Enabled') == 1) { ?><script type="text/javascript" src="js/jquery.js"></script><?php } ?>
    <script type="text/javascript" src="js/jquery.selectboxes.js"></script>
    <script type="text/javascript" src="js/addressbook_add_contact.js"></script>
    <form id="addcontact" action="<?php echo($ME); ?>" method="post">
        <input type="hidden" name="ab" value="<?php echo($addressbook->id); ?>" />
        <input type="hidden" name="courseid" value="<?php if (is_object($course)) echo($course->id); ?>" />
        <div class="mdltxt_half_centred">
            <?php echo(moodletxt_vomit_errors(array('noName', 'noNumber', 'invalidNumber', 'addFailed'), $errorArray)); ?>
            <?php echo(moodletxt_vomit_errors(array('contactAdded'), $noticeArray, true)); ?>
            <label class="mdltxt_align_form" for="firstname"><?php echo(get_string('addcontactlblfirstname', 'block_moodletxt')); ?> </label>
            <input type="text" size="25" name="firstname" id="firstname" value="<?php echo($firstname); ?>" /><br />
            <label class="mdltxt_align_form" for="lastname"><?php echo(get_string('addcontactlbllastname', 'block_moodletxt')); ?> </label>
            <input type="text" size="25" name="lastname" id="lastname" value="<?php echo($lastname); ?>" /><br />
            <label class="mdltxt_align_form" for="company"><?php echo(get_string('addcontactlblcompany', 'block_moodletxt')); ?> </label>
            <input type="text" size="25" name="company" id="company" value="<?php echo($company); ?>" /><br />
            <label class="mdltxt_align_form" for="phoneno"><?php echo(get_string('addcontactlblphoneno', 'block_moodletxt')); ?> </label>
            <input type="text" size="25" name="phoneno" id="phoneno" maxlength="14" value="<?php echo($phoneno); ?>" /><br /><br />
            <h2 style="text-align:center;"><?php echo(get_string('addcontactheadergroups', 'block_moodletxt')); ?></h2>
            <p style="text-align:center;"><?php echo(get_string('addcontactpara1', 'block_moodletxt')); ?></p>
            <div style="text-align:center;">
                <div class="mdltxt_left" style="text-align:left;">
                    <select size="10" name="potentialGroups" id="potentialGroups" multiple="multiple" style="width:50%;">
                        <?php echo($groupListString); ?>
                    </select>
                </div>
                <div class="mdltxt_right" style="text-align:right;">
                    <select size="10" name="selectedGroups" id="selectedGroups" multiple="multiple" style="width:50%;">
                        <?php echo($selectedGroupListString); ?>
                    </select>
                </div>
                <input type="button" id="select_multiple" value="&gt; &gt; &gt;" /><br />
                <input type="button" id="deselect_multiple" value="&lt; &lt; &lt;" /><br /><br />
                <input type="submit" name="addButton" value="<?php echo(get_string('addcontactbutton', 'block_moodletxt')); ?>" /><br />
                <input type="submit" name="addButtonReturn" value="<?php echo(get_string('addcontactbuttonreturn', 'block_moodletxt')); ?>" />
            </div>
            <div class="clearer"></div>
        </div>
    </form>
