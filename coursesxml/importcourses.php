<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dattwood
 * Date: 01/06/11
 * Time: 09:20
 * Imports courses into the course search database
 */
#

include('corero_conection_2.php');


echo '<h1>Import courses into course search table</h1>';

// select the ng database
$select = mssql_select_db('ngreports');


$query = "SELECT * FROM dbo.PRPHProvisionHeader prph
INNER JOIN dbo.PRPIProvisionInstance prpi ON prph.PRPH_Code = prpi.PRPI_Code
where prph_include_web_prospectus ='1' and prpi_actual_next_instance_isn is not null";

$result = mssql_query($query);

$num_rows = mssql_num_rows($result);

echo 'number of hits: ' . $num_rows;


// Set up the basic course information

while ($row = mssql_fetch_assoc($result)) {
    // check is if the course is web enabled
    if ($row['PRPH_Include_Web_Prospectus'] == 1) {
        echo $row['PRPH_Title'];
        // check if the course already exists in the table
        $select = mssql_select_db('coursesearch');
        $query2 = "SELECT * FROM courses WHERE course_code='" . $row['PRPH_Code'] . "'";
        $result2 = mssql_query($query2);
        $num_rows2 = mssql_num_rows($result2);
        echo ' num rows 2: ' . $num_rows2;
        // if the course doesn't exist in the course search table add it else update it
        if ($num_rows2 == 0) {
            $queryAdd = "INSERT INTO courses
                (course_code, course_name, startdate, lengthYears, lengthWeeks, lengthDays,  includeWeb, faculty, department, prpiAvailable, instance, hoursPerWeek  )
                VALUES('" . trim($row['PRPH_Code']) . "','" . $row['PRPH_Title'] . "','" . $row['PRPI_Start_Date_A27'] . "','" . $row['PRPI_Length_Years'] . "','" . $row['PRPI_Length_Weeks'] . "','" . $row['PRPI_Length_Days'] . "','" . $row['PRPH_Include_Web_Prospectus'] . "','" . $row['PRPH_ML3'] . "','" . $row['PRPH_ML2'] . "','" . $row['PRPI_Available_App'] . "','" . $row['PRPI_Instance'] . "','" . $row['PRPI_Hours_per_Week'] . "')";
            mssql_query($queryAdd);
        } else {
            // update an existing course
            $queryUpdate = "UPDATE courses SET
                            course_code ='" . trim($row['PRPH_Code']) . "',  course_name ='" . $row['PRPH_Title'] . "', startdate ='" . $row['PRPI_Start_Date_A27'] . "', lengthYears ='" . $row['PRPI_Length_Years'] . "', lengthWeeks ='" . $row['PRPI_Length_Weeks'] . "', lengthDays ='" . $row['PRPI_Length_Days'] . "',  includeWeb ='" . $row['PRPH_Include_Web_Prospectus'] . "', faculty ='" . $row['PRPH_ML3'] . "', department ='" . $row['PRPH_ML2'] . "', prpiAvailable ='" . $row['PRPI_Available_App'] . "', instance ='" . $row['PRPI_Instance'] . "', hoursPerWeek ='" . $row['PRPI_Hours_per_Week']
                           . "' WHERE course_code='" . $row['PRPH_Code'] . "'";
            mssql_query($queryUpdate);
        }


    }
}

// Import the Fees
// Select the fees
$select = mssql_select_db('coursesearch');
$courses = "SELECT * FROM courses";
echo $courses;
$resultCourses = mssql_query($courses);

while ($rowCourse = mssql_fetch_assoc($resultCourses)) {
    // define blank variables
    $mat = 0;
    $tui = 0;
    $exm = 0;

    $select = mssql_select_db('ngreports');
    $queryFees = "SELECT * FROM prfsfees WHERE PRFS_Code = '" . $rowCourse['course_code'] . "' AND PRFS_Instance='" . $rowCourse['instance'] . "'";
    echo $queryFees . ' ';
    $resultFee = mssql_query($queryFees);
    while ($rowFee = mssql_fetch_assoc($resultFee)) {
        echo $rowFee['PRFS_Fee_Amount'];
        if (trim($rowFee['PRFS_Fee_Type']) == 'EXM') {
            $select = mssql_select_db('coursesearch');
            $queryUpdateEXM = "UPDATE courses SET exam_fee ='" . $rowFee['PRFS_Fee_Amount'] . "' WHERE course_code='" . $rowFee['PRFS_Code'] . "' AND instance='" . $rowFee['PRFS_Instance'] . "'";
            $exm = $rowFee['PRFS_Fee_Amount'];
            echo $queryUpdateEXM;
            mssql_query($queryUpdateEXM);
        } elseif (trim($rowFee['PRFS_Fee_Type']) == 'TUI') {
            $select = mssql_select_db('coursesearch');
            $queryUpdateTUI = "UPDATE courses SET tuition_fee ='" . $rowFee['PRFS_Fee_Amount'] . "' WHERE course_code='" . $rowFee['PRFS_Code'] . "' AND instance='" . $rowFee['PRFS_Instance'] . "'";
            $tui = $rowFee['PRFS_Fee_Amount'];
            mssql_query($queryUpdateTUI);
            echo $queryUpdateTUI;
        } elseif (trim($rowFee['PRFS_Fee_Type']) == 'MAT') {
            $select = mssql_select_db('coursesearch');
            $queryUpdateMAT = "UPDATE courses SET material_fee ='" . $rowFee['PRFS_Fee_Amount'] . "' WHERE course_code='" . $rowFee['PRFS_Code'] . "' AND instance='" . $rowFee['PRFS_Instance'] . "'";
            $mat = $rowFee['PRFS_Fee_Amount'];
            mssql_query($queryUpdateMAT);
            echo $queryUpdateMAT;
        }
        // Work out the total fees
        $feeTotal = $mat + $tui + $exm;
        $queryUpdateFee = "UPDATE courses SET total ='" . $feeTotal . "' WHERE course_code='" . $rowFee['PRFS_Code'] . "' AND instance='" . $rowFee['PRFS_Instance'] . "'";
         mssql_query($queryUpdateFee);

    }
}


$select = mssql_select_db('coursesearch');
$courses = "SELECT * FROM courses";
//echo $courses;
$resultCourses = mssql_query($courses);
while ($rowCourse = mssql_fetch_assoc($resultCourses)) {
    $select = mssql_select_db('ngreports');
    //

    // Select the description fields for the course and uses t-sql to pivot the results into one row
    $queryDesc = "SELECT prtx_code,
max(case when (prtx_category = '100' and prtx_paragraph = '1') then prtx_text else '' end) how_is_it_learned1,
max(case when (prtx_category = '100' and prtx_paragraph = '2') then prtx_text else '' end) how_is_it_learned2,
max(case when (prtx_category = '100' and prtx_paragraph = '3') then prtx_text else '' end) how_is_it_learned3,
max(case when (prtx_category = '100' and prtx_paragraph = '4') then prtx_text else '' end) how_is_it_learned4,
max(case when (prtx_category = '110' and prtx_paragraph = '1') then prtx_text else '' end) assesment1,
max(case when (prtx_category = '110' and prtx_paragraph = '2') then prtx_text else '' end) assesment2,
max(case when (prtx_category = '110' and prtx_paragraph = '3') then prtx_text else '' end) assesment3,
max(case when (prtx_category = '110' and prtx_paragraph = '4') then prtx_text else '' end) assesment4,
max(case when (prtx_category = '50' and prtx_paragraph = '1') then prtx_text else '' end) description1,
max(case when (prtx_category = '50' and prtx_paragraph = '2') then prtx_text else '' end) description2,
max(case when (prtx_category = '50' and prtx_paragraph = '3') then prtx_text else '' end) description3,
max(case when (prtx_category = '50' and prtx_paragraph = '4') then prtx_text else '' end) description4,
max(case when (prtx_category = '55' and prtx_paragraph = '1') then prtx_text else '' end) suitability1,
max(case when (prtx_category = '55' and prtx_paragraph = '2') then prtx_text else '' end) suitability2,
max(case when (prtx_category = '55' and prtx_paragraph = '3') then prtx_text else '' end) suitability3,
max(case when (prtx_category = '55' and prtx_paragraph = '4') then prtx_text else '' end) suitability4,
max(case when (prtx_category = '60' and prtx_paragraph = '1') then prtx_text else '' end) pre_requisities1,
max(case when (prtx_category = '60' and prtx_paragraph = '2') then prtx_text else '' end) pre_requisities2,
max(case when (prtx_category = '60' and prtx_paragraph = '3') then prtx_text else '' end) pre_requisities3,
max(case when (prtx_category = '60' and prtx_paragraph = '4') then prtx_text else '' end) pre_requisities4,
max(case when (prtx_category = '900' and prtx_paragraph = '1') then prtx_text else '' end) required_reading1,
max(case when (prtx_category = '900' and prtx_paragraph = '2') then prtx_text else '' end) required_reading2,
max(case when (prtx_category = '900' and prtx_paragraph = '3') then prtx_text else '' end) required_reading3,
max(case when (prtx_category = '900' and prtx_paragraph = '4') then prtx_text else '' end) required_reading4,
max(case when (prtx_category = '905' and prtx_paragraph = '1') then prtx_text else '' end) how_to_apply1,
max(case when (prtx_category = '905' and prtx_paragraph = '2') then prtx_text else '' end) how_to_apply2,
max(case when (prtx_category = '905' and prtx_paragraph = '3') then prtx_text else '' end) how_to_apply3,
max(case when (prtx_category = '905' and prtx_paragraph = '4') then prtx_text else '' end) how_to_apply4
from prtxprospectustext
WHERE prtx_code= '" . $rowCourse['course_code'] . "'
group by prtx_code";
    //    echo $queryDesc;
    //    echo '</br>';
    $resultDesc = mssql_query($queryDesc);

    while ($rowDesc = mssql_fetch_assoc($resultDesc)) {
        $select = mssql_select_db('coursesearch');

        // Need to use str_replace in order to escape single quotes when inserting into the database
        $queryUpdateDesc = "UPDATE courses SET

    How_will_I_learn_1='" . str_replace("'", "''", $rowDesc['how_is_it_learned1']) . "',
    How_will_I_learn_2='" . str_replace("'", "''", $rowDesc['how_is_it_learned2']) . "',
    How_will_I_learn_3='" . str_replace("'", "''", $rowDesc['how_is_it_learned3']) . "',
    How_will_I_learn_4='" . str_replace("'", "''", $rowDesc['how_is_it_learned4']) . "',
    What_will_I_learn_1='" . str_replace("'", "''", $rowDesc['description1']) . "',
    What_will_I_learn_2='" . str_replace("'", "''", $rowDesc['description2']) . "',
    What_will_I_learn_3='" . str_replace("'", "''", $rowDesc['description3']) . "',
    What_will_I_learn_4='" . str_replace("'", "''", $rowDesc['description4']) . "',
    How_will_I_be_assessed_1='" . str_replace("'", "''", $rowDesc['assesment1']) . "',
    How_will_I_be_assessed_2='" . str_replace("'", "''", $rowDesc['assesment2']) . "',
    How_will_I_be_assessed_3='" . str_replace("'", "''", $rowDesc['assesment3']) . "',
    How_will_I_be_assessed_4='" . str_replace("'", "''", $rowDesc['assesment4']) . "',
    Are_there_entry_requirements_1='" . str_replace("'", "''", $rowDesc['pre_requisities1']) . "',
    Are_there_entry_requirements_2='" . str_replace("'", "''", $rowDesc['pre_requisities2']) . "',
    Are_there_entry_requirements_3='" . str_replace("'", "''", $rowDesc['pre_requisities3']) . "',
    Are_there_entry_requirements_4='" . str_replace("'", "''", $rowDesc['pre_requisities4']) . "'

    WHERE course_code='" . $rowDesc['prtx_code'] . "'";
        echo $queryUpdateDesc;
        mssql_query($queryUpdateDesc);
    }
}




// Tidy up the table - remove all courses that have PRPH_Include_Web_Prospoectus unticked

$select = mssql_select_db('coursesearch');
$courses = "SELECT * FROM courses";
$result = mssql_query($courses);

while ($row = mssql_fetch_assoc($result)) {
    $select = mssql_select_db('ngreports');
    $queryRemove = "SELECT * FROM dbo.PRPHProvisionHeader WHERE PRPH_code='" . $row['course_code'] . "' AND PRPH_Include_Web_Prospectus='1'";
    echo '<br/>' . $queryRemove . '<br/>';
    $resultRemove = mssql_query($queryRemove);
    $num_rows_remove = mssql_num_rows($resultRemove);

    // If a row is returned the record is web enabled
if ($num_rows_remove != 1) {
  echo 'remove the course';
    $select = mssql_select_db('coursesearch');
    $courses = "SELECT * FROM courses";
    $queryDelete = "DELETE FROM courses WHERE course_code='" . $row['course_code'] . "'";
    mssql_query($queryDelete);
}


}








 
