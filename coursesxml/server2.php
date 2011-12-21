<?php
require_once('./nusoap/lib/nusoap.php');
$namespace = "http://localhost/nusoaphelloworld/index.php";

// create a new soap server
$server = new soap_server();

// configure our WSDL
$server->configureWSDL("HelloExample");

// set our namespace
$server->wsdl->schemaTargetNamespace = $namespace;

//Register a method that has parameters and return types
$server->register(
// method name:
'HelloWorld',
// parameter list:
array('name'=>'xsd:string'),
// return value(s):
array('return'=>'xsd:string'),
// namespace:
$namespace,
// soapaction: (use default)
false,
// style: rpc or document
'rpc',
// use: encoded or literal
'encoded',
// description: documentation for the method
'Simple Hello World Method');

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

//Our Simple method
function HelloWorld($name)
{
return "Hello " . $name;
}

//Our complex method
function HelloComplexWorld($mycomplextype)
{
return $mycomplextype;
}

// Get our posted data if the service is being consumed
// otherwise leave this data blank.
$POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';

// pass our posted data (or nothing) to the soap service
$server->service($POST_DATA);
exit();

?>
