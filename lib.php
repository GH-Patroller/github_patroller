<?php
defined('MOODLE_INTERNAL') || die();

function pluginpatroller_add_instance($pluginpatroller) {
    global $DB;

    $pluginpatroller->timecreated = time();
    $pluginpatroller->timemodified = time();

    return $DB->insert_record('pluginpatroller', $pluginpatroller);
}

function pluginpatroller_update_instance($pluginpatroller) {
    global $DB;

    $pluginpatroller->timemodified = time();
    $pluginpatroller->id = $pluginpatroller->instance;

    // Obtener el contexto del módulo de actividad
    $context = context_module::instance($pluginpatroller->coursemodule);

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

