<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include('database_conection.php');

$name = $_POST['name'];
$from = $_POST['from_date'];
$to = $_POST['to_date'];
$details = $_POST['details'];
$religion = $_POST['religion'];
$id = $_POST['id'];


echo $id . '<br/>';
echo $name . '<br/>';
echo $from . '<br/>';
echo $to . '<br/>';
echo $details . '<br/>';
echo $religion . '<br/>';

$todate = date('Y-m-d',strtotime($to));
$fromdate = date('Y-m-d',strtotime($from));

echo $todate;
echo $fromdate;


$query = "UPDATE events SET name='" . $name . "',details='" . $details . "',startdate='" . $fromdate . "',enddate='" . $todate . "',religion='" . $religion . "' WHERE id='" . $id . "'";

echo $query;

mysql_query($query);

mysql_close($link);
echo '<meta http-equiv="Refresh" content="0;URL=./index.php" />';
?>
