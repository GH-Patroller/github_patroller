<?php


function show_students_commits_table($context, $course, $plugin_instance)
{
    global $DB;
    $repositories = get_all_repositories_by_course_id($course->id);
    $student_commits = [];
    $options_repos = array(
        'All' => 'Todos los Repositorios'
    );



    $selected_repo = isset($_GET['filterRepo']) ? $_GET['filterRepo'] : 'All';

    foreach ($repositories as $key => $value) {
        $options_repos[$key] = $value;
    };

    if ($_GET['update']) {
        echo '<div style="background-color: #add8e6; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;">
		Este proceso puede tomar unos minutos, por favor aguarde un momento.
		</div>';
        update_commit_information($course->id, $plugin_instance->id);
        redirect(new moodle_url('/mod/pluginpatroller/view.php', array('id' => $context->instanceid, 'tab' => 'tab3', 'sucx' => 'true')));
    }


    if ($_GET['sucx']) {
        echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;">
		Se obtuvieron los datos correctamente.
		</div>';
        redirect(new moodle_url('/mod/pluginpatroller/view.php', array('id' => $context->instanceid, 'tab' => 'tab3')));
    }

    //Actualizar commits
    echo '<form method="get" action="">
    <input type="hidden" name="id" value="' . $context->instanceid . '">
    <input type="hidden" name="tab" value="tab3">
    <input type="hidden" name="update" value="true">';

    echo '<div style="display: flex; justify-content: space-around; max-width: 500px;">';
    echo '<p>Última actualización: ' . str_replace(array("T", "-03:00"), " ", $plugin_instance->last_api_refresh) . '</p>';
    echo '<button type="submit" class="btn btn-primary" style="margin-right: 15px">Actualizar</button>';
    echo '</div>';
    echo '</form>';

    // Selector de curso
    echo '<div style="margin:15px; display: flex; justify-content: space-around; max-width: 700px;">';
    echo '<div>';
    echo '<form method="get" action="">
			<input type="hidden" name="id" value="' . $context->instanceid . '">
			<input type="hidden" name="tab" value="tab3">';

    echo '<label for="filterRepo" style="margin-right: 15px;">' . get_string('filterbyrepo', 'pluginpatroller') . ':</label>';
    echo html_writer::select($options_repos, 'filterRepo', $selected_repo, null, array(
        'id' => 'filterRepo',
        'onchange' => 'filterTable()',
        'style' => 'margin-right: 55px; margin-left: 8px;'
    ));

    echo '</div>';

    echo '</form>';
    echo '<p id="averageCommits">Commits promedio por estudiante: -</p>';
    echo '</div>';




    foreach ($repositories as $key => $value) {
        $repo = [];
        $options_repos[$key] = $value;
        $repo = get_students_by_repoid_and_course_id($key, $course->id);
        foreach ($repo as $student) {
            $student->repoid = $key;
            $student->reponame = $value;
        }
        $student_commits = array_merge($student_commits, $repo);
    };

    echo '<form method="post" action="">';
    echo '<table class="generaltable" id="repoTable">';
    echo '<thead>';
    echo '<tr class="headerrow">';
    echo '<th>Repositorio</th>';
    echo '<th>Usuario de Github</th>';
    echo '<th>Nombre Completo</th>';
    echo '<th>Fecha del Ultimo commit</th>';
    echo '<th>Cantidad de commits</th>';
    echo '<th>Líneas Agregadas</th>';
    echo '<th>Líneas eliminadas</th>';
    echo '<th>Líneas modificadas</th>';
    echo '<th>Calificación</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($student_commits as $student) {
        if ($student->invitacion_enviada) {
            echo '<tr>';
            echo "<td><a href='https://github.com/GHPatroller/{$student->reponame}/graphs/contributors' target='_blank'>" . htmlspecialchars($student->reponame) . "</a></td>";
            echo "<td><a href='https://github.com/GHPatroller/{$student->reponame}/commits?author={$student->alumno_github}' target='_blank'>" . gitlogo() . htmlspecialchars($student->alumno_github) . "</a></td>";
            echo '<td>' . htmlspecialchars($student->nombre_alumno) . '</td>';
            echo '<td>' . htmlspecialchars(str_replace("T", " ", $student->fecha_ultimo_commit)) . '</td>';
            echo '<td>' . htmlspecialchars($student->cantidad_commits) . '</td>';
            echo '<td>' . htmlspecialchars($student->lineas_agregadas) . '</td>';
            echo '<td>' . htmlspecialchars($student->lineas_eliminadas) . '</td>';
            echo '<td>' . htmlspecialchars($student->lineas_modificadas) . '</td>';

            // Menú desplegable de calificación
            echo '<td>';
            echo '<select name="calificacion[' . $student->id . ']">';

            // Opción predeterminada "Seleccionar" cuando la calificación está vacía
            $selected_default = empty($student->calificacion_alumno) ? 'selected' : '';
            echo "<option value='' $selected_default>Seleccionar nota</option>";

            // Opciones de calificación del 1 al 10
            for ($i = 1; $i <= 10; $i++) {
                $selected = ($student->calificacion_alumno == $i) ? 'selected' : '';
                echo "<option value='$i' $selected>$i</option>";
            }
            echo '</select>';
            echo '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody>';
    echo '</table>';
    echo '<button type="submit" name="guardar_calificaciones" class="btn btn-primary">Guardar Calificaciones</button>';
    echo '</form>';

    // Procesar el formulario y guardar las calificaciones
    if (isset($_POST['guardar_calificaciones'])) {
        $cambio_datos = false; // Variable para rastrear si hubo cambios

        // Actualizar calificaciones
        foreach ($_POST['calificacion'] as $student_id => $calificacion) {
            $alumno_actual = $DB->get_record('alumnos_data_patroller', ['id' => $student_id, 'id_materia' => $course->id], 'calificacion_alumno, id_alumno');

            // Si la calificación actual es diferente de la nueva o si es vacía, actualiza
            if ($alumno_actual->calificacion_alumno != $calificacion) {
                if ($calificacion === '') {
                    // Asigna NULL si el valor es vacío
                    $DB->set_field('alumnos_data_patroller', 'calificacion_alumno', null, ['id' => $student_id, 'id_materia' => $course->id]);
                } else {
                    // Asigna el valor seleccionado
                    $DB->set_field('alumnos_data_patroller', 'calificacion_alumno', $calificacion, ['id' => $student_id, 'id_materia' => $course->id]);
                }
                $cambio_datos = true;

                // Obtener el elemento de calificación en Moodle Gradebook
                $resultado = $DB->get_record('grade_items', ['courseid' => $course->id, 'itemname' => $plugin_instance->name]);

                $grade = new stdClass();
                $grade->userid = $alumno_actual->id_alumno;
                $grade->rawgrade = $calificacion === '' ? null : $calificacion; // Asigna null si la calificación está vacía

                $source = 'mod/pluginpatroller';
                $courseid = $resultado->courseid;
                $itemtype = $resultado->itemtype;
                $itemmodule = $resultado->itemmodule;
                $iteminstance = $resultado->iteminstance;
                $itemnumber = $resultado->itemnumber;

                // Actualiza la calificación en Moodle Gradebook
                grade_update($source, $courseid, $itemtype, $itemmodule, $iteminstance, $itemnumber, $grade);
            }
        }

        // Redirigir si se hicieron cambios
        if ($cambio_datos) {
            redirect(new moodle_url('/mod/pluginpatroller/view.php', array('id' => $context->instanceid, 'tab' => 'tab3')), '', 0);
        }
    }
}
// JavaScript de la función filterTable
echo '<script>
    function filterTable() {
        // Crear el mapeo dinámico desde el <select>
        var selectElement = document.getElementById("filterRepo");
        var repoMapping = {};

        for (var i = 0; i < selectElement.options.length; i++) {
            var key = selectElement.options[i].value;
            var value = selectElement.options[i].text;
            repoMapping[key] = value;
        }

        //Creamos variables para calcular el promedio de commits por estudiante
        var averageCommitText = document.getElementById("averageCommits");
        var totalCommits = 0;
        var totalStudentsShown = 0;
        
        //Creamos variables para filtrar la table
        var cursoFilter = selectElement.value; // Obtiene el valor seleccionado
        var table = document.getElementById("repoTable");
        var tr = table.getElementsByTagName("tr");

        // Recorre todas las filas de la tabla para aplicar el filtro
        for (var i = 1; i < tr.length; i++) {
            var tdRepo = tr[i].getElementsByTagName("td")[0]; // Primera columna (Repo)
            if (tdRepo) {
                var repoValue = tdRepo.textContent || tdRepo.innerText;
                
                // Si "All" está seleccionado o coincide con el nombre del repositorio, muestra la fila
                if (cursoFilter === "All" || repoValue === repoMapping[cursoFilter]) {
                    tr[i].style.display = "";
                    totalCommits += parseInt(tr[i].getElementsByTagName("td")[4].innerHTML); //Sumamos el campo de "Cantidad de Commits" al total
                    totalStudentsShown++ //Sumamos 1 a la cantidad total de estudiantes mostrados
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
        //Actualizamos el promedio de commits si la cantidad de alumnos asignados al repo es diferente de 0 
        averageCommitText.innerText = "Commits promedio por estudiante: " + (totalStudentsShown != 0 ? totalCommits/totalStudentsShown : 0);
    }
	
	    // Ejecutar filterTable al cargar la página si hay un valor en el select
    window.onload = function() {
        var selectedValue = document.getElementById("filterRepo").value;
        if (selectedValue !== "") { // Si no es "All" o vacío
            filterTable();
        }
    };
</script>
';


function gitlogo()
{
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"  style="margin-right: 8px;">
  <path fill="currentColor" d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.438 9.799 8.207 11.387.6.11.793-.261.793-.58v-2.174c-3.338.726-4.043-1.61-4.043-1.61-.546-1.386-1.333-1.756-1.333-1.756-1.09-.744.082-.729.082-.729 1.205.084 1.84 1.236 1.84 1.236 1.07 1.832 2.809 1.302 3.495.996.108-.776.418-1.302.761-1.601-2.665-.305-5.466-1.333-5.466-5.932 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.3 1.23a11.45 11.45 0 013.003-.403c1.018.005 2.042.138 3.003.403 2.29-1.552 3.296-1.23 3.296-1.23.654 1.653.243 2.874.119 3.176.77.84 1.236 1.91 1.236 3.221 0 4.61-2.807 5.624-5.48 5.922.43.372.815 1.1.815 2.22v3.293c0 .321.192.694.801.577C20.565 21.795 24 17.298 24 12 24 5.37 18.63 0 12 0z"/>
</svg>';
}
