<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


//require_once(‘config.php’);

$ws = "http://moodledev.midkent.ac.uk/coursesxml/server2.php?wsdl";

$client = new SoapClient($ws);

var_dump($client->__getFunctions());

include('./nusoap/lib/nusoap.php');
//
//$client = new soapclient('http://moodledev.midkent.ac.uk/coursesxml/server.php');
//
//// Check for an error
//$err = $client->getError();
//if ($err) {
//// Display the error
//echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
//// At this point, you know the call that follows will fail
//}
//
//$result = $client->call('getStudent', array('id'=>'10086914' ));
//
//print_r($result);


//$client = new SoapClient("http://moodledev.midkent.ac.uk/coursesxml/server.php?wsdl");
//echo $client->getStudent, array('id' => '1008695'));

$client = new nusoap_client("http://moodledev.midkent.ac.uk/coursesxml/server2.php");
$result = $client->call(HelloWorld());




//$client = new SoapClient("http://moodledev.midkent.ac.uk/coursesxml/server.php?wsdl");
//$result = $client->__soapCall("getStudent", array("id" => "1008695"));

print_r($result);



?>
