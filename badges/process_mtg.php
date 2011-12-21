<?php

include('databaseconnection.php');

$var1 = $_GET['var1'];
$var2 = $_GET['var2'];
$var3 = $_GET['var3'];
$var4 = $_GET['var4'];

//process the add MTG form
if (isset($_POST['submit_mtg'])) {
    $mtg_score = $_POST['mtg_score'];
    $query_mtg = "UPDATE students set mtg='" . $mtg_score . "' WHERE students_name='" . $var2 . "'";


    //echo $query_mtg;
    mysql_query($query_mtg);

   echo '<h1>clear form</h1>';
    echo 'process mtg';
    echo $query_mtg;
    echo '<meta http-equiv="refresh" content="1;url=' . $siteurl  . '/badges/flightplan_details.php?var1=' . $var1 . '&var2=' . $var2 . '&var3=' . $var3 . '&var4=' . $var4 . '">';
}


?>
