<?php
/**
 * Created by JetBrains PhpStorm.
 * User: DATTWOOD
 * Date: 20/10/11
 * Time: 10:16
 * To change this template use File | Settings | File Templates.
 */
 
include('../config.php');
$query = "SELECT * FROM mdl_user WHERE  lastname !='Guardian '  AND username not like '11%'  AND idnumber !='' AND (description  != 'Acoount created as part of Guardian insert' or description is null) order by idnumber  ";
$count ='';
$result = mysql_query($query);

while ($row = mysql_fetch_assoc($result)) {
    $studentusername = $row['username'];
    $studentId = $row['idnumber'];
    $studentRecordID = $row['id'];

    echo '<br/>';
echo 'student ' . $row['firstname'] . '  ' . $row['lastname'] . ' - ' . $row['idnumber'] . ' - ' . $row['username']  . ' Student Record ID is ' . $studentRecordID . '<br/>';

    // get the user and guardian records
    $query2 = "SELECT * FROM mdl_user WHERE idnumber='" . $studentId . "' AND username='" . $studentId . "' AND description='Acoount created as part of Guardian insert'";
    echo $query2 . '<br/>';
    $result2 = mysql_query($query2);

    $num_rows = mysql_num_rows($result2);
    echo 'Num rows: ' . $num_rows;

    if ($num_rows == 1){
        echo 'a hit ';
    while ($row2 = mysql_fetch_array($result2)) {
      $guardianID =  $row2['id'];
        $guardianUsername = $row2['username'];

        // fix the flightplan
        $queryFlight = "SELECT * FROM flightplan WHERE student_id='" .  $guardianUsername . "'";
        $resultFlight = mysql_query($queryFlight);
        $num_rows_flight = mysql_num_rows($resultFlight);
        if ($num_rows_flight == 1) {
            while ($row4 = mysql_fetch_assoc($resultFlight)) {
                $flightId = $row4['id'];
            }
        $queryFlightUpdate = "UPDATE flightplan SET student_id='" . $studentusername . "' WHERE id='" . $flightId . "'";
            echo 'update flightplan<br/>' . $queryFlightUpdate . '<br/>';
//          !!!!!!
           mysql_query($queryFlightUpdate);

        }


 // merge the plp records

$queryUpdatePosts = "update mdl_ilpconcern_posts set setforuserid='" . $studentRecordID . "' where setforuserid='" . $guardianID . "'";
    echo 'Query posts' . $queryUpdatePosts . '<br/>';
//!!!!!!
        mysql_query($queryUpdatePosts);

$queryUpdateTargets = "update mdl_ilptarget_posts set setforuserid='" . $studentRecordID . "' where setforuserid='" . $guardianID . "'";
    echo 'query targets ' . $queryUpdateTargets . '<br/>';
//     !!!!
        mysql_query($queryUpdateTargets);


// remove gaurdian user form course and replace with real user


$queryRoles = "select * from mdl_role_assignments where userid='" . $guardianID . "' AND roleid='5'";
        $resultRoles = mysql_query($queryRoles);
        echo $queryRoles . '<br/>';

        while ($row10 = mysql_fetch_assoc($resultRoles)) {
            $wrongRoleId = $row10['id'];
            $contextid = $row10['contextid'];
            $modifierid = $row10['modifierid'];
            $timestart = $row10['timestart'];
            $timeend = $row10['timeend'];
            $enrol = $row10['enrol'];
            $hidden = $row10['hidden'];
            $timemodified = $row10['timemodified'];
            $sortorder = $row10['sortorder'];

            // insert the correct user
            if ($guardianID != $studentRecordID) {

            $queryInRole = "INSERT INTO mdl_role_assignments (roleid, contextid, userid, hidden, timestart, timeend, timemodified, modifierid, enrol, sortorder) VALUES ('5','" . $contextid . "','" . $studentRecordID . "','" . $hidden . "','" . $timestart . "','" . $timeend . "','" . $timemodified  . "','" . $modifierid . "','" . $enrol . "','" . $sortorder . "')";
            echo 'Insert the right student into  the role ' . $queryInRole . '<br/>';
// !!!!
                mysql_query($queryInRole);

            // Delete the guradin student from the roles table
            $queryRoleDelete = "DELETE FROM mdl_role_assignments WHERE id='" . $wrongRoleId . "'";
            echo 'Delete the wrong role ' . $queryRoleDelete . '<br/>';
//!!!!!
                mysql_query($queryRoleDelete);
            }
            
        }



        // fix the subject reports

        $querySub = "SELECT * FROM mdl_ilp_student_info_per_teacher WHERE student_userid='". $guardianID . "'";
        $resultSub = mysql_query($querySub);

        while ($row11 = mysql_fetch_assoc($resultSub)) {
            $subId = $row11['id'];

           $updateSub = "UPDATE mdl_ilp_student_info_per_teacher SET student_userid='" . $studentRecordID . "' WHERE id='" . $subId . "'";
            echo 'update sub :' . $updateSub . '<br/>';

            mysql_query($updateSub);
        }





        // delete the guaridn account
        $queryDelete = "UPDATE mdl_user SET deleted='1' WHERE id='$guardianID' ";
//     !!!!
        mysql_query($queryDelete);

        echo '<br/>Delete' . $queryDelete . '<br/>';
    echo $queryUpdate . '<br/>';
$count++;
    }
    }






}

echo '<br/>Fixed accounts =' . $count;




?>