<?php

function mostrar_alumnos_inscritos($context) {
    global $DB;

    // Verificar si ya hay datos en la tabla alumnos_data_patroller
    $hay_datos = $DB->record_exists('alumnos_data_patroller', array());



    // Mostrar el botón solo si no hay datos en la tabla
    if (!$hay_datos) {
        echo '<form method="post" action="">
                <button type="submit" name="cargar_alumnos" class="btn btn-primary">Cargar Alumnos</button>
              </form>';
    }

    // Si se envía el formulario, insertar los alumnos en la tabla
    if (isset($_POST['cargar_alumnos'])) {
        $enrolled_users = get_enrolled_users($context);
        
        foreach ($enrolled_users as $user) {
            $data = new stdClass();
            $data->nombre_alumno = $user->firstname . ' ' . $user->lastname;
            $data->mail_alumno = $user->email;
            $DB->insert_record('alumnos_data_patroller', $data);
        }

        // Redirigir para evitar múltiples envíos del formulario al recargar la página
       
        redirect(new moodle_url('/mod/pluginpatroller/view.php', array('id' => $context->instanceid, 'tab' => 'tab2')));
    }

    // Mostrar la tabla de alumnos inscritos
    echo '<table class="generaltable">';
    echo '<thead>';
    echo '<tr class="headerrow">';
    echo '<th>Usuario en Moodle</th>';
    echo '<th>Correo</th>';
    echo '<th>Nombre GitHub</th>';
    echo '<th>Repositorio</th>'; 
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    // Recuperar los registros de la tabla alumnos_data_patroller
    $alumnos = $DB->get_records('alumnos_data_patroller');

    if ($alumnos) {
        foreach ($alumnos as $alumno) {
            echo '<tr>';
            echo '<td>' . $alumno->nombre_alumno . '</td>';
            echo '<td>' . $alumno->mail_alumno . '</td>';
            echo '<td>' . $alumno->alumno_github . '</td>';
            echo '<td>' . $alumno->id_repos . '</td>';
            
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="2">No hay alumnos inscritos en la base de datos.</td></tr>';
    }

    echo '</table>';
}

?>