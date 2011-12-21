<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: present/header.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: Present options header
////////////////////////////////////////////////////////////////////////////
//BASIC SECURITY TO STOP PAGE BEING DISPLAYED IF $sPageCaller VARIABLE NOT FOUND	
if(!isset($sPageCaller)||$sPageCaller!=='present.php'){
	require_once("../../../config.php");
	redirect($CFG->wwwroot);
}
////////////////////////////////////////////////////////////////////////////
//INCLUDE JAVASCRIPT MOODLE EXTERNAL JS FILE lib/javascript-static.js WHICH lib/formslib.php USES
echo '
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>'.get_string('easyvoterpresent', 'easyvoter').$easyvoter->name.'</title>
    <link rel="stylesheet" type="text/css" href="styles/default.css" />
	<script type="text/javascript" src="'.$CFG->wwwroot.'/lib/javascript-static.js"></script>
	<script type="text/javascript" src="'.$CFG->wwwroot.'/mod/easyvoter/scripts/functionlibrary.js"></script>
    </head>
    <body>
	<div id="content">
	<div id="title">'.$easyvoter->name.'</div>
	<div id="optionstitle">'.get_string('easyvoterpresentoptions', 'easyvoter').'</div>
	<div id="optionscontent">
';
?>