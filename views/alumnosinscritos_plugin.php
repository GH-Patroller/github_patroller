<?php

function mostrar_alumnos_inscritos_plugin($context, $course)
{
    global $DB;
    $repositories = get_all_repositories_by_course_id($course->id);


    $options_repos = array(
        'All' => 'Todos los Repositorios',
    );



    echo '<h2>Envio de Invitaciones</h2>';

    if (isset($_GET['repository_selected'])) {
        if ($_GET['repository_selected'] == 'All') {
            $repo_list = $repositories;
        } else {
            $repo_list[$_GET['repository_selected']] = $repositories[$_GET['repository_selected']];
        }

        echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;">
    Las invitaciones se enviaron correctamente.
</div>';

        invite_students_by_repo_name_list($repo_list, $course->id);
    }

    foreach ($repositories as $key => $value) {
        $options_repos[$key] = $value;
    }

    echo '<div style="margin: 35px">';
    echo '<form method="get" action="">
			<input type="hidden" name="id" value="' . $context->instanceid . '">
			<input type="hidden" name="tab" value="tab2">
		<label for="repository_select">Selecionar Repositorio:      </label>
		' . html_writer::select($options_repos, 'repository_selected', '', null, array('id' => 'repository_select')) .
        '
            <button type="submit" class="btn btn-primary" style="margin-left: 25px">Enviar invitaciones</button>
		</form>
		';

    echo '</div>';

    echo "<hr style='margin-top: 50px'>";
    echo "<hr style='margin-bottom: 50px'>";

    echo '<h2>Lista de Alumnos Inscriptos a Github Patroller</h2>';
    filter_sede_curso($course);


    // Mostrar la tabla de alumnos inscritos
    echo '<form method="post" action="">';
    echo '<table class="generaltable" id="userTable">';
    echo '<thead>';
    echo '<tr class="headerrow">';
    echo '<th>Sede</th>';
    echo '<th>Curso</th>';
    echo '<th>Nombre Completo</th>';
    echo '<th>Usuario en GitHub</th>';  // Nombre GitHub editable
    echo '<th>Grupo</th>';  // Mostrar nombre del Grupo
    echo '<th>Repositorio</th>';  // Mostrar nombre del Repositorio
    echo '<th>Invitación enviada</th>';  // Mostrar nombre del Repositorio
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Recuperar los registros de la tabla alumnos_data_patroller
    $alumnos = $DB->get_records('alumnos_data_patroller', array('id_materia' => $course->id));
    $repositorios = $DB->get_records('repos_data_patroller', array('id_materia' => $course->id));


    if ($alumnos) {
        foreach ($alumnos as $alumno) {
            // Si el alumno tiene un repositorio asignado se muestra en la tabla
            if ($alumno->id_repos) {
                // Obtener los datos de la tabla repos_data_patroller
                $repo = $DB->get_record('repos_data_patroller', ['id' => $alumno->id_repos, 'id_materia' => $course->id], 'sede, curso, nombre_repo');
                $alumno_data = $DB->get_record('user', ['id' => $alumno->id_alumno]);
                $alumno_data->sede = get_sede_by_user($course, $alumno_data);
                $alumno_data->curso = get_grupo_by_user($course, $alumno_data);

                echo '<tr>';
                //echo '<td>' . (!isset($repo->sede)) ? "El repositorio no existe ": $repo->sede . '</td>';  // Mostrar la concatenación de sede y curso primero
                echo '<td>' . $repo->sede . '</td>';  // Mostrar la concatenación de sede y curso primero
                echo '<td>' . $repo->curso . '</td>';  // Mostrar la concatenación de sede y curso primero
                echo '<td>' . $alumno->nombre_alumno . '</td>';  // Mostrar el nombre del alumno

                // Hacer editable el campo de "Nombre GitHub"
                echo '<td>';
                echo '<input type="text" pattern="^[A-Za-z0-9]+(-?[A-Za-z0-9]+)*$"  title="Solo letras, números y guiones; debe empezar y terminar con un carácter alfanumérico" name="github[' . $alumno->id . ']" value="' . $alumno->alumno_github . '" />';

                echo '</td>';

                // Mostrar el nombre del Grupo (con desplegable)
                echo '<td>';
                echo '<select name="repositorio[' . $alumno->id . ']">';

                foreach ($repositorios as $repositorio) {
                    if ($repositorio->sede == $alumno_data->sede && $repositorio->curso == $alumno_data->curso) {
                        $selected = ($alumno->id_repos == $repositorio->id) ? 'selected' : '';
                        echo '<option value="' . $repositorio->id . '" ' . $selected . '>' . $repositorio->num_grupo . '</option>';
                    }
                }

                echo '<td>' . $repo->nombre_repo . '</td>';  // Mostrar El nombre del Repositorio

                if ($alumno->invitacion_enviada == 0) {

                    echo '<td>' . "La invitación no ha sido enviada" . '</td>';  // Mostrar el nombre del alumno

                } else {
                    echo '<td>' . "La invitación ha sido enviada correctamente" . '</td>';  // Mostrar el nombre del alumno
                }
                echo '</tr>';



                echo '</select>';
                echo '</td>';
            }
        }

        echo '</tbody>';
        echo '</table>';
        echo '<button type="submit" name="guardar_cambios" class="btn btn-primary">Guardar Cambios</button>';
        echo '</form>';
    } else {
        echo '<tr><td colspan="4">No hay alumnos inscritos en la base de datos.</td></tr>';
        echo '</tbody>';
        echo '</table>';
    }

    // Si se envía el formulario para guardar los cambios en "Nombre GitHub"
    if (isset($_POST['guardar_cambios'])) {
        $cambio_datos = false; // Variable para rastrear si hubo cambios

        // Actualización de nombres de GitHub
        foreach ($_POST['github'] as $key_alumnoid => $value_githubname) {
            $alumno_actual = $DB->get_record('alumnos_data_patroller', ['id' => $key_alumnoid, 'id_materia' => $course->id], 'alumno_github, invitacion_enviada');

            if ($alumno_actual->alumno_github != $value_githubname) {
                // Cambiar el nombre de usuario de GitHub y actualizar invitacion_enviada a "no enviada"
                $DB->set_field('alumnos_data_patroller', 'alumno_github', $value_githubname, ['id' => $key_alumnoid, 'id_materia' => $course->id]);
                $DB->set_field('alumnos_data_patroller', 'invitacion_enviada', 0, ['id' => $key_alumnoid, 'id_materia' => $course->id]);
                $cambio_datos = true;
            }
        }

        // Actualización de repositorios
        foreach ($_POST['repositorio'] as $alumno_id => $repo_id) {
            $alumno_actual = $DB->get_record('alumnos_data_patroller', ['id' => $alumno_id, 'id_materia' => $course->id], 'id_repos, invitacion_enviada');

            if ($alumno_actual->id_repos != $repo_id) {
                // Cambiar el repositorio y actualizar invitacion_enviada a "no enviada"
                $DB->set_field('alumnos_data_patroller', 'id_repos', $repo_id, ['id' => $alumno_id, 'id_materia' => $course->id]);
                $DB->set_field('alumnos_data_patroller', 'invitacion_enviada', 0, ['id' => $alumno_id, 'id_materia' => $course->id]);
                $cambio_datos = true;
            }
        }

        // Redirigir solo si se hicieron cambios
        if ($cambio_datos) {
            redirect(new moodle_url('/mod/pluginpatroller/view.php', array('id' => $context->instanceid, 'tab' => 'tab2')), '', 0);
        }
    }
}
