<?php
 
function xmldb_block_nwkc_geolocation_upgrade($oldversion) {
    global $CFG;
 
    $result = TRUE;
	
    if ($oldversion < 2011032213) {

    /// Define table nwkc_geolocation to be created
        $table = new XMLDBTable('nwkc_geolocation');

    /// Adding fields to table nwkc_geolocation
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('studentid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('timecheckedin', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('lat', XMLDB_TYPE_NUMBER, '10, 8', null, null, null, null, null, null);
        $table->addFieldInfo('lon', XMLDB_TYPE_NUMBER, '10, 8', null, null, null, null, null, null);

    /// Adding keys to table nwkc_geolocation
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Launch create table for nwkc_geolocation
        $result = $result && create_table($table);
    }
	
	
    if ($result && $oldversion < 2011032214) {

    /// Define field location to be added to nwkc_geolocation
        $table = new XMLDBTable('nwkc_geolocation');
        $field = new XMLDBField('location');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'small', null, null, null, null, null, null, 'lon');

    /// Launch add field location
        $result = $result && add_field($table, $field);
    }
	
 
    return $result;
}
?>