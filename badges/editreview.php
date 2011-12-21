<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
echo '<link rel="stylesheet" type="text/css" href="reviews.css" />';
echo '<link rel="stylesheet" type="text/css" href="' . $siteurl  . '/theme/midkent_newstyle/style.css" />';

echo '<div id="one_badge">';


$review_number = $_POST['review_number'];
$review_attendence = $_POST['review_attendence'];
$review_score = $_POST['review_score'];
$var1 = $_POST['var1'];
$var2 = $_POST['var2'];
$var3 = $_POST['var3'];
$var4 = $_POST['var4'];


//echo 'var1 is ' .  $var1;
//echo 'var2 is ' . $var2;
//echo 'var4 is ' .  $var3;
//echo 'var4 is ' . $var4;

$id = $_POST['id'];
$recordid = $_POST['recordid'];





echo '<h2>Edit review number ' . $review_number . ' for ' . $var1 . '</h2>';

echo 'Review number is: ' . $review_number . '<br/>';
echo 'Recoreded attendence is: ' . $review_attendence . '<br/>';
echo 'Recorded review score is: ' . $review_score . '<br/>';

echo '<br/>';
echo '<b>Please be careful editing attendence numbers as these were orginally set by data from MIS</b>';
echo '<br/>';

echo '<table><tr><td>';
echo '<form name=edit_review action="process_edit_review.php" method=POST>';
echo 'Enter new attendence value: <input type="text" value="' . $review_attendence . '" name="new_review_attendence"></br>';

echo '<select name="newscore">';
echo '<option>Enter new Quality of work value:</option>';
    echo '<option>120</option>';
    echo '<option>110</option>';
    echo '<option>100</option>';
    echo '<option>90</option>';
    echo '<option>80</option>';
    echo '</select></br>';

    echo '<input type="hidden" name="var1" value="' . $var1 . '">';
    echo '<input type="hidden" name="var2" value="' . $var2 . '">';
    echo '<input type="hidden" name="var3" value="' . $var3 . '">';
    echo '<input type="hidden" name="var4" value="' . $var4 . '">';
    echo '<input type="hidden" name="id" value="' . $id . '">';
    echo '<input type="hidden" name="review_number" value="' . $review_number . '">';
echo '<input type="submit" value="Submit Change" >';
echo '</form>';

echo '</td><td>';



echo '</td></tr></table>';


echo '<div id="deletebutton">';
echo '<form name=edit_review action="delete_review.php" method=POST>';
    echo '<input type="hidden" name="var1" value="' . $var1 . '">';
    echo '<input type="hidden" name="var2" value="' . $var2 . '">';
    echo '<input type="hidden" name="var3" value="' . $var3 . '">';
    echo '<input type="hidden" name="var4" value="' . $var4 . '">';
echo '<input type="hidden" name="id" value="' . $id . '">';
 echo '<input type="hidden" name="review_number" value="' . $review_number . '">';
 //echo 'student id is ' . $id;
  //echo 'id is: ' . $review_number;

echo '<INPUT TYPE="image" SRC="images/delete-icon.png"  BORDER="0" ALT="Submit Form" TITLE="Delete this review!">';

echo '</form>';
echo '</div>';



//echo $var1;

echo '<div id="backbutton"><a href=' . $siteurl  . '/badges/flightplan_details.php?var1='. urlencode($var1) . '&var2=' . $var2 . '&var3=' . $var3 . '&var4=' . $var4 . '"><img style="border: 0px;" title="Back to Reviews Screen" src="' . $siteurl  . '/badges/images/bt-left-icon.png"></a></div>';

echo '</div>';
echo '</div>';

?>
