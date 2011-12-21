<?php
/* 
 * SOAP server to stream the course information out of NG
 */


require_once('./nusoap/lib/nusoap.php');
include('corero_conection_2.php');


$namespace = 'http://moodledev.midkent.ac.uk/server-course.php';

$server = new soap_server();

$server->configureWSDL('Course');

$server->wsdl->schemaTargetNamespace = 'http://moodledev.midkent.ac.uk/coursexml/';


// Create a complex type

$server->wsdl->addComplexType(
    'CourseArray',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(
         array('ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'tns:course[]')
    ),
    'tns:Course'
);

$server->wsdl->addComplexType(
    'CourseArray2',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(
         array('ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'tns:course[]')
    ),
    'tns:Course2'
);


// The student array that passes back into the studentarray above via tns:course
$server->wsdl->addComplexType(
    'Course',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'coursename' => array('name'=>'coursename','type'=>'xsd:string'),
        'coursecode' => array('name'=>'coursecode','type'=>'xsd:string')
    )
);

// The student array that passes back into the coursearray2 above via tns:course2
$server->wsdl->addComplexType(
    'Course2',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'coursename2' => array('name'=>'coursename','type'=>'xsd:string'),
        'coursecode2' => array('name'=>'coursecode','type'=>'xsd:string'),
        'startdate' => array('name'=>'startdate','type'=>'xsd:string'),
        'lengthYears' => array('name'=>'lengthYears','type'=>'xsd:string'),
    'lengthWeeks' => array('name'=>'lengthWeeks','type'=>'xsd:string'),
    'lengthDays' => array('name'=>'lengthDays','type'=>'xsd:string'),
        'includeWeb' => array('name'=>'includeWeb','type'=>'xsd:string'),
        'faculty'    => array('name'=>'faculty','type'=>'xsd:string'),
        'department'  => array('name'=>'department','type'=>'xsd:string'),
        'PRPIAvaialable'  => array('name'=>'PRPIAvaialable','type'=>'xsd:string'),
        'instance'   => array('name'=>'instance','type'=>'xsd:string'),
        'hoursPerWeek' => array('name'=>'hoursPerWeek','type'=>'xsd:string'),
        'campus' => array('name'=>'campus','type'=>'xsd:string'),
        'tuition_fee' => array('name'=>'tuition_fee','type'=>'xsd:string'),
        'exam_fee' => array('name'=>'exam_fee','type'=>'xsd:string'),
        'material_fee' => array('name'=>'material_fee','type'=>'xsd:string'),
        'total' => array('name'=>'total','type'=>'xsd:string'),
        'year' => array('name'=>'year','type'=>'xsd:string'),
        'lvl' => array('name'=>'lvl','type'=>'xsd:string'),
        'What_will_I_learn_1' => array('name'=>'What_will_I_learn_1','type'=>'xsd:string'),
        'What_will_I_learn_2' => array('name'=>'What_will_I_learn_2','type'=>'xsd:string'),
        'What_will_I_learn_3' => array('name'=>'What_will_I_learn_3','type'=>'xsd:string'),
        'What_will_I_learn_4' => array('name'=>'What_will_I_learn_4','type'=>'xsd:string'),
        'How_will_I_learn_1' => array('name'=>'How_will_I_learn_1','type'=>'xsd:string'),
        'How_will_I_learn_2' => array('name'=>'How_will_I_learn_2','type'=>'xsd:string'),
        'How_will_I_learn_3' => array('name'=>'How_will_I_learn_3','type'=>'xsd:string'),
        'How_will_I_learn_4' => array('name'=>'How_will_I_learn_4','type'=>'xsd:string'),
        'How_will_be_assessed_1' => array('name'=>'How_will_be_assessed_1','type'=>'xsd:string'),
        'How_will_be_assessed_2' => array('name'=>'How_will_be_assessed_2','type'=>'xsd:string'),
        'How_will_be_assessed_3' => array('name'=>'How_will_be_assessed_3','type'=>'xsd:string'),
        'How_will_be_assessed_4' => array('name'=>'How_will_be_assessed_4','type'=>'xsd:string'),
        'Are_there_entry_requirements_1' => array('name'=>'Are_there_entry_requirements_1','type'=>'xsd:string'),
        'Are_there_entry_requirements_2' => array('name'=>'Are_there_entry_requirements_2','type'=>'xsd:string'),
        'Are_there_entry_requirements_3' => array('name'=>'Are_there_entry_requirements_3','type'=>'xsd:string'),
        'Are_there_entry_requirements_4' => array('name'=>'Are_there_entry_requirements_4','type'=>'xsd:string'),
        'What_may_it_lead_to_1' => array('name'=>'What_may_it_lead_to_1','type'=>'xsd:string'),
        'What_may_it_lead_to_2' => array('name'=>'What_may_it_lead_to_2','type'=>'xsd:string'),
        'What_may_it_lead_to_3' => array('name'=>'What_may_it_lead_to_3','type'=>'xsd:string'),
        'What_may_it_lead_to_4' => array('name'=>'What_may_it_lead_to_4','type'=>'xsd:string'),
        'Are_there_other_costs_1' => array('name'=>'Are_there_other_costs_1','type'=>'xsd:string'),
        'Are_there_other_costs_2' => array('name'=>'Are_there_other_costs_2','type'=>'xsd:string'),
        'Are_there_other_costs_3' => array('name'=>'Are_there_other_costs_3','type'=>'xsd:string'),
        'Are_there_other_costs_4' => array('name'=>'Are_there_other_costs_4','type'=>'xsd:string')

    )
);


$server->register(
    'getCourse',
    array('name' => 'xsd:string'),
    array('return' => 'tns:CourseArray'),
    $namespace
);


$server->register(
    'getCourse2',
    array('name' => 'xsd:string'),
    array('return' => 'tns:CourseArray2'),
    $namespace
);

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


// Search by course code
function getCourse2($name)
{

$query = "SELECT * FROM courses WHERE course_code like '%" . $name . "%'";

         $result = mssql_query($query);
    $course = array();
    while ($row = mssql_fetch_array($result)) {


        $course[] = array('coursename2' => $row['course_name'],
                          'coursecode2' => $row['course_code'],
                          'startdate' => $row['startdate'],
                          'lengthYears' => $row['lengthYears'],
                          'lengthWeeks' => $row['lengthWeeks'],
                          'lengthDays' => $row['lengthDays'],
                          'includeWeb' => $row['includeWeb'],
                          'faculty'   => $row['faculty'],
                          'department' => $row['department'],
                          'PRPIAvaialable' => $row['prpiAvailable'],
                          'instance' => $row['instance'],
                          'hoursPerWeek' => $row['hoursPerWeek'],
                          'campus' => $row['campus'],
                          'tuition_fee' => $row['tuition_fee'],
                          'exam_fee' => $row['exam_fee'],
                          'material_fee' => $row['material_fee'],
                          'total' => $row['total'],
                          'year' => $row['year'],
                          'lvl' => $row['lvl'],
                            'What_will_I_learn_1'  => $row['What_will_I_learn_1'],
                            'What_will_I_learn_2'  => $row['What_will_I_learn_2'],
                            'What_will_I_learn_3'  => $row['What_will_I_learn_3'],
                            'What_will_I_learn_4'  => $row['What_will_I_learn_4'],
                            'How_will_I_learn_1'  => $row['How_will_I_learn_1'],
                            'How_will_I_learn_2'  => $row['How_will_I_learn_2'],
                            'How_will_I_learn_3'  => $row['How_will_I_learn_3'],
                            'How_will_I_learn_4'  => $row['How_will_I_learn_4'],
                            'How_will_be_assessed_1'  => $row['How_will_I_be_assessed_1'],
                            'How_will_be_assessed_2'  => $row['How_will_I_be_assessed_2'],
                            'How_will_be_assessed_3'  => $row['How_will_I_be_assessed_3'],
                            'How_will_be_assessed_4'  => $row['How_will_I_be_assessed_4'],
                            'Are_there_entry_requirements_1'  => $row['Are_there_entry_requirements_1'],
                            'Are_there_entry_requirements_2'  => $row['Are_there_entry_requirements_2'],
                            'Are_there_entry_requirements_3'  => $row['Are_there_entry_requirements_3'],
                            'Are_there_entry_requirements_4'  => $row['Are_there_entry_requirements_4'],
                            'What_may_it_lead_to_1'  => $row['What_may_it_lead_to_1'],
                            'What_may_it_lead_to_2'  => $row['What_may_it_lead_to_2'],
                            'What_may_it_lead_to_3'  => $row['What_may_it_lead_to_3'],
                            'What_may_it_lead_to_4'  => $row['What_may_it_lead_to_4'],
                            'Are_there_other_costs_1'  => $row['Are_there_other_costs_1'],
                            'Are_there_other_costs_2'  => $row['Are_there_other_costs_2'],
                            'Are_there_other_costs_3'  => $row['Are_there_other_costs_3'],
                            'Are_there_other_costs_4'  => $row['Are_there_other_costs_4']

        );
    }
    return $course;
}

//$server->service(isset($HTTP_RAW_POST_DATA)?
//    $HTTP_RAW_POST_DATA : '');

// Get our posted data if the service is being consumed
// otherwise leave this data blank.
$HTTP_RAW_POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';

// pass our posted data (or nothing) to the soap service
$server->service($HTTP_RAW_POST_DATA);
exit

?>