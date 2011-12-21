<?php

    /**
     * Output file for user address book management
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2008112112
     * @since 2008112112
     */

?>
    <div class="mdltxt_left">
        <h3><?php echo(get_string('addressexisting', 'block_moodletxt')); ?></h3>
        <?php echo(moodletxt_vomit_errors(array('folderAdded'), $noticeArray, true)); ?>
        <ul class="mdltxt_addressbook_list">
            <?php echo($abulout); ?>
        </ul>
    </div>
    <div class="mdltxt_right">
        <h3><?php echo(get_string('addressadd', 'block_moodletxt')); ?></h3>
        <form id="addbookform" action="<?php echo($ME); ?>" method="post">
            <input type="hidden" name="formid" value="addbook" />
            <?php echo($courseidpassback); ?>
            <fieldset>
                <?php echo(moodletxt_vomit_errors(array('noBookName', 'nameTooLong', 'nameExists', 'addFailed', 'invalidBookType', 'globalNotAllowed'), $errorArray)); ?>
                <?php echo(moodletxt_vomit_errors(array('bookAdded'), $noticeArray, true)); ?>
                <label for="bookname"><?php echo(get_string('addresslblnewname', 'block_moodletxt')); ?></label>
                <input type="text" id="bookname" name="bookname" value="<?php echo($bookname); ?>" maxlength="50" /><br />
                <label for="booktype"><?php echo(get_string('addresslbltype', 'block_moodletxt')); ?></label>
                <select id="booktype" name="booktype" size="1">
                    <option value="private"<?php echo($seltypeprivate); ?>><?php echo(get_string('addressfragtypeprivate', 'block_moodletxt')); ?></option>
<?php if ($canHaveGlobalAddressBooks) { ?>
                    <option value="global"<?php echo($seltypeglobal); ?>><?php echo(get_string('addressfragtypeglobal', 'block_moodletxt')); ?></option>
<?php } ?>
                </select>
                <input type="submit" value="<?php echo(get_string('addressbutaddbook', 'block_moodletxt')); ?>" />
            </fieldset>
        </form>
    </div>
    <div class="mdltxt_right" style="clear:right;">
        <h3><?php echo(get_string('addressdelete', 'block_moodletxt')); ?></h3>
        <form id="deletebookform" action="<?php echo($ME); ?>" method="post">
            <?php echo($courseidpassback); ?>
            <input type="hidden" name="formid" value="delbook" />
            <fieldset>
                <?php echo(moodletxt_vomit_errors(array('notOwner', 'notDestOwner', 'destSame', 'moveContactsFailed', 'moveGroupsFailed', 'bookNotDeleted'), $errorArray)); ?>
                <?php echo(moodletxt_vomit_errors(array('bookDeleted'), $noticeArray, true)); ?>
                <?php echo(get_string('addresslblkill', 'block_moodletxt')); ?>
                <select id="delbooklist" name="delbooklist" size="1">
                    <?php echo($delbooklistout); ?>
                </select><br />
                <?php echo(get_string('addresslblmove', 'block_moodletxt')); ?>
                <select id="delbookdestination" name="delbookdestination" size="1">
                    <option value="0"><?php echo(get_string('addressfragnodest', 'block_moodletxt')); ?></option>
                    <?php echo($delbookdestlistout); ?>
                </select>
                <span style="float:right;">
                    <input type="submit" value="<?php echo(get_string('addressbutdelbook', 'block_moodletxt')); ?>" />
                </span>
            </fieldset>
        </form>
    </div>
    <div class="mdltxt_clearer">
    </div>
