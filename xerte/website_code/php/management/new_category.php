<?PHP require("../../../config.php");
require("../../../session.php");

require("../database_library.php");
require("../user_library.php");

if(is_user_admin()){

	$mysql_id = database_connect("New_securty.php database connect success","New_security.php database connect failed");

	$query = "INSERT INTO " . $xerte_toolkits_site->database_table_prefix . "syndicationcategories (category_name) values  ('" . $_POST['newcategory'] . "')";

	if(mysql_query($query)){

		// change these

		//receive_message($_SESSION['toolkits_logon_username'], "USER", "SUCCESS", "Folder creation succeeded for " . $_SESSION['toolkits_logon_username'], "Folder creation succeeded for " . $_SESSION['toolkits_logon_username']);

	}else{

		// change these

		//receive_message($_SESSION['toolkits_logon_username'], "USER", "CRITICAL", "Folder creation failed for " . $_SESSION['toolkits_logon_username'], "Folder creation failed for " . $_SESSION['toolkits_logon_username']);


	}

	$query="select * from " . $xerte_toolkits_site->database_table_prefix . "syndicationcategories order by category_name ASC";

	echo "<p>Add a new category</p>";

	echo "<p>The new category is <form><textarea cols=\"100\" rows=\"2\" id=\"newcategory\">Enter name here</textarea></form></p>";
       echo "<p><form action=\"javascript:new_category();\"><input type=\"submit\" label=\"Add\" /></form></p>"; 

	echo "<p>Manage existing categories</p>";

	$query_response = mysql_query($query);

	while($row = mysql_fetch_array($query_response)){

		echo "<p>" . $row['category_name'] . " - <a href=\"javascript:remove_category('" . $row['category_id'] .  "')\">Remove </a></p>";

	}

			
}else{

	echo "the feature is for administrators only";

}

?>

