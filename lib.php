<?php
defined('MOODLE_INTERNAL') || die();

function pluginpatroller_add_instance($pluginpatroller) {
    global $DB;

    $pluginpatroller->timecreated = time();
    $pluginpatroller->timemodified = time();

    // Insert the activity into the database
    $pluginpatroller->id = $DB->insert_record('pluginpatroller', $pluginpatroller);

    // Create grade item for the activity
    pluginpatroller_grade_item_update($pluginpatroller);

    return $pluginpatroller->id;
}

function pluginpatroller_update_instance($pluginpatroller) {
    global $DB;

    $pluginpatroller->timemodified = time();
    $pluginpatroller->id = $pluginpatroller->instance;

    // Update grade item
    pluginpatroller_grade_item_update($pluginpatroller);

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

// Function to create or update the grade item in Moodle's Gradebook
function pluginpatroller_grade_item_update($pluginpatroller, $maxgrade = 100) {
    $item = array(
        'itemname' => $pluginpatroller->name,
        'gradetype' => GRADE_TYPE_VALUE,
        'grademax' => $maxgrade,
        'grademin' => 0,
    );
    grade_update('mod/pluginpatroller', $pluginpatroller->course, 'mod', 'pluginpatroller', $pluginpatroller->id, 0, null, $item);
}
