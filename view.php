<?php
require_once('../../config.php');
require_once($CFG->libdir.'/filelib.php');
require_once('lib.php'); // Si tienes funciones específicas de tu plugin, aquí cargamos el archivo

$id = required_param('id', PARAM_INT); // ID del módulo de curso.

if ($id) {
    // Obtener el módulo del curso (cm), el curso, y la instancia del plugin
    $cm = get_coursemodule_from_id('pluginpatroller', $id, 0, false, MUST_EXIST);
    $course = get_course($cm->course);
    $pluginpatroller = $DB->get_record('pluginpatroller', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    print_error('Course module ID is required.');
}

require_login($course, true, $cm); // Verificar que el usuario está logueado y tiene acceso a la actividad

$context = context_module::instance($cm->id); // Obtener el contexto de la actividad
$PAGE->set_url('/mod/pluginpatroller/view.php', array('id' => $cm->id)); // Establecer la URL de la página
$PAGE->set_title(format_string($pluginpatroller->name)); // Título de la página
$PAGE->set_heading(format_string($course->fullname)); // Título de la cabecera

echo $OUTPUT->header(); // Mostrar el encabezado de la página

// Fetch the GitHub token from plugin settings in the database
$token = get_config('pluginpatroller', 'token_patroller');
$owner = get_config('pluginpatroller', 'owner_patroller');

echo $token."<br/>";
echo $owner."<br/>";
echo "<hr/>";

$name = $pluginpatroller->name;  


echo $name."<br/>";


// Código para mostrar los archivos subidos y sus detalles
$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'mod_pluginpatroller', 'uploadedfiles', false, 'itemid, filepath, filename', false);

// Verificar si hay archivos subidos
if (count($files) > 0) {
    foreach ($files as $file) {
        if (!$file->is_directory()) {
            // Mostrar detalles del archivo
            $filename = $file->get_filename();
            $filesize = $file->get_filesize();

            // Generar la URL del archivo usando el contextid dinámico
            $fileurl = moodle_url::make_pluginfile_url($context->id, 'mod_pluginpatroller', 'uploadedfiles', 0, $file->get_filepath(), $file->get_filename());

            // Mostrar el nombre del archivo, su tamaño y el enlace de descarga
            echo "Archivo subido: <a href='{$fileurl}'>{$filename}</a><br>";
            echo "Tamaño del archivo: " . display_size($filesize) . "<br>";
            echo "URL del archivo: <a href='{$fileurl}'>{$fileurl}</a><br>";
			echo "<hr/>";
			echo "<pre>";
			echo $file;
			echo "</pre>";
        }
    }
} else {
    echo "No hay ningún archivo subido aún.";
}

echo $OUTPUT->footer(); // Mostrar el pie de página de Moodle
