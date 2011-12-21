<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


$server = '10.0.100.70';

$link = mssql_connect($server, 'plp', '88Boom!');



if (!$link) {
    die('something went wrong with the connecting to Correo mssql database');
}

$select = mssql_select_db('NG');

$query = "SELECT TOP 10 * FROM dbo.studstudent";

$result = mssql_query($query);

while ($row = mssql_fetch_assoc($result)) {
 echo $row('stud_surname') . '<br/>';
}

//select the database to use
//$select = mssql_select_db('NGReports');

?>
