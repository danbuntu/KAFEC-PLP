<?php

    /**
     * View-message page output file
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2007082812
     * @since 2007082812
     */

?>
    <div class="mdltxt_right">
        <h2 style="margin-top:0;"><?php echo(get_string('viewheaderstatuskey', 'block_moodletxt')); ?></h2>
        <div style="border:1px #000000 solid;padding:4px;">
            <?php echo('<img src="pix/status_red.gif" width="16" height="17" alt="' . get_string('statuskeyfailedalt', 'block_moodletxt') . '" title="' . get_string('statuskeyfailedtitle', 'block_moodletxt') . '" />'); ?>
            <?php echo(get_string('statuskeyfailed', 'block_moodletxt')); ?><br />
            <?php echo('<img src="pix/status_yellow.gif" width="16" height="17" alt="' . get_string('statuskeysentalt', 'block_moodletxt') . '" title="' . get_string('statuskeysenttitle', 'block_moodletxt') . '" />'); ?>
            <?php echo(get_string('statuskeysent', 'block_moodletxt')); ?><br />
            <?php echo('<img src="pix/status_green.gif" width="16" height="17" alt="' . get_string('statuskeyreceivedalt', 'block_moodletxt') . '" title="' . get_string('statuskeyreceivedtitle', 'block_moodletxt') . '" />'); ?>
            <?php echo(get_string('statuskeyreceived', 'block_moodletxt')); ?>
        </div>
    </div>
    <div class="mdltxt_left">
        <h2><?php echo(get_string('viewheadermessagedetails', 'block_moodletxt')); ?></h2>
        <div style="border:1px #000000 solid;padding:4px;">
            <label for="messageauthor" style="font-weight:bold;"><?php echo(get_string('viewlabelauthor', 'block_moodletxt')); ?></label>
            <span id="messageauthor"><?php echo('<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $thismessage->moodleuserid . '">' . $thismessage->username . '</a>'); ?></span><br /><br />
            <label for="timesent" style="font-weight:bold;"><?php echo(get_string('viewlabeltimesent', 'block_moodletxt')); ?></label>
            <span id="timesent"><?php echo(userdate($thismessage->timesent, "%H:%M:%S,  %d %B %Y")); ?></span><br /><br />
            <label for="scheduledfor" style="font-weight:bold;"><?php echo(get_string('viewlabelscheduled', 'block_moodletxt')); ?></label>
            <span id="scheduledfor"><?php echo(userdate($thismessage->scheduledfor, "%H:%M:%S,  %d %B %Y")); ?></span><br /><br />
            <label for="messagetext" style="font-weight:bold;"><?php echo(get_string('viewlabelmessagetext', 'block_moodletxt')); ?></label>
            <span id="messagetext"><?php echo($thismessage->messagetext); ?></span>
        </div>
    </div>
    <div class="clearer">
    </div>
    <div style="text-align:right;">
        <form action="<?php echo($ME); ?>" method="get">
            <input type="hidden" name="messageid" value="<?php echo($thismessage->id); ?>" />
            <input type="hidden" name="updatestatus" value="1" />
            <input type="hidden" name="courseid" value="<?php echo($courseid); ?>" />
            <input type="submit" value="Update Status" />
        </form>
    </div>
    <h2 style="margin-top:2em;"><?php echo(get_string('viewheadercurrentstatus', 'block_moodletxt')); ?></h2>

<?php

    if (isset($table)) {

        $table->print_html();

    } else {

?>
    <div style="text-align:center;">
        <p>
             <?php echo(get_string('errornorecipients', 'block_moodletxt')); ?>
        </p>
    </div>
<?php

    }

?>
