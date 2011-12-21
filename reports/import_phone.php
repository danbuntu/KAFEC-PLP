<?php

/*
 * Imports student mobile numbers from NG and imprts them into Moodle
 */

// connect to NG
include('corero_conection.php');


//import active students from correro
$query = "SELECT  row_number() over (order by STEN.STEN_Student_ID) as RowNumber, RTRIM(STEN.STEN_Student_ID) as STEN_Student_ID, STUDstudent.STUD_Forename_1, STUDstudent.STUD_Mobile_Telephone, STUDstudent.STUD_Surname

FROM                      STEM INNER JOIN
                      STEN ON STEM.STEM_Student_ID = STEN.STEN_Student_ID AND STEM.STEM_Provision_Code = STEN.STEN_Provision_Code AND
                      STEM.STEM_Provision_Instance = STEN.STEN_Provision_Instance INNER JOIN
                      STUDstudent ON STEM.STEM_Student_ID = STUDstudent.STUD_Student_ID INNER JOIN
                      STEN AS STEN_1 ON STEN.STEN_Student_ID = STEN_1.STEN_Student_ID
                      INNER JOIN ACYR ON STEN.STEN_Year = ACYR.ACYR_College_Year
WHERE (STEN.STEN_Completion_Stat = '1') AND (STEN.STEN_Outcome = '9') AND (ACYR.ACYR_ENR_PY_CY_NY = 'CY') AND (STEN.STEN_Status_code  <> 'DELETED') AND (STEN.STEN_Funding_Stream = '21' OR STEN.STEN_Funding_Stream = '22')
GROUP BY STEN.STEN_Student_ID, STUDstudent.STUD_Forename_1, STUDstudent.STUD_Forename_2, STUDstudent.STUD_Surname, STUDstudent.STUD_Mobile_Telephone, STEN.STEN_Year
ORDER BY  RTRIM(STEN.STEN_Student_ID)
";
//echo $query;
$result = mssql_query($query);

$num_rows = mssql_num_rows($result);

echo ' ' . $num_rows . '</br>';

//build the array of students
while ($row = mssql_fetch_assoc($result)) {
    $students[] = $row;
}

print_r($students);


// swap to the moodle database
include('database_connection.php');

// loop throughthe students array and update moodle records as need
foreach ($students as $row) {
    // Ignore the guardian accounts
    $query = "UPDATE mdl_user set phone2='" . trim($row['STUD_Mobile_Telephone']) . "' WHERE idnumber='" . trim($row['STEN_Student_ID']) . "' AND username not like 'G%'";
    echo $query . '</br>';
    mysql_query($query);
}

//trash the array
unset($students);

//send out an email to say the job has run
$to = 'dan.attwood@midkent.ac.uk';
$subject = 'phone import run';
$message = 'the import has run';
$headers = 'From: Moodle server <moodle1@midkent.ac.uk>' . "\r\n";
mail($to, $subject, $message, $headers);
?>
