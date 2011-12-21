<?php

    /**
     * User inbox page output file for MoodleTxt
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2008082112
     * @since 2007082312
     */

?>
    <div style="text-align:right;">
        <form id="jumptofolder" action="<?php echo($ME); ?>" method="get">
            <span style="margin-right:3em;">
                <a href="inbox_folders.php<?php echo($folderlinkcid); ?>">Manage your Folders</a>
            </span>
            Jump to:
            <select id="jumpfolderlist" name="folder" size="1" onchange="javascript: jumpFolder(this);">
                <option value="">Your folders...</option>
                <?php echo($selfolderlist); ?>
            </select>
        </form>
    </div>
    <script type="text/javascript" src="js/inbox.js"></script>
    <h3><?php echo($userfolder->name); ?></h3>

<?php

    if (count($errorArray) > 0) {

        echo('
    <div class="error">
        <ul>');

        foreach($errorArray as $error)
            echo('
        <li>' . $error . '</li>');

        echo('
    </div>');

    }

?>
    <form id="selectedmessages" action="<?php echo($ME); ?>" method="post">
        <input type="hidden" name="folder" value="<?php echo($userfolder->id); ?>" />
        <input type="hidden" id="folderorinbox" name="folderorinbox" value="folder" />
<?php

    // Print out results table
    $table->print_html();

    if ($showControls) {

?>
        <div style="margin-left:1em;margin-top:2em;">
            <img src="pix/select_arrow.png" width="38" height="22" alt="Arrow" />
            <a href="javascript:checkAllBoxes('blocks-moodletxt-inboxmessages');">Check all</a> |
            <a href="javascript:uncheckAllBoxes('blocks-moodletxt-inboxmessages');">Uncheck all</a>
            <span style="margin-left:4em;">
                <select id="selectedaction" name="selectedaction" size="1"
                    onchange="javascript:selectAction(this);">
                    <option value="">With selected...</option>
                    <option value="killmaimburn">Delete</option>
                    <option value="copy">Copy to...</option>
                    <option value="move">Move to...</option>
                </select>
                <select id="folderlist" name="folderlist" size="1" disabled="disabled" onchange="javascript:setListSwitch('folder');">
                    <option value="">Your folders...</option>
                    <?php echo($selfolderlist); ?>
                </select>
                <select id="inboxlist" name="inboxlist" size="1" disabled="disabled" onchange="javascript:setListSwitch('inbox');">
                    <option value="">Other user's accounts...</option>
                    <?php echo($selinboxlist); ?>
                </select>
            </span>
        </div>
<?php

    }

?>
    </form>
