<?php

require_once $CFG->libdir.'/dmllib.php';

$settings->add(new admin_setting_configcheckbox('block_participants_useroles', get_string('useroles', 'block_participants'),
                   get_string('configuseroles', 'block_participants'), 0));

// Get a list of non-guest roles
$nonguestroles = array();
if ($roles = get_all_roles()) {
    foreach ($roles as $role) {
        $rolename = strip_tags(format_string($role->name, true));
        if (!isset($guestroles[$role->id])) {
            $nonguestroles[$role->id] = $rolename;
        }
    }
}

$settings->add(new admin_setting_configmultiselect('block_participants_rolestouse', get_string('useroles', 'block_participants'),
                      get_string('configuseroles', 'block_participants'), array(), $nonguestroles));

?>