<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include ('databaseconnection.php');
$academicyear = 2010;
$count = 0;
$medalsdb = 'medals';


// find all the records form the ad table
$queryad = "SELECT * FROM users.adusers";
echo 'Query AD is: ' . $queryad . '<br/>';

// run the adusers query
$resultad = mysql_query($queryad);
$num_rows = mysql_num_rows($resultad);
echo 'Num of rows is: ' . $num_rows . '<br>';


//loop though the adusers
while ($rowad = mysql_fetch_assoc($resultad)) {
    $loginname = $rowad['logon'];
    $studentid = $rowad['ref'];

    //check the length of $studentid anf padd with a 0 if 7 digits
    if (strlen($studentid) == 7) {
        $studentid = '0' . $studentid;
    }


    //  echo 'student name ' . $loginname . '<br/>';
    //  echo 'student id ' . $studentid . '<br/>';



    mysql_select_db($medalsdb, $db);

    $querymedals = "SELECT * FROM students where students_name='" . $loginname . "'";
    echo '<br/>Query medals is: ' . $querymedals . '<br/>';
    $resultmedals = mysql_query($querymedals);
    $num_row_medals = mysql_num_rows($resultmedals);
    echo 'Row medals is: ' . $num_row_medals . '<br/>';
    //check for a hit
    if ($num_row_medals > 0) {
        echo 'hit in medals<br/>';
        $count = $count + 1;

        echo 'student name ' . $loginname . '<br/>';
        echo 'student id ' . $studentid . '<br/>';

        //fetch array from medals table
        $resultmedals = mysql_fetch_array($resultmedals);
        // assign the student name to check
        $studentmedalid = $resultmedals['0'];
        $loginmedal = $resultmedals['1'];

        echo 'student medal id from ' . $medalsdb . ' is ' . $studentmedalid . '<br/>';
        echo 'Login name from ' . $medalsdb . ' is ' . $loginmedal . '<br/>';
        echo 'count is: ' . $count . '<br/>';

        //Get the attendance form MIS
        include('get_attendance.php');

        // echo the total attendance
       $totalattendance =  round($totalattendance);
        echo 'attandance for this student is :' . $totalattendance . '<br/>';


        //adjust the attendance figure to fit the boundaries

        if ($totalattendance >= 95) {
    $totalattendance = 100;
} elseif ($totalattendance >= 90 and $totalattendance <= 94) {
   $totalattendance = 100;
} elseif ($totalattendance >= 85 and $totalattendance <= 89) {
    $totalattendance = 90;
} elseif ($totalattendance >= 80 and $totalattendance <= 84) {
   $totalattendance = 80;
} elseif ($totalattendance <= 80) {
   $totalattendance = 70;
}

echo 'Boundary adjusted attendence is: ' . $totalattendance . '<br/>';




        //select the reviews the student has against them
    $queryreviews = "SELECT * FROM reviews WHERE student_id='" . $studentmedalid . "' AND review_number='2'";
    echo 'query to select review based in table id: ' .  $queryreviews . '<br/>';
    
    //run the query
    $resultreviews = mysql_query($queryreviews);

        // get the review ids and update
    while ($row = mysql_fetch_assoc($resultreviews)) {
     $reviewid = $row['id'];
     $reviewnumber = $row['review_number'];
     $reviewscore = $row['review_score'];

     //work out the calculated score
     $calculated = ($reviewscore + $totalattendance) / 2;

     $queryupdate = "UPDATE reviews SET review_attendence=" . $totalattendance .  ", calculated=" . $calculated . " WHERE id=" . $reviewid . "";
        echo 'update query is: ' . $queryupdate . '<br/>';
        mysql_query($queryupdate);
    }


        
        
    }
}


mysql_close($db);
mysql_close($db2);
?>
