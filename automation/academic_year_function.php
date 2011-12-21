<?php


// work out the current academic year

function greaterDate($start_date, $end_date) {
    $start = strtotime($start_date);
    $end = strtotime($end_date);
    if ($start - $end < 0)
        return 1;
    else
        return 0;
}

$tdate = date("d M");
//echo 'date is: ' . $tdate;

$date1 = '31 Jul';
if (greaterDate($date1, $tdate)) {
    //  echo 'yes date';
    $academicyear = date("Y");
    // echo $academicyear;
} else {
    //  echo 'no date';
    $academicyear = date("Y") - 1;
    // echo $academicyear;
}
//end of working out the academic year
//
// Begin layout table

?>
