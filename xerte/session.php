<?PHP

/**
* 
* Session page, allows pages other than index.php to maintain the session
*
* @author Patrick Lockley
* @version 1.0
* @copyright Copyright (c) 2008,2009 University of Nottingham
* @package
*/

require("integration_library.php");

require_once("config.php");

session_start();


/*if($_SESSION['toolkits_sessionid']!=session_id()){

	echo session_id() . " " . $_SESSION['toolkits_sessionid'] . "<Br><pre>";

	print_r($_SESSION);

	echo "</pre>";

	echo "<br> Le session est mort";

}*/

?>