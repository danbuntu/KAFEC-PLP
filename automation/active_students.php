<?php

/*
 * Check against all students in moodle active withint he last year
 */
//Load the main config file
include('../config.php');

include('../connections/corero_conection_testing.php');

include('academic_year_function.php');
include('automation_lib.php');

// Get the active students from NG

$query = "SELECT row_number() over (order by STEN.STEN_Student_ID) as RowNumber, RTRIM(STEN.STEN_Student_ID) as STEN_Student_ID, STUDstudent.STUD_Forename_1, STUDstudent.STUD_Surname

FROM                      STEM INNER JOIN
                      STEN ON STEM.STEM_Student_ID = STEN.STEN_Student_ID AND STEM.STEM_Provision_Code = STEN.STEN_Provision_Code AND
                      STEM.STEM_Provision_Instance = STEN.STEN_Provision_Instance INNER JOIN
                      STUDstudent ON STEM.STEM_Student_ID = STUDstudent.STUD_Student_ID INNER JOIN
                      STEN AS STEN_1 ON STEN.STEN_Student_ID = STEN_1.STEN_Student_ID
                      INNER JOIN ACYR ON STEN.STEN_Year = ACYR.ACYR_College_Year
WHERE (STEN.STEN_Completion_Stat = '1') AND (STEN.STEN_Outcome = '9') AND (ACYR.ACYR_ENR_PY_CY_NY = 'CY') AND (STEN.STEN_Status_code  <> 'DELETED') AND (STEN.STEN_Funding_Stream = '21' OR STEN.STEN_Funding_Stream = '22')
GROUP BY STEN.STEN_Student_ID, STUDstudent.STUD_Forename_1, STUDstudent.STUD_Forename_2, STUDstudent.STUD_Surname, STEN.STEN_Year
ORDER BY  RTRIM(STEN.STEN_Student_ID)
";





$result = mssql_query($query);

$rows = mssql_num_rows($result);

// Feed sql results into an array
while ($row = mssql_fetch_array($result)) {
    $students[] = $row;
}

print_r($students);

// Import the students into the automation table

foreach ($students as $row) {
    $id = $row["1"];

    $query = "SELECT * FROM automation WHERE learner_ref='" . $row['1'] . "'";
    $result = mysql_query($query);
    $num_rows = mysql_num_rows($result);
    echo $row['3'];
    // See of the record already exists
    if ($num_rows >= '1') {
        echo 'Record already exits</br>';
    } else {
        echo "Record doesn't exist creating it";
        // Insert the records and use real_escape to work round apostrophies
        $query = "INSERT INTO automation (learner_ref, firstname, lastname) VALUES ('" . $row["1"] . "','" . mysql_real_escape_string($row["2"]) . "','" . mysql_real_escape_string($row["3"]) . "')";
        echo $query . '</br>';
        mysql_query($query);
    }

    // Import the attandence figures
    $attendquery = "SELECT  VREGT.REGT_Year AS Year, VREGT.REGT_Student_ID AS StuID, RTRIM(VREGT.REGT_Provision_Code) AS Course,
                      PRPIProvisionInstance.PRPI_Title AS [Course Title], SUM(CASE WHEN AttPresAbs = 'N' THEN 1 ELSE 0 END) AS Present,
                      SUM(CASE WHEN AttPresAbs IN ('Y', 'N') THEN 1 ELSE 0 END) AS Possible, SUM(CASE WHEN AttPresAbs = 'N' THEN 1 ELSE 0 END) AS Absent
FROM         REGHrghdr INNER JOIN
                      VREGT ON REGHrghdr.REGH_ISN = VREGT.REGT_REGH_ISN INNER JOIN
                      REGDropin ON VREGT.REGT_REGH_ISN = REGDropin.REGD_REGH_ISN AND VREGT.REGT_Student_ID = REGDropin.REGD_Student_ID INNER JOIN
                      PRPIProvisionInstance ON VREGT.REGT_Provision_Code = PRPIProvisionInstance.PRPI_Code AND
                      VREGT.REGT_Provision_Instance = PRPIProvisionInstance.PRPI_Instance LEFT OUTER JOIN
                          (SELECT     RGAT_Attendance_Code AS AttCode, RGAT_Present AS AttPresAbs
                            FROM          RGATAttendance
                            WHERE      (RGAT_Present = 'Y') OR
                                                   (RGAT_Present = 'N')) AS AttMark ON REGDropin.REGD_Attendance_Mark = AttMark.AttCode
WHERE     (REGHrghdr.REGH_Register_Type = 'T')
GROUP BY PRPIProvisionInstance.PRPI_Title, VREGT.REGT_Year, VREGT.REGT_Student_ID, RTRIM(VREGT.REGT_Provision_Code)
HAVING      (VREGT.REGT_Year ='" . $academicyear . "') AND (VREGT.REGT_Student_ID ='" . $id . "' )
ORDER BY RTRIM(VREGT.REGT_Provision_Code)";

    //  Echo $attendquery;
    $attendresult = mssql_query($attendquery);

    while ($row = mssql_fetch_assoc($attendresult)) {

        $present = $present + $row['Present'];
        $possible = $possible + $row['Possible'];
        $absent = $absent + $row['Absent'];
        $totalAttendance = attendance($present, $possible, $absent);
        //reset varibles
        $present = "0";
        $possible = "0";
        $absent = "0";
    }


    echo 'total att is: ' . $totalAttendance . '<br/>';

    $queryupdate = "UPDATE automation SET att='" . round($totalAttendance) . "' WHERE learner_ref='" . $id . "'";
    echo $queryupdate . '</br>';
    mysql_query($queryupdate);


    $newBoundary = setBoundary($totalAttendance);
    echo 'boundary is ' . $newBoundary;

    // Get the current boundary

    $query = "SELECT boundary FROM automation  WHERE learner_ref='" . $id . "'";

    $result = mysql_query($query);


    // Compare the current boundary to the new one
    while ($row = mysql_fetch_assoc($result)) {
        $currentBoundary = $row["boundary"];
        echo 'current boundary is ' . $currentBoundary;
        if ($currentBoundary != $newBoundary) {
            // Insert the new boundary record if needed
            $queryUpdateBoundary = "UPDATE automation SET boundary='" . $newBoundary . "' WHERE learner_ref='" . $id . "'";
            echo $queryUpdateBoundary;
            mysql_query($queryUpdateBoundary);
        } else {

            // Compare the boundaries
            compareBoundaries($currentBoundary, $newBoundary, $id);
        }
    }
}


// set the attendance boundary
// if the boundary has changed send out txt/ email
// mark last mesage sent date
// Change rag and set reason for status change
?>
