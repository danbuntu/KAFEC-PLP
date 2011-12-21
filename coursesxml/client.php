<?php
//require_once(‘config.php’);

$ws = "http://moodledev.midkent.ac.uk/coursesxml/server.php?wsdl";

$client = new SoapClient($ws);

var_dump($client->__getFunctions());

//include('./nusoap/lib/nusoap.php');
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


$client = new SoapClient("http://moodledev.midkent.ac.uk/coursesxml/server.php?wsdl");
$result =  $client->__soapCall("getStudent", array("10086915"));


print_r($result);

//$client = new nusoap_client("http://moodledev.midkent.ac.uk/coursesxml/server.php", "wsdl");
//echo $client->call('getStudent', '10086914');



//// Check for a fault
//if ($client->fault) {
//    echo '<p><b>Fault: ';
//    print_r($result);
//    echo '</b></p>';
//} else {
//    // Check for errors
//    $err = $client->getError();
//    if ($err) {
//        // Display the error
//        echo '<p><b>Error: ' . $err . '</b></p>';
//    } else {
//        // Display the result
//        print_r($result);
//    }
//}


echo 'test2233';
//$client = new SoapClient("http://moodledev.midkent.ac.uk/coursesxml/server.php?wsdl");
//$result = $client->__soapCall("getStudent", array("id" => "1008695"));

//echo $result;
?>