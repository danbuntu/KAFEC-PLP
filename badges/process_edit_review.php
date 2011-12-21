<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include('databaseconnection.php');

echo '<link rel="stylesheet" type="text/css" href="reviews.css" />';
echo '<link rel="stylesheet" type="text/css" href="' . $siteurl  . '/theme/midkent_newstyle/style.css" />';

echo '<div id="one_badge">';



    $newatt = $_POST['new_review_attendence'];
    $newscore = $_POST['newscore'];

    $var1 = $_POST['var1'];
$var2 = $_POST['var2'];
$var3 = $_POST['var3'];
$var4 = $_POST['var4'];
$id = $_POST['id'];
$review_number = $_POST['review_number'];


    echo $newatt . ' ' . $newscore;
    echo '</br>';
    echo $var1 . ' ' . $var2 . ' ' . $var3 . ' ' . $var4;
echo '<br/>ID is: ' . $id;
echo '<br/>Review number is: ' . $review_number;

//reworkout the calculated score

echo '<br/>newscore is: ' . $newscore . '<br/>';
echo 'new attendence: ' . $newatt . '<br/>';
$calculated = ($newscore + $newatt) / 2;
echo 'Calculated is: ' . $calculated;



//@FIXME compress to one query
   $query = "update reviews set review_score='" . $newscore . "', review_attendence='" . $newatt .  "' WHERE student_id='" . $id . "' and review_number='" . $review_number . "'";
      $query2 = "update reviews set calculated='" . $calculated .   "' WHERE student_id='" . $id . "' and review_number='" . $review_number . "'";

   echo '<br/>' . $query;
    mysql_query($query);
     mysql_query($query2);

        echo '<meta http-equiv="refresh" content="1;url=' . $siteurl  . '/badges/flightplan_details.php?var1=' . $var1 . '&var2=' . $var2 . '&var3=' . $var3 . '&var4=' . $var4 . '">';

        mysql_close($link4);
echo '</div>';
?>
