<?php

$link= mysql_connect('127.0.0.1', 'root', '88Boom', 'moodle');
if (!$link) {
die('Could not connect: ' . mysql_error());
}
//echo 'Connected to medals';

mysql_select_db('moodle') or die ('Unable to select the database');

?>
