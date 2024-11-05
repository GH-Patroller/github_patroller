<?php


function show_students_commits_table($context)
{
    global $DB; // Asegúrate de tener acceso global al DB
    $repositories = get_all_repositories();
    $student_commits = [];
    $options_repos = array(
        'All' => 'Todos los Repositorios',
    );

	$selected_repo = isset($_GET['filterRepo']) ? $_GET['filterRepo'] : 'All';

	foreach ($repositories as $key => $value) {
        $options_repos[$key] = $value;
    };


	if ($_GET['filterRepo']) {
		if ($selected_repo == 'All')  {
			foreach($repositories as $key => $value){
				get_commit_information_by_repo_name($key, $value);
			}
		}else{
				get_commit_information_by_repo_name($selected_repo, $repositories[$selected_repo]);
		}
		
		echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;">
    Se obtubieron los datos correctamente.
	</div>';
	
		
    }


    // Selector de curso
    //$options_repo = array_merge($repositories, ["" => "All"]);
    echo '<div style="margin:15px">';
    echo '<form method="get" action="">
			<input type="hidden" name="id" value="' . $context->instanceid . '">
			<input type="hidden" name="tab" value="tab3">';

    echo '<label for="filterRepo" style="margin-right: 15px;">' . get_string('filterbyrepo', 'pluginpatroller') . ':</label>';
    //echo html_writer::select($options_repos, 'filterRepo', '', null, array('id' => 'filterRepo', 'onchange' => 'filterTable()', 'style' => 'margin-right: 55px; margin-left: 8px;'));
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
		$repo = get_students_by_repoid($key);
		foreach ($repo as $student) {
			$student->repoid = $key;
			$student->reponame = $value;
		}
//		echo "<pre>";
	//	var_dump($repo);
        $student_commits = array_merge($student_commits, $repo);


    };



    echo '<table class="generaltable" id="repoTable">';
    echo '<thead>';
    echo '<tr class="headerrow">';
    echo '<th>Repo</th>';
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
        echo '<td>' . $student->reponame . '</td>';
        echo '<td>' . $student->alumno_github . '</td>';
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