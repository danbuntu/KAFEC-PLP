<?PHP
require("../../../integration_library.php");
/**
* 
* export, allows the creation of zip and scorm packages
*
* @author Patrick Lockley
* @version 1.0
* @copyright Copyright (c) 2008,2009 University of Nottingham
* @package
*/

require("../../../config.php");
require("../../../session.php");
include "archive.php";
include "scorm_library.php";
include "../database_library.php";
include "../screen_size_library.php";
include "../template_status.php";
include "../user_library.php";

$folder_id_array = array();
$folder_array = array();
$file_array = array();
$delete_file_array = array();
$delete_folder_array = array();

if(is_numeric($_GET['template_id'])){

	if((is_template_exportable(mysql_real_escape_string($_GET['template_id'])))||(is_user_creator(mysql_real_escape_string($_GET['template_id'])))||is_user_admin()){

		$mysql_id=database_connect("Scorm export database connect success","Scorm export database connect failed");
	
		/*
		* Get the file path
		*/
	
		$query = "select " . $xerte_toolkits_site->database_table_prefix . "templatedetails.template_name as zipname, " . $xerte_toolkits_site->database_table_prefix . "templaterights.template_id, " . $xerte_toolkits_site->database_table_prefix . "logindetails.username, " . $xerte_toolkits_site->database_table_prefix . "originaltemplatesdetails.template_name, " . $xerte_toolkits_site->database_table_prefix . "originaltemplatesdetails.template_framework from " . $xerte_toolkits_site->database_table_prefix . "templaterights, " . $xerte_toolkits_site->database_table_prefix . "logindetails, " . $xerte_toolkits_site->database_table_prefix . "originaltemplatesdetails, " . $xerte_toolkits_site->database_table_prefix . "templatedetails where " . $xerte_toolkits_site->database_table_prefix . "templatedetails.template_type_id = " . $xerte_toolkits_site->database_table_prefix . "originaltemplatesdetails.template_type_id and " . $xerte_toolkits_site->database_table_prefix . "templaterights.template_id = " . $xerte_toolkits_site->database_table_prefix . "templatedetails.template_id and " . $xerte_toolkits_site->database_table_prefix . "templatedetails.creator_id = " . $xerte_toolkits_site->database_table_prefix . "logindetails.login_id and " . $xerte_toolkits_site->database_table_prefix . "templaterights.template_id=\"" . mysql_real_escape_string($_GET['template_id']) . "\" AND role=\"creator\"";

		$query_response = mysql_query($query);
	
		$row = mysql_fetch_array($query_response);
	
		/*
		* Set up the paths
		*/
	
		$dir_path = $xerte_toolkits_site->users_file_area_full . $row['template_id'] . "-" . $row['username'] . "-" . $row['template_name'] . "/";
	
		$parent_template_path = $xerte_toolkits_site->basic_template_path . $row['template_framework'] . "/parent_templates/" . $row['template_name'] . "/";
	
		$scorm_path = $xerte_toolkits_site->basic_template_path . $row['template_framework'] . "/scorm/";
	
		/*
		* Make the zip
		*/
	
		$zipfile = new zip_file("example_zipper_new" . date(U) . ".zip");
		$zipfile->set_options(array('basedir' => $dir_path, 'prepand' => "", 'inmemory' => 1, 'recurse' => 1, 'storepaths' => 1));
	
		/*
		* Copy the core files over from the parent folder
		*/
	
		folder_loop($parent_template_path);
	
		copy($dir_path . "data.xml",$dir_path . "template.xml");
	
		copy_parent_files();
	
		/*
		* If scorm copy the scorn files as well
		*/
	
		$scorm=mysql_real_escape_string($_GET['scorm']);
	
		if($scorm=="true"){
	
			folder_loop($scorm_path);
	
			copy_scorm_files();
	
		}else{
	
			copy($scorm_path . "rloObject.js", $dir_path . "rloObject.js");
	
			array_push($delete_file_array,  $dir_path . "rloObject.js");	
	
			copy($scorm_path . "MainPreloader.swf", $dir_path . "MainPreloader.swf");
	
			array_push($delete_file_array,  $dir_path . "MainPreloader.swf");	
	
			copy($scorm_path . "resources.swf", $dir_path . "resources.swf");
	
			array_push($delete_file_array,  $dir_path . "resources.swf");	
	
		}
	
		copy($xerte_toolkits_site->root_file_path . "XMLEngine.swf", $dir_path . "XMLEngine.swf");
	
		array_push($delete_file_array,  $dir_path . "XMLEngine.swf");

		if($scorm=="true"){
	
			copy($dir_path . $row['template_name'] . ".rlt", $dir_path . "learningobject.rlo");
	
			unlink($dir_path . $row['template_name'] . ".rlt");
	
			array_push($delete_file_array,  $dir_path . "learningobject.rlo");

		}else{

			copy($dir_path . $row['template_name'] . ".rlt", $dir_path . "learningobject.rlt");
	
			unlink($dir_path . $row['template_name'] . ".rlt");
	
			array_push($delete_file_array,  $dir_path . "learningobject.rlt");

		}
	
		folder_loop($dir_path);
	
		/*
		* Create scorm manifests of a basic HTML page
		*/
	
		if($scorm=="true"){
	
			lmsmanifest_create();
	
			scorm_html_page_create($row['template_name'],$row['template_framework']);
	
		}else{
	
			basic_html_page_create($row['template_name'],$row['template_framework']);
	
		}
	
	
		/*
		* Add the files to the zip file, create the archive, then send it to the user
		*/
	
		xerte_zip_files();
	
		$zipfile->create_archive();
	
		$zipfile->download_file($row['zipname']);
	

		/*
		* remove the files
		*/
	
		clean_up_files();
	
		unlink($dir_path . "template.xml");

	}

}

?>
