<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$link2= mysql_connect('127.0.0.1', 'root', '88Boom', 'moodle');
if (!$link2) {
die('Could not connect: ' . mysql_error());
}
//echo 'Connected to reports';

mysql_select_db('moodle') or die ('Unable to select the database');




?>
