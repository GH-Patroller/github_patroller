<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_pluginpatroller_mod_form extends moodleform_mod
{

    function definition()
    {
        global $CFG, $DB;
        $mform = $this->_form;

        // Modo Examen section header
        $mform->addElement('header', 'patrollerheader', get_string('patrollerheader', 'pluginpatroller'));

        /* Existing fields */
        $mform->addElement('text', 'name', get_string('name', 'pluginpatroller'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addElement('text', 'execution_interval', get_string('execution_interval', 'pluginpatroller'));
        $mform->setType('execution_interval', PARAM_INT);
        $mform->setDefault('execution_interval', 4); // Valor por defecto de 4 alumnos
        $mform->addRule('execution_interval', null, 'required', null, 'client');



        // Estándar de Moodle para elementos de curso
        $this->standard_intro_elements();
        $this->standard_coursemodule_elements();

        // Botones de acción para guardar/cancelar
        $this->add_action_buttons();
    }

    //FUNCION QUE SETEA LOS DATOS ANTES DE MOSTRAR EL FORM
    /*
	function data_preprocessing(&$default_values) {
		// N/A
	}
	*/
}
