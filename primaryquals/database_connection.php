<?php


$linkpri = mysql_connect('127.0.0.1', 'root', '88Boom', 'moodle');
if (!$linkpri) {
    die('Could not connect: ' . mysql_error());
}

mysql_select_db(moodle);



?>
