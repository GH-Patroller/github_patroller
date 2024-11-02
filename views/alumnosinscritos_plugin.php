<?php

function mostrar_alumnos_inscritos_plugin($context)
{
    global $DB;

    echo '<h2>Lista de Alumnos Inscriptos a Github Patroller</h2>';

    //Botón para enviar invitaciones
    echo '<form method="post" action="URL_DEL_ARCHIVO" style="display:inline;">';
    echo '<button type="submit" class="btn btn-primary">Enviar invitaciones</button>';
    echo '</form>';

    // Verificar si ya hay datos en la tabla alumnos_data_patroller
    $hay_datos = $DB->record_exists('alumnos_data_patroller', array());
    $repositorios = $DB->get_records('repos_data_patroller');

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
            $data->id_alumno = $user->id;
            $DB->insert_record('alumnos_data_patroller', $data);
        }

        // Redirigir para evitar múltiples envíos del formulario al recargar la página
        redirect(new moodle_url('/mod/pluginpatroller/view.php', array('id' => $context->instanceid, 'tab' => 'tab2')));
    }

    // Mostrar la tabla de alumnos inscritos
    echo '<form method="post" action="">';
    echo '<table class="generaltable">';
    echo '<thead>';
    echo '<tr class="headerrow">';
    echo '<th>Sede y Curso</th>';  // Mostrar primero "Sede y Curso"
    echo '<th>Nombre y Apellido</th>';
    echo '<th>Usuario en GitHub</th>';  // Nombre GitHub editable
    echo '<th>Grupo</th>';  // Mostrar nombre del Grupo
    echo '<th>Repositorio</th>';  // Mostrar nombre del Repositorio
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Recuperar los registros de la tabla alumnos_data_patroller
    $alumnos = $DB->get_records('alumnos_data_patroller');

    if ($alumnos) {
        foreach ($alumnos as $alumno) {
            // Si el alumno tiene un repositorio asignado se muestra en la tabla
            if ($alumno->id_repos) {
                // Obtener los datos de la tabla repos_data_patroller
                $repo = $DB->get_record('repos_data_patroller', ['id' => $alumno->id_repos], 'sede, curso, nombre_repo');
                $alumno_data = $DB->get_record('user', ['id' => $alumno->id_alumno], 'institution, department');
                $sede_curso = '';
                if (!empty($alumno->id_repos)) {
                    // Obtener sede, curso y nombre del repositorio
                    if ($repo) {
                        // Concatenar sede y curso
                        $sede_curso = $repo->sede . ' - ' . $repo->curso;
                        $repo_nombre = $repo->nombre_repo;
                    } else {
                        $sede_curso = 'Sede y curso no asignados';
                        $repo_nombre = 'Repositorio no asignado';
                    }
                }


                echo '<tr>';
                echo '<td>' . $sede_curso . '</td>';  // Mostrar la concatenación de sede y curso primero
                echo '<td>' . $alumno->nombre_alumno . '</td>';  // Mostrar el nombre del alumno

                // Hacer editable el campo de "Nombre GitHub"
                echo '<td>';
                echo '<input type="text" pattern="^[A-Za-z0-9]+(-?[A-Za-z0-9]+)*$"  title="Solo letras, números y guiones; debe empezar y terminar con un carácter alfanumérico" name="github[' . $alumno->id . ']" value="' . $alumno->alumno_github . '" />';

                echo '</td>';

                // Mostrar el nombre del Grupo (con desplegable)
                echo '<td>';
                echo '<select name="repositorio[' . $alumno->id . ']">';

                foreach ($repositorios as $repo) {
                    if ($repo->sede == $alumno_data->institution && $repo->curso == $alumno_data->department) {
                        $selected = ($alumno->id_repos == $repo->id) ? 'selected' : '';
                        echo '<option value="' . $repo->id . '" ' . $selected . '>' . $repo->num_grupo . '</option>';
                    }
                }

                echo '<td>' . $repo_nombre . '</td>';  // Mostrar El nombre del Repositorio

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
        echo "<pre>";
        var_dump($_POST);
        foreach ($_POST['github'] as $key_alumnoid => $value_githubname) {
            $DB->set_field('alumnos_data_patroller', 'alumno_github', $value_githubname, ['id' => $key_alumnoid]);

        }
        foreach ($_POST['repositorio'] as $alumno_id => $repo_id) {
            $DB->set_field('alumnos_data_patroller', 'id_repos', $repo_id, ['id' => $alumno_id]);

        }

        // Redirigir para reflejar los cambios
        redirect(new moodle_url('/mod/pluginpatroller/view.php', array('id' => $context->instanceid, 'tab' => 'tab2')), '', 0);

    }
}
?>