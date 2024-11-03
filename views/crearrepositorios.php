<?php

//git checkout -b "GHP-9 ADD:sdsdsd"
function formulario ($course, $id, $context) {
	global $DB;

	$enrolled_users = get_enrolled_users($context);

	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_repositorios'])) {
		
		$enrolled_users = get_enrolled_users($context);

		// Agrupar los alumnos por sede y curso
		$totalAlumnos = [];
		foreach ($enrolled_users as $alumno) {

			// Obtener los roles del usuario en este contexto
			$roles = get_user_roles($context, $alumno->id, true);

			// Listar los roles en una cadena
			if (is_array($roles) && count($roles) > 0 && count($roles) < 2) {
				foreach ($roles as $role) {
					if (role_get_name($role, $context) == "Student") {
						//echo "<br>".$alumno->firstname." > ".role_get_name($role, $context);
						$sede = strtoupper(substr($alumno->institution, 0, 3)); // YAT o BEL
						$curso = strtoupper($alumno->department); // A, B, C, etc.
						$clave = "$sede-$curso";

						if (!isset($totalAlumnos[$clave])) {
							$totalAlumnos[$clave] = 0;
						}
						$totalAlumnos[$clave] ++;
					}
				}
			}

		}	



		// Generar los códigos para los repositorios
		$anio = date("Y"); // Año en curso (2024)
		$materia = $course->shortname; // Nombre de la materia (Matemáticas)

		$codigos = [];
		foreach ($totalAlumnos as $clave => $valor) {
			$totalGrupos = (int) ceil($valor / 4); // Dividir los alumnos en grupos de 4

			for ($i = 1; $i <= $totalGrupos; $i++) {
				$codigo = "PNT2-$anio-$clave-$i";
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

			// Insertar en la base de datos
			$result = create_repository_by_repo_name($data->nombre_repo);
			if($result){
				$DB->insert_record('repos_data_patroller', $data);
			} 
		}
		
		redirect(new moodle_url('/mod/pluginpatroller/view.php', array('id' => $context->instanceid, 'tab' => 'tab1')));
	} else {



		// Agrupar los alumnos por sede y curso
	$totalAlumnos = [];
	foreach ($enrolled_users as $alumno) {

		// Obtener los roles del usuario en este contexto
		$roles = get_user_roles($context, $alumno->id, true);

		// Listar los roles en una cadena
		if (is_array($roles) && count($roles) > 0 && count($roles) < 2) {
			foreach ($roles as $role) {
				if (role_get_name($role, $context) == "Student") {
					//echo "<br>".$alumno->firstname." > ".role_get_name($role, $context);
		$sede = strtoupper(substr($alumno->institution, 0, 3)); // YAT o BEL
		$curso = strtoupper($alumno->department); // A, B, C, etc.
		$clave = "$sede-$curso";

		if (!isset($totalAlumnos[$clave])) {
			$totalAlumnos[$clave] = 0;
		}
		$totalAlumnos[$clave] ++;
				}
			}
		}
		
	}


		?>
		<br>Nombre de Materia:<span id="nombre_materia"><?php echo $course->shortname; ?>
	</span>
	<br>Año:<span id="ano"><?php echo date("Y"); ?>
		</span>
		
		<?php
		
		$data = $DB->get_records('repos_data_patroller');

    if ($data) {
		echo '<h2>Repositorios Creados </h2>';
		$anio = date("Y");
				$codigos = [];
		foreach ($totalAlumnos as $clave => $valor) {
			$totalGrupos = (int) ceil($valor / 4); // Dividir los alumnos en grupos de 4

			for ($i = 1; $i <= $totalGrupos; $i++) {
				$codigo = "PNT2-$anio-$clave-$i";
				$codigos[] = $codigo;
			}
		}
		
		echo "<table border='1' class='generaltable'>";
		echo "<tr><th>Sede-Curso</th><th>Cantidad de Alumnos</th><th>Cantidad de Grupos</th><th>Códigos de Repositorio</th></tr>";
		foreach ($totalAlumnos as $clave => $valor) {

			$totalGrupos = (int) ceil($valor / 4);
			$codigosPorGrupo = array_filter($codigos, function($codigo) use ($clave) {
				return strpos($codigo, $clave) !== false;
			});
			$codigosHtml = implode("<br>", $codigosPorGrupo);
			echo "<tr><td>$clave</td><td>$valor</td><td>$totalGrupos</td><td>$codigosHtml</td></tr>";
		}
		echo "</table>";
		
	}else{	
		echo "<h2>Repositorios a crear</h2>";
		echo "<table border='1' class='generaltable'>";
		echo "<thead><tr><th>Sede-Curso</th><th>Cantidad de Alumnos</th></tr></thead>";
		echo "<tbody>";
		foreach ($totalAlumnos as $clave => $cantidad) {
		echo "<tr><td>$clave</td><td>$cantidad</td></tr>";
		}
		echo "</tbody>";
		echo "</table>";
		

?>


<?php
		
?>
	<form method="post">
		<button type="submit" name="crear_repositorios" class="btn btn-primary">Crear Repositorios</button>
	</form>
<?php			
	}
	}
}


?>
