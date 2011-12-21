<?php 

$settings->add(new admin_setting_heading('block_nwkc_geolocation_intro', '', '<strong>This is the NWKC GeoLocation Block</strong>.'));

$settings->add(new admin_setting_heading('block_nwkc_geolocation_sitesdesc', '', 'Enter latitude and longitude coordinates for site as csv. i.e. 51.30,0.29,51.31,0.30'));


$settings->add(new admin_setting_configtext('block_nwkc_geolocation_site0_desc', 'Site 1 Description', '', '', PARAM_RAW));
$settings->add(new admin_setting_configtext('block_nwkc_geolocation_site0', 'Site 1 Coordinates', '', '', PARAM_RAW));

$settings->add(new admin_setting_configtext('block_nwkc_geolocation_site1_desc', 'Site 2 Description', '', '', PARAM_RAW));
$settings->add(new admin_setting_configtext('block_nwkc_geolocation_site1', 'Site 2 Coordinates', '', '', PARAM_RAW));

$settings->add(new admin_setting_configtext('block_nwkc_geolocation_site2_desc', 'Site 3 Description', '', '', PARAM_RAW));
$settings->add(new admin_setting_configtext('block_nwkc_geolocation_site2', 'Site 3 Coordinates', '', '', PARAM_RAW));

$settings->add(new admin_setting_configtext('block_nwkc_geolocation_loc0', 'Location 0', '', 'Hidden', PARAM_RAW));
$settings->add(new admin_setting_configtext('block_nwkc_geolocation_loc1', 'Location 1', '', 'On Campus', PARAM_RAW));
$settings->add(new admin_setting_configtext('block_nwkc_geolocation_loc2', 'Location 2', '', 'Library', PARAM_RAW));
$settings->add(new admin_setting_configtext('block_nwkc_geolocation_loc3', 'Location 3', '', 'Open Access', PARAM_RAW));
$settings->add(new admin_setting_configtext('block_nwkc_geolocation_loc4', 'Location 4', '', 'Refectory', PARAM_RAW));
$settings->add(new admin_setting_configtext('block_nwkc_geolocation_loc5', 'Location 5', '', 'Sports', PARAM_RAW));

?>