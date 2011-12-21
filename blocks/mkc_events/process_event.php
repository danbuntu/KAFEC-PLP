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


echo $name . '<br/>';
echo $from . '<br/>';
echo $to . '<br/>';
echo $details . '<br/>';
echo $religion . '<br/>';


$todate = date('Y-m-d',strtotime($to));
$fromdate = date('Y-m-d',strtotime($from));
echo $todate;
echo $fromdate;

$query = "INSERT INTO events (name,details,startdate,enddate,religion) VALUES ('" . $name . "','" . $details . "','" . $fromdate . "','" . $todate . "','" . $religion . "')";
echo $query;
mysql_query($query);
mysql_close($link);
echo '<meta http-equiv="Refresh" content="0;URL=./index.php" />';
?>
