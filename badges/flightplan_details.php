<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include('databaseconnection.php');

echo '<link rel="stylesheet" type="text/css" href="reviews.css" />';
echo '<link rel="stylesheet" type="text/css" href="' . $siteurl . '/theme/midkent_newstyle/style.css" />';


//echo $totalattendance;


//set the maximum number of reviews
$totalreviews = '4';

//Get the varibles from the url
$var1 = $_GET["var1"];
$var2 = $_GET["var2"];
$var3 = $_GET["var3"];
$var4 = $_GET["var4"];
$var5 = $_GET["var5"];
$year = date("Y");
$date = date("d M Y");
$totalattendance = $_GET['var6'];


echo '<div id="one_badge">';

// work out the current academic year
//@FIXME break out into a class?
function greaterDate($start_date, $end_date) {
    $start = strtotime($start_date);
    $end = strtotime($end_date);
    if ($start - $end < 0)
        return 1;
    else
        return 0;
}

$tdate = date("d M");
//echo 'date is: ' . $tdate;

$date1 = '31 Jul';
if (greaterDate($date1, $tdate)) {
    //  echo 'yes date';
    $academicyear = date("Y");
    // echo $academicyear;
} else {
    //  echo 'no date';
    $academicyear = date("Y") - 1;
    // echo $academicyear;
}
//end of working out the academic year
//

//include('get_attendance.php');


echo '<h1>Reviews for ' . $var1 . '</h1><br/>';
//echo User name: ' . $var2 . ' ' . $var3 . ' ' . $var4 . '</h1>';
//Get students current attendance
//@FIXME this is hardcoded and needs to pass the student id through
//@FIXME add query to pull in QCA number
include('check_student.php');


//Process the add new review form
if (isset($_POST['submit_review'])) {
    $attendance = $_POST['attendance'];
    $score = $_POST['score'];
    $calculated = ($score + $attendance) / 2;
    $count = $_POST['count'];

    if ($count > $totalreviews) {
        echo '<h1>error to many reviews for this year</h1>';
    } elseif ($count <= $totalreviews) {

        echo 'att is ' . $attendance;
        echo ' score is ' . $score;
        echo ' count is ' . $count;
        echo ' student number is ' . $student_number;
        $var1 = $_GET['var1'];
        $var2 = $_GET['var2'];
        $var3 = $_GET['var3'];
        $var4 = $_GET['var4'];
        $var5 = $_GET['var5'];

        $query_add_review = "INSERT INTO reviews (student_id, review_attendence, review_score, review_number, calculated, date) VALUES (" . $student_number . ',' . $attendance . ',' . $score . ',' . $count . ',' . $calculated . ', CURDATE() )';
        echo '<br/>' . $query_add_review . '<br/>';
        mysql_query($query_add_review);
//refresh the page to clear the form
        // clearform();
    }
}


$query_reviews = "SELECT * FROM reviews AS re left JOIN students AS stu on re.student_id=stu.id where stu.id='" . $student_number . "' AND year(date)=" . $academicyear . " ORDER BY re.review_number";

//echo $query_reviews;
$reviews = mysql_query($query_reviews);
$num_or_rows = mysql_num_rows($reviews);

$count = 1;

//layout table
echo '<table style="width: 100%;"><tr><td style="width: 80%;">';


//echo 'Todays date is: ' . $date . ' The current academic year is ' . $academicyear . '<br/>';
echo '<h2>Current reviews for this academic year ' . $year . '</h2>';

if ($num_or_rows > 0) {

//echo the reviews already in the system
    while ($row = mysql_fetch_assoc($reviews)) {

        echo '<div id="one_badge">';
        echo '<table style="text-align: left;"><tr><th>';
        echo 'Review number:</th><td>' . $row['review_number'] . '</td></tr>';
        echo '<tr><th>Attendance score:</th><td>' . $row['review_attendence'] . '</td></tr>';
        echo '<tr><th>Review score:</th><td>' . $row['review_score'] . '</td></tr>';
        echo '<tr><th>Average score:</th><td>' . $row['calculated'] . '</td></tr>';
        echo '<form name="edit_review" action="editreview.php" method="POST">';
        echo '<input type="hidden" name="review_number" value="' . $row['review_number'] . '">';
        echo '<input type="hidden" name="review_attendence" value="' . $row['review_attendence'] . '">';
        echo '<input type="hidden" name="review_score" value="' . $row['review_score'] . '">';
        echo '<input type="hidden" name="recordid" value="' . $row['id'] . '">';
        echo '<input type="hidden" name="var1" value="' . $var1 . '">';
        echo '<input type="hidden" name="var2" value="' . $var2 . '">';
        echo '<input type="hidden" name="var3" value="' . $var3 . '">';
        echo '<input type="hidden" name="var4" value="' . $var4 . '">';
        echo '<input type="hidden" name="id" value="' . $student_number . '">';
        echo '<tr><td colspan="2" align="center"><input type="submit" value="Edit review"></td></tr></table>';
        echo '</form>';
        echo '</div>';
        $count = $count + 1;
    }
} else {
    echo 'No reviews yet';
}

echo '<div id="dump">';

echo '<div id="add_review">';

//form to add a new review

echo '<form name=add_review" action="flightplan_details.php?var1=' . $var1 . '&var2=' . $var2 . '&var3=' . $var3 . '&var4=' . $var4 . '" method="POST">';

//work attendence as a percentage based on brackets
if ($totalattendance >= 95) {
    $attendancescore = 100;
} elseif ($totalattendance >= 90 and $totalattendance <= 94) {
    $attendancescore = 100;
} elseif ($totalattendance >= 85 and $totalattendance <= 89) {
    $attendancescore = 90;
} elseif ($totalattendance >= 80 and $totalattendance <= 84) {
    $attendancescore = 80;
} elseif ($totalattendance <= 80) {
    $attendancescore = 70;
}
echo '<br/>';
if ($count >= 5) {
    echo '<h2>There are already 4 reviews logged for this year</h2>';
    echo '</form>';
} else {
    echo '<h2>Add review number ' . $count . '</h2>';
    echo '<table><tr><td>';
    echo ' Current attendance is: ' . round($totalattendance) . ' and so the score for the flightplan is: ' . $attendancescore;
    echo '<input type="hidden" name="attendance" value="' . $attendancescore . '">';
    echo '</td></tr><tr><td>';
// dropdown list for the quality of work score
    echo 'Enter Quality of Work/ Grades achieved score: ';
    echo '<select name="score">';
    echo '<option>Please select an option</option>';
    echo '<option>120</option>';
    echo '<option>110</option>';
    echo '<option>100</option>';
    echo '<option>90</option>';
    echo '<option>80</option>';
    echo '</select>';

    echo '<input type="hidden" name="count" value="' . $count . '" /><br/>';

    echo '</td></tr><tr><td style="text-align: right">';
    echo '<input name="submit_review" type="submit" value="Submit Score">';
    echo '</form>';
    echo '</td></tr></table>';
    echo '</div>';
    include('plp_summary.php');
}

echo '</td><td style="background-color: #aeaeae; width: 20%; text-align: center;">';
echo '<h2>Set Manual MTG Scores</h2>';
//form to add QCA
//form to add MTG
echo '<h2>MTG Score</h2>';

echo 'Current MTG score is: <br/>' . $mtg;

echo '<form name="add_mtg" action="process_mtg.php?var1=' . $var1 . '&var2=' . $var2 . '&var3=' . $var3 . '&var4=' . $var4 . '" method="POST">';
echo 'Enter new MTG: <input type="text" size=5 name="mtg_score" value="" /><br/>';

echo '<input name="submit_mtg" type="submit" value="Submit MTG">';

echo '</form>';

echo '</td></tr></table>';
echo '<div id="backbutton"><a href="' . $siteurl . '/blocks/ilp/view.php?courseid=' . $var4 . '&id=' . $var3 . '"><img style="border: 0px;" title="Back to Students PLP" src="' . $siteurl . '/badges/images/bt-left-icon.png"></a></div>';

echo '</div>';


mysql_close($link4);
?>
