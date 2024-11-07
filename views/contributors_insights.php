<?php


function show_students_commits_table($context, $course)
{
    global $DB;
    $repositories = get_all_repositories_by_courseid($course->id);
    $owner = get_config('pluginpatroller', 'owner_patroller');
    $student_commits = [];
    $options_repos = array(
        'All' => 'Todos los Repositorios'
    );

    $selected_repo = isset($_GET['filterRepo']) ? $_GET['filterRepo'] : 'All';

    foreach ($repositories as $key => $value) {
        $options_repos[$key] = $value;
    };

    if ($_GET['filterRepo']) {
        update_commit_information($course->id);
        redirect(new moodle_url('/mod/pluginpatroller/view.php', array('id' => $context->instanceid, 'tab' => 'tab3', 'sucx' => 'true')));
    }

    if ($_GET['sucx']) {
        echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;">
		Se obtubieron los datos correctamente.
		</div>';
    }

    // Selector de curso
    echo '<div style="margin:15px">';
    echo '<form method="get" action="">
			<input type="hidden" name="id" value="' . $context->instanceid . '">
			<input type="hidden" name="tab" value="tab3">';

    echo '<label for="filterRepo" style="margin-right: 15px;">' . get_string('filterbyrepo', 'pluginpatroller') . ':</label>';
    echo html_writer::select($options_repos, 'filterRepo', $selected_repo, null, array(
        'id' => 'filterRepo',
        'onchange' => 'filterTable()',
        'style' => 'margin-right: 55px; margin-left: 8px;'
    ));

    echo '<button type="submit" class="btn btn-primary" style="margin-right: 15px">Traer Datos de GiHub</button>';

    echo '</form>';
    echo '</div>';




    foreach ($repositories as $key => $value) {
        $repo = [];
        $options_repos[$key] = $value;
        $repo = get_students_by_repoid($key, $course->id);
        foreach ($repo as $student) {
            $student->repoid = $key;
            $student->reponame = $value;
        }
        $student_commits = array_merge($student_commits, $repo);
    };


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
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($student_commits as $student) {

        echo '<tr>';
        echo "<td><a href='https://github.com/GHPatroller/{$student->reponame}/graphs/contributors' target='_blank'>" . htmlspecialchars($student->reponame) . "</a></td>";
        echo "<td><a href='https://github.com/GHPatroller/{$student->reponame}/commits?author={$student->alumno_github}' target='_blank'>" . gitlogo() . htmlspecialchars($student->alumno_github) . "</a></td>";
        echo '<td>' . $student->nombre_alumno . '</td>';
        echo '<td>' . $student->fecha_ultimo_commit . '</td>';
        echo '<td>' . $student->cantidad_commits . '</td>';
        echo '<td>' . $student->lineas_agregadas . '</td>';
        echo '<td>' . $student->lineas_eliminadas . '</td>';
        echo '<td>' . $student->lineas_modificadas . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '<hr/>';
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
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
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
