<?php

require_once('../../config.php');
require_once('lib.php'); // Si tienes funciones específicas de tu plugin, aquí cargamos el archivo

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

echo "<h2>Datos de Config Patroller</h2>";

// Fetch the GitHub token from plugin settings in the database
$token = get_config('pluginpatroller', 'token_patroller');
$owner = get_config('pluginpatroller', 'owner_patroller');

echo $token."<br/>";
echo $owner."<br/>";
echo "<hr/>";


// Mostrar los datos de la tabla pluginpatroller
echo "<h2>Datos de Instancia PluginPatroller</h2>";
$pluginpatroller_records = $DB->get_records('pluginpatroller');
foreach ($pluginpatroller_records as $record) {
    echo "<p>ID: {$record->id}</p>";
    echo "<p>Nombre: {$record->name}</p>";
    echo "<p>Formato de Introducción: {$record->introformat}</p>";
    echo "<p>Creación: " . date('d-m-Y H:i:s', $record->timecreated) . "</p>";
    echo "<p>Modificación: " . date('d-m-Y H:i:s', $record->timemodified) . "</p>";
    echo "<p>Última Actualización del API: {$record->apimodified}</p>";
    echo "<hr>";
}

// Mostrar los datos de la tabla data_patroller
echo "<h2>Datos de Alumnos DataPatroller</h2>";
$data_patroller_records = $DB->get_records('data_patroller');
foreach ($data_patroller_records as $record) {
    echo "<p>ID: {$record->id}</p>";
    echo "<p>Sede: {$record->sede}</p>";
    echo "<p>Curso: {$record->curso}</p>";
    echo "<p>Grupo: {$record->num_grupo}</p>";
    echo "<p>Nombre del Repositorio: {$record->nombre_repo}</p>";
    echo "<p>Nombre del Alumno: {$record->nombre_alumno}</p>";
    echo "<p>Correo del Alumno: {$record->mail_alumno}</p>";
    echo "<p>Usuario GitHub del Alumno: {$record->alumno_github}</p>";
    echo "<p>Cantidad de Commits: {$record->cantidad_commits}</p>";
    echo "<p>Líneas Agregadas: {$record->lineas_agregadas}</p>";
    echo "<p>Líneas Eliminadas: {$record->lineas_eliminadas}</p>";
    echo "<p>Líneas Modificadas: {$record->lineas_modificadas}</p>";
    echo "<p>Fecha Último Commit: " . date('d-m-Y H:i:s', $record->fecha_ultimo_commit) . "</p>";
    echo "<hr>";
}

echo $OUTPUT->footer();

?>
