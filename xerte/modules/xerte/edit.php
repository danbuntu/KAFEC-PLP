<?PHP /**
* 
* duplicate page, allows the site to edit a xerte module
*
* @author Patrick Lockley
* @version 1.0
* @copyright Copyright (c) 2008,2009 University of Nottingham
* @package
*/


/**
* 
* Function create folder loop
* This function outputs the xerte editor code
* @param array $row_edit - the mysql query for this folder
* @param number $xerte_toolkits_site - a number to make sure that we enter and leave each folder correctly
* @param bool $read_status - a read only flag for this template
* @param number $version_control - a setting to handle the delettion of lock files when the window is closed
* @version 1.0
* @author Patrick Lockley
*/

function output_editor_code($row_edit, $xerte_toolkits_site, $read_status, $version_control){

	require_once("config.php");

	require_once($xerte_toolkits_site->php_library_path . "database_library.php");

	database_connect("Edit xerte connect success","Edit xerte connect fail");

	$query_for_username = "select username from " . $xerte_toolkits_site->database_table_prefix . "logindetails where login_id=\"" . $row_edit['user_id'] . "\"";

	$query_for_username_response = mysql_query($query_for_username);

	$row_username = mysql_fetch_array($query_for_username_response);

	/**
	* create the preview xml used for editing
	*/

	if(!file_exists($xerte_toolkits_site->root_file_path . $xerte_toolkits_site->users_file_area_short . $row_edit['template_id'] . "-" . $row_username['username'] . "-" . $row_edit['template_name'] . "/preview.xml")){

		copy($xerte_toolkits_site->root_file_path . $xerte_toolkits_site->users_file_area_short . $row_edit['template_id'] . "-" . $row_username['username'] . "-" . $row_edit['template_name'] . "/data.xml",$xerte_toolkits_site->root_file_path . $xerte_toolkits_site->users_file_area_short . $row_edit['template_id'] . "-" . $row_username['username'] . "-" . $row_edit['template_name'] . "/preview.xml");

		chmod($xerte_toolkits_site->root_file_path . $xerte_toolkits_site->users_file_area_short . $row_edit['template_id'] . "-" . $row_username['username'] . "-" . $row_edit['template_name'] . "/preview.xml",0777);

	}

	/**
	* set up the strings used in the flash vars
	*/

	$string_for_flash_xml = $xerte_toolkits_site->users_file_area_short . $row_edit['template_id'] . "-" . $row_username['username'] . "-" . $row_edit['template_name'] . "/preview.xml";

	$string_for_flash_media = $xerte_toolkits_site->users_file_area_short . $row_edit['template_id'] . "-" . $row_username['username'] . "-" . $row_edit['template_name'] . "/media/";

	$string_for_flash_xwd = "modules/" . $row_edit['template_framework'] . "/parent_templates/" . $row_edit['template_name'] . "/";

	$query_for_template_name = "select " . $xerte_toolkits_site->database_table_prefix . "originaltemplatesdetails.template_name, " . $xerte_toolkits_site->database_table_prefix . "originaltemplatesdetails.template_framework from " . $xerte_toolkits_site->database_table_prefix . "originaltemplatesdetails, " . $xerte_toolkits_site->database_table_prefix . "templatedetails where " . $xerte_toolkits_site->database_table_prefix . "templatedetails.template_type_id = " . $xerte_toolkits_site->database_table_prefix . "originaltemplatesdetails.template_type_id AND template_id =\"" . $_GET['template_id'] . "\"";

	$query_name_response = mysql_query($query_for_template_name);

	$row_name = mysql_fetch_array($query_name_response);

	/**
	* sort of the screen sies required for the preview window
	*/

	$temp = explode("~",get_template_screen_size($row_edit['template_name'],$row_edit['template_framework']));

	/**
	* set up the onunload function used in version control
	*/

	if($version_control){

		echo str_replace("$1", $row_edit['template_id'] . "-" . $row_username['username'] . "-" . $row_edit['template_name'] . "/",file_get_contents("modules/" . $row_edit['template_framework'] . "/edit_xerte_top"));

	}else{

		echo str_replace("$1", $row_edit['template_id'] . "-" . $row_username['username'] . "-" . $row_edit['template_name'] . "/",file_get_contents("modules/" . $row_edit['template_framework'] . "/edit_xerte_top"));

	}

	/**
	* set up the flash vars the editor needs.
	*/

	echo "so.addVariable(\"xmlvariable\", \"$string_for_flash_xml\");";
	echo "so.addVariable(\"rlovariable\", \"$string_for_flash_media\");";
	echo "so.addVariable(\"originalpathvariable\", \"$string_for_flash_xwd\");";
	echo "so.addVariable(\"template_id\", \"" . $row_edit['template_id'] . "\");";
	echo "so.addVariable(\"template_height\", \"" . $temp[1] . "\");";
	echo "so.addVariable(\"template_width\", \"" . $temp[0] . "\");";
	echo "so.addVariable(\"read_and_write\", \"" . $read_status . "\");";
	echo "so.addVariable(\"savepath\", \"" . $xerte_toolkits_site->flash_save_path . "\");";
	echo "so.addVariable(\"upload_path\", \"" . $xerte_toolkits_site->flash_upload_path . "\");";
	echo "so.addVariable(\"preview_path\", \"" . $xerte_toolkits_site->flash_preview_check_path . "\");";
	echo "so.addVariable(\"flv_skin\", \"" . $xerte_toolkits_site->flash_flv_skin . "\");";
	echo "so.addVariable(\"site_url\", \"" . $xerte_toolkits_site->site_url . "\");";
	echo "so.addVariable(\"apache\", \"" . $xerte_toolkits_site->apache . "\");";
	echo "so.write(\"flashcontent\");";
	echo "</script></body></html>";

}


?>