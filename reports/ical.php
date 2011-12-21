<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// https://www.google.com/calendar/ical/41fjg3he5r9vl96gviphu35lk4%40group.calendar.google.com/public/basic.ics

$calendarID = "41fjg3he5r9vl96gviphu35lk4@group.calendar.google.com";

$googleFeedsURL = "http://www.google.com/calendar/feeds/" . $calendarID . "/public/full";

require_once 'Zend/Loader.php';

Zend_Loader::loadClass('Zend_Gdata');

Zend_Loader::loadClass('Zend_Gdata_Query');

Zend_Loader::loadClass('Zend_Gdata_Calendar');



?>
