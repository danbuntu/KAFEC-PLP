<?php
    require_once('../../config.php');

    $id = optional_param('id', 1, PARAM_INT);
    $action = optional_param('action', 'view', PARAM_TEXT);

    //if (! ($howtorecord = get_record('resource', 'id', $id)) ) {
    //    error('Invalid resource id ('.$id.')');
    //}
    
	// only 2 possible contexts, site or course
	if ($id == SITEID) { // site context
		  $currentcontext = get_context_instance(CONTEXT_SYSTEM, SITEID);
	} else { // course context
		$currentcontext = get_context_instance(CONTEXT_COURSE, $id);
	}
	
	if (has_capability('moodle/site:doanything', $currentcontext)) {
		$role = "Admin";
	} elseif (has_capability('moodle/course:update', $currentcontext)) {
		$role = "Teacher";
	} else {
		$role = "Student";
	}
    
	$title = 'Suggestion Box';
	// $role = 'Student';
	if ($role !== 'Student') {
		//Admin and Teachers Inbox View
		$bodytext = '
					<div class="sideblock">
					  <div class="header">
						<h2>Suggestions Inbox</h2>
					  </div>

					  <div class="content">
					  <table border=0>';
		
		$query = "SELECT block.*, user.username 
				  FROM {$CFG->prefix}block_suggestionbox block 
				  INNER JOIN {$CFG->prefix}user user ON user.id = block.userid 
				  WHERE courseid = '$id' 
				  ORDER BY datesubmitted DESC";
		
		$suggestions = get_records_sql($query);

        //Now, we have in suggestions, the list of all the suggestions for this course
        if (!empty($suggestions)) {
            foreach ($suggestions as $suggestion) {
				if ($role == "Admin") {
					$author = 'by <a class="name" href="'.$CFG->wwwroot.'/user/view.php?id='.$suggestion->userid.'&course='.$id.'">'.$suggestion->username.'</a>';
				} else {
					$author = '';
				}
				
				$bodytext .= '			  
							<tr>
							  <td align="left" width="580">
								<img src="'.$CFG->pixpath. '/t/email.gif"> <b><a href="#" onClick="document.getElementById(\'message'.$suggestion->id.'\').style.display = \'\';">'.$suggestion->title.'</a></b>
								'.$author.'
							  </td>
							  <td width="20" class="topic starter">
								<form action="submit.php" method="POST"><input type="image" src="'.$CFG->pixpath.'/t/delete.gif" value="Delete" alt="Delete this suggestion" name="Delete this suggestion"><input name="action" type="hidden" value="delete" /><input name="msgid" type="hidden" value="'.$suggestion->id.'" /><input name="courseid" type="hidden" value="'.$id.'" /></form>
							  </td>
							</tr>
							<tr id=message'.$suggestion->id.' style="display:none;">
							  <td align="left">
								<div class="footer">
								  '.$suggestion->datesubmitted.'
								</div>
								'.nl2br($suggestion->message).'<br />
								<br />
								<div class="footer">
								  <a href="#" onClick="document.getElementById(\'message'.$suggestion->id.'\').style.display = \'none\';">Close [x]</a>
								</div>
							  <td></td>
							  </td>
							  <td></td>
							</tr>';
			}
		} else {
			$bodytext .= '			  
							<tr>
							  <td align="left" width="600" class="topic starter">
								No Suggestions
							  </td>
							</tr>';
		}
	} else {
		// Student View
		$bodytext = '
					<div class="sideblock">
					  <div class="header">
						<h2>Suggestions Box</h2>
					  </div>
					  
					  <div class="content">
					    <center><b>Note about privacy</b> - Your comments will be private to your tutors. However, IDs are stored so that abusive messages may be traced.<br /></center>
					    <br />
					  <table border=0>';

		$bodytext .= '
					    <tr>
					      <td align="left">
					        <form action="submit.php" method="POST">
							  <label for="title">Title</title><br />
					          <input name="title" type="text" size="87"><br />
					          <br />
							  <label for="suggestion">Suggestion</title><br />
					          <textarea name="suggestion" cols="67" rows="8"></textarea><br />
					          <br />
					          <center><input type="submit" name="submit" Value="Submit"></center>
					          <input name="action" type="hidden" value="add" />
					          <input name="courseid" type="hidden" value="'.$id.'" />
					        </form>
					      </td>
					    </tr>';
	}
	
	$bodytext .= '
				  </table>
				  </div>
				</div>';

	print_header($title, NULL, '');

	echo('<div id="content"><div class="box generalbox generalboxcontent boxaligncenter clearfix">');
	echo($bodytext);
	echo('</div></div>');

    print_footer('', '20');
    
?>