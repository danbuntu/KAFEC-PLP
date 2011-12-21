<?php

	$CFG->repository = get_config(null, 'block_mrcute_repository');
	$CFG->repositorywebroot = get_config(null, 'block_mrcute_repositorywebroot');
	$CFG->repositoryroles = get_config(null, 'block_mrcute_repositoryroles');
	$CFG->mrcuteenablenln = get_config(null, 'block_mrcute_enablenln');

	//non-windows compatible GUID generation
	function guid(){
	    if (function_exists('com_create_guid')){
	        return com_create_guid();
	    }else{
	        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
	        $charid = strtoupper(md5(uniqid(rand(), true)));
	        $hyphen = chr(45);// "-"
	        $uuid = chr(123)// "{"
	                .substr($charid, 0, 8).$hyphen
	                .substr($charid, 8, 4).$hyphen
	                .substr($charid,12, 4).$hyphen
	                .substr($charid,16, 4).$hyphen
	                .substr($charid,20,12)
	                .chr(125);// "}"
	        return $uuid;
	    }
	}

	//update imsmanifest.xml - title, description & keywords
	function updatemanifest($resource, $path) {
		$manifest = simplexml_load_file($path.'/imsmanifest.xml'); 
		$ns = $manifest->getNamespaces(true);
		$ns = $ns['imsmd'];
		
		//more NLN version mismatch failcakes - 'record' was renamed 'lom' in 1.2 Draft
		if(!$meta = $manifest->metadata->children($ns)->lom->general){
			$meta = $manifest->metadata->children($ns)->record->general;
		}

		$meta->title->langstring = $resource->title;
		$meta->description->langstring = $resource->description;

		//versions 1.1 and 1.2 of the IMCP spec seem to differ, 1.1=keywords, 1.2=keyword
		//some NLN materials seem to violate this, using 'keywords' but claiming 1.2 compliance
		$keywords = $meta->xpath('imsmd:keyword');
		foreach($keywords as $keyword){
			unset($keyword[0]);
		}
		$keywords = $meta->xpath('imsmd:keywords');
		foreach($keywords as $keyword){
			unset($keyword[0]);
		}

		//using singular 'keyword' as per 1.2 spec
		foreach($resource->keywordsarray as $newkeyword){
			$meta->addChild('imsmd:keyword')->addChild('imsmd:langstring', $newkeyword);
		}

		$doc = new DOMDocument('1.0');
		$doc->preserveWhiteSpace = false;
		$doc->formatOutput = true;
		$doc->loadXML($manifest->asXML());

		return file_put_contents($path.'/imsmanifest.xml',$doc->saveXML());
	}

	function rscandir($base='', &$data=array()) {
		$array = array_diff(scandir($base), array('.', '..'));
		foreach($array as $value) {
			if (is_dir($base.$value)) { 
				$data[] = $base.$value.'/'; 
				$data = rscandir($base.$value.'/', $data); 
			} else if (is_file($base.$value)) {
				$data[] = $base.$value;    
			}
		}
		return $data;
	}

	function emailtojorum($resource)
	{
		global $CFG, $USER;

		$strjorumemailintro = get_string('jorumemailintro','resource_mrcuteput');

		$mail =& get_mailer();
		$mail->Sender = $USER->email;
		$mail->From = $USER->email;
		$mail->FromName = $USER->firstname.' '.$USER->lastname;

		$mail->AddAddress($CFG->jorumemail);

		$mail->Subject = 'MrCUTE: '.$resource->title;
		$mail->Body = $strjorumemailintro."\n\n".
					  $resource->title."\n".
					  $resource->description;

		$path_parts = pathinfo($resource->filepath);
		$destination = $CFG->dataroot.'/temp/'.$path_parts['basename'].'.zip';
		$originalfiles = rscandir($CFG->repository.$resource->filepath.'/');
		zip_files($originalfiles, $destination);
 
		$mail->AddAttachment($destination, basename($destination), 'base64', 'application/zip');

		if(!$mail->Send()){
			unlink($destination);
			error( $mail->ErrorInfo );
		} else {
			unlink($destination);
			return true;
		}
	}


?>