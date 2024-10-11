<?php
defined('MOODLE_INTERNAL') || die();

function pluginpatroller_add_instance($pluginpatroller) {
    global $DB;

    $pluginpatroller->timecreated = time();
    $pluginpatroller->timemodified = time();
    $pluginpatroller->startdate = $pluginpatroller->startdate;
    $pluginpatroller->enddate = $pluginpatroller->enddate;

    
    // Save the new field 'otrocampo' into 'patrollerprueba' table
    // NEW FIELD
    $record = new stdClass();
    $record->otrocampo = $pluginpatroller->otrocampo;
    $record->timecreated = time();
    $DB->insert_record('patrollerprueba', $record);

    return $DB->insert_record('pluginpatroller', $pluginpatroller);
    
}

function pluginpatroller_update_instance($pluginpatroller) {
    global $DB;

    $pluginpatroller->timemodified = time();
    $pluginpatroller->id = $pluginpatroller->instance;
    $pluginpatroller->startdate = $pluginpatroller->startdate;
    $pluginpatroller->enddate = $pluginpatroller->enddate;

    
    // Update the new field 'otrocampo' in 'patrollerprueba' table
    // NEW FIELD
    $record = $DB->get_record('patrollerprueba', array('id' => $pluginpatroller->instance));
    $record->otrocampo = $pluginpatroller->otrocampo;
    $record->timemodified = time();
    $DB->update_record('patrollerprueba', $record);

    return $DB->update_record('pluginpatroller', $pluginpatroller);
    
}


function pluginpatroller_delete_instance($id) {
    global $DB;
    if (!$pluginpatroller = $DB->get_record('pluginpatroller', array('id' => $id))) {
        return false;
    }
    return $DB->delete_records('pluginpatroller', array('id' => $pluginpatroller->id));
}

function pluginpatroller_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $pluginpatrollernode) {
    global $PAGE;

}



