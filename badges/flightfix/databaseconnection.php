<?php
echo '<h2>Import new users</h2>';
session_start();

$db = mysql_connect("127.0.0.1", "root", "88boom") or die("Could not connect.");

if(!$db)

	die("no db");

if(!mysql_select_db("users",$db))

 	die("No database selected.");

?>
