<?php


function show_students_commits_table($context)
{
    global $DB; // Asegúrate de tener acceso global al DB
    $repositories = get_all_repositories();
    $student_commits = [];
    $options_repos = array(
        '' => '-- select a repository --',
    );


    foreach ($repositories as $key => $value) {
        $options_repos[$key] = $value;
    };


    echo '<div>';
    echo '<form method="get" action="">
			<input type="hidden" name="id" value="' . $context->instanceid . '">
			<input type="hidden" name="tab" value="tab3">
            <button type="submit" class="btn btn-primary">SUBMIT REPO</button>
		<label for="repository_select"> Repositorio:      </label>
		' . html_writer::select($options_repos, 'repository_selected', '', null, array('id' => 'repository_select')) .
        '</form>';

    if (isset($_GET['repository_selected'])) {
        echo "<pre>";
        $student_commits = get_student_by_repoid($_GET['repository_selected']);
    }
    echo '</div>';

    echo '<table class="generaltable" id="commit_table">';
    echo '<thead>';
    echo '<tr class="headerrow">';
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
