<?php
    require_once('../../config.php');

    $id = optional_param('id', 0, PARAM_INT);

    if (! ($howtorecord = get_record('resource', 'id', $id)) ) {
        error('Invalid resource id ('.$id.')');
    }

	$title = $howtorecord->name;
	$bodytext = $howtorecord->alltext;

	print_header($howtorecord->name, NULL, '');

	echo('<div id="content"><div class="box generalbox generalboxcontent boxaligncenter clearfix">');
	echo($howtorecord->alltext);
	echo('</div></div>');

    print_footer(NULL, '20');
    
?>