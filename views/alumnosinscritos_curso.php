<?php

function mostrar_alumnos_inscritos_curso($context)
{
    global $DB;

    echo '<h2>Lista de Alumnos Inscriptos a la materia</h2>';
    // Obtener todos los usuarios inscritos en el contexto actual
    $enrolled_users = get_enrolled_users($context);

    echo '<div>';

    // Selector de sede
    $options_sede = array(
        '' => get_string('all', 'moodle'), // Todos
        'YA' => 'YA',
        'BE' => 'BE'
    );
    echo '<label for="filterSede" style="margin-right: 15px;">' . get_string('filterbysede', 'pluginpatroller') . ':</label>';
    echo html_writer::select($options_sede, 'filterSede', '', null, array('id' => 'filterSede', 'onchange' => 'filterTable()', 'style' => 'margin-right: 55px; margin-left: 8px;'));

    

    // Selector de curso
    $options_curso = array(
        '' => get_string('all', 'moodle'), // Todos
        'A' => 'A',
        'B' => 'B',
        'C' => 'C'
    );
    echo '<label for="filterCurso" style="margin-right: 15px;">' . get_string('filterbycurso', 'pluginpatroller') . ':</label>';
    echo html_writer::select($options_curso, 'filterCurso', '', null, array('id' => 'filterCurso', 'onchange' => 'filterTable()', 'style' => 'margin-right: 55px; margin-left: 8px;'));

    echo '</div>';
    ?>
    <script>
        function filterTable() {
            var sedeFilter = document.getElementById('filterSede').value.toUpperCase();
            var cursoFilter = document.getElementById('filterCurso').value.toUpperCase();
            var table = document.getElementById("userTable");
            var tr = table.getElementsByTagName("tr");

            for (var i = 1; i < tr.length; i++) {
                var tdSede = tr[i].getElementsByTagName("td")[0];
                var tdCurso = tr[i].getElementsByTagName("td")[1];
                if (tdSede && tdCurso) {
                    var sedeValue = tdSede.textContent || tdSede.innerText;
                    var cursoValue = tdCurso.textContent || tdCurso.innerText;
                    if ((sedeFilter === "" || sedeValue.toUpperCase() === sedeFilter) &&
                        (cursoFilter === "" || cursoValue.toUpperCase() === cursoFilter)) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>

    <?php

    // Comenzar la tabla
    echo '<table class="generaltable" id="userTable">';
    echo '<thead>';
    echo '<tr class="headerrow">';
    echo '<th>Sede</th>';
    echo '<th>Curso</th>';
    echo '<th>Usuario en Moodle</th>';
    echo '<th>Nombre Completo</th>';
    echo '<th>Correo</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Iterar sobre cada usuario inscrito y agregar filas a la tabla
    foreach ($enrolled_users as $user) {
        // Obtener los roles del usuario en este contexto
        $roles = get_user_roles($context, $user->id, true);
        $has_student_role = false;

        // Verificar si el usuario tiene el rol de estudiante
        foreach ($roles as $role) {
            if ($role->shortname == "student") {
                $has_student_role = true;
                break;
            }
        }

        // Continuar solo si el usuario tiene el rol de estudiante
        if ($has_student_role) {
            $alumno = $DB->get_record('alumnos_data_patroller', ['id_alumno' => $user->id]);
            // Si el alumno no tiene un repositorio asignado, mostrarlo en la tabla
            if (!$alumno->id_repos) {
                // Crear la fila de la tabla
                echo '<tr>';
                echo '<td>' . $user->institution . '</td>';
                echo '<td>' . $user->department . '</td>';
                echo '<td>' . $user->username . '</td>';
                echo '<td>' . $user->firstname . ' ' . $user->lastname . '</td>';
                echo '<td>' . $user->email . '</td>';
                echo '</tr>';
            }
        }
    }

    // Cerrar la tabla
    echo '</tbody>';
    echo '</table>';
}
?>
