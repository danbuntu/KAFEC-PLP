
<?php
/**
 * Switch logo based on todays dates and contents of dates.xml
 */
// build the url to out put the logo
function constructurl($logo, $alttext)
{
     // echo $logo;
    // echo $alttext;
    // hard coded path - shouldn't be used
    // echo '<img src=" . /theme/midkent_newstyle/logos/' . $logo . '" . alt="' . $alttext . '" />';
    // build the path based on moodle current_theme function
    echo '<img src="' . $CFG -> httpswwwroot . '/theme/' . current_theme() . '/logos/' . $logo . '" . alt="' . $alttext . '" />';
    } 

// debug set file name - hardcoded and wrong
// $file = 'c:\xampplite\moodle\theme\midkent_newstyle\dates.xml' or die('Could not open file!');
// set the file path for dates.xml based on current_theme()
$file = $CFG -> httpswwwroot . '/theme/' . current_theme() . '/' . 'dates.xml';

// debug
// echo $file;
// echo $CFG->wwwroot.'/theme/'.current_theme();
// get todays date
$date = date("j/M");

// open the file
$xml = simplexml_load_file($file) or die ('Unable to load load xml file!');

// parse the xml and set the default logo and then set if a date is present
foreach ($xml as $item) {
    if ($item -> date == 'default') {
        $logo = $item -> logo;
         $alttext = $item -> text;
         } 
    elseif ($item -> date == $date) {
        
        $logo = $item -> logo;
         $alttext = $item -> text;
         } 
    } 

// call contructurl function in order to display the logo and alt text
constructurl($logo, $alttext);
?>
