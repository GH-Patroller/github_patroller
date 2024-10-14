<?php
defined('MOODLE_INTERNAL') || die();

function pluginpatroller_add_instance($pluginpatroller) {
    global $DB;

    $pluginpatroller->timecreated = time();
    $pluginpatroller->timemodified = time();

    // Obtener el contexto del módulo de actividad
    $context = context_module::instance($pluginpatroller->coursemodule);

    // Subir el archivo al área de archivos del módulo
    $draftitemid = file_get_submitted_draft_itemid('myfile');
    file_save_draft_area_files($draftitemid, $context->id, 'mod_pluginpatroller', 'uploadedfiles', 0, array('subdirs' => false));

    // Obtener nombre del archivo subido
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_pluginpatroller', 'uploadedfiles', 0, 'itemid, filepath, filename', false);

    if ($files) {
        foreach ($files as $file) {
            $pluginpatroller->filename = $file->get_filename(); // Guardar el nombre del archivo subido
            break;
        }
    }

    return $DB->insert_record('pluginpatroller', $pluginpatroller);
}

function pluginpatroller_update_instance($pluginpatroller) {
    global $DB;

    $pluginpatroller->timemodified = time();
    $pluginpatroller->id = $pluginpatroller->instance;

    // Obtener el contexto del módulo de actividad
    $context = context_module::instance($pluginpatroller->coursemodule);

    // Subir el archivo al área de archivos del módulo
    $draftitemid = file_get_submitted_draft_itemid('myfile');
    file_save_draft_area_files($draftitemid, $context->id, 'mod_pluginpatroller', 'uploadedfiles', 0, array('subdirs' => false));

    // Obtener nombre del archivo subido
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_pluginpatroller', 'uploadedfiles', 0, 'itemid, filepath, filename', false);

    if ($files) {
        foreach ($files as $file) {
            $pluginpatroller->filename = $file->get_filename(); // Actualizar el nombre del archivo subido
            break;
        }
    }

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


function pluginpatroller_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $DB, $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    if ($filearea !== 'uploadedfiles') {
        return false;
    }

    require_login($course, true, $cm);
    if (!has_capability('mod/pluginpatroller:view', $context)) {
        return false;
    }

    $itemid = array_shift($args);
    $filepath = '/' . implode('/', $args);
    $filename = array_pop($args);

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_pluginpatroller', $filearea, $itemid, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        send_file_not_found();
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}
