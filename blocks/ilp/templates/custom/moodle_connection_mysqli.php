<?php

$mysqli = new mysqli('localhost', 'root', '88Boom!', 'moodle');

if ($mysqli->errno) {
    echo 'error connecting' . $mysqli->error;
}



?>