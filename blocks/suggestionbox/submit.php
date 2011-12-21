<?php
    require_once('../../config.php');

    $action = required_param('action', PARAM_TEXT);
    $courseid = optional_param('courseid', 0, PARAM_INT);
    // Parameter for deletion
    $msgid = optional_param('msgid', 0, PARAM_INT);
	
	if ($action == "add") {
		$record->userid			= $USER->id;
		$record->datesubmitted	= time();
		$record->courseid		= $courseid;
		$record->title			= optional_param('title', '', PARAM_TEXT);
		$record->message		= $suggestion = optional_param('suggestion', '', PARAM_TEXT);

		insert_record("block_suggestionbox", $record);
		$title = 'Record Added';
		$bodytext = 'Record Added';
		$linktext = 'Close';
		$linkurl = 'window.close()';
	} elseif ($msgid != 0) {
		delete_records("block_suggestionbox", "id", $msgid);
		$title = 'Record Deleted';
		$bodytext = 'Record Deleted';
		$linktext = 'Back to Suggestion Inbox';
		$linkurl = 'window.location.href=\''.$CFG->wwwroot.'/blocks/suggestionbox/view.php?id='.$courseid.'&action=submit\';';
	} else {
		error('Invalid parameters');
	}
	
	print_header($title, NULL, '');

	echo('<div id="content"><center><div class="box generalbox generalboxcontent boxaligncenter clearfix">');
	echo('<div class="sideblock">'.$bodytext.'</div>');
	echo('<form action="'.$CFG->wwwroot.'/blocks/suggestionbox/view.php?id='.$courseid.'&action=submit"><input type="button" value="'.$linktext.'" onclick="'.$linkurl.'"></form>');
	echo('</div></center></div>');

    print_footer('', '20');	
?>