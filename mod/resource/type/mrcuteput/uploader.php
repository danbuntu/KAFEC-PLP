<?php
/**
 * $Id$
 * Renders the form used to upload to the ims repository
 */

    require_once('../../../../config.php');
    require_once('lib.php');

	$rid				= optional_param('rid', 0, PARAM_INT);
	$blockmode			= optional_param('blockmode', 0, PARAM_BOOL);
	$submitbutton		= optional_param('submitbutton', 0, PARAM_BOOL);
	$posttitle			= optional_param('title', '', PARAM_TEXT);
	$postdescription	= optional_param('description', '', PARAM_TEXT);
	$postkeywords		= optional_param('keywords', '', PARAM_TEXT);
	$postvisible		= optional_param('visible', 1, PARAM_BOOL);
	$poststatus			= optional_param('status', 0, PARAM_INT);
	$postrestricted		= optional_param('restricted', 0, PARAM_BOOL);
	$postcategory		= optional_param('category', '', PARAM_PATH);
	$postitems			= optional_param('items', '', PARAM_NOTAGS);
	$postjorum			= optional_param('jorum', 0, PARAM_BOOL);
	$postdeploy			= optional_param('deploy', '', PARAM_NOTAGS);
	
	//add custom js and css files into standard header
	$CFG->stylesheets[] = 'uploader.css';

	require_js('mrcuteput.js');
	if (!$submitbutton){
		require_js('mootools.js');
		require_js('Stickman.MultiUpload.js');
		require_js('uploader.js');
	}

	//print standard head
	print_header();

	//get language strings
	$strpackageupdated = get_string('packageupdated','resource_mrcuteput');
	$stredit = get_string('edit','resource_mrcuteput');
	$strrepository = get_string('repository','resource_mrcuteput');
    $strpreview = get_string('preview','resource_mrcuteput');
    $strchoose = get_string('choose','resource_mrcuteput');

	echo "<h1>".get_string('uploadformtitle','resource_mrcuteput')."</h1>";

    if (empty($USER->id)) {
        require_login();
    }
    if (!$user = get_record("user", "id", $USER->id) ) {
        error("Failed to get user details");
    }

	//rid supplied, so load data for editing
    if (!empty($rid) && $resource = get_record("resource_ims", "id", $rid)) {

		$resource->deploy = htmlentities($resource->deploy);

		$resource->keywords = str_replace(chr(124).chr(124), "\n", $resource->keywords);

		if($resource->status == 'Draft'){
			$resource->status = true;
		} else {
			$resource->status = false;
		}
		
		//populate copyright info
		switch ($resource->copyright)
		{
			case 0:
				$resource->restricted	= false;
			    break;
			case 2:
				$resource->restricted	= true;
			    break;
			default:
				break;
		}

		if($resource->centity){
			$vcard = explode("\n", $resource->centity);
			foreach ($vcard as $vcardlines) {
				$vcarditem = explode(':', $vcardlines);
				if ($vcarditem[0] == "ORG") {
					$vcarduser = $vcarditem[1];
				}
			}
			if($uploader = get_record("user", "username", $vcarduser)){
				$resource->uploader = $uploader->firstname.' '.$uploader->lastname." (<a href=\"mailto:$uploader->email\">$uploader->email</a>)";
			}
			//disable copyright checkbox for non-owning moderators
			if($vcarduser != $USER->username){
				$resource->isowner = 'false';
			}
		}		
		
    } else {
		$resource = new object();
	}


	if ( $submitbutton ){

		$resource->title			= $posttitle;
		$resource->description		= $postdescription;
		//replace newlines (Windows, Mac and Unix) with pipes: '||' (ascii 124)
		$resource->keywords			= str_replace(array("\r\n","\n","\r"), chr(124).chr(124), $postkeywords);

		$resource->visible = $postvisible;

		if( $poststatus ){
			$resource->status			= 'Draft';
		} else {
			$resource->status			= 'Final';
		}
		
		//process copyright & release info, condense to 
		if( !$postrestricted ){
			$resource->copyright	= 0;
		} elseif ($postrestricted){
			$resource->copyright	= 2;
		}

		//no rid == new resource
	    if (empty($rid)) {

			/// Copy files to ims repo

			//replaces spaces with _
			$packagefolder = str_replace(' ', '_', $posttitle);


			//build destination path
			$dirto = $CFG->repository.'/'.$postcategory.'/'.$packagefolder;

			//warn if destination path exists
			if(file_exists($dirto))
			{
				error('A resource named: &lt;'.$dirto.'&gt; already exists.', "javascript:history.go(-1);");
			}

			//make destination dir
			@mkdir($dirto);

			//copy files
			foreach($_FILES as $file)
			{
				if ( !move_uploaded_file($file['tmp_name'], $dirto.'/'.basename($file['name'])) )
				{
					error( "Failed saving &lt;".$file['name'].'&gt;');
				}
			}

			$manifestid		= guid();
			$metadataid		= guid();
			$organisationid	= guid();
			
			$resource->identifier		= $metadataid;
			$resource->lang				= 'en'; //TODO: multilang?
			$resource->role				= 'Author';
			$resource->centity			= "\nBEGIN:vCard\nORG:".$user->username."\nEND:vCard\n"; //moodle username as vCard author
			$resource->contributeddate	= time(); //unix timestamp - other format needed?
			$resource->modifieddate	= time(); //unix timestamp - other format needed?

			$resource->rightsdesc = ''; //no longer used? set to '' to avoid not null db errors 

			$resource->mimetype			= ''; //$mimetype = mimeinfo("type", $fileto); //TODO: what mime type to set with multiple files??
			$resource->vperson			= '';
			$resource->vdate			= '';
			$resource->vcomments		= '';

			$deploy						= array();
			
			$i=1;
			foreach($postitems as $item){
			
				if( substr($item, 0, 6) === 'files_'){
					//handle file
					$file = explode(':', $item, 2);
					$title = $file[1];
					$href = $_FILES[ $file[0] ]['name'];
				} else {
					//treat as URI
					$uri = explode('<', htmlspecialchars_decode($item));
					$title = $uri[0];
					$href = substr($uri[1], 0, strlen($uri[1])-1);
				}

				$deploy[$i]					= new stdClass;
				$deploy[$i]->title			= $title;

				$deploy[$i]->identifier		= guid();
			    $deploy[$i]->identifierref	= guid();

			    $deploy[$i]->id				= $i;
			    $deploy[$i]->level			= 1;
			    $deploy[$i]->parent			= 0;
			    $deploy[$i]->href			= $href;
				$i++;
			}
			$deploy["title"]			= $posttitle;

			$resource->deploy			= serialize($deploy);
			
			$resource->filepath = '/'.$postcategory.'/'.$packagefolder;

			
			//write imsmanifest
			$manifestfile = $dirto.'/'.'imsmanifest.xml';
			touch($manifestfile);
			
			if (!class_exists('xmlWriter')) {
				error("XMLWriter unavailable");
			}
			$x = new xmlWriter();
			$x->openURI($manifestfile);
			$x->setIndent(true);
			$x->setIndentString('  ');
			$x->startDocument('1.0','UTF-8');
			
			$x->startElement('manifest');
			
			$x->writeAttribute("xmlns",					"http://www.imsglobal.org/xsd/imscp_v1p1");
			$x->writeAttribute("xmlns:imsmd",			"http://www.imsglobal.org/xsd/imsmd_v1p2");
			$x->writeAttribute("xmlns:xsi",				"http://www.w3.org/2001/XMLSchema-instance");
			$x->writeAttribute("identifier",			$resource->identifier);
			$x->writeAttribute("xsi:schemaLocation",	"http://www.imsglobal.org/xsd/imscp_v1p1 imscp_v1p1.xsd http://www.imsglobal.org/xsd/imsmd_v1p2 imsmd_v1p2p4.xsd");
			
			$x->startElement('metadata');
				$x->startElement('imsmd:lom');

					$x->startElement('imsmd:general');
						$x->writeElement('imsmd:identifier', $resource->identifier);
						$x->startElement('imsmd:title');
							$x->startElement('imsmd:langstring');
								$x->writeAttribute("xml:lang","en");
								$x->text($posttitle);
							$x->endElement(); //imsmd:langstring
						$x->endElement(); //end imsmd:title
						$x->writeElement("imsmd:language", "en");
						$x->startElement('imsmd:description');
							$x->startElement('imsmd:langstring');
								$x->writeAttribute("xml:lang","en");
								$x->text($postdescription);
							$x->endElement(); //imsmd:langstring
						$x->endElement(); //end imsmd:description
						$x->startElement('imsmd:keyword');
							foreach(explode(chr(124).chr(124),$resource->keywords) as $keyword)
							{
								$x->startElement('imsmd:langstring');
									$x->writeAttribute("xml:lang","en");
									$x->text($keyword);
								$x->endElement(); //end imsmd:langstring
							}
						$x->endElement(); //end imsmd:keywords
					$x->endElement(); //end imsmd:general
					
					$x->startElement('imsmd:lifecycle');
					
					    $x->startElement('imsmd:status');
					        $x->startElement('imsmd:value');
								$x->startElement('imsmd:langstring');
									$x->writeAttribute("xml:lang","en");
									$x->text($resource->status);
								$x->endElement(); //end imsmd:langstring
							$x->endElement(); //end imsmd:value	
						$x->endElement(); //end imsmd:status
						
						$x->startElement('imsmd:contribute');
						    $x->startElement('imsmd:role');
						        $x->startElement('imsmd:value');
									$x->startElement('imsmd:langstring');
										$x->writeAttribute("xml:lang","en");
										$x->text($resource->role);
									$x->endElement(); //end imsmd:langstring
								$x->endElement(); //end imsmd:value
							$x->endElement(); //end imsmd:role
							$x->startElement('imsmd:centity');
								$x->writeElement('imsmd:vcard', $resource->centity);
							$x->endElement(); //end imsmd:centity
							$x->startElement('imsmd:date');
								$x->writeElement('imsmd:datetime', $resource->contributeddate);
							$x->endElement(); //end imsmd:date
						$x->endElement(); //end imsmd:contribute

					$x->endElement(); //end imsmd:lifecycle
					
					$x->startElement('imsmd:technical');
						$x->writeElement("imsmd:format",$resource->mimetype);
						$x->writeElement("imsmd:size", @filesize($fileto));
					$x->endElement(); //end imsmd:technical
					
					$x->startElement('imsmd:rights');
							$x->startElement('imsmd:copyrightandotherrestrictions');
						        $x->startElement('imsmd:value');
									$x->startElement('imsmd:langstring');
										$x->writeAttribute("xml:lang","en");
										$x->text('');
									$x->endElement(); //end imsmd:langstring
								$x->endElement(); //end imsmd:value
							$x->endElement(); //end imsmd:copyrightandotherrestrictions
							$x->startElement('imsmd:description');
								$x->startElement('imsmd:langstring');
									$x->writeAttribute("xml:lang","en");
									$x->text('');
								$x->endElement(); //end imsmd:langstring
							$x->endElement(); //end imsmd:description
					$x->endElement(); //end imsmd:rights
					
					$x->startElement('imsmd:annotation');
						$x->startElement('imsmd:person');	
							$x->writeElement("imsmd:vcard",'');
						$x->endElement(); //end imsmd:person
						$x->startElement('imsmd:date');	
							$x->writeElement("imsmd:datetime",'');
						$x->endElement(); //end imsmd:date
						$x->startElement('imsmd:description');
							$x->startElement('imsmd:langstring');
								$x->writeAttribute("xml:lang","en");
								$x->text('');
							$x->endElement(); //end imsmd:langstring
						$x->endElement(); //end imsmd:description
					$x->endElement(); //end imsmd:annotation

				
				$x->endElement(); //end imsmd:lom
			$x->endElement(); //end metadata


			$x->startElement('organizations');
				$x->startElement('organization');
					$x->writeAttribute('identifier',	$organisationid);
					$x->writeAttribute('structure',		'hierarchical');
					$x->writeElement('title',			$posttitle);

					$i=1;
					while( array_key_exists($i, $deploy) ){
						$x->startElement('item');
							$x->writeAttribute('identifier',	$deploy[$i]->identifier);
							$x->writeAttribute('identifierref',	$deploy[$i]->identifierref);
							$x->writeAttribute('isvisible',		'true');
							$x->writeElement('title',			$deploy[$i]->title);
						$x->endElement(); //end item
						$i++;
					}
					
					
				$x->endElement(); //end organization
			$x->endElement(); //end organizations

			$x->startElement('resources');

				$i=1;
				while( array_key_exists($i, $deploy) ){

					$x->startElement('resource');
						$x->writeAttribute('href',			$deploy[$i]->href);
						$x->writeAttribute('identifier',	$deploy[$i]->identifierref);
						$x->writeAttribute('type',			'webcontent');
					$x->endElement(); //end resource
					$i++;
				}
				
			$x->endElement(); //end resources
			
			$x->endElement(); //end manifest
			
			$x->flush(); //flush manifest to disk

			//email to jorum
			if($postjorum){
				emailtojorum($resource);
			}

			//insert resource
			if(!$newrid = insert_record("resource_ims", $resource, true)){
				error('Failed to add package');
			} else {
				echo "<br /><em>Package added</em>";
			}

		} else {

			$editdeploy = unserialize(html_entity_decode( stripslashes($postdeploy) ));
			$editdeploy["title"] = $posttitle;
			$resource->deploy = serialize($editdeploy);
			$resource->modifieddate = time();
			
			
			//update manifest file
			$resource->keywordsarray = explode(chr(124).chr(124), $resource->keywords);
			$manifestfile = $CFG->repository.get_field('resource_ims', 'filepath', 'id', $resource->id);
			if( !updatemanifest($resource, $manifestfile) ){
				error("Failed to update ".$manifestfile."/imsmanifest.xml");
			}

			//update existing resource
			if(!update_record("resource_ims", $resource)){
				error('Failed to update package');
			} else {
				$newrid = $resource->id;
				echo "<br /><em>$strpackageupdated</em>";
			}

		}

		echo '<ul style="list-style:none;padding:10px;margin:0px;">'; 
		echo "<li><img src=\"images/ims.gif\" alt=\"IMS CP Package\" /> $resource->title";

		if(!$blockmode){
			echo "(<a onclick=\"return set_value('#$resource->filepath','$resource->title', '".str_replace(array("\r\n","\n","\r"), '', rawurlencode($resource->description))."')\" href=\"#\">$strchoose</a>) ";
		}
		
		echo "(<a href=\"uploader.php?rid=$newrid&blockmode=$blockmode\">$stredit</a>) ".
		"(<a href=\"../mrcuteget/preview.php?directory=$resource->filepath&amp;choose=id_reference_value\">$strpreview</a>)".
		"</li>\n";
		echo '</ul>';


		//print basic footer - is there a better way of doing this?
		echo "</div></div></body></html>";

	//render form
	} else {
		?>

<form action="uploader.php" method="post" id="mform1" class="mform" enctype="multipart/form-data">
<fieldset class="hidden">
	<input type="hidden" name="rid" value="<?php @print($rid); ?>" />
	<input type="hidden" name="deploy" value="<?php @print($resource->deploy); ?>" />
	<input type="hidden" name="blockmode" value="<?php @print($blockmode); ?>" />
		<div>
		<?php if(!$rid){ ?>
			<div class="fitem">
				<div class="fitemtitle">
					<div class="fgrouplabel"></div>
				</div>
				<fieldset class="felement fgroup">
					<input onclick="showPopup('filepopup');" name="file" value="Add a file" type="button" id="id_file" />
					<input onclick="showPopup('urlpopup');" name="web" value="Add a weblink" type="button" id="id_web" />
				</fieldset>
			</div>
			<div class="fitem">
				<div class="fitemtitle"></div>
				<div class="felement fcheckbox">
					<span>
						<input onclick="enableMultiple(this.checked);" name="checkmultiple" type="checkbox" value="1" id="id_8394d7" />
						<label for="id_8394d7">Enable multiple items</label>
					</span>
				</div>
			</div>
			<div class="fitem">
				<div class="fitemtitle"></div>
				<div class="felement fselect">
					<select size="1" name="items[]" multiple="multiple" id="id_items"></select>
					<div id="itemControls">
						<div id="collectioncontrols">
							<input onclick="moveUp(document.getElementById('id_items'))" name="up" value="&uarr;" type="button" id="id_up" title="Move item up" /><br />
							<input onclick="moveDown(document.getElementById('id_items'))" name="down" value="&darr;" type="button" id="id_down" title="Move item down" /><br />
							<input onclick="removeItem(document.getElementById('id_items'))" name="remove" value="&#10006;" type="button" id="id_remove" title="Remove item" />
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
			<div class="fitem">
				<div class="fitemtitle">
					<label for="id_title">Title </label>
				</div>
				<div class="felement ftext">
					<input size="42" name="title" type="text" id="id_title" value="<?php @print($resource->title); ?>" />
				</div>
			</div>
			<div class="fitem">
				<div class="fitemtitle"><label for="id_description">Description </label></div>
				<div class="felement ftextarea"><textarea cols="40" rows="5" name="description" id="id_description"><?php @print($resource->description); ?></textarea></div>
			</div>
			<div class="fitem">
				<div class="fitemtitle"><label for="id_keywords">Keywords </label></div>
				<div class="felement ftextarea"><textarea cols="30" rows="3" name="keywords" id="id_keywords"><?php
					@print( $resource->keywords );
				?></textarea></div>
			</div>
		</div>
		
		<div id="categorygroup">
		<?php if(!$rid){ ?>
		<div class="fitem">
		<div class="fitemtitle"><label>Category</label></div>
		<div class="felement fgroup">
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
				<span>
					<input name="category" type="radio" value="<?php echo $file ?>" id="id_<?php echo htmlentities(str_replace(' ', '', $file)); ?>" />
					<label for="id_<?php echo htmlentities(str_replace(' ', '', $file)); ?>"><?php echo $file ?></label>
				</span>
			<?php
				}
			}
			closedir($dir_handle);
		?>
		</div>
		</div>
		<?php } ?>
		<div class="fitem">
		<?php if(false && !$rid){ ?>
			<div class="fitemtitle"></div>
			<div class="felement fcheckbox">
				<span><input name="jorum" type="checkbox" value="1" id="id_8baaa3" />
				<label for="id_8baaa3">Submit this resource to Jorum</label></span>
			</div>
		<?php } ?>
		<?php if (isset($resource->uploader)) { ?>
			<div class="fitemtitle">Uploader</div>
			<div class="felement"><?php echo $resource->uploader; ?></div>
		<?php } ?>
		<?php if($rid){ ?>
			<div class="fitemtitle"></div>
			<div class="felement fcheckbox">
				<span><input name="visible" type="checkbox" value="0" id="id_8baaa2" <?php if(isset($resource->visible) && $resource->visible == 0){echo 'checked="checked"';} ?>/>
				<label for="id_8baaa2" style="font-size:0.8em;">This material is hidden (pending permanent deletion)</label></span>
			</div>
		<?php } ?>
			<div class="fitemtitle"></div>
			<div class="felement fcheckbox">
				<span><input name="status" type="checkbox" value="1" id="id_8baaa4" <?php if(isset($resource->status) && $resource->status){echo 'checked="checked"';} ?>/>
				<label for="id_8baaa4" style="font-size:0.8em;">This material is unapproved<br />(Only the uploader may access this material)</label></span>
			</div>
			<div class="fitemtitle"></div>
			<div class="felement fcheckbox">
				<span><input name="restricted" type="checkbox" value="1" id="id_8baaa5" <?php if(isset($resource->restricted) && $resource->restricted){echo 'checked="checked"';} ?>/>
				<label for="id_8baaa5" style="font-size:0.8em;">I am <strong><em>not</em></strong> willing to share this material</label></span>
			</div>
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
	
	<?php if(!$rid){ ?>
	<div id="filepopup" class="popup">
		<label>File <input type="file" name="files" /></label><br />
		<label>Title <input type="text" name="multifiletitle" /></label><br />
		<input type="button" name="addmultifile" value="Add" />
		<input type="button" value="Cancel" onclick="hidePopup('filepopup')" />
	</div>
	<div id="urlpopup" class="popup">
		<label>URL <input type="text" name="url" value="http://" /></label><br />
		<label>Title <input type="text" name="urltitle" /></label><br />
		<input type="button" name="addurl" value="Add" onclick="addURL();"/>
		<input type="button" value="Cancel" onclick="hidePopup('urlpopup');" />
	</div>
	<?php } ?>
</form>

</div>
</div>
</body>
</html>
<?php
}
?>