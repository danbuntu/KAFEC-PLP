<?php

/**
* Upgrade DB for MRCUTE2 :
* Add modifieddate & visible field
*/

    require_once('../../../../config.php');
	require_once($CFG->libdir.'/ddllib.php');

	$table = new XMLDBTable('resource_ims');
	
	$field = new XMLDBField('modifieddate');
	if(!field_exists($table, $field)){
		$field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'contributeddate');
		add_field($table, $field);
	} else {
		echo 'modifieddate already added<br />';
	}
	
	$field = new XMLDBField('visible');
	if(!field_exists($table, $field)){
		$field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '1', 'status');
		add_field($table, $field);
	} else {
		echo 'visible already added<br />';
	}
	
?>