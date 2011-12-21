<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


$server = '10.0.100.71';

$link = mssql_connect($server, 'sa', 'r3sult5!');


if (!$link) {
    die('something went wrong with the connecting to Correo mssql database');
}


//select the database to use
//$select = mssql_select_db('NGReports');
$select = mssql_select_db('NG_Dan');

?>
