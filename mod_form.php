<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_pluginpatroller_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $DB;
        $mform = $this->_form;

        // Modo Examen section header
        $mform->addElement('header', 'patrollerheader', get_string('patrollerheader', 'pluginpatroller'));

        /* Existing fields */
        $mform->addElement('text', 'name', get_string('name', 'pluginpatroller'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        // Añadir campo de subida de archivos
        $mform->addElement('filepicker', 'myfile', get_string('uploadfile', 'mod_myplugin'), null, 
            array('accepted_types' => array('.csv', '.xls', '.xlsx'))); // Solo archivos CSV, XLS, y XLSX
        $mform->addRule('myfile', null, 'required', null, 'client'); // Regla de campo requerido
        $mform->setType('myfile', PARAM_FILE); // Sobrescribir archivo anterior

        // Estándar de Moodle para elementos de curso
        $this->standard_intro_elements();
        $this->standard_coursemodule_elements();

        // Botones de acción para guardar/cancelar
        $this->add_action_buttons();
    }


}

?>

