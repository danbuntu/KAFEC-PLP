<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include('database_conection.php');

$id = $_GET['id'];

echo '<h2>Delete ' . $id . '</h2>';


$query = "DELETE FROM events WHERE id='"  . $id . "'";

mysql_query($query);

mysql_close($link);
echo '<meta http-equiv="Refresh" content="0;URL=./index.php" />';




?>
