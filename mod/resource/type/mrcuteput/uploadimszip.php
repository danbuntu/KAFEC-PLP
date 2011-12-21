<?php

    require_once('../../../../config.php');
	require_once("$CFG->libdir/pclzip/pclzip.lib.php");
    require_once('../../../../lib/xmlize.php');
	require_once('../mrcuteget/lib.php');

	if (empty($USER->id)) {
		require_login();
	}
	
	$tempdir = $CFG->dataroot.'/temp';
	
	$fileframe		= optional_param('fileframe', 0, PARAM_BOOL);
	$savepackage	= optional_param('savepackage', 0, PARAM_BOOL);
	$tempfilename	= optional_param('tempfilename', '', PARAM_FILE);
	$postcategory	= optional_param('category', '', PARAM_NOTAGS);
	$posttitle		= optional_param('title', '', PARAM_NOTAGS);
	
	
	if ($fileframe) 
	{
		$valid = false;
		if ( isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK )
		{
			//check for imsmanifest.xml
			$z = new PclZip($_FILES['file']['tmp_name']);
			foreach($z->listContent() as $file)
			{
				if( $file['filename'] == 'imsmanifest.xml' )
				{
					$valid = true;
					break;
				}
			}
		}

		$filename = $_FILES['file']['name'];

		?><html>
	<head>
		<title>upload</title>
	</head>
	<body>
		<script language="javascript" type="text/javascript">
		<?php

		if ($valid)
		{
			$manifestxml = $z->extract(PCLZIP_OPT_BY_NAME, 'imsmanifest.xml', PCLZIP_OPT_EXTRACT_AS_STRING);
			
			$manifest = xmlize($manifestxml[0]["content"]);
			
			$package = @ims_extract_metadata( $manifest );
			$package->title = ims_get_cp_title( $manifest );
			
			$tempfilename = md5(uniqid(rand(), true)).'.zip';
			
			move_uploaded_file($_FILES['file']['tmp_name'], $tempdir.'/'.$tempfilename);
			//$foldername = substr( $filename, 0, strpos($filename, '.zip') );

			echo "\n";
			echo 'window.parent.success();';
			echo "\n";
			echo "window.parent.packageinfo('".
				htmlspecialchars(str_replace(array("\r\n","\n","\r"),'\n',$package->title))."','".
				rawurlencode($package->description)."','".
				$tempfilename.
			"');";
		}
		else
		{
			echo 'window.parent.error("'.$filename.'");';
		}

		?>
		</script>
		<?php
		?>
	</body>
</html><?php
		exit();
	}

	//get language strings
	$stredit = get_string('edit','resource_mrcuteput');
	$strrepository = get_string('repository','resource_mrcuteput');
    $strpreview = get_string('preview','resource_mrcuteput');
    $strchoose = get_string('choose','resource_mrcuteput');
	
	$CFG->stylesheets[] = 'uploader.css';
	
	require_js('mrcuteput.js');
	require_js('uploadimszip.js');
	
	//print standard head
	print_header();
?>
	<h1>Upload IMS</h1>
<?php
	if ( $savepackage )
	{
		
		$from = $tempdir.'/'.$tempfilename;
		$package = '/'.$postcategory.'/'.str_replace(' ', '_', $posttitle);
		$to = $CFG->repository.$package;
		
		if( !is_dir($to) ) {
			@mkdir($to);
		} else {
			print_error('mrcute_fileexists', 'resource_mrcuteput', 'javascript:history.go(-1)', $package);
		}

		if(!unzip_file($from, $to, false)){
			print_error('mrcute_unzipfailed', 'resource_mrcuteput', 'javascript:history.go(-1)');
		} else {
			@unlink($from);
		}

		if(!$resource = @ims_deploy_file_return($package)){
			print_error('mrcute_deployfailed', 'resource_mrcuteput', 'javascript:history.go(-1)');
		}
	
		echo '<ul style="list-style:none;padding:10px;margin:0px;">'; 
		echo "<li><img src=\"images/ims.gif\" alt=\"IMS CP Package\" /> $resource->title";
		
		echo "(<a onclick=\"return set_value('#$resource->filepath','$resource->title', '".str_replace(array("\r\n","\n","\r"), '', $resource->description)."')\" href=\"#\">$strchoose</a>) ";

		echo "(<a href=\"uploader.php?rid=$resource->id\">$stredit</a>) ".
			"(<a href=\"../mrcuteget/preview.php?directory=$resource->filepath&amp;choose=id_reference_value\">$strpreview</a>)".
			"</li>\n";
		echo '</ul>';
		
	} else {

?>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" target="upload_iframe" method="post" enctype="multipart/form-data" class="mform">
				<fieldset class="hidden">
					<input type="hidden" name="fileframe" value="true" />
					<div>
						<div class="fitem">
							<div class="fitemtitle">
								<div class="fgrouplabel"><label for="thefile">IMS zip file</label></div>
							</div>
							<fieldset class="felement fgroup">
								<input type="file" name="file" id="thefile" onchange="return upload('thefile')" />
								<img id="progressimg" src="images/loading.gif" style="visibility:hidden;vertical-align:middle;margin-left:5px;" alt="progress" /><br />
								<div id="imserror" style="display:none;color:red;"></div>
							</fieldset>
						</div>
					</div>
				</fieldset>
			</form>

			<iframe name="upload_iframe" style="display:none;"></iframe>

			<form class="mform" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data" onsubmit="submitpackage();">
				<fieldset class="hidden">
				<input type="hidden" name="savepackage" value="true" />
				<input type="hidden" name="tempfilename" id="tempfilename" value="" />
					<div>

						<div class="fitem">
							<div class="fitemtitle">
								<div class="fgrouplabel"><label for="title" style="color:gray;">Title</label></div>
							</div>
							<fieldset class="felement fgroup">
								<input type="text" name="title" id="title" style="width:280px;color:gray;" value="" />
							</fieldset>
							
							<div class="fitemtitle">
								<div class="fgrouplabel"><label for="description" style="color:gray;">Description</label></div>
							</div>
							<fieldset class="felement fgroup">
								<textarea rows="10" cols="40" name="description" id="description" style="color:gray;"></textarea>
							</fieldset>
							
							<div class="fitemtitle">
								<div class="fgrouplabel"><label for="category">Category</label></div>
							</div>
							<fieldset class="felement fgroup">

								<select id="category" name="category">
									<option selected="selected">Choose:</option>
<?php
							$path = $CFG->repository;
							$dir_handle = @opendir($path) or die("Unable to open $path");

							$categorygroup = array();
							while ($file = readdir($dir_handle)) 
							{
								if(
									is_dir($CFG->repository."/".$file) && 
									!file_exists("$CFG->repository/$file/imsmanifest.xml") && 
									substr($file,0,1) != "."
									)
								{
?>
									<option><?php echo $file ?></option>
<?php
								}
							}
							closedir($dir_handle);
?>
								</select>
							</fieldset>
						
						</div>
					</div>
				</fieldset>
				<fieldset class="hidden">
					<div>
						<div class="fitem">
							<div class="fitemtitle">
								<div class="fgrouplabel"></div>
							</div>
							<fieldset class="felement fgroup">
								<input name="submitbutton" value="Save" type="submit" id="id_submitbutton" /> 
								<input name="cancel" value="Cancel" type="button" id="id_cancel" onclick="window.close();" />
							</fieldset>
						</div>
					</div>
				</fieldset>
				
			</form>
<?php } ?>
		</div>
	</div>
</body>
</html>
