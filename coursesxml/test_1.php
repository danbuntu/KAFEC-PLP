<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include('corero_conection.php');

echo 'test';

$student = getCourse('math');
print_r($student);

function getCourse($name)
{
    $query = "SELECT *
FROM dbo.PRPHProvisionHeader
INNER JOIN dbo.PRPIProvisionInstance ON dbo.PRPHProvisionHeader.PRPH_Code = dbo.PRPIProvisionInstance.PRPI_Code
INNER JOIN dbo.PRILILR ON dbo.PRPIProvisionInstance.PRPI_Instance = dbo.PRILILR.PRIL_Instance AND dbo.PRPIProvisionInstance.PRPI_Code = dbo.PRILILR.PRIL_Code
INNER JOIN dbo.LEARNING_AIM ON dbo.PRILILR.PRIL_Aim_A09 = dbo.LEARNING_AIM.LEARNING_AIM_REF
INNER JOIN dbo.ALL_ANNUAL_VALUES ON dbo.LEARNING_AIM.LEARNING_AIM_REF = dbo.ALL_ANNUAL_VALUES.LEARNING_AIM_REF
WHERE dbo.PRPHProvisionHeader.PRPH_Title like '%" . $name . "%'";
    //echo $query;

    $result = mssql_query($query);
    $course = array();
    while ($row = mssql_fetch_array($result)) {
        //$student = $row['STUD_Forename_1'];
        //             $student[] = $row['STUD_Forename_1'];
        //             $student[] = $row['STUD_Surname'];
        $course[] = array('coursename' => $row['PRPH_Title'], 'coursecode' => $row['PRPH_Code']);
    }
    return $course;
}


?>