<?php

require_once('../../config.php');
require_once('lib.php'); // Si tienes funciones específicas de tu plugin, aquí cargamos el archivo
require_once('utils.php');
require_once('views/contributors_insights.php');
require_once('views/crearrepositorios.php');
require_once('views/alumnosinscritos_curso.php');
require_once('views/alumnosinscritos_plugin.php');
require_once('views/alumnosinscritos_vistaalumno.php');

global $DB, $OUTPUT, $PAGE, $USER;

$PAGE->requires->css('/mod/pluginpatroller/css/style.css');

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

echo 'Usuario: ' . $USER->username; // Muestra el nombre de usuario
echo 'ID de Usuario: ' . $USER->id; // Muestra el ID del usuario
echo 'Correo: ' . $USER->email;     // Muestra el correo del usuario

$roleTeacherid = 3;
$roleStudentid = 5;

// Verificar si el usuario tiene el rol de estudiante en el contexto actual
$is_student = user_has_role_assignment($USER->id, $roleStudentid, $context->id);

// Definir las pestañas
$tabrows = array();
if (!$is_student) {
    $tabrows[] = new tabobject('tab1', new moodle_url('/mod/pluginpatroller/view.php', array('id' => $id, 'tab' => 'tab1')), 'Crear Repositorios');
}
$tabrows[] = new tabobject('tab2', new moodle_url('/mod/pluginpatroller/view.php', array('id' => $id, 'tab' => 'tab2')), 'Alumnos Github Patroller');
if (!$is_student) {
    $tabrows[] = new tabobject('tab3', new moodle_url('/mod/pluginpatroller/view.php', array('id' => $id, 'tab' => 'tab3')), 'Contributors Insights');
}
if (!$is_student) {
    $tabrows[] = new tabobject('tab4', new moodle_url('/mod/pluginpatroller/view.php', array('id' => $id, 'tab' => 'tab4')), 'Alumnos Inscriptos');
}

print_tabs(array($tabrows), optional_param('tab', 'tab1', PARAM_TEXT));

// Verificar valor del parámetro 'tab'
$tab = optional_param('tab', 'tab1', PARAM_TEXT);

// Contenido según la pestaña activa
switch ($tab) {
    case 'tab1':
        if (!$is_student) {
            formulario($course, $id, $context);
        }
        break;
    case 'tab2':
        if ($is_student) {
            mostrar_alumnos_inscritos_plugin_alumno($context);
        } else {
            mostrar_alumnos_inscritos_plugin($context);
        }
        break;
    case 'tab3':
        if (!$is_student) {
            show_students_commits_table($context);
        }
        break;
        case 'tab4':
            if (!$is_student) {
                mostrar_alumnos_inscritos_curso($context);
            }
            break;
    default:
        echo "<p>Pestaña desconocida.</p>";
}

echo "<hr>";

echo $OUTPUT->footer();
