<?php

require_once('../../config.php');
require_once('lib.php'); // Si tienes funciones específicas de tu plugin, aquí cargamos el archivo


global $DB, $OUTPUT, $PAGE; 

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

// Definir las pestañas
$tabrows = array();
$tabrows[] = new tabobject('tab1', new moodle_url('/mod/pluginpatroller/view.php', array('id' => $id, 'tab' => 'tab1')), 'Configuraciones');
$tabrows[] = new tabobject('tab2', new moodle_url('/mod/pluginpatroller/view.php', array('id' => $id, 'tab' => 'tab2')), 'Alumnos Inscritos');
$tabrows[] = new tabobject('tab3', new moodle_url('/mod/pluginpatroller/view.php', array('id' => $id, 'tab' => 'tab3')), 'Contributors Insights');


print_tabs(array($tabrows), optional_param('tab', 'tab1', PARAM_TEXT));

// Verificar valor del parámetro 'tab'
$tab = optional_param('tab', 'tab1', PARAM_TEXT);

// Contenido según la pestaña activa
switch ($tab) {
    case 'tab1':
        echo '<button type="button" class="btn btn-primary" onclick="location.href=\'crearrepositorios.php?id='.$id.'\'">Crear Repositorios</button>';   
        mostrar_configuraciones();
        break;
    case 'tab2':
        mostrar_alumnos_inscritos($context); // Asegúrate de pasar el contexto correctamente
        break;
    case 'tab3':
        mostrar_contributors_insights();
        break;
    default:
        echo "<p>Pestaña desconocida.</p>";
}

echo "<hr>";

function mostrar_alumnos_inscritos($context) {
    global $DB; // Asegúrate de tener acceso global al DB si es necesario
    $enrolled_users = get_enrolled_users($context);

    // Comenzar la tabla
    echo '<table class="generaltable">';
    echo '<thead>';
    echo '<tr class="headerrow">';
    echo '<th>Usuario en Moodle</th>';
    echo '<th>Nombre Completo</th>';
    echo '<th>Correo</th>';
    echo '<th>Roles</th>';
    echo '<th>Grupo</th>'; // Nueva columna para el grupo
    echo '<th></th>'; // Nueva columna para el botón de guardar
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Iterar sobre cada usuario inscrito y agregar filas a la tabla
    foreach ($enrolled_users as $user) {
        // Obtener los roles del usuario en este contexto
        $roles = get_user_roles($context, $user->id, true);

        // Listar los roles en una cadena
        $role_names = array();
        foreach ($roles as $role) {
            $role_names[] = role_get_name($role, $context);
        }
        $roles_text = implode(', ', $role_names);

        // Crear la fila de la tabla
        echo '<tr>';
        echo '<td>' . $user->username . '</td>';
        echo '<td>' . $user->firstname . ' ' . $user->lastname . '</td>';
        echo '<td>' . $user->email . '</td>';
        echo '<td>' . $roles_text . '</td>';

        // Columna para el menú desplegable de grupo
        echo '<td>';
        echo '<select name="grupo_' . $user->id . '">';
        for ($i = 1; $i <= 10; $i++) {
            echo '<option value="' . $i . '">' . $i . '</option>';
        }
        echo '</select>';
        echo '</td>';

        // Columna para el botón de guardar
        echo '<td>';
        echo '<form method="post" action="guardar_grupo.php">';
        echo '<input type="hidden" name="userid" value="' . $user->id . '">';
        echo '<input type="hidden" name="courseid" value="' . $context->instanceid . '">';
        echo '<input type="submit" value="Guardar" class="btn btn-primary">';
        echo '</form>';
        echo '</td>';

        echo '</tr>';
    }

    // Cerrar la tabla
    echo '</tbody>';
    echo '</table>';
}

function mostrar_contributors_insights() {
    global $DB; // Asegúrate de tener acceso global al DB
    // Mostrar los datos de la tabla data_patroller
    echo "<h2>Datos de Alumnos </h2>";
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
}

function mostrar_configuraciones() {
    global $DB; // Asegúrate de tener acceso global al DB
    echo "<h2>Datos de Config Patroller</h2>";

    // Fetch the GitHub token from plugin settings in the database
    $token = get_config('pluginpatroller', 'token_patroller');
    $owner = get_config('pluginpatroller', 'owner_patroller');

    echo $token . "<br/>";
    echo $owner . "<br/>";
    echo "<hr/>";

    // Mostrar los datos de la tabla pluginpatroller
    echo "<h2>Datos de Instancia PluginPatroller</h2>";
    $pluginpatroller_records = $DB->get_records('pluginpatroller');
    foreach ($pluginpatroller_records as $record) {
        echo "<p>ID: {$record->id}</p>";
        echo "<p>Nombre: {$record->name}</p>";
        echo "<p>Formato de Introducción: {$record->introformat}</p>";
        echo "<p>Tiempo de Creación: " . date('d-m-Y H:i:s', $record->timecreated) . "</p>";
        echo "<p>Tiempo de Modificación: " . date('d-m-Y H:i:s', $record->timemodified) . "</p>";
        echo "<p>Última Actualización del API: {$record->apimodified}</p>";
        echo "<hr>";
    }
}

echo $OUTPUT->footer();

?>
