<?php  /// Moodle Configuration File 

unset($CFG);

$CFG->dbtype    = 'mysql';
$CFG->dbhost    = 'localhost';
//$CFG->dbhost    = '10.0.100.64';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'root';
//$CFG->dbuser    = 'remote';
$CFG->dbpass    = '88Boom!';
//$CFG->dbpass    = 'remote';
$CFG->dbpersist =  false;
$CFG->prefix    = 'mdl_';

$CFG->wwwroot   = 'http://moodledev.midkent.ac.uk';
$CFG->dirroot   = 'S:\htdocs\moodle';
$CFG->dataroot  = 'S:\MoodleData';
// $CFG->dataroot  = '\\\s-sharepointdb\S$\MoodleData';
$CFG->admin     = 'admin';
$CFG->latinexcelexport = true;

$CFG->directorypermissions = 00777;  // try 02777 on a server in Safe Mode

$CFG->unicodedb = true;  // Database is utf8

// These variables define the specific settings for defined course formats.
// They override any settings defined in the formats own config file.
     $CFG->defaultblocks_social = 'participants,search_forums,calendar_month,calendar_upcoming,social_activities,recent_activity,admin,course_list';
     $CFG->defaultblocks_topics = 'participants,activity_modules,admin,course_list:howto,news_items,calendar_upcoming,recent_activity';
     $CFG->defaultblocks_weeks = 'participants,activity_modules,admin,course_list:howto,news_items,calendar_upcoming,recent_activity';

// These blocks are used when no other default setting is found.
     $CFG->defaultblocks = 'participants,activity_modules,admin,course_list:howto,news_items,calendar_upcoming,recent_activity';

require_once("$CFG->dirroot/lib/setup.php");
// MAKE SURE WHEN YOU EDIT THIS FILE THAT THERE ARE NO SPACES, BLANK LINES,
// RETURNS, OR ANYTHING ELSE AFTER THE TWO CHARACTERS ON THE NEXT LINE.

// line to find the opensssl.cnf
$CFG->opensslcnf = 'S:\Program Files (x86)\Apache Software Foundation\Apache2.2\bin';

?>