<?php

require_once("nusoap/lib/nusoap.php");
$namespace = "http://localhost/nusoaphelloworld/index.php";

// create a new soap server
$server = new soap_server();

// configure our WSDL
$server->configureWSDL("HelloExample");

// set our namespace
$server->wsdl->schemaTargetNamespace = $namespace;


//Create a complex type
$server->wsdl->addComplexType('MyComplexType','complexType','struct','all','',
array( 'ID' => array('name' => 'ID','type' => 'xsd:int'),
'YourName' => array('name' => 'YourName','type' => 'xsd:string')));

//Register our method using the complex type
$server->register(
// method name:
'HelloComplexWorld',
// parameter list:
array('name'=>'tns:MyComplexType'),
// return value(s):
array('return'=>'tns:MyComplexType'),
// namespace:
$namespace,
// soapaction: (use default)
false,
// style: rpc or document
'rpc',
// use: encoded or literal
'encoded',
// description: documentation for the method
'Complex Hello World Method');



//Our complex method
function HelloComplexWorld($mycomplextype)
{
 $query = "SELECT * FROM studstudent WHERE STUD_Student_ID='" . $id . "'";
        echo $query;

        $result = mssql_query($query);
        $student = array();
        while ($row = mssql_fetch_array($result)) {
            $student[] = $row;
        }
        return $student;
    }
}

// Get our posted data if the service is being consumed
// otherwise leave this data blank.
$POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';

// pass our posted data (or nothing) to the soap service
$server->service($POST_DATA);
exit();
?>
