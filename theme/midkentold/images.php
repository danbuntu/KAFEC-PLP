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
		8 => "GuyFawkes",
		9 => "Halloween",
		10 => "May4th",
		11 => "StAndrews",
		12 => "StDavids",
		13 => "StGeorges",
		14 => "StPatricks",
		15 => "StValentines",
		16 => "Summer",
		17 => "SummerSolstice",
		18 => "TalkLikeAPirate",
		19 => "Winter",
		20 => "WinterSolstice"
	);

	// Loop through the images and show each one with its tooltip
	
	foreach ($logos as $i => $value) {
		echo('<img src='.$CFG->wwwroot.'/theme/'.current_theme().'/logos/'.$logos[$i].'-logo.jpg ');
		echo(implode('', file($CFG->wwwroot.'/theme/'.current_theme().'/logos/'.$logos[$i].'.txt')).' />');
		//echo('<img src='.$CFG->wwwroot.'/theme/'.current_theme().'/logos/'.$logos[$i].'-logo.jpg /></ br>');
	}

?>