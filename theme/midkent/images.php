<?php
	
    require_once('C:/xampplite/moodle/config.php');

	// Create an array of all the know special images until we can generate this automatically from the directory
	$logos = array(
		0 => "Default", 
		1 => "4thJuly", 
		2 => "AustraliaDay",
		3 => "Autumn",
		4 => "CarShare",
		5 => "ChineseNewYear",
		6 => "Christmas",
		7 => "Diwali",
		8 => "EarthDay",
		9 => "GuyFawkes",
		10 => "Halloween",
		11 => "May4th",
		12 => "PiDay",
		13 => "StAndrews",
		14 => "StDavids",
		15 => "StGeorges",
		16 => "StPatricks",
		17 => "StValentines",
		18 => "Summer",
		19 => "SummerSolstice",
		20 => "TalkLikeAPirate",
		21 => "Winter",
		22 => "WinterSolstice"
	);

	// Loop through the images and show each one with its tooltip
	
	foreach ($logos as $i => $value) {
		echo('<img src='.$CFG->wwwroot.'/theme/'.current_theme().'/logos/'.$logos[$i].'-logo.gif ');
		echo(implode('', file($CFG->wwwroot.'/theme/'.current_theme().'/logos/'.$logos[$i].'.txt')).' />');
		//echo('<img src='.$CFG->wwwroot.'/theme/'.current_theme().'/logos/'.$logos[$i].'-logo.jpg /></ br>');
	}

?>