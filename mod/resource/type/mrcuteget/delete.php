<?php
/**
* $Id$
 **/
 	require_once('../../../../config.php');
	require_once('lib.php');

	$rid = optional_param('rid', 0, PARAM_INT);
	$returnto = optional_param('returnto', 0, PARAM_TEXT);
	$postsubmit = optional_param('submit', 0, PARAM_BOOL);
	
	if (empty($USER->id)) {
        require_login();
    }
	
		if(!$resource = get_record("resource_ims", "id", $rid)){
		header("HTTP/1.0 404 Not Found");
	echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested resource was not found on this server.</p>
</body></html>';
		exit;
	}

	$strdeleteconfirm = get_string('deleteconfirm', 'resource_mrcuteget', $resource->title);
	$strdeletecheck = get_string('deletecheck', 'resource_mrcuteget', $resource->title);
	
	print_header("Deleting $resource->title");

	if(!has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM, SITEID))){
		notify('You do not have access to delete resources');
		return;
	} else {
		
		if($postsubmit){
			notify("Deleting $resource->title...");
			if ( !deleteresource($resource) ){
				error('Could not delete &lt;'.$resource->title.'&gt;');
			}
		?>
		<input name="continue" value="Continue &raquo;" type="button" id="id_continue" onclick="document.location.href='<?php echo $returnto; ?>'" />
		<?php
		} else {
			notify($strdeleteconfirm);
			?>
			<form action="delete.php" method="post" id="delete" class="mform">
				<input type="hidden" value="<?php echo $rid; ?>" name="rid">
				<input type="hidden" value="<?php echo $returnto; ?>" name="returnto">
				<fieldset class="hidden">
					<div>
						<div class="fitem">
							<div class="fitemtitle">
								<div class="fgrouplabel"></div>
							</div>
							<fieldset class="felement fgroup">
								<input name="cancel" value="&laquo; Cancel" style="color:green;" type="button" id="id_cancel" onclick="javascript:history.go(-1);" />
								<input name="submit" value="Delete &raquo;" style="color:red;" type="submit" id="id_submitbutton" onclick="return confirm('<?php echo $strdeletecheck; ?>');" />
							</fieldset>
						</div>
					</div>
				</fieldset>
			</form>
			<?php
		}
		
	}

	echo "</div></div></body></html>";

?>