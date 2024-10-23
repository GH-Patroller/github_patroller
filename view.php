<?php

require_once('../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/filelib.php');
require_once('lib.php'); // Si tienes funciones específicas de tu plugin, aquí cargamos el archivo
require(__DIR__ . '\classes\tasks\commit_retriever_task.php');
global $DB, $OUTPUT, $PAGE;


// Configurar la página
$id = required_param('id', PARAM_INT);

// Obtener el módulo del curso (cm), el curso, y la instancia del plugin
if ($id) {
    $cm = get_coursemodule_from_id('pluginpatroller', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $pluginpatroller = $DB->get_record('pluginpatroller', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    print_error('Course module ID is required.');
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
$PAGE->set_url('/mod/pluginpatroller/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($pluginpatroller->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();

$commit_retriever_task = new commit_retriever_task();
echo '<pre>', print_r($commit_retriever_task->execute('PR3-2024-2C-D-G1')), '</pre>';

echo $OUTPUT->footer();
