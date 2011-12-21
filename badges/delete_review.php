<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include('databaseconnection.php');



echo '<link rel="stylesheet" type="text/css" href="reviews.css" />';
echo '<link rel="stylesheet" type="text/css" href="' . $siteurl . '/theme/midkent_newstyle/style.css" />';

echo '<div id="one_badge">';


$newatt = $_POST['new_review_attendence'];
$newscore = $_POST['newscore'];

$var1 = $_POST['var1'];
$var2 = $_POST['var2'];
$var3 = $_POST['var3'];
$var4 = $_POST['var4'];

echo '<h1>Deleting record hang on mo</h1>';

echo 'var1 is ' . $var1;
echo 'var2 is ' . $var2;
echo 'var4 is ' . $var3;
echo 'var4 is ' . $var4;

$review_number = $_POST['review_number'];
$id = $_POST['id'];



//Get the review id number to get round the no safe update issue
$query = "SELECT * FROM reviews WHERE student_id=" . $id . ' AND review_number=' . $review_number;
echo $query;
//echo '<br/>';
$result = mysql_query($query);
//print_r($result);
//get the row id and assign to a string
while ($row = mysql_fetch_array($result)) {
    $reviewid = $row['0'];
}


echo 'table id is ' . $reviewid;

echo $id . ' ' . $review_number;

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

$query = "DELETE FROM reviews WHERE id=" . $reviewid;

//   $query = "update reviews set review_score='" . $newscore . "', review_attendence='" . $newatt .  "' WHERE student_id='" . $id . "' and review_number='" . $review_number . "'";
//      $query2 = "update reviews set calculated='" . $calculated .   "' WHERE student_id='" . $id . "' and review_number='" . $review_number . "'";

echo '<br/>' . $query;
mysql_query($query);
//     mysql_query($query2);
  echo '<meta http-equiv="refresh" content="1;url=' . $siteurl  . '/badges/flightplan_details.php?var1=' . urlencode($var1) . '&var2=' . $var2 . '&var3=' . $var3 . '&var4=' . $var4 . '">';

mysql_close($link4);
echo '</div>';
?>
