<?php
echo '<h2>Import new users</h2>';
session_start();

$db2 = mysql_connect("127.0.0.1", "root", "88Boom!") or die("Could not connect.");
if(!$db2)

	die("no db");

if(!mysql_select_db("medals",$db2))

 	die("No database selected.");

?>
