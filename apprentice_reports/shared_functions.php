<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dattwood
 * Date: 22/06/11
 * Time: 15:20
 * Functions shared by all services
 */


// function to take a date and workout the current academic year

function academicYear() {

    $start_date = date("d M");
$end_date = '31 Jul';

    $start = strtotime($start_date);
    $end = strtotime($end_date);
    if ($start - $end < 0)
         return date("Y");
    else
       return date("Y") - 1;
}






?>

