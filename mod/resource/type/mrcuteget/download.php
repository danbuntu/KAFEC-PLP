<?php
/**
 * $Id$
 * Zip package files for download as IMS CP
 **/
    require_once('../../../../config.php');
	require_once('lib.php');

	$rid = optional_param('rid', 0, PARAM_INT);

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

	$path_parts = pathinfo($resource->filepath);
	$destination = $CFG->dataroot.'/temp/'.$path_parts['basename'].'.zip';
	$originalfiles = rscandir($CFG->repository.$resource->filepath.'/');
	
	zip_files($originalfiles, $destination);
 
	header('Content-Type: application/zip');
	header('Content-Disposition: attachment; filename="'.basename($destination).'"'); 
	header('Content-Length: '.filesize($destination));
	readfile($destination);
	unlink($destination);
	
?>