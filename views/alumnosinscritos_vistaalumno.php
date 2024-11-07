<?php

function mostrar_alumnos_inscritos_plugin_alumno($context, $course)
{
    global $DB, $USER;

    // Verificar si ya hay datos en la tabla alumnos_data_patroller
    $hay_datos = $DB->record_exists('alumnos_data_patroller', array());

    // Obtener la lista de repositorios filtrados por sede y curso del usuario
    $repositorios = $DB->get_records('repos_data_patroller', [
        'sede' => get_sede_by_user($course, $USER),
        'curso' => get_grupo_by_user($course, $USER),
        'id_materia' => $course->id,

    ]);

    // Si se envía el formulario para guardar los datos seleccionados
    if (isset($_POST['guardar_repositorio'])) {
        $alumno_id = clean_param($_POST['alumno_id'], PARAM_INT);
        $repositorio_id = clean_param($_POST['repositorio'], PARAM_INT);
        $github_username = clean_param($_POST['github_username'], PARAM_TEXT);
        $cambio_datos = false;

        // Obtener los valores actuales de id_repos y alumno_github del alumno
        $alumno_actual = $DB->get_record('alumnos_data_patroller', ['id' => $alumno_id, 'id_materia' => $course->id], 'id_repos, alumno_github');

        // Comprobar si el repositorio o el nombre de GitHub han cambiado
        if ($alumno_actual->id_repos != $repositorio_id) {
            // Actualizar el repositorio y setear invitacion_enviada a 0
            $DB->set_field('alumnos_data_patroller', 'id_repos', $repositorio_id, ['id' => $alumno_id, 'id_materia' => $course->id]);
            $DB->set_field('alumnos_data_patroller', 'invitacion_enviada', 0, ['id' => $alumno_id, 'id_materia' => $course->id]);
            $cambio_datos = true;
        }

        if ($alumno_actual->alumno_github != $github_username) {
            // Actualizar el nombre de usuario de GitHub y setear invitacion_enviada a 0
            $DB->set_field('alumnos_data_patroller', 'alumno_github', $github_username, ['id' => $alumno_id, 'id_materia' => $course->id]);
            $DB->set_field('alumnos_data_patroller', 'invitacion_enviada', 0, ['id' => $alumno_id, 'id_materia' => $course->id]);
            $cambio_datos = true;
        }

        // Redirigir solo si hubo cambios
        if ($cambio_datos) {
            redirect(new moodle_url('/mod/pluginpatroller/view.php', ['id' => $context->instanceid, 'tab' => 'tab2', 'state' => 'true']), '', 0);
        }
    }

    if ($_GET['state']) {
        echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;">
		Los datos se actualizaron correctamente
		</div>';
    }

    // Mostrar la tabla de alumnos inscritos
    echo '<table class="generaltable">';
    echo '<thead>';
    echo '<tr class="headerrow">';
    echo '<th>Sede y Curso</th>';  // Mostrar los campos de Sede y Curso
    echo '<th>Nombre Completo</th>';
    echo '<th>Nombre GitHub</th>';  // Campo editable solo para el usuario actual
    echo '<th>Grupo</th>';          // Desplegable editable solo para el usuario actual
    echo '<th>Guardar</th>';        // Botón para guardar solo para el usuario actual
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Recuperar los registros de la tabla alumnos_data_patroller
    $alumnos = $DB->get_records('alumnos_data_patroller', array('id_materia' => $course->id));
    if ($alumnos) {
        $mi_sede = get_sede_by_user($course, $USER);
        $mi_curso = get_grupo_by_user($course, $USER);
        foreach ($alumnos as $alumno) {
            $alumno_data = $DB->get_record('user', ['id' => $alumno->id_alumno]);
            // Verificar si el usuario actual pertenece a la misma Sede y Curso
            $sede_alumno = get_sede_by_user($course, $alumno_data);
            $curso_alumno = get_grupo_by_user($course, $alumno_data);

            if ($sede_alumno == $mi_sede && $curso_alumno == $mi_curso) {
                echo '<tr>';
                echo '<td>' . $sede_alumno . ' - ' . $curso_alumno . '</td>';  // Concatenar Sede y Curso
                echo '<td>' . $alumno->nombre_alumno . '</td>';

                // Comprobar si el usuario actual está visualizando su propio registro
                if ($alumno->id_alumno == $USER->id) {
                    // Permitir que el usuario edite su propio registro
                    echo '<td>';
                    echo '<form method="post" action="">';
                    echo '<input type="text" pattern="^[A-Za-z0-9]+(-?[A-Za-z0-9]+)*$"  title="Solo letras, números y guiones; debe empezar y terminar con un carácter alfanumérico" name="github_username" value="' . $alumno->alumno_github . '" />';
                    echo '</td>';

                    // Crear el campo desplegable para seleccionar el repositorio
                    echo '<td>';
                    echo '<select name="repositorio">';
                    foreach ($repositorios as $repo) {
                        $selected = ($alumno->id_repos == $repo->id) ? 'selected' : '';
                        echo '<option value="' . $repo->id . '" ' . $selected . '>' . $repo->num_grupo . '</option>';
                    }
                    echo '</select>';
                    echo '</td>';

                    // Botón de "Guardar" solo para el usuario actual
                    echo '<td>';
                    echo '<input type="hidden" name="alumno_id" value="' . $alumno->id . '" />';
                    echo '<button type="submit" name="guardar_repositorio" class="btn btn-primary">Guardar</button>';
                    echo '</form>';
                    echo '</td>';
                } else {
                    // Si no es el usuario actual, mostrar los datos pero sin permitir la edición
                    echo '<td>' . $alumno->alumno_github . '</td>';
                    echo '<td>';

                    // Mostrar el repositorio sin la opción de editar
                    $repo = $DB->get_record('repos_data_patroller', ['id' => $alumno->id_repos], 'nombre_repo');
                    if ($repo) {
                        echo $repo->nombre_repo;
                    } else {
                        echo 'Repositorio no asignado';
                    }

                    echo '</td>';
                    echo '<td></td>';  // Sin botón de guardar para los otros usuarios
                }

                echo '</tr>';
            }
        }
    } else {
        echo '<tr><td colspan="5">No hay alumnos inscritos en la base de datos.</td></tr>';
    }

    echo '</tbody>';
    echo '</table>';
}
?>