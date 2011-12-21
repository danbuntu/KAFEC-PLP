<?php
/**
 * $Id$
 * Renders the form used to search the ims repository and performs search query
 **/
 
    require_once('../../../../config.php');
    require_once('lib.php');
    require_once('finder_form.php');

	//switch for 'block mode' i.e. called from button in mrcute block not adding a resource
	$blockmode	= optional_param('blockmode', 0, PARAM_BOOL);
    $directory	= optional_param('directory', '', PARAM_PATH);
	
	$getsearch		= optional_param('search', '', PARAM_NOTAGS);
	$getcategory	= optional_param('category', '', PARAM_NOTAGS);
	$getpage		= optional_param('page', 0, PARAM_INT);
	$getsesskey		= optional_param('sesskey', '', PARAM_NOTAGS);
	$_qf__mod_resource_ims_mod_form = optional_param('_qf__mod_resource_ims_mod_form', '', PARAM_NOTAGS);
	$gettitle		= optional_param('title', '', PARAM_NOTAGS);
	$getkeywords	= optional_param('keywords', '', PARAM_NOTAGS);
	$getauthor		= optional_param('author', '', PARAM_NOTAGS);
	$getdescription = optional_param('description', '', PARAM_NOTAGS);
	$getsortby		= optional_param('sortby', '', PARAM_NOTAGS);
	$getvisibility	= optional_param('visibility', '', PARAM_NOTAGS);
	
	//get language strings
    $strdeployall	= get_string('deployall','resource_mrcuteget');
	$strrepository = get_string('repository','resource_mrcuteget');
    $strpreview = get_string('preview','resource_mrcuteget');
    $strchoose = get_string('choose','resource_mrcuteget');
	$stredit = get_string('edit','resource_mrcuteget');
	$strdraft = get_string('draft', 'resource_mrcuteget');
	$strrestricted = get_string('restricted', 'resource_mrcuteget');
	$strhidden = get_string('hidden', 'resource_mrcuteget');
	$strfindtitle = get_string('findtitle', 'resource_mrcuteget');

	$CFG->stylesheets[] = 'finder.css';
	$CFG->stylesheets[] = 'mootabs1.1.css';

	if($getsearch){
		require_js('mootools.js');
		require_js('mootabs1.1.js');
		require_js('mrcuteget.js');
	}
	//print standard head
	print_header($strfindtitle);

    if (empty($USER->id)) {
        require_login();
    }

	//get users roles to compare with $CFG->repositoryroles
	$roles = get_user_roles(get_record('context', 'id', CONTEXT_SYSTEM), $USER->id);
	foreach($roles as $role) { $userroles[] = $role->shortname;	}

	//compare roles allowed to validate with user's assigned roles
	$hasconfigrole = false;
	foreach( explode(",",$CFG->repositoryroles) as $configrole){
		if ( in_array($configrole, $userroles) ){ $hasconfigrole = true; }
	}
	
	//either role may moderate resources
	if(
		has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM, SITEID))
		OR $hasconfigrole
	){
		$hasapproval = true;
	} else {
		$hasapproval = false;
	}

	//print link to ims finder/browser
    echo "<a href=\"finder.php?blockmode=$blockmode&amp;directory=&amp;choose=id_reference\">$strrepository</a>";
	echo " &#187; <a href=\"finder.php?blockmode=$blockmode\">$strfindtitle</a>";

	/// If admin, add extra buttons - redeploy & help.
    if (has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM, SITEID))) {
        echo " | (<a href=\"repository_deploy.php?file=$directory&amp;all=force\">$strdeployall</a>) ";
        helpbutton("deploy", $strdeployall, "resource", true);
    }
	
	//process search form
	$mform = new mod_resource_ims_mod_form(null,null,'get');
//	$mform = new mod_resource_ims_mod_form();
	
	if ( $mform->is_cancelled() ){
	
		//cancel - close window		
		?><script type="text/javascript">
	        //<![CDATA[
			window.close();
	        //]]>
		</script><?php
	
	} else if ( $fromform=$mform->get_data() ){

		$title			= '';
		$description	= '';
		$keywords		= '';
	
		if ( isset($fromform->search) )
		{
			$search	= $fromform->search;
		}
		if ( isset($fromform->title) )
		{
			$title = $fromform->title;
		}			
		if ( isset($fromform->description) )
		{
			$description = $fromform->description;
		}
		if ( isset($fromform->keywords) )
		{
			$keywords = $fromform->keywords;
		}
		if ( isset($fromform->author) )
		{
			$author = $fromform->author;
		}
		if ( isset($fromform->sortby) )
		{
			$sortby = $fromform->sortby;
		}
		if ( isset($fromform->visibility) )
		{
			$visibility = $fromform->visibility;
		} else {
			$visibility = null;
		}
		
		//create list of categories to search 
		$categories = array();
		if( is_array($getcategory) ){
			foreach($getcategory as $category){
				if ($category)
				{
					$categories[] = $category;
				}
			}
		}
	//	echo "<pre>".print_r($categories,1)."</pre>";

		$search = trim(strip_tags($search)); // trim & clean raw searched string
		
		$searchterms = explode(" ", $search);    // Search for words independently
		foreach ($searchterms as $key => $searchterm) {
			if (strlen($searchterm) < 2) {
				unset($searchterms[$key]);
			}
		}
		$search = trim(implode(" ", $searchterms));

		//write the breadcrumb
		$searched = $space = "";
		if($search){
			$searched = $search;
			$space = " ";
			echo " &#187; '$searched'";
		}
		if ($author){
			$searched .= $space.$author;
		}

		//to allow case-insensitive search for postgesql
	    if ($CFG->dbfamily == 'postgres') {
	        $LIKE = 'ILIKE';
	        $NOTLIKE = 'NOT ILIKE';   // case-insensitive
	        $REGEXP = '~*';
	        $NOTREGEXP = '!~*';
	    } else {
	        $LIKE = 'LIKE';
	        $NOTLIKE = 'NOT LIKE';
	        $REGEXP = 'REGEXP';
	        $NOTREGEXP = 'NOT REGEXP';
	    }

		$titlesearch		= '';
		$descriptionsearch	= '';
		$keywordsearch		= '';

		foreach ($searchterms as $searchterm) {
			if ($titlesearch) {
	            $titlesearch .= ' AND ';
	        }
			if ($descriptionsearch) {
	            $descriptionsearch .= ' AND ';
	        }
			if ($keywordsearch) {
	            $keywordsearch .= ' AND ';
	        }

			if (substr($searchterm,0,1) == '+') {
	            $searchterm 		 = substr($searchterm,1);
	            $titlesearch		.= " title $REGEXP '(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)' ";
	            $descriptionsearch	.= " description $REGEXP '(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)' ";
	            $keywordsearch		.= " keywords $REGEXP '(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)' ";
	        } else if (substr($searchterm,0,1) == "-") {
	            $searchterm 		 = substr($searchterm,1);
	            $titlesearch 		.= " title $NOTREGEXP '(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)' ";
	            $descriptionsearch 	.= " description $NOTREGEXP '(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)' ";
	            $keywordsearch 		.= " keywords $NOTREGEXP '(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)' ";
	        } else {
	            $titlesearch 		.= ' title '.		$LIKE .' \'%'. $searchterm .'%\' ';
	            $descriptionsearch 	.= ' description '.	$LIKE .' \'%'. $searchterm .'%\' ';
	            $keywordsearch 		.= ' keywords '.	$LIKE .' \'%'. $searchterm .'%\' ';
	        }

		}

		$selectsql  = '';
		$categoriesand = '';
		
		if($search){
			$selectsql .= '(';

			$selectsqlor	= '';
			$authorand		= '';
			if($title && $search){
				$selectsql	   .= $titlesearch;
				$selectsqlor	= ' OR ';
				$authorand		= ' AND ';
			}
			
			if($description && $search){
				$selectsql	   .= $selectsqlor.$descriptionsearch;
				$selectsqlor	= ' OR ';
				$authorand		= ' AND ';
			}
			
			if ($keywords && $search){
				$selectsql	   .= $selectsqlor.$keywordsearch;
				$authorand		= ' AND ';
			}

			if ($author){
				$selectsql	.= $authorand.'(role LIKE \'Author\' AND centity LIKE \'%'.$author.'%\')';
			}

			$selectsql .= ')';

			$categoriesand = ' AND ';
		}
		
		if( count($categories) ){
			$selectsql .= $categoriesand.' ( ';
			$or = '';
			foreach($categories as $category){
				$selectsql .= $or." filepath LIKE '%".$category."%'";
				$or = ' OR ';
			}
			$selectsql .= ')';
		}

		//only show visible=1 unless user has admin/approval rights, or is the packages owner (centity)
		if(!$hasapproval) {
			$selectsql .= ' AND (';
			$selectsql .= 'visible=1';
			$selectsql .= ' OR ';
			$selectsql .= "centity LIKE '%".$USER->username."%'";
			$selectsql .= ')';
		} else {
			switch ($visibility)
			{
				case "hidden":
					$selectsql .= ' AND (visible=0) ';
					break;
				case "nothidden":
					$selectsql .= ' AND (visible=1) ';
					break;
			}

			
		}

		switch ($sortby) {
			case "name":
				$sort = "title ASC";
				break;
			case "modified":
				$sort = "modifieddate DESC";
				break;
			case "created":
				$sort = "contributeddate DESC";
				break;
		}
		
		$page = $getpage;
		$recordsperpage = 5;
		
		$resources=null;
		if($selectsql){
			$totalcount = count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix .'resource_ims WHERE '. $selectsql);
			$resources = get_records_sql('SELECT * FROM '.$CFG->prefix .'resource_ims WHERE '. $selectsql .' ORDER BY '. $sort, ($page * $recordsperpage), $recordsperpage);
		}
		
		
		///JORUM
		if ( isset($fromform->jorum) )
		{
			$jorum = $fromform->jorum;
			$jorumsearch = $fromform->search;
		} else {
			$jorum = false;
		}

		///JORUM
		if ( isset($fromform->nln) )
		{
			$nln = $fromform->nln;
			$nlnsearch = $fromform->search;
		} else {
			$nln = false;
		}

		
?>
		<div id="searchTabs">
			<ul class="mootabs_title">
				<li title="moodle" class="mootabs_button"><?php echo $SITE->shortname; ?></li>
			<?php if ($jorum){ ?>
				<li title="jorum" class="mootabs_button">JORUM</li>
			<?php } ?>
			<?php if ($nln){ ?>
				<li title="nln" class="mootabs_button">NLN</li>
			<?php } ?>
			</ul>
		
			<div id="moodle" class="mootabs_panel">
<?php
		//output results in same format as ims finder.php
		if ($resources) {
			echo '<ul style="list-style:none;padding:10px;margin:0px;">'; 
			//echo "<pre>".print_r($resources,1)."</pre>";
			foreach($resources as $resource)
			{
				if($resource->centity){
					$vcard = explode("\n", $resource->centity);
					foreach ($vcard as $vcardlines) {
						$vcarditem = explode(':', $vcardlines);
						if ($vcarditem[0] == "ORG") {
							$vcarduser = $vcarditem[1];
						}
					}
				}		
			
				//skip 'Draft' and copyright restricted ('copyright'>1) resources from non admins and non owners
				if(!$hasapproval && $vcarduser != $USER->username){
					if($resource->status == 'Draft' || $resource->copyright>1){
						continue;
					}
				}

				echo "<li>";
				echo "<p>";
				echo "<img src=\"images/ims.gif\" alt=\"IMS CP Package\" /> <b>$resource->title</b> ";	
				if (!$blockmode){
					echo "(<a onclick=\"return set_value('#$resource->filepath','$resource->title', '".str_replace(array("\r\n","\n","\r"), '', rawurlencode($resource->description))."')\" href=\"#\">$strchoose</a>) ";
				} else {
					if($hasapproval || $vcarduser == $USER->username){
						echo " (<a href=\"../mrcuteput/uploader.php?rid=$resource->id&amp;blockmode=$blockmode\">$stredit</a>) ";
					}
				}
				
				echo " (<a href=\"preview.php?blockmode=$blockmode&amp;directory=$resource->filepath&amp;choose=id_reference\">$strpreview</a>) ";
				//mark draft and restricted materials
				if($resource->status && $resource->status == 'Draft'){
					echo " (<span style=\"color:red;\"><em>$strdraft</em></span>)";
				}
				if($resource->copyright && $resource->copyright>1){
					echo " (<span style=\"color:red;\"><em>$strrestricted</em></span>)";
				}
				if($resource->visible == 0){
					echo " (<span style=\"color:red;\"><em>$strhidden</em></span>)";
				}
				
				echo " <span style=\"font-size:0.8em;\">".truncate_string($resource->description,300)."</span><br />";

			
				echo "<a href=\"download.php?rid=$resource->id\" target=\"_blank\" title=\"Download IMS Zip Package\"><img src=\"$CFG->pixpath/f/zip.gif\" alt=\"Download IMS Zip Package\" /></a> ";
				echo "<a href=\"delete.php?rid=$resource->id&amp;returnto=".urlencode($_SERVER['REQUEST_URI'])."\" title=\"Permanently Delete Package\"><img src=\"$CFG->pixpath/t/delete.gif\" alt=\"Permanently Delete Package\" /></a> ";
				echo "<span style=\"font-size:0.75em; color:#777777;\">".str_replace("_", " ", $resource->filepath);
				if (isset($vcarduser)){
					echo " - ".$vcarduser;
				}

				switch ($sortby) {
					case "modified":
						if($resource->modifieddate>0){
							echo ' [Modified: '.date('d-m-Y', $resource->modifieddate).']';
						}
						break;
					case "created":
						if($resource->contributeddate>0){
							echo ' [Created: '.date('d-m-Y', $resource->contributeddate).']';
						}
						break;
				}

				echo "</span>";
				echo "</p>";
				echo "</li>\n";

			}
			
			echo '</ul>';
			
			$baseurl =	"finder.php?".
						"blockmode=$blockmode&amp;".
						"search=$search&amp;".
						"perpage=$recordsperpage&amp;".
						"sesskey=".$getsesskey."&amp;".
						"_qf__mod_resource_ims_mod_form=".$_qf__mod_resource_ims_mod_form."&amp;";
						if($gettitle){
							$baseurl .= "title=".$gettitle."&amp;";
						}
						if($getkeywords){
							$baseurl .= "keywords=".$getkeywords."&amp;";
						}
						if($getauthor){
							$baseurl .= "author=".$getauthor."&amp;";
						}
						if($getdescription){
							$baseurl .= "description=".$getdescription."&amp;";
						}
						if($getsortby){
							$baseurl .= "sortby=".$getsortby."&amp;";
						}
						if($getvisibility){
							$baseurl .= "visibility=".$getvisibility."&amp;";
						}
					
				foreach($categories as $category)
				{
					$baseurl .= "category%5B%5D=".$category."&amp;";
				}
	        print_paging_bar($totalcount, $page, $recordsperpage, $baseurl, 'page',($recordsperpage == 99999));
			
		} else {
			echo "No resources found";
		}
?>
			</div>
<?php

		if($jorum) {
			echo '<div id="jorum" class="mootabs_panel">';

			if($jorumresults = searchJorum(urlencode($jorumsearch))){

				echo '<ul style="list-style:none;padding:10px;margin:0px;">';
				
				foreach($jorumresults as $jorumresult){
					echo "<li>";
					echo "<p>";
					echo "<img src=\"images/ims.gif\" alt=\"IMS CP Package\" /> <b>".$jorumresult['title']."</b> ";	
					if (!$blockmode){
					//	echo "(<a onclick=\"return set_value('JORUM#".$jorumresult['viewuri']."','".$jorumresult['title']."', '".str_replace(array("\r\n","\n","\r"), '', $jorumresult['description'])."')\" href=\"#\">$strchoose</a>) ";
					}

					echo " (<a href=\"".$jorumresult['viewuri']."\" target=\"_blank\">$strpreview</a>) ";
					echo "<span style=\"font-size:0.8em;\">".truncate_string($jorumresult['description'],300)."</span><br />";
					
					echo "</p>";
					echo "</li>\n";

				}
				
				echo '</ul>';
			} else {
				echo "No resources found";	
			}
			echo '</div>';
		}
		
		if($nln)
		{
?>
			<div id="nln" class="mootabs_panel" style="padding:2px;overflow:hidden;"><iframe border="0" src="browse_start.php?q=<?php echo $nlnsearch; ?>" style="border:0;width:100%;height:100%;"></iframe></div>
<?php
		}
		
		echo '</div>';

		
		//print basic footer - is there a better way of doing this?
		echo "</div></div></body></html>";

	//render form
	} else {
	
		$toform = new object();
		$toform->blockmode = $blockmode;
	    $mform->set_data($toform);
	    $mform->display();
	
		//print_footer();
	    echo "</div></div></body></html>";

	}

?>
