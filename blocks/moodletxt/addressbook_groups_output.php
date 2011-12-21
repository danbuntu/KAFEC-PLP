<?php

    /**
     * Output file for user address book management
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2009102912
     * @since 2008120312
     */
?>
    <script type-"text/javascript">
        //<![CDATA[

        var groupMembers = new Array();
        <?php echo($javascriptGroupString); ?>


        //]]>
    </script>
    <?php if (moodletxt_get_setting('jQuery_Include_Enabled') == 1) { ?><script type="text/javascript" src="js/jquery.js"></script><?php } ?>
    <script type="text/javascript" src="js/jquery.selectboxes.js"></script>
    <script type="text/javascript" src="js/addressbook_groups.js"></script>
    <form id="addgroup" action="<?php echo($ME); ?>" method="post">
        <input type="hidden" name="ab" value="<?php echo($addressbook->id); ?>" />
        <input type="hidden" name="formid" value="addgroup" />
        <input type="hidden" name="courseid" value="<?php if (is_object($course)) echo($course->id); ?>" />
        <div class="mdltxt_half_centred">
            <fieldset>
                <legend><?php echo(get_string('mangroupslegaddgroup', 'block_moodletxt')); ?></legend>
                <?php echo(moodletxt_vomit_errors(array('newNoName', 'newGroupNotAdded'), $errorArray)); ?>
                <?php echo(moodletxt_vomit_errors(array('newGroupAdded'), $noticeArray, true)); ?>
                <label for="newname" class="mdltxt_align_form"><?php echo(get_string('mangroupslblnewname', 'block_moodletxt')); ?></label>
                <input type="text" size="25" id="newname" name="groupname" value="<?php echo($newgroupname); ?>" /><br />
                <label for="newdescription" class="mdltxt_align_form"><?php echo(get_string('mangroupslblnewdesc', 'block_moodletxt')); ?></label>
                <textarea id="newdescription" rows="5" cols="30" name="description"><?php echo($newgroupdescription); ?></textarea><br />
                <input type="submit" value="<?php echo(get_string('mangroupsbutaddgroup', 'block_moodletxt')); ?>" class="align_form" />
            </fieldset>
        </div>
    </form>
    <form id="updategroup" action="<?php echo($ME); ?>" method="post">
        <input type="hidden" name="ab" value="<?php echo($addressbook->id); ?>" />
        <input type="hidden" name="formid" value="updategroup" />
        <input type="hidden" name="courseid" value="<?php if (is_object($course)) echo($course->id); ?>" />
        <div class="mdltxt_half_centred">
            <fieldset>
                <legend><?php echo(get_string('mangroupslegupdate', 'block_moodletxt')); ?></legend>
                <?php echo(moodletxt_vomit_errors(array('badGroup'), $errorArray)); ?>
                <?php echo(moodletxt_vomit_errors(array('groupUpdated'), $noticeArray, true)); ?>
                <div style="text-align:center;">
                    <?php echo(get_string('mangroupslblgrouplist', 'block_moodletxt')); ?>
                    <select name="group" id="group" size="1" style="width:50%;">
                        <option value="0"></option>
                        <?php echo($groupListString); ?>
                    </select><br /><br />
                    <div class="mdltxt_left" style="text-align:left;">
                        <?php echo(get_string('mangroupslblpotential', 'block_moodletxt')); ?><br />
                        <select size="10" name="potentialContacts" id="potentialContacts" multiple="multiple" style="width:50%;">
                            <?php echo($contactListString); ?>
                        </select>
                    </div>
                    <div class="mdltxt_right" style="text-align:right;">
                        <?php echo(get_string('mangroupslblselected', 'block_moodletxt')); ?><br />
                        <select size="10" name="selectedContacts[]" id="selectedContacts" multiple="multiple" style="width:50%;">
                        </select>
                    </div>
                    <input type="button" id="select_multiple" value="&gt; &gt; &gt;" /><br />
                    <input type="button" id="deselect_multiple" value="&lt; &lt; &lt;" /><br /><br />
                    <input type="submit" value="<?php echo(get_string('mangroupsbutton', 'block_moodletxt')); ?>" />
                </fieldset>
            </div>
            <div class="clearer"></div>
        </div>
    </form>
    <form id="deletegroup" action="<?php echo($ME); ?>" method="post">
        <input type="hidden" name="ab" value="<?php echo($addressbook->id); ?>" />
        <input type="hidden" name="formid" value="deletegroup" />
        <input type="hidden" name="courseid" value="<?php if (is_object($course)) echo($course->id); ?>" />
        <div class="mdltxt_half_centred">
            <fieldset>
                <?php echo(moodletxt_vomit_errors(array('deleteBadGroup', 'deleteNoChoiceMade', 'deleteBadChoice', 'deleteDestSame', 'deleteDestInvalid'), $errorArray)); ?>
                <?php echo(moodletxt_vomit_errors(array('groupsMerged', 'groupAndContactsDeleted', 'groupDeleted'), $noticeArray, true)); ?>
                <legend><?php echo(get_string('mangroupslegdelete', 'block_moodletxt')); ?></legend>
                <label for="deletegroupid" class="mdltxt_align_form"><?php echo(get_string('mangroupslbldelete', 'block_moodletxt')); ?></label>
                <select id="deletegroupid" name="groupid" size="1">
                    <?php echo($deleteGroupListString); ?>
                </select><br /><br />
                <label for="deleteradiodonowt">
                    <input id="deleteradiodonowt" type="radio" name="contactchoice" value="donothing" />
                    <?php echo(get_string('mangroupsactionleave', 'block_moodletxt')); ?>
                </label><br />
                <label for="deleteradiodelete">
                    <input id="deleteradiodelete" type="radio" name="contactchoice" value="delete" />
                    <?php echo(get_string('mangroupsactionnuke', 'block_moodletxt')); ?>
                </label><br />
                <label for="deleteradiomerge">
                    <input id="deleteradiomerge" type="radio" name="contactchoice" value="merge" />
                    <?php echo(get_string('mangroupsactionmerge', 'block_moodletxt')); ?>
                </label>
                <select id="contactdest" name="contactdest" size="1">
                    <?php echo($contactDestListString); ?>
                </select><br /><br />
                <input type="submit" value="<?php echo(get_string('mangroupsbutdelete', 'block_moodletxt')); ?>" />
            </fieldset>
        </div>
    </form>