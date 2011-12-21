<?php

/*
 * Functions to run the automation
 */


// Sends a message to the moodle user
function sendMoodleMessage($moodleID, $message, $fromID, $format, $messageType) {
    $query = "INSERT INTO mdl_message (useridfrom, useridto, message, format, timecreated, messagetype) VALUES ('" . $fromID . "','" . $moodleID . "','" . $message . "','" . $format . "',CURDATE(),'" . $messageType . "')";
    echo ' ' . $query . ' ';
    mysql_query($query);
    echo 'Moodle Message sent';
}

// Get the base site url
function getSiteUrl() {
    $url="http://".$_SERVER['HTTP_HOST'];
echo $url;
return $url;
}

// Returns the url of the students PLP
function getStudentPlpPath($moodleID, $url) {
    $plpUrl = $url . '/blocks/ilp/view.php?courseid=1&id=' . $moodleID;
    echo ' built url is: ' . $plpUrl;
    return $plpUrl;
}


// Workout the total attendance
function attendance($present, $possible, $absent) {
    $present = $possible - $absent;
    $totalAttendance = $present / $possible;
// Times by 100 to move deicmal place
    $totalAttendance = $totalAttendance * 100;
    return $totalAttendance;
}

// Set the newBoundary Variable
function setBoundary($totalAttendance) {
    // Set the boundary
    echo $totalAttendance;
    if (round($totalAttendance) == '0') {
        $newBoundary = 'X';
    } elseif (round($totalAttendance) >= '95') {
        $newBoundary = '1';
    } elseif (round($totalAttendance) >= '90' && round($totalAttendance) <= '94') {
        $newBoundary = '2';
    } elseif (round($totalAttendance) <= '89' && round($totalAttendance) >= '85') {
        $newBoundary = '3';
    } elseif (round($totalAttendance) >= '80' && round($totalAttendance) <= '84') {
        $newBoundary = '4';
    } else {
        $newBoundary = '5';
    }
    return $newBoundary;
}

function getMoodleID($studentID) {
    $query = "SELECT id FROM mdl_user WHERE idnumber='" . $studentID . "'";
    echo 'moodle query: ' . $query . '<br/>';
    $result = mysql_query($query);

    while ($row = mysql_fetch_assoc($result)) {
        $moodleID = $row["id"];
    }

    echo 'moodle id in function is: ' . $moodleID;
    return $moodleID;
}

// Function to compare the two boundaries and act accordingly
function compareBoundaries($currentBoundary, $newBoundary, $studentID) {
    include('messages.php');
    
    if ($currentBoundary == $newBoundary) {
        echo 'all is good and student id is ' . $studentID;
        //students att is dropping
        // Send out an email
        // $studentEmail = getStudentEmail($id);
        // echo $studentEmail;
// Student is slipping
    } elseif (($currentBoundary < $newBoundary) && (!empty($currentBoundary))) {
        echo '<br/>studentid test' . $studentID . '<br/>';

        // Get students name
        list($firstname, $lastname) = getStudentsName($studentID);

               $moodleID = getMoodleID($studentID);
        echo 'Moodleid is: ' . $moodleID;

        // Get the site URl for use later
$url = getSiteUrl();
echo ' url from out of function ' . $url;

echo 'PLP url  <br/>';

// Get the PLP url to send in the message to the HOF
$plpUrl = getStudentPlpPath($moodleID, $url);

// Send a meesage to the student in moodle regarding their attendance
$moodleMessage = "Automation system message";
$fromID = '2';
$format = '1';
$messageType = 'direct';
sendMoodleMessage($moodleID, $moodleMessage, $fromID, $format, $messageType);

// Use 'list' to assign varibles from the returned student array
        $studentEmail = "lisa.simpson@midkent.ac.uk";
        $studentEmail = getStudentEmail($studentID);

        echo $studentEmail;
        //   echo $guardianEmail;

        $subject = 'Falling attendance for ' . $firstname . ' ' . $lastname;



       //   sendEmail($studentEmail, $subject, $studentMessage);
        // Send the guardian email

//        $guardianEmail = getGuardianEmail($studentID);
//        if (!empty($guardianEmail)) {
//            $guardianEmail = 'dan.attwood@midkent.ac.uk';
//           //  sendEmail($guardianEmail, $subject, $guardianMessage);
//        }

         $guardianEmail = "dan.attwood@midkent.ac.uk";
        sendEmail($guardianEmail, $subject, $guardianMessage);

        // HOF emails
        $hofMessage = "this is the hof message. You can download a word document to use as a template to request a meeting with your student here: http://moodledev.midkent.ac.uk/automation/attachments/Automated_Letters.docx
           To view this students PLP please click here " . $plpUrl;
        // Get the HOF email address(s)
        $hofEmail = emailHOF($studentID);
        //step through the hof mail array

//        print_r($hofEmail);
//        foreach ($hofEmail as $row) {
//            $hofEmail = $row;
//                 //  sendEmail($hofEmail, $subject, $hofMessage);
//        }
        $hofEmail = "dan.attwood@midkent.ac.uk";
        sendEmail($hofEmail, $subject, $hofMessage);

        echo ' emails sent to ' . $to;

        $to = $studentEmail . ',' . $guardianEmail . ',' . $hofEmail;

        // $message = "Your attendance is falling";
        //  echo ' send out an email ';
        //    sendEmail($to, $subject, $studentMessage);

    
        $concernMessage = "Automaticaly set due to drop in attendence. RAG set to boundary level " . $newBoundary;

        setRAG($moodleID, $studentID, $newBoundary, $concernMessage);
        $logMsg = "Email sent to " . $studentID . ' ' . $firstname . ' ' . $lastname . " new boundary set to " . $newBoundary . " from " . $currentBoundary . " - emails sent to " . $to;
        writeToLog($logMsg);
        // The student is climbing again
    } elseif ($currentBoundary > $newBoundary) {

    }
    // Set the rag and reason for status change
}

function getStudentsName($studentID) {
    $query = "SELECT * FROM automation WHERE learner_ref ='" . $studentID . "'";
    echo $query;
    $result = mysql_query($query);

    while ($row = mysql_fetch_assoc($result)) {
        $name[] = $row['firstname'];
        $name[] = $row['lastname'];
        echo 'Student name ' . $firstname . ' ' . $lastname;
    }
    return $name;
}

// Get the students fculty and HOF
function getFaculty($studentID) {
// Get the students faculty
    $studentID = '1111';
    $query = "Select distinct stud_student_id, stud_surname, stud_forename_1, stud_title, pr.PRPH_ML3
FROM studstudent as stu join sten ON stu.STUD_Student_ID=sten.STEN_Student_ID
JOIN PRPHProvisionHeader pr ON sten.sten_Provision_Code=pr.PRPH_Code
INNER JOIN ACYR ON STEN.STEN_Year = ACYR.ACYR_College_Year
WHERE (STEN.STEN_Completion_Stat = '1') AND (STEN.STEN_Outcome = '9') AND (ACYR.ACYR_ENR_PY_CY_NY = 'CY') AND stud_student_id = '" . $studentID . "'";
    echo '</br>' . $query . '</br>';
    $result = mssql_query($query);

    while ($row = mssql_fetch_array($result)) {
        $studentFaculty[] = $row;
    }
    return $studentFaculty;
}

// Send out email to the head of faculty
function emailHOF($studentID) {
    // Get the student faculty
    $studentFaculty = getFaculty($studentID);

    echo 'student faculty is: ' . print_r($studentFaculty) . '</br>';

    //echo 'test ' . $studentFaculty[0][4];
    // Create an array to store the hof emails in
    $hofEmail = array();
    //step through the array of faculties and pick out the hofs emails and put in hofEmail array
    foreach ($studentFaculty as $row) {
        echo 'foreach ' . $row[4];
        $fac = $row[4];
        echo 'fac 4 is: ' . $fac;
        include('hofs.php');


//print_r($hofs);
        $hofEmail[] = array_search("ACE", $hofs);
        echo 'hof is: ' . $hofEmail;
    }
    echo 'HOF emails are: ';
    print_r($hofEmail);
    return $hofEmail;
}

// Gets the student email form Moodle based on the users learner ref
function getStudentEmail($id) {
    $query = "SELECT id, email FROM mdl_user WHERE idnumber='" . $id . "' AND username not like 'G%'";
    echo $query;
    $studentmail = mysql_query($query);
    while ($row = mysql_fetch_assoc($studentmail)) {
        $student = $row["email"];
    }
    return $student;
}

// Check is the student has a parental aggreement signed and if so grab the parent email address
function getGuardianEmail($studentID) {
    $queryparent = "SELECT dbo.STYRstudentYR.STYR_Student_ID
FROM dbo.STYRstudentYR INNER JOIN dbo.GNUCustom ON (dbo.STYRstudentYR.STYR_Year = dbo.GNUCustom.GNUC_Year) AND (dbo.STYRstudentYR.STYR_ISN = dbo.GNUCustom.GNUC_Entity_ISN)
WHERE (((dbo.GNUCustom.GNUC_Flag_1)=1) AND ((dbo.STYRstudentYR.STYR_Year)='2010') AND ((dbo.STYRstudentYR.STYR_Student_ID)='" . $studentID . "'))
GROUP BY dbo.STYRstudentYR.STYR_Student_ID";
    echo $queryparent;
    $result = mssql_query($queryparent);
    $num_rows = mssql_num_rows($result);

    if ($num_rows > 0) {
        //aggreement signed - get the parents email address
        $queryGuardianEmail = "SELECT SCONContacts.SCON_email as Guardian_Email
FROM STYRstudentYR INNER JOIN GNUCustom ON STYRstudentYR.STYR_Year = GNUCustom.GNUC_Year AND STYRstudentYR.STYR_ISN = GNUCustom.GNUC_Entity_ISN
INNER JOIN ACYR ON GNUCustom.GNUC_Year = ACYR.ACYR_College_Year INNER JOIN SCONContacts ON SCONContacts.SCON_Student_ID = STYRstudentYR.STYR_Student_ID
WHERE ACYR.ACYR_ENR_PY_CY_NY = 'CY' AND GNUCustom.GNUC_Flag_1 = 1 AND SCONContacts.SCON_email <> ''
AND STYRstudentYR.STYR_Student_ID = '" . $studentID . "'";
        echo $queryGuardianEmail;
        $result = mssql_query($queryGuardianEmail);
        while ($row = mssql_fetch_assoc($result)) {
            $guardianEmail = $row["Guardian_Email"];
        }
        return $guardianEmail;
    }
}

// Sends out an email
function sendEmail($to, $subject, $message) {
    $headers = 'From: attendance@midkent.ac.uk';
    mail($to, $subject, $message, $headers);
}

// Sets the RAG and enters a reason for status change
// @FIXME this confused the (moodle)ID and studentID. $id needs to change to moodleID and be rethreaded as needed


function setRAG($moodleID, $studentID, $newBoundary, $concernMessage) {
//insert a record in the reason for status change - set by user 2 (the admin user) and with status 2 reason for status change
    $currentDate = currentDate();
    //$concernset = "Automaticaly set due to drop in attendence. RAG set to boundary level " . $newBoundary;

    $insert = "INSERT INTO mdl_ilpconcern_posts (setforuserid, setbyuserid, course, courserelated, targetcourse, timecreated, timemodified, deadline, concernset, status, format) ";
    $values = "VALUES ('" . $moodleID . "','2','1','0','0','" . $currentDate . "','" . $currentDate . "','" . $currentDate . "','" . $concernMessage . "','2','1')";

    $query = $insert . $values;
    echo $query;
    mysql_query($query);
    echo ' Concern posted';

//set the actual rag
    //check for an exsiting record
    $queryrag = "SELECT * FROM mdl_ilpconcern_status WHERE userid='" . $moodleID . "'";
    $resultrag = mysql_query($queryrag);
    $num_rows = mysql_num_rows($resultrag);

    // Get the current rag and check if its worse than the new one
    while ($row = mysql_fetch_array($resultrag)) {
        $currentRAG = $row['status'];
    }
    echo '<br/>studentid going into ragcolour is ' . $studentID . '<br/>';
    // Get the new RAG
    $newRAG = ragColour($studentID);

    //if a record exists update it else insert a new record
    if ($num_rows >= 1) {

        // Update the rag if needed
        if ($currentRAG < $newRAG) {
            $query = "UPDATE mdl_ilpconcern_status SET modified='" . $currentDate . "', modifiedbyuser='2', status='" . $newRAG . "' WHERE userid='" . $moodleID . "'";
            echo '<br/>Updataing the rag' . $query . '<br/>';
            mysql_query($query);
        }
    } else {
        // We create the record is needed as by default students have no rag record (which is green)
        $query = "INSERT INTO mdl_ilpconcern_status (userid, created, modified, modifiedbyuser, status VALUES ('" . $studentID . "','" . $currentDate . "','" . $currentDate . "','2','" . $newRAG . "'";
        echo '<br/>' . $query . '<br/>';
        mysql_query($query);
    }
}

function currentDate() {
    $currentDate = date('d-m-Y h:i:s');
    echo 'currentdate is ' . $currentDate;
    $currentDate = strtotime($currentDate);
    return $currentDate;
}

function writeToLog($logMsg) {
    echo 'Writing to log';
    $fh = fopen("log.txt", "a+");
    $logMsg = "[" . date('d-m-Y H:i:s') . "] " . $logMsg . "\r"; //backslash r backslach n
    echo $logMsg;
    fputs($fh, $logMsg);
    fclose($fh);
    return true;
}

// Check to see if the student has withdrawn from the course as per MIS
function checkStudentWithdrawn($studentID) {
    echo 'function test';
    $query = "SELECT row_number() over (order by STEN.STEN_Student_ID) as RowNumber, RTRIM(STEN.STEN_Student_ID) as STEN_Student_ID, STUDstudent.STUD_Forename_1, STUDstudent.STUD_Surname

FROM                      STEM INNER JOIN
                      STEN ON STEM.STEM_Student_ID = STEN.STEN_Student_ID AND STEM.STEM_Provision_Code = STEN.STEN_Provision_Code AND
                      STEM.STEM_Provision_Instance = STEN.STEN_Provision_Instance INNER JOIN
                      STUDstudent ON STEM.STEM_Student_ID = STUDstudent.STUD_Student_ID INNER JOIN
                      STEN AS STEN_1 ON STEN.STEN_Student_ID = STEN_1.STEN_Student_ID
                      INNER JOIN ACYR ON STEN.STEN_Year = ACYR.ACYR_College_Year
WHERE (STEN.STEN_Completion_Stat = '1') AND (STEN.STEN_Outcome = '9') AND (ACYR.ACYR_ENR_PY_CY_NY = 'CY') AND
(STEN.STEN_Status_code  <> 'DELETED') AND (STEN.STEN_Funding_Stream = '21' OR STEN.STEN_Funding_Stream = '22')
AND STEN.STEN_Student_ID = '" . $studentID . "'";

    $result = mssql_query($query);

    $num_rows = mssql_num_rows($result);


// Student is inactive
    if ($num_rows < 1) {
        echo '<h1><font color="red">The Student has withdrawn deleting from the automation database</font></h1>';
        // If withdrawn remove them from the automation table to tidy it up
        $query = "DELETE FROM automation WHERE learner_ref='" . $studentID . "'";
        echo $query;
        mysql_query($query);
    }
}

// Set the RAC colour
function ragColour($studentID) {

    $query = "SELECT * FROM automation WHERE learner_ref='" . $studentID . "'";
    echo '<br/>Rag colour query' . $query . '<br/>';

    $result = mysql_query($query);
    while ($row = mysql_fetch_assoc($result)) {
        $totalAttendance = $row["att"];
    }
    echo 'Total att from ragColour is: ' . $totalAttendance;
    if (round($totalAttendance) == '0') {
        $newRAG = 'X';
    } elseif (round($totalAttendance) >= '95') {
        $newRAG = '0';
    } elseif (round($totalAttendance) >= '90' && round($totalAttendance) <= '94') {
        $newRAG = '0';
    } elseif (round($totalAttendance) <= '89' && round($totalAttendance) >= '85') {
        $newRAG = '1';
    } elseif (round($totalAttendance) >= '80' && round($totalAttendance) <= '84') {
        $newRAG = '2';
    } else {
        $newRAG = '2';
    }
    return $newRAG;

//        if ($rag == 'Green') {
//            $ragStatus = 0;
//        } elseif ($rag == 'Amber') {
//            $ragStatus = 1;
//        } elseif ($rag == 'Red') {
//            $ragStatus = 2;
//        }
}

function sendTxt() {
    
}

?>
