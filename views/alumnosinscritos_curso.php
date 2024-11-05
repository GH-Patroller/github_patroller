<?php

function mostrar_alumnos_inscritos_curso($context, $course)
{
    global $DB;

    echo '<h2>Lista de Alumnos Inscriptos a la materia</h2>';
    // Obtener todos los usuarios inscritos en el contexto actual
    $enrolled_users = get_enrolled_users($context);

    filter_sede_curso($course);
    
    // Comenzar la tabla
    echo '<table class="generaltable" id="userTable">';
    echo '<thead>';
    echo '<tr class="headerrow">';
    echo '<th>Sede</th>';
    echo '<th>Curso</th>';
    echo '<th>Usuario en Moodle</th>';
    echo '<th>Nombre Completo</th>';
    echo '<th>Correo</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Iterar sobre cada usuario inscrito y agregar filas a la tabla
    foreach ($enrolled_users as $user) {
        // Obtener los roles del usuario en este contexto
        $roles = get_user_roles($context, $user->id, true);
        $has_student_role = false;

        // Verificar si el usuario tiene el rol de estudiante
        foreach ($roles as $role) {
            if ($role->shortname == "student") {
                $has_student_role = true;
                break;
            }
        }

        // Continuar solo si el usuario tiene el rol de estudiante
        if ($has_student_role) {
            $alumno = $DB->get_record('alumnos_data_patroller', ['id_alumno' => $user->id]);
            // Si el alumno no tiene un repositorio asignado, mostrarlo en la tabla
            if (!$alumno->id_repos) {
                // Crear la fila de la tabla
                echo '<tr>';
                echo '<td>' . get_sede_by_user($course, $user) . '</td>';
                echo '<td>' . get_grupo_by_user($course, $user) . '</td>';
                echo '<td>' . $user->username . '</td>';
                echo '<td>' . $user->firstname . ' ' . $user->lastname . '</td>';
                echo '<td>' . $user->email . '</td>';
                echo '</tr>';
            }
        }
    }

    // Cerrar la tabla
    echo '</tbody>';
    echo '</table>';
}
?>