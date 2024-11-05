<?php

function formulario ($course, $id, $context, $execution_interval) {
		global $DB;

	$totalAlumnos = get_totalAlumnos($context, $course);
	
	//-------------------------------------------
	// Aptretó el boto de crear repositorio
	//-------------------------------------------
	

	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_repositorios'])) {

		crear_repositorios ($context, $course, $execution_interval);
		redirect(new moodle_url('/mod/pluginpatroller/view.php', array('id' => $context->instanceid, 'tab' => 'tab1')));


	//-------------------------------------------
	// Sinó Apreto el botón, ejecuta las tablas de vista
	//-------------------------------------------

	} else {
		$data = $DB->get_records('repos_data_patroller');

		echo "<br>Nombre de Materia: <span id='nombre_materia'>" . $course->shortname . "</span>";
		echo "<br>Año: <span id='ano'>" . date("Y") . "</span>";


		//-------------------------------------------
		// Primera vista, Los repositorios ya fueron creados
		//-------------------------------------------

	    if ($data) {
		// Ya existen repositorios Creados
			echo '<h2>Repositorios Creados </h2>';
			$records = $DB->get_records('repos_data_patroller', null, '', '*', 0, 50);
			// Verificar si hay registros
			if (!empty($records)) {
				echo "<table border='1' class='generaltable'>";
				echo "<thead><tr><th>ID</th><th>Sede</th><th>Curso</th><th>Num Grupo</th><th>Nombre Repo</th></tr></thead>";
				echo "<tbody>";
				
				// Recorrer los registros y mostrarlos en la tabla
				foreach ($records as $record) {
					echo "<tr>";
					echo "<td>" . htmlspecialchars($record->id) . "</td>";
					echo "<td>" . htmlspecialchars($record->sede) . "</td>";
					echo "<td>" . htmlspecialchars($record->curso) . "</td>";
					echo "<td>" . htmlspecialchars($record->num_grupo) . "</td>";
					echo "<td>" . htmlspecialchars($record->nombre_repo) . "</td>";
					echo "</tr>";
				}
				
				echo "</tbody>";
				echo "</table>";
			}

		//-------------------------------------------
		// Segunda vista, Los repositorios no existen
		//-------------------------------------------
				
		}else{	
			// Primera vez, no existe ningun repositorio creado
	

echo "<h2>Repositorios a crear</h2>";
    echo "<table border='1' class='generaltable'>";
    echo "<thead><tr><th>Sede-Curso</th><th>Cantidad de Alumnos</th><th>Repositorios</th></tr></thead>";
    echo "<tbody>";
    
    // Recorrer los registros, mostrar los datos y calcular repositorios
    foreach ($totalAlumnos as $clave => $cantidad) {
        $totalGrupos = (int) ceil($cantidad / $execution_interval); // Dividir los alumnos en grupos dependiendo del número mágico
        
        // Generar los repositorios
        $repositorios = [];
        for ($i = 1; $i <= $totalGrupos; $i++) {
            $repositorios[] = "$clave-G$i";
        }
        $repositoriosStr = implode('<br>', $repositorios);
        
        echo "<tr>";
        echo "<td>$clave</td>";
        echo "<td>$cantidad</td>";
        echo "<td>$repositoriosStr</td>";
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";





	
/*		
			echo "<h2>Repositorios a crear</h2>";
			echo "<table border='1' class='generaltable'>";
			echo "<thead><tr><th>Sede-Curso</th><th>Cantidad de Alumnos</th></tr></thead>";
			echo "<tbody>";
			foreach ($totalAlumnos as $clave => $cantidad) {
				echo "<tr><td>$clave</td><td>$cantidad</td></tr>";
			}
			echo "</tbody>";
			echo "</table>";
*/			
			echo '<form method="post">';
			echo '<button type="submit" name="crear_repositorios" class="btn btn-primary">Crear Repositorios</button>';
			echo '</form>';	
		}
	}
}


//-------------------------------------------
//                  Funciones
//-------------------------------------------

function crear_repositorios ($context, $course, $execution_interval) {
		global $DB;

	// Generar los códigos para los repositorios
	$anio = date("Y"); // Año en curso (2024)
	$totalAlumnos = get_totalAlumnos($context, $course);
	$parts = explode('-', $course->shortname);
	$materia = $parts[0]; // Nombre de la materia (Matemáticas)
	
	$codigos = [];
	foreach ($totalAlumnos as $clave => $valor) {
		$totalGrupos = (int) ceil($valor / $execution_interval); // Dividir los alumnos en grupos dependiendo del número mágico

		for ($i = 1; $i <= $totalGrupos; $i++) {
			$codigo = "$materia-$anio-$clave-$i";
			$codigos[] = $codigo;
		}
	}

	// Insertar los repositorios ficticios en la base de datos
	foreach ($codigos as $codigo) {
		$parts = explode('-', $codigo);
		$sede = $parts[2];
		$curso = $parts[3];
		$num_grupo = $parts[4];

		// Datos a insertar en la tabla data_patroller
		$data = new stdClass();
		$data->sede = $sede;
		$data->curso = $curso;
		$data->num_grupo = $num_grupo;
		$data->nombre_repo = $codigo;

		/*
		echo "<br>";
		echo $codigo;
		echo "<br>";
		*/
		
		// Insertar en la base de datos
		$DB->insert_record('repos_data_patroller', $data);
		
		// Insertar en github
		create_repository_by_repo_name($codigo);
	}

}
	
function get_totalAlumnos ($context, $course) {
	global $DB;
	
	$enrolled_users = get_enrolled_users($context);
	
	$parts = explode('-', $course->shortname);

	$materia = $parts[0];

	$totalAlumnos = [];
	foreach ($enrolled_users as $user) {
		// Obtener los grupos a los que pertenece el usuario
		
		$sede = get_sede_by_user($course, $user);
		$grupo = get_grupo_by_user($course, $user);
		$clave = "$sede-$grupo";
		
		if (isset($sede) && isset($grupo)) {
			if (!isset($totalAlumnos[$clave])) {
				$totalAlumnos[$clave] = 0;
			}
			$totalAlumnos[$clave] ++;					
		}
	}
	return $totalAlumnos;
}



?>
