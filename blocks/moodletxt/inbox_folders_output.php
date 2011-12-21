<?php

    /**
     * Inbox folder page output file for MoodleTxt
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2007082312
     * @since 2007082312
     */

?>
    <div class="mdltxt_left">
        <h3><?php echo(get_string('inboxfoldersexisting', 'block_moodletxt')); ?></h3>
        <?php echo(moodletxt_vomit_errors(array('folderAdded'), $noticeArray, true)); ?>
        <ul class="mdltxt_inbox_folderlist">
            <?php echo($folderulout); ?>
        </ul>
    </div>
    <div class="mdltxt_right">
        <h3><?php echo(get_string('inboxfoldersadd', 'block_moodletxt')); ?></h3>
        <form id="addfolderform" action="<?php echo($ME); ?>" method="post">
            <input type="hidden" name="formid" value="addfolder" />
            <?php echo($courseidpassback); ?>
            <fieldset>
                <?php echo(moodletxt_vomit_errors(array('noFolderName', 'folderTooLong', 'folderExists', 'folderAddFailed'), $errorArray)); ?>
                <?php echo(moodletxt_vomit_errors(array('folderAdded'), $noticeArray, true)); ?>
                <?php echo(get_string('inboxfolderslblname', 'block_moodletxt')); ?>
                <input type="text" name="foldername" value="<?php echo($foldername); ?>" maxlength="30" />
                <input type="submit" value="<?php echo(get_string('adminbutaddfolder', 'block_moodletxt')); ?>" />
            </fieldset>
        </form>
    </div>
    <div class="mdltxt_right" style="clear:right;">
        <h3><?php echo(get_string('inboxfoldersdel', 'block_moodletxt')); ?></h3>
        <form id="deletefolderform" action="<?php echo($ME); ?>" method="post">
            <?php echo($courseidpassback); ?>
            <input type="hidden" name="formid" value="delfolder" />
            <fieldset>
                <?php echo(moodletxt_vomit_errors(array('invalidDelFolder', 'destinationFolderSame'), $errorArray)); ?>
                <?php echo(get_string('inboxfolderslblkill', 'block_moodletxt')); ?>
                <select id="delfolderlist" name="delfolderlist" size="1">
                    <?php echo($folderlistout); ?>
                </select><br />
                <?php echo(get_string('inboxfolderslbldest', 'block_moodletxt')); ?>
                <select id="delfolderdestination" name="destinationfolder" size="1">
                    <?php echo($folderlist2out); ?>
                </select>
                <span style="float:right;">
                    <input type="submit" value="<?php echo(get_string('adminbutdelfolder', 'block_moodletxt')); ?>" />
                </span>
            </fieldset>
        </form>
    </div>
    <div class="mdltxt_clearer">
    </div>
