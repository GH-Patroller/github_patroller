<?php

function mostrar_alumnos_inscritos_plugin_alumno($context)
{
    global $DB, $USER;

    // Verificar si ya hay datos en la tabla alumnos_data_patroller
    $hay_datos = $DB->record_exists('alumnos_data_patroller', array());

    // Obtener la lista de repositorios filtrados por sede y curso del usuario
    $repositorios = $DB->get_records('repos_data_patroller', [
        'sede' => $USER->institution,
        'curso' => $USER->department
    ]);

    // Si se envía el formulario para guardar los datos seleccionados
    if (isset($_POST['guardar_repositorio'])) {
        $alumno_id = clean_param($_POST['alumno_id'], PARAM_INT);
        $repositorio_id = clean_param($_POST['repositorio'], PARAM_INT);
        $github_username = clean_param($_POST['github_username'], PARAM_TEXT);

        // Obtener el valor actual de id_repos del alumno
        $alumno_actual = $DB->get_record('alumnos_data_patroller', ['id' => $alumno_id], 'id_repos, alumno_github');

        // Comprobar si hubo un cambio en id_repos
        $cambio_repos = ($alumno_actual->id_repos != $repositorio_id);

        // Guardar los datos en la base de datos
        if (
            $DB->set_field('alumnos_data_patroller', 'id_repos', $repositorio_id, ['id' => $alumno_id]) &&
            $DB->set_field('alumnos_data_patroller', 'alumno_github', $github_username, ['id' => $alumno_id])
        ) {

            // Setear invitacion_enviada a 0 solo si hubo un cambio en id_repos
            if ($cambio_repos) {
                $DB->set_field('alumnos_data_patroller', 'invitacion_enviada', 0, ['id' => $alumno_id]);
            }

            // Redirigir para reflejar los cambios
            redirect(new moodle_url('/mod/pluginpatroller/view.php', ['id' => $context->instanceid, 'tab' => 'tab2']), '', 0);
        } else {
            print_error('update_failed', 'pluginpatroller');
        }
    }


    // Mostrar la tabla de alumnos inscritos
    echo '<table class="generaltable">';
    echo '<thead>';
    echo '<tr class="headerrow">';
    echo '<th>Sede y Curso</th>';  // Mostrar los campos de Sede y Curso
    echo '<th>Usuario en Moodle</th>';
    echo '<th>Nombre GitHub</th>';  // Campo editable solo para el usuario actual
    echo '<th>Grupo</th>';          // Desplegable editable solo para el usuario actual
    echo '<th>Guardar</th>';        // Botón para guardar solo para el usuario actual
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Recuperar los registros de la tabla alumnos_data_patroller
    $alumnos = $DB->get_records('alumnos_data_patroller');
    if ($alumnos) {
        foreach ($alumnos as $alumno) {
            $alumno_data = $DB->get_record('user', ['id' => $alumno->id_alumno]);
            // Verificar si el usuario actual pertenece a la misma Sede y Curso
            if ($alumno_data->institution == $USER->institution && $alumno_data->department == $USER->department) {
                echo '<tr>';
                echo '<td>' . $alumno_data->institution . ' - ' . $alumno_data->department . '</td>';  // Concatenar Sede y Curso
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