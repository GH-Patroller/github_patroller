<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_pluginpatroller_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $DB;
        $mform = $this->_form;

        // Modo Examen section header
        $mform->addElement('header', 'patrollerheader', get_string('patrollerheader', 'pluginpatroller'));

		/*
        // Add the "Name" field
        $mform->addElement('text', 'name', get_string('name', 'pluginpatroller'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        
        // Add the checkbox for "Exam Mode"
        $mform->addElement('advcheckbox', 'exammode', get_string('exammode', 'pluginpatroller'), get_string('exammode_desc', 'pluginpatroller'));
        $mform->setType('exammode', PARAM_BOOL);
        $mform->addHelpButton('exammode', 'helpidentifier', 'pluginpatroller');

        // Add date fields
        $mform->addElement('date_time_selector', 'startdate', get_string('startdate', 'pluginpatroller'));
        $mform->disabledIf('startdate', 'exammode', 'notchecked');
        $mform->addElement('date_time_selector', 'enddate', get_string('enddate', 'pluginpatroller'));
        $mform->disabledIf('enddate', 'exammode', 'notchecked');
		*/

        // Add the "Owner" field
        $mform->addElement('text', 'owner', get_string('owner', 'pluginpatroller'), array('size'=>'64'));
        $mform->setType('owner', PARAM_TEXT);
        $mform->addRule('owner', null, 'required', null, 'client');

        // Add the "Repo" field
        $mform->addElement('text', 'repo', get_string('repo', 'pluginpatroller'), array('size'=>'64'));
        $mform->setType('repo', PARAM_TEXT);
        $mform->addRule('repo', null, 'required', null, 'client');

        $this->standard_intro_elements();
        $this->standard_coursemodule_elements();
        
        // Adding new "otrocampo" field
        // NEW FIELD
        $mform->addElement('text', 'otrocampo', get_string('otrocampo', 'pluginpatroller'), array('size' => '64'));
        $mform->setType('otrocampo', PARAM_TEXT);
        $mform->addRule('otrocampo', null, 'required', null, 'client');
        
        $this->add_action_buttons();
    
    }

    function data_preprocessing(&$default_values) {
		/*
        if (isset($default_values['startdate'])) {
            $default_values['startdate'] = $default_values['startdate'];
        }
        if (isset($default_values['enddate'])) {
            $default_values['enddate'] = $default_values['enddate'];
        }
        if (isset($default_values['exammode'])) {
            $default_values['exammode'] = (bool)$default_values['exammode'];
        }
		*/
		
        // Preprocess the "owner" and "repo" fields
        if (isset($default_values['owner'])) {
            $default_values['owner'] = $default_values['owner'];
        }
        if (isset($default_values['repo'])) {
            $default_values['repo'] = $default_values['repo'];
        }
    }

}
?>
