<?php

include('databaseconnection.php');

$var1 = $_GET['var1'];
$var2 = $_GET['var2'];
$var3 = $_GET['var3'];
$var4 = $_GET['var4'];



//process the add score QCA form
if (isset($_POST['submit_qca'])) {
    $qca_score = $_POST['qca_score'];
    $query_qca = "UPDATE students set qca='" . $qca_score . "' WHERE students_name='" . $var2 . "'";
    //echo $query_mtg;


    echo $var1;
    echo $var2;
    echo $var3;
    echo $var4;

    mysql_query($query_qca);
    echo '<h1>clear form</h1>';
    echo 'process';
    echo $query_qca;
    echo '<meta http-equiv="refresh" content="1;url=' . $siteurl . '/badges/flightplan_details.php?var1=' . $var1 . '&var2=' . $var2 . '&var3=' . $var3 . '&var4=' . $var4 . '">';
}
?>
