<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dattwood
 * Date: 21/06/11
 * Time: 08:52
 * Gets the employer details
 */
 

$queryEmploy = "SELECT * FROM apprentice_employers JOIN apprentice_courses ON apprentice_employers.id=apprentice_courses.employerid WHERE apprentice_courses.course_code='$courseId'";
$resultEmploy = mysql_query($queryEmploy);

if (!$resultEmploy) {
    $message = 'Invalid:' . mysql_error();
    die($message);
}

// get the course Id
$query = "SELECT * FROM mdl_course WHERE id='$courseId'";
$resultCourse = mysql_query($query);

if (!$resultCourse) {
    $message = 'Invalid:' . mysql_error();
    die($message);
}

?>