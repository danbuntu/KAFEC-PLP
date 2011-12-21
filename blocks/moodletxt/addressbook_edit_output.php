<?php

    /**
     * Output file for user address book management
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2010061812
     * @since 2008112112
     */
?>
    <?php if (moodletxt_get_setting('jQuery_Include_Enabled') == 1) { ?><script type="text/javascript" src="js/jquery.js"></script><?php } ?>
    <script type="text/javascript" src="js/jquery.tablesorter.js"></script>
    <script type="text/javascript" src="js/jquery.tablesorter.pager.js"></script>
    <script type="text/javascript" src="js/addressbook_edit.php"></script>
    <script type="text/javascript" src="js/jquery.tablesorter.filter.js"></script>
    <div id="tablePager" class="tablePager">
        <!-- First Name: <input id="filterFirstName" name="filterFirstName" type="text" size="20" /><br /> -->
        Last Name: <input id="filterLastName" name="filterLastName" type="text" size="20" /><br />
        <!-- Phone Number: <input id="filterPhoneNo" name="filterPhoneNo" type="text" size="20" /><br /><br /> -->
        <a href="javascript:return false;" class="tablePagerFirst">&lt;&lt;</a>
        <a href="javascript:return false;" class="tablePagerPrev">&lt;</a>
        <input type="text" size="6" disabled="disabled" class="tablePagerDisplay" />
        <a href="javascript:return false;" class="tablePagerNext">&gt;</a>
        <a href="javascript:return false;" class="tablePagerLast">&gt;&gt;</a>
  		<select class="tablePagerSize" style="margin-left:1em;">
			<option value="5">5</option>
			<option value="10" selected="selected">10</option>
			<option value="15">15</option>
			<option value="20">20</option>
			<option value="25">25</option>
			<option value="30">30</option>
			<option value="50">50</option>
		</select>
        <?php echo(get_string('editbookcontactsperpage', 'block_moodletxt')); ?>
    </div>
    <div style="width:50%;padding-top:1em;">
        <p>
            <a href="addressbook_add_contact.php<?php echo($addlinkstring); ?>"><?php echo(get_string('editbookaddlink', 'block_moodletxt')); ?></a><br />
            <a href="addressbook_groups.php<?php echo($addlinkgroups); ?>"><?php echo(get_string('editbookgroupslink', 'block_moodletxt')); ?></a>
        </p>
        <form action="<?php echo($ME); ?>" method="post">
            <input type="hidden" name="formid" value="updatename" />
            <input type="hidden" name="ab" value="<?php echo($addressbook->id); ?>" />
            <input type="hidden" name="courseid" value="<?php if (is_object($course)) echo($course->id); ?>" />
            <?php echo(moodletxt_vomit_errors(array('invalidBookId', 'noBookName', 'nameTooLong', 'nameExists', 'invalidBookType'), $errorArray)); ?>
            <?php echo(moodletxt_vomit_errors(array('bookUpdated'), $noticeArray, true)); ?>
            <?php echo(get_string('editbooklblname', 'block_moodletxt')); ?>
            <input type="text" id="bookname" name="bookname" size="30" maxlength="50" value="<?php echo($addressbook->name); ?>" />
            <?php echo(get_string('editbooklbltype', 'block_moodletxt')); ?>
            <select id="booktype" name="booktype" size="1">
                <option value="private"<?php echo($seltypeprivate); ?>><?php echo(get_string('addressfragtypeprivate', 'block_moodletxt')); ?></option>
<?php if ($canHaveGlobalAddressBooks) { ?>
                <option value="global"<?php echo($seltypeglobal); ?>><?php echo(get_string('addressfragtypeglobal', 'block_moodletxt')); ?></option>
<?php } ?>
            </select>
            <input type="submit" value="<?php echo(get_string('editbookbutupdate', 'block_moodletxt')); ?>" />
        </form>
    </div>
<?php

     if (is_array($contactlist) && count($contactlist) > 0) {

?>
    <span style="float:right;"><b><?php echo(get_string('editbookdoubleclick', 'block_moodletxt')); ?></b></span>
    <form id="editForm" action="<?php echo($ME); ?>" method="post">
        <input id="rowid" type="hidden" name="rowid" value="" />
        <input type="hidden" name="ab" value="<?php echo($addressbook->id); ?>" />
        <input id="formid" name="formid" type="hidden" value="deleteContacts" />
        <input type="hidden" name="courseid" value="<?php if (is_object($course)) echo($course->id); ?>" />
        <input id="deleteSwitch" name="deleteSwitch" type="hidden" value="" />
        <p style="margin-top:1em;margin-bottom:0;">
            <b><?php echo(get_string('editbooklbltable', 'block_moodletxt')); ?></b>
        </p>
        <table id="contactsTable" class="mdltxt_resultlist mdltxt_fullwidth mdltxt_dynamictable">
            <thead>
                <tr>
                    <th></th>
                    <th><?php echo(get_string('editbooktableheader1', 'block_moodletxt')); ?><div class="sortIcon"></div></th>
                    <th><?php echo(get_string('editbooktableheader2', 'block_moodletxt')); ?><div class="sortIcon"></div></th>
                    <th><?php echo(get_string('editbooktableheader3', 'block_moodletxt')); ?><div class="sortIcon"></div></th>
                    <th><?php echo(get_string('editbooktableheader4', 'block_moodletxt')); ?><div class="sortIcon"></div></th>
                </tr>
            </thead>
            <tbody>
<?php

        foreach ($contactlist as $contact) {
?>
                <tr id="<?php echo($contact->id); ?>">
                    <td class="mdltxt_columnline"><input type="checkbox" name="deleteContacts[]" value="<?php echo($contact->id); ?>" /></td>
                    <td class="mdltxt_columnline"><?php echo($contact->lastname); ?></td>
                    <td class="mdltxt_columnline"><?php echo($contact->firstname); ?></td>
                    <td class="mdltxt_columnline"><?php echo($contact->company); ?></td>
                    <td><?php echo($contact->phoneno); ?></td>
                </tr>
<?php
        }
?>
            </tbody>
        </table>
        <div style="margin-left:2em;margin-top:1em;">
            <img src="pix/select_arrow.png" width="38" height="22" alt="Arrow" />
            <a id="checkAllBoxes" href="#"><?php echo(get_string('editbookbutcheckall', 'block_moodletxt')); ?></a> |
            <a id="uncheckAllBoxes" href="#"><?php echo(get_string('editbookbutuncheckall', 'block_moodletxt')); ?></a>
            <span style="margin-left:4em;">
                <?php echo(get_string('editbookselectedaction', 'block_moodletxt')); ?>
                <a id="deleteSelected" href="#"><?php echo(get_string('editbookdelselected', 'block_moodletxt')); ?></a> |
                <a id="deleteExceptSelected" href="#"><?php echo(get_string('editbookdelnotselected', 'block_moodletxt')); ?></a>
            </span>
        </div>
    </form>
    <div class="clearer"></div>
<?php
     }
?>