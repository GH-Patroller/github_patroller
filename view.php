<?php

require_once('../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/filelib.php');
require_once('lib.php'); // Si tienes funciones específicas de tu plugin, aquí cargamos el archivo
require_once(__DIR__ . '/utils.php');
require_once('vistas/alumnosinscritos.php');

$PAGE->requires->css('/mod/pluginpatroller/style.css');

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

$context = context_module::instance($cm->id); // Asegúrate de que el contexto se obtiene correctamente
$PAGE->set_url('/mod/pluginpatroller/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($pluginpatroller->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();

// Definir las pestañas
$tabrows = array();
$tabrows[] = new tabobject('Commits', new moodle_url('/mod/pluginpatroller/view.php', array('id' => $id, 'tab' => 'tab1')), 'Configuración');
$tabrows[] = new tabobject('Configuration', new moodle_url('/mod/pluginpatroller/view.php', array('id' => $id, 'tab' => 'tab2')), 'Commits');
$tabrows[] = new tabobject('Students', new moodle_url('/mod/pluginpatroller/view.php', array('id' => $id, 'tab' => 'tab3')), 'Students');


print_tabs(array($tabrows), optional_param('tab', 'tab1', PARAM_TEXT));

// Verificar valor del parámetro 'tab'
$tab = optional_param('tab', 'tab1', PARAM_TEXT);

// Contenido según la pestaña activa
switch ($tab) {
    case 'tab1':
        mostrar_contributors_insights();
        break;
    case 'tab2':
        echo '<button type="button" class="btn btn-primary" onclick="location.href=\'config/crearrepositorios.php?id=' . $id . '\'">Crear Repositorios</button>';
        mostrar_configuraciones();
        break;
    case 'tab2':
        mostrar_contributors_insights();
        break;
    case 'tab3':
        mostrar_alumnos_inscritos($context); // Asegúrate de pasar el contexto correctamente
        break;
    default:
        echo "<p>Pestaña desconocida.</p>";
}

echo "<hr>";

echo $OUTPUT->footer();