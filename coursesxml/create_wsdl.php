<?php

require_once("WSDLCreator.php");
header("Content-Type: application/xml");


$test = new WSDLCreator("Courses", "http://moodledev.midkent.ac.uk/coursexml/wsdl");
//$test->includeMethodsDocumentation(false);

$test->addFile("server.php");

$test->setClassesGeneralURL("http://moodledev.midkent.ac.uk");

$test->addURLToClass("courses", "http://moodledev.midkent.ac.uk/coursexml/");
$test->addURLToTypens("XMLCreator", "http://moodledev.midkent.ac.uk/php2swdl");

//$test->ignoreMethod(array("example1_1"=>"getEx"));

$test->createWSDL();

$test->printWSDL(true); // print with headers
//print $test->getWSDL();
//$test->downloadWSDL();
$test->saveWSDL(dirname(__FILE__)."/test.wsdl", false);



?>