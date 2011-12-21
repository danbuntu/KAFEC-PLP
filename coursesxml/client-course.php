<?php
//require_once(‘config.php’);

$ws = "http://moodledev.midkent.ac.uk/coursesxml/server-course.php?wsdl";

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


?>

<form method="POST" action="client-course.php" name="coursesearch">
    <input type="text" name="term" id="term"/>
    <input type="submit" id="submit" name="submit" value="Search"/>
</form>

course 2 - test - 71M300/1A -- 53M101/1A - 87P569/1A3 - 62D334/1A -
62D500/1A -
87M553/1A -
96M553/1A -


<form method="POST" action="client-course.php" name="coursesearch2">
    <input type="text" name="term" id="term"/>
    <input type="submit" id="submit2" name="submit2" value="Search"/>
</form>


<?php

if (isset($_POST['submit'])) {
    $term = htmlentities($_POST['term']);
    echo $term;
    $client = new SoapClient("http://moodledev.midkent.ac.uk/coursesxml/server-course.php?wsdl");
    $result = $client->__soapCall("getCourse", array($term));

    //echo '<table>';
    //while ($result = $row) {
    //    echo '<tr><td>' . $row</td></table>'

    print_r($result);

}


if (isset($_POST['submit2'])) {
    $term = htmlentities($_POST['term']);
    echo $term;
    $client = new SoapClient("http://moodledev.midkent.ac.uk/coursesxml/server-course.php?wsdl");
    $result = $client->__soapCall("getCourse2", array($term));

    //echo '<table>';
    //while ($result = $row) {
    //    echo '<tr><td>' . $row</td></table>'

    print_r($result);

}


echo '</br>';
echo '</br>';
echo '</br>';

foreach ($result as $item) {
    echo 'Title: ' . $item->coursename2 . '<br/>';
    echo 'Code: ' . $item->coursecode2 . '<br/>';
    echo 'Start Date: ' . $item->startdate . '<br/>';
    echo 'Length in Years: ' . $item->lengthYears . '<br/>';
    echo 'Length in Weeks: ' . $item->lengthWeeks . '<br/>';
    echo 'Length in Days: ' . $item->lengthDays . '<br/>';
    echo 'Include in Web: ' . $item->includeWeb . '<br/>';
    echo 'Faculty: ' . $item->faculty . '<br/>';
    echo 'Department: ' . $item->department . '<br/>';
    echo 'PRPI Avaialble: ' . $item->PRPIAvaialable . '<br/>';
    echo 'instance: ' . $item->instance . '<br/>';;
    echo 'Hours per Week: ' . $item->hoursPerWeek . '<br/>';
    echo 'Campus: ' . $item->campus . '<br/>';
    echo 'Tuition Fee: ' . $item->tuition_fee . '<br/>';
    echo 'Exam Fee: ' . $item->exam_fee . '<br/>';
    echo 'Material Fee: ' . $item->material_fee . '<br/>';
    echo 'Total: ' . $item->total . '<br/>';
    echo 'Year: ' . $item->year . '<br/>';
    echo 'Lvl: ' . $item->lvl . '<br/>';
    echo 'What_will_I_learn_1: ' . $item->What_will_I_learn_1 . '<br/>';
    echo 'What_will_I_learn_2: ' . $item->What_will_I_learn_2 . '<br/>';
    echo 'What_will_I_learn_3: ' . $item->What_will_I_learn_3 . '<br/>';
    echo 'What_will_I_learn_4: ' . $item->What_will_I_learn_4 . '<br/>';
    echo 'How_will_I_learn_1: ' . $item->How_will_I_learn_1 . '<br/>';
    echo 'How_will_I_learn_2: ' . $item->How_will_I_learn_2 . '<br/>';
    echo 'How_will_I_learn_3 ' . $item->How_will_I_learn_3 . '<br/>';
    echo 'How_will_I_learn_4: ' . $item->How_will_I_learn_4 . '<br/>';
    echo 'How_will_be_assessed_1: ' . $item->How_will_be_assessed_1 . '<br/>';
    echo 'How_will_be_assessed_2: ' . $item->How_will_be_assessed_2 . '<br/>';
    echo 'How_will_be_assessed_3: ' . $item->How_will_be_assessed_3 . '<br/>';
    echo 'How_will_be_assessed_4: ' . $item->How_will_be_assessed_4 . '<br/>';
    echo 'Are_there_entry_requirements_1: ' . $item->Are_there_entry_requirements_1 . '<br/>';
    echo 'Are_there_entry_requirements_2: ' . $item->Are_there_entry_requirements_2 . '<br/>';
    echo 'Are_there_entry_requirements_3: ' . $item->Are_there_entry_requirements_3 . '<br/>';
    echo 'Are_there_entry_requirements_4: ' . $item->Are_there_entry_requirements_4 . '<br/>';
    echo 'What_may_it_lead_to_1: ' . $item->What_may_it_lead_to_1 . '<br/>';
    echo 'What_may_it_lead_to_2: ' . $item->What_may_it_lead_to_2 . '<br/>';
    echo 'What_may_it_lead_to_3: ' . $item->What_may_it_lead_to_3 . '<br/>';
    echo 'What_may_it_lead_to_4: ' . $item->What_may_it_lead_to_4 . '<br/>';
    echo 'Are_there_other_costs_1: ' . $item->Are_there_other_costs_1 . '<br/>';
    echo 'Are_there_other_costs_4: ' . $item->Are_there_other_costs_2 . '<br/>';
    echo 'Are_there_other_costs_3: ' . $item->Are_there_other_costs_3 . '<br/>';
    echo 'Are_there_other_costs_4: ' . $item->Are_there_other_costs_4 . '<br/>';

}

?>