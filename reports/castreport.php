<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$link = mysql_connect('10.0.100.38', 'root', '88Boom');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}

//select maidstonedb
mysql_select_db('castmedway') or die('Unable to select the database');

//find if student is on cast medway if yes print detials and then check RAG

$query = "SELECT * FROM students";

$result = mysql_query($query);


echo '<table border=1><tr><th>Student ID</th><th>Firstname</th><th>Lastname</th><th>statud</th></tr><tr>';

while ($row = mysql_fetch_assoc($result)) {


    echo '<tr><td>' . $row['learnerref'] . '</td><td>' . $row['firstname'] . '</td><td>' . $row['lastname'];

    $link2 = mysql_connect('127.0.0.1', 'dan', 'dan');
    if (!$link2) {
        die('Could not connect: ' . mysql_error());
    }
    mysql_select_db('moodle') or die('Unable to select the database');

//get their moodle userid
    $queryid = " SELECT * FROM mdl_user WHERE idnumber='" . $row['learnerref'] . "'";
    //echo $queryid . ' ';
    $result2 = mysql_query($queryid);
    $num_rows = mysql_num_rows($result2);
    //echo '#rows is ' . $num_rows;
    while ($row2 = mysql_fetch_assoc($result2)) {
        $id = $row2['id'];
        //  echo 'moodle id is: ' . $id;
    }
    //select the RAG status
    $queryilp = "SELECT * FROM mdl_ilpconcern_status WHERE userid='" . $id . "'";

    $result3 = mysql_query($queryilp);

    while ($row3 = mysql_fetch_assoc($result3)) {
        $status = $row3['status'];
        //echo 'status is: ' . $status;
        if ($status == '0') {
            echo '</td><td><font color=green>green</font></td></tr>';
        } elseif ($status == '1') {
            echo '</td><td><font color=gold>amber</font></td></tr>';
        } elseif ($status == '2') {
            echo '</td><td><font color=red>red</font></td></tr>';
        }
    }
}


echo '</table>';

mysql_close($link);
?>
