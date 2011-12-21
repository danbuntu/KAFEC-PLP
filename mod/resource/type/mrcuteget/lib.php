<?php

$CFG->repository = get_config(null, 'block_mrcute_repository');
$CFG->repositorywebroot = get_config(null, 'block_mrcute_repositorywebroot');
$CFG->repositoryroles = get_config(null, 'block_mrcute_repositoryroles');
$CFG->mrcuteenablenln = get_config(null, 'block_mrcute_enablenln');

function truncate_string($details,$max)
{
    if(strlen($details)>$max)
    {
        $details = substr($details,0,$max);
        $i = strrpos($details," ");
        $details = substr($details,0,$i);
        $details = $details."&hellip;";
    }
    return $details;
}

function searchJorum ($query)
{
	$inputs = array();
	$inputs["operation"] = "searchRetrieve";
	$inputs["version"] = "1.1";
	$inputs["query"] = $query;
//	$url  = "http://bodach.ucs.ed.ac.uk:8038/intradv3/IntraLibrary-SRU";
	$url  = "http://repository.jorum.ac.uk/intralibrary/IntraLibrary-SRU";
//	$url  = "http://coleg.intralibrary.com/IntraLibrary-SRU";
	$url .= "?";
	$and  = '';
	
	foreach ($inputs as $name=>$value){
		$url .= $and.$name.'='.$value;
		$and = '&';
	}
	
	$x = new XMLReader();
	$x->open($url);
	$package = array();
	$i=0;
	
	while ($x->read())
	{
		if($x->nodeType == XMLReader::ELEMENT ){
			$name = $x->name;
		}
		if ($x->nodeType == XMLReader::TEXT && $name){
	        $value = $x->value;
	    }
		if ($value){			
		    if( $name == 'dc:title'){
				$package[$i]['title'] = $value;
			}
		    if( $name == 'dc:description'){
				$package[$i]['description'] = $value;
			}
		    if( $name == 'dc:identifier' && $x->nodeType == XMLReader::TEXT && !isset($package[$i]['viewuri']) ){

				$package[$i]['viewuri'] = $value;
			}
		}
		if ($x->nodeType == XMLReader::END_ELEMENT && $x->name == 'SRW:record'){
			$i++;
		}
	}

	if(sizeof($package)){
		return $package;
	} else {
		return false;
	}
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

	//recursively remove directory contents
	function rrmdir($path)
	{
		$path= rtrim($path, '/').'/';
		$handle = opendir($path);
		while( false !== ($file = readdir($handle)) )
		{
			if($file != "." and $file != ".." )
			{
				$fullpath = $path.$file;
				if( is_dir($fullpath) )
				{
					rrmdir($fullpath);
					@rmdir($fullpath);
				}
			}
		}
		closedir($handle);
	}

	function deleteresource($resource)
	{
		global $CFG;

		$path = $CFG->repository.$resource->filepath.'/';
		
		echo '<p style="font-size:0.7em;">';
		echo "<b>Deleting:</b> Deployment index $resource->id...";
		ob_flush();
		
		if(!delete_records('resource_ims', 'id', $resource->id)) {
			return false;
		}
		
		echo " Done<br />";
		
		if(!is_dir($path)){return false;}
		
		$files = rscandir($path);
		$directories = array();
		
		foreach ($files as $file) {
			if(is_file($file)){
				echo "<b>Deleting:</b> $file...";
				ob_flush();
				@unlink($file);
				echo " Done<br />";
			}
		}

		echo "<b>Deleting:</b> $path...";
		@rrmdir($path);
		echo ' Done<br /></p>';
		if(!@rmdir($path))
		{
			return false;
		} else {
			return true;
		}

	}
	
	function decode_utf16($str) {
        $c0 = ord($str[0]);
        $c1 = ord($str[1]);

        if ($c0 == 0xFF && $c1 == 0xFE) {
            $be = false;
        } else if ($c0 == 0xFE && $c1 == 0xFF) {
            $be = true;
        } else {
            return $str;
        }

        $str = substr($str, 2);
        $len = strlen($str);
        $dec = '';
        for ($i = 0; $i < $len; $i += 2) {
            $c = ($be) ? ord($str[$i]) << 8 | ord($str[$i + 1]) : ord($str[$i + 1]) << 8 | ord($str[$i]);
            if ($c >= 0x0001 && $c <= 0x007F) {
                $dec .= chr($c);
            } else if ($c > 0x07FF) {
                $dec .= chr(0xE0 | (($c >> 12) & 0x0F));
                $dec .= chr(0x80 | (($c >>  6) & 0x3F));
                $dec .= chr(0x80 | (($c >>  0) & 0x3F));
            } else {
                $dec .= chr(0xC0 | (($c >>  6) & 0x1F));
                $dec .= chr(0x80 | (($c >>  0) & 0x3F));
            }
        }
        return $dec;
    }

	
	
	
	/**
	* functions moved from repository_deploy.php 
	*/
		

	/// Deploys all packages found in the folder recursively.
    function ims_deploy_folder($file, $all='') {
        global $CFG;
        
        $dirpath = "$CFG->repository/$file";
        $dir = opendir($dirpath);
        while (false != ($filename = readdir($dir))) {
            if ($filename != '.' && $filename != '..') {
                $path = $dirpath.'/'.$filename;
                if (is_dir($path) && file_exists("$path/imsmanifest.xml")) {
                    if ($all == 'force' || !file_exists("$path/moodle_inx.ser")) {
                        echo "DEPLOYING $path<br/>";
                        ims_deploy_file($file.'/'.$filename, $all);
                    }
                }
                else if (is_dir($path)) {
                    echo "DEPLOYING $path<br/>";
                    ims_deploy_folder($file.'/'.$filename, $all); 
                }
                else {
                    echo "WONT DEPLOY $path<br/>";
                }
            }
        }
        closedir($dir);     
    }

///
/// Common and useful functions used by the body of the script
///

    /*** This function will return a tree of manifests (xmlized) as they are
     *   found and extracted from one manifest file. The first manifest in the
     *   will be the main one, while the rest will be submanifests. In the
     *   future (when IMS CP suppors it, external submanifest will be detected
     *   and retrieved here too). See IMS specs for more info.
     */
    function ims_extract_manifests($data) {

        $manifest = new stdClass;    //To store found manifests in a tree structure

    /// If there are some manifests
        if (!empty($data['manifest'])) {
        /// Add manifest to results array
            $manifest->data = $data['manifest'];
        /// Look for submanifests
            $submanifests = ims_extract_submanifests($data['manifest']['#']);
        /// Add them as child
            if (!empty($submanifests)) {
                $manifest->childs = $submanifests;
            }
        }
    /// Return tree of manifests found
        return $manifest;
    }

    /* This function will search recursively for submanifests returning an array
     * containing them (xmlized) following a tree structure.
     */
    function ims_extract_submanifests($data) {

        $submanifests = array();  //To store found submanifests

    /// If there are some manifests
        if (!empty($data['manifest'])) {
        /// Get them
            foreach ($data['manifest'] as $submanifest) {
            /// Create a new submanifest object
                $submanifest_object = new stdClass;
                $submanifest_object->data = $submanifest;
            /// Look for more submanifests recursively
                $moresubmanifests = ims_extract_submanifests($submanifest['#']);
            /// Add them to results array
                if (!empty($moresubmanifests)) {
                    $submanifest_object->childs = moresubmanifests;
                }
            /// Add submanifest object to results array
                $submanifests[] = $submanifest_object;
            }
        }
    /// Return array of manifests found
        return $submanifests;
    }

    /*** This function will return an ordered and nested array of items
     *   that is a perfect representation of the prefered organization
     */
    function ims_process_organizations($data) {

        global $CFG;
        
    /// Get the default organization
        $default_organization = $data['@']['default'];
        debugging('default_organization: '.$default_organization);

    /// Iterate (reverse) over organizations until we find the default one
        if (empty($data['#']['organization'])) {  /// Verify <organization> exists
            return false;
        }
        $count_organizations = count($data['#']['organization']);
        debugging('count_organizations: '.$count_organizations);

        $current_organization = $count_organizations - 1;
        while ($current_organization >= 0) {
        /// Load organization and check it
            $organization = $data['#']['organization'][$current_organization];
            if ($organization['@']['identifier'] == $default_organization) {
                    $current_organization = -1;   //Match, so exit.
            }
            $current_organization--;
        }

    /// At this point we MUST have the final organization
        debugging('final organization: '.$organization['#']['title'][0]['#']);
        if (empty($organization)) {
            return false;    //Error, no organization found
        }

    /// Extract items map from organization
        $items = $organization['#']['item'];
        if (empty($organization['#']['item'])) {  /// Verify <item> exists
            return false;
        }
        if (!$itemmap = ims_process_items($items)) {
            return false;    //Error, no items found
        }
        return $itemmap;
    }

    /*** This function gets the xmlized representation of the items
     *   and returns an array of items, ordered, with level and info
     */
    function ims_process_items($items, $level = 1, $id = 1, $parent = 0) {
        global $CFG;

        $itemmap = array();

    /// Iterate over items from start to end
        $count_items = count($items);
        debugging('level '.$level.'-count_items: '.$count_items);

        $current_item = 0;
        while ($current_item < $count_items) {
        /// Load item 
            $item = $items[$current_item];
            $obj_item = new stdClass;
            $obj_item->title         = $item['#']['title'][0]['#'];
            $obj_item->identifier    = $item['@']['identifier'];
            $obj_item->identifierref = $item['@']['identifierref'];
            $obj_item->id            = $id;
            $obj_item->level         = $level;
            $obj_item->parent        = $parent;
        /// Only if the item has everything
            if (!empty($obj_item->title) && 
                !empty($obj_item->identifier)) {
            /// Add to itemmap
                $itemmap[$id] = $obj_item;
                debugging('level '.$level.'-id '.$id.'-parent '.$parent.'-'.$obj_item->title);
            /// Counters go up
                $id++;
            /// Check for subitems recursively
                $subitems = $item['#']['item'];
                if (count($subitems)) {
                /// Recursive call
                    $subitemmap = ims_process_items($subitems, $level+1, $id, $obj_item->id);
                /// Add at the end and counters if necessary
                    if ($count_subitems = count($subitemmap)) {
                        foreach ($subitemmap as $subitem) {
                        /// Add the subitem to the main items array
                            $itemmap[$subitem->id] = $subitem;
                        /// Counters go up
                            $id++;
                        }
                    }
                }
            }
            $current_item++;
        }
        return $itemmap;
    }

    /*** This function will load an array of resources to be used later. 
     *   Keys are identifiers
     */
    function ims_load_resources($data, $manifest_base, $resources_base) {
        global $CFG;

        $resources = array();

        if (empty($data)) {  /// Verify <resource> exists
            return false;
        }
        $count_resources = count($data);
        debugging('count_resources: '.$count_resources);

        $current_resource = 0;
        while ($current_resource < $count_resources) {
        /// Load resource 
            $resource = $data[$current_resource];

        /// Create a new object resource
            $obj_resource = new stdClass;
            $obj_resource->identifier = $resource['@']['identifier'];
            $obj_resource->resource_base = $resource['@']['xml:base'];
            $obj_resource->href = $resource['@']['href'];
            if (empty($obj_resource->href)) {
                $obj_resource->href = $resource['#']['file']['0']['@']['href'];
            }
            
        /// Some packages are poorly done and use \ in roots. This makes them 
        /// not display since the URLs are not valid.
            if (!empty($obj_resource->href)) {
                $obj_resource->href = strtr($obj_resource->href, "\\", '/');    
            }

        /// Only if the resource has everything
            if (!empty($obj_resource->identifier) &&
                !empty($obj_resource->href)) {
            /// Add to resources (identifier as key)
            /// Depending of $manifest_base, $resources_base and the particular
            /// $resource_base variable, concatenate them to build the correct href
                $href_base = '';
                if (!empty($manifest_base)) {
                    $href_base = $manifest_base;
                }
                if (!empty($resources_base)) {
                    $href_base .= $resources_base;
                }
                if (!empty($obj_resource->resource_base)) {
                    $href_base .= $obj_resource->resource_base;
                }
                $resources[$obj_resource->identifier] = $href_base.$obj_resource->href;
            }
        /// Counters go up
            $current_resource++;
        }
        return $resources;
    }
    
    /*** This function finds out the title of the resource from the XML.
     *   First 2 conditions cover nearly all cases. The third is a fair guess
     *   if no metadata is supplied. This is eventually saved in the serialized
     *   hash as $items['title'].
     */    
    function ims_get_cp_title($xmlobj) {
        $md = $xmlobj['manifest']['#']['metadata']['0']['#'];
        if (isset($md['imsmd:lom'])) {
            return $md['imsmd:lom']['0']['#']['imsmd:general']['0']['#']['imsmd:title']['0']['#']['imsmd:langstring']['0']['#'];
        }
        else if (isset($md['imsmd:record'])) {
            return $md['imsmd:record']['0']['#']['imsmd:general']['0']['#']['imsmd:title']['0']['#']['imsmd:langstring']['0']['#'];
        }
        else if ($title = $xmlobj['manifest']['#']['organizations']['0']['#']['organization']['0']['#']['title']['0']['#']) {
            return $title;  
        }
        else {
            return "NO TITLE FOUND";
        }
    }

	function ims_extract_metadata($xmlobj) {
	
		//this is necessary as for some reason there are two types of metadata root tag 'lom' and 'record'
		if (isset($xmlobj['manifest']['#']['metadata']['0']['#']['imsmd:lom'])) {
			$md = $xmlobj['manifest']['#']['metadata']['0']['#']['imsmd:lom'];
        }
        else if (isset($xmlobj['manifest']['#']['metadata']['0']['#']['imsmd:record'])) {
			$md = $xmlobj['manifest']['#']['metadata']['0']['#']['imsmd:record'];
		}
	
		$metadata = new object();

		//general
		$metadata->identifier	= $md['0']['#']['imsmd:general']['0']['#']['imsmd:identifier']['0']['#'];
	//	$metadata->title			= $md['0']['#']['imsmd:general']['0']['#']['imsmd:title']['0']['#']['imsmd:langstring']['0']['#'];
		$metadata->lang			= $md['0']['#']['imsmd:general']['0']['#']['imsmd:language']['0']['#'];
		$metadata->description	= $md['0']['#']['imsmd:general']['0']['#']['imsmd:description']['0']['#']['imsmd:langstring']['0']['#'];
		
		//build '||' (double pipe) separated list of keywords
		$keywords = array();
		foreach($md['0']['#']['imsmd:general']['0']['#']['imsmd:keywords'] as $keyword)
		{
			$keywords[] = $keyword['#']['imsmd:langstring']['0']['#'];
		}
		
//changed/added by Richard Goddard 3rd June 2008 to accept both "keywords" (original) and "keyword" (new)...

		foreach($md['0']['#']['imsmd:general']['0']['#']['imsmd:keyword'] as $keyword)
		{
			$keywords[] = $keyword['#']['imsmd:langstring']['0']['#'];
		}

//end of Richard's changes
		
		$metadata->keywords	= implode($keywords,'||');
		unset($keywords);
		
		//lifecycle
		$metadata->status	= $md['0']['#']['imsmd:lifecycle']['0']['#']['imsmd:status']['0']['#']['imsmd:langstring']['0']['#'];
		$metadata->role		= $md['0']['#']['imsmd:lifecycle']['0']['#']['imsmd:contribute']['0']['#']['imsmd:role']['0']['#']['imsmd:langstring']['0']['#'];
		$metadata->centity	= $md['0']['#']['imsmd:lifecycle']['0']['#']['imsmd:contribute']['0']['#']['imsmd:centity']['0']['#']['imsmd:vcard']['0']['#'];

		//technical
		//multiple mimetypes can be described - currently uses only the first one (..?)
		$metadata->mimetype	= $md['0']['#']['imsmd:technical']['0']['#']['imsmd:format']['0']['#'];
		
		//rights
		//$metadata->copyright	= $md['0']['#']['imsmd:rights']['0']['#']['imsmd:copyrightandotherrestrictions']['0']['#']['imsmd:langstring']['0']['#'];
		$metadata->rightsdesc	= $md['0']['#']['imsmd:rights']['0']['#']['imsmd:description']['0']['#']['imsmd:langstring']['0']['#'];
		
		return $metadata;
	}

	function ims_deploy_file($file, $all='') {   
        global $CFG;

		if (record_exists("resource_ims", "filepath", $file))
		{	
			echo "Package already deployed<br />\n";
			return;
		}
		
		
    /// Load request parameters 
        $resourcedir = "$CFG->repository/$file";
        
    /// Get some needed strings
        $strdeploy = get_string('deploy','resource');
    
    ///
    /// Main process, where everything is deployed
    ///

    /// Load imsmanifest to memory (instead of using a full parser,
    /// we are going to use xmlize intensively (because files aren't too big)
        if (!$imsmanifest = ims_file2var ($resourcedir.'/imsmanifest.xml')) {
            error (get_string ('errorreadingfile', 'error', 'imsmanifest.xml'));
        }
		
    /// Check if the first line is a proper one, because I've seen some
    /// packages with some control characters at the beginning.
        $inixml = strpos($imsmanifest, '<?xml ');
        if ($inixml !== false) {
            if ($inixml !== 0) {
                //Strip strange chars before "< ?xml "
                $imsmanifest = substr($imsmanifest, $inixml);
            }
        } else {
			
            if (
                (ord($imsmanifest[0]) == 0xFF && ord($imsmanifest[1]) == 0xFE) || 
                (ord($imsmanifest[0]) == 0xFE && ord($imsmanifest[1]) == 0xFF)) {
					
	//changed/added by Richard Goddard 3rd June 2008 to accept NLN UTF-16 manifests...
	$imsmanifest = decode_utf16 ( $imsmanifest );
	//end of Richard's changes

			} else {
                error (get_string ('invalidxmlfile', 'error', 'imsmanifest.xml'));
            }
        }
    
    /// xmlize the variable
        $data = xmlize($imsmanifest, 0);

    ///    traverse_xmlize($data);
        $title = ims_get_cp_title($data);
    ///    foreach ($GLOBALS['traverse_array'] as $line) echo $line;
    
    /// Extract every manifest present in the imsmanifest file.
    /// Returns a tree structure.
        if (!$manifests = ims_extract_manifests($data)) {
            error (get_string('nonmeaningfulcontent', 'error'));
        }
    
    /// Process every manifest found in inverse order so every one 
    /// will be able to use its own submanifests. Not perfect because
    /// theorically this will allow some manifests to use other non-childs
    /// but this is supposed to be
    
    /// Detect if all the manifest share a common xml:base tag
		if(isset($data['manifest']['@']['xml:base'])){
			$manifest_base = $data['manifest']['@']['xml:base'];
		}
    /// Parse XML-content package data
    /// First we select an organization an load all the items

        if (!$items = ims_process_organizations($data['manifest']['#']['organizations']['0'])) {
            if ($all == 'force') return; else error (get_string('nonmeaningfulcontent', 'error'));
        }
    
    /// Detect if all the resources share a common xml:base tag
        $resources_base = $data['manifest']['#']['resources']['0']['@']['xml:base'];
      
    /// Now, we load all the resources available (keys are identifiers)
        if (!$resources = ims_load_resources($data['manifest']['#']['resources']['0']['#']['resource'], $manifest_base, $resources_base)) {
            error (get_string('nonmeaningfulcontent', 'error'));
        }
    ///Now we assign to each item, its resource (by identifier)
        foreach ($items as $key=>$item) {
            if (!empty($resources[$item->identifierref])) {
                $items[$key]->href = $resources[$item->identifierref];
            } else {
                $items[$key]->href = '';
            }
        }
    
    /// Create the INDEX
        $items['title'] = $title;

	//Superseded by MRCUTE, now stored in db
	/*    if (!ims_save_serialized_file($resourcedir.'/moodle_inx.ser', $items)) {
            error (get_string('errorcreatingfile', 'error', 'moodle_inx.ser'));
        }
    */
	
    /// Parse XML-metadata
		$metadata = ims_extract_metadata($data);
		$metadata->title			= $title;
		$metadata->contributeddate	= 0;
		$metadata->vperson			= '';
		$metadata->vdate			= 0;
		$metadata->vcomments		= '';
		//save serialized deployment info in DB instead of file
		$metadata->deploy			= serialize($items);
		
		$metadata->filepath			= $file;
		
		//data is from files so we must addslashes
		$metadata=addslashes_object($metadata);
		
		//echo "<pre>".print_r($metadata ,1)."</pre>";
		
		if (!insert_record('resource_ims', $metadata)) {
			error("Error: Unable to deploy");
	    }
   
    /// End button (go to view mode)
        echo '<center>';
        print_simple_box(get_string('imspackageloaded', 'resource'), 'center');
        $link = $CFG->wwwroot.'/mod/resource/type/ims/preview.php';
        $options['directory'] = $file;
        $label = get_string('viewims', 'resource');
        $method = 'get';
        print_single_button($link, $options, $label, $method);
        echo '</center>';
    
    ///
    /// End of main process, where everything is deployed
    ///
    }

	function ims_deploy_file_return($file, $all='') {   
        global $CFG;

		if (record_exists("resource_ims", "filepath", $file))
		{	
			return false;
		}
		
    /// Load request parameters 
        $resourcedir = "$CFG->repository/$file";
        
    ///
    /// Main process, where everything is deployed
    ///

    /// Load imsmanifest to memory (instead of using a full parser,
    /// we are going to use xmlize intensively (because files aren't too big)
        if (!$imsmanifest = ims_file2var ($resourcedir.'/imsmanifest.xml')) {
            //error (get_string ('errorreadingfile', 'error', 'imsmanifest.xml'));
			return false;
        }
		
    /// Check if the first line is a proper one, because I've seen some
    /// packages with some control characters at the beginning.
        $inixml = strpos($imsmanifest, '<?xml ');
        if ($inixml !== false) {
            if ($inixml !== 0) {
                //Strip strange chars before "< ?xml "
                $imsmanifest = substr($imsmanifest, $inixml);
            }
        } else {
			
            if (
                (ord($imsmanifest[0]) == 0xFF && ord($imsmanifest[1]) == 0xFE) || 
                (ord($imsmanifest[0]) == 0xFE && ord($imsmanifest[1]) == 0xFF)) {

					//changed/added by Richard Goddard 3rd June 2008 to accept NLN UTF-16 manifests...
					$imsmanifest = decode_utf16 ( $imsmanifest );
					//end of Richard's changes

			} else {
                error (get_string ('invalidxmlfile', 'error', 'imsmanifest.xml'));
            }
        }
    
    /// xmlize the variable
        $data = xmlize($imsmanifest, 0);

    ///    traverse_xmlize($data);
        $title = ims_get_cp_title($data);
    ///    foreach ($GLOBALS['traverse_array'] as $line) echo $line;
    
    /// Extract every manifest present in the imsmanifest file.
    /// Returns a tree structure.
        if (!$manifests = ims_extract_manifests($data)) {
            //error (get_string('nonmeaningfulcontent', 'error'));
			return false;
        }
    
    /// Process every manifest found in inverse order so every one 
    /// will be able to use its own submanifests. Not perfect because
    /// theorically this will allow some manifests to use other non-childs
    /// but this is supposed to be
    
    /// Detect if all the manifest share a common xml:base tag
		if(isset($data['manifest']['@']['xml:base'])){
			$manifest_base = $data['manifest']['@']['xml:base'];
		}
    /// Parse XML-content package data
    /// First we select an organization an load all the items

        if (!$items = ims_process_organizations($data['manifest']['#']['organizations']['0'])) {
            if ($all == 'force') return; else error (get_string('nonmeaningfulcontent', 'error'));
        }
    
    /// Detect if all the resources share a common xml:base tag
        $resources_base = $data['manifest']['#']['resources']['0']['@']['xml:base'];
      
    /// Now, we load all the resources available (keys are identifiers)
        if (!$resources = ims_load_resources($data['manifest']['#']['resources']['0']['#']['resource'], $manifest_base, $resources_base)) {
            //error (get_string('nonmeaningfulcontent', 'error'));
			return false;
        }
    ///Now we assign to each item, its resource (by identifier)
        foreach ($items as $key=>$item) {
            if (!empty($resources[$item->identifierref])) {
                $items[$key]->href = $resources[$item->identifierref];
            } else {
                $items[$key]->href = '';
            }
        }
    
    /// Create the INDEX
        $items['title'] = $title;

	//Superseded by MRCUTE, now stored in db
	/*    if (!ims_save_serialized_file($resourcedir.'/moodle_inx.ser', $items)) {
            error (get_string('errorcreatingfile', 'error', 'moodle_inx.ser'));
        }
    */
	
    /// Parse XML-metadata
		$metadata = ims_extract_metadata($data);
		$metadata->title			= $title;
		$metadata->contributeddate	= 0;
		$metadata->vperson			= '';
		$metadata->vdate			= 0;
		$metadata->vcomments		= '';
		//save serialized deployment info in DB instead of file
		$metadata->deploy			= serialize($items);
		
		$metadata->filepath			= $file;
		
		//data is from files so we must addslashes
		$metadata=addslashes_object($metadata);
		 
		//echo "<pre>".print_r($metadata ,1)."</pre>";
		
		if (!$metadata->id = insert_record('resource_ims', $metadata)) {
			return false;
		} else {
			return $metadata;
		}
       
    }

	function ims_file2var ($file)
	{
		$status = true;
		$var = '';
		$fp = fopen($file, 'r')
			or $status = false;
		if ($status) {
		   while ($data = fread($fp, 4096)) {
			   $var = $var.$data;
		   }
		   fclose($fp);
		}
		if (!$status) {
			$var = false;
		}
		return $var;
	}


?>