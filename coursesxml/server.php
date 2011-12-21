<?php
/* 
 * SOAP server to stream the course information out of NG
 */


require_once('./nusoap/lib/nusoap.php');
include('corero_conection.php');


$namespace = 'http://moodledev.midkent.ac.uk/server.php';

$server = new soap_server();

$server->configureWSDL('Student');

$server->wsdl->schemaTargetNamespace = 'http://moodledev.midkent.ac.uk/coursexml/';


// Create a complex type

$server->wsdl->addComplexType(
    'StudentArray',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(
         array('ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'tns:student[]')
    ),
    'tns:Student'
);

// The student array that passes back into the studentarray above via tns:Student
$server->wsdl->addComplexType(
    'Student',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'firstname' => array('name'=>'firstname','type'=>'xsd:string'),
        'lastname' => array('name'=>'lastname','type'=>'xsd:string')
    )
);


$server->register(
    'getStudent',
    array('id' => 'xsd:int'),
    array('return' => 'tns:StudentArray'),
    $namespace
//        false,
//        'rpc',
//        'encoded',
//        'Complex student method'
);


function getStudent($id)
{
    $query = "SELECT STUD_Forename_1, STUD_Surname FROM studstudent WHERE STUD_Student_ID='" . $id . "'";
    //echo $query;

    $result = mssql_query($query);
    $student = array();
    while ($row = mssql_fetch_array($result)) {
        //$student = $row['STUD_Forename_1'];
        //             $student[] = $row['STUD_Forename_1'];
        //             $student[] = $row['STUD_Surname'];
        $student[] = array('firstname' => $row['STUD_Forename_1'], 'lastname' => $row['STUD_Surname']);
    }
    return $student;
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