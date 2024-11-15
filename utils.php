<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.
require_once('../../config.php');
require_once('lib.php'); // Si tienes funciones específicas de tu plugin, aquí cargamos el archivo

defined('MOODLE_INTERNAL') || die();

function mostrar_configuraciones()
{
    global $DB; // Asegúrate de tener acceso global al DB
    echo "<h2>Datos de Config Patroller</h2>";

    // Fetch the GitHub token from plugin settings in the database
    $token = get_config('pluginpatroller', 'token_patroller');
    $owner = get_config('pluginpatroller', 'owner_patroller');

    echo $token . "<br/>";
    echo $owner . "<br/>";
    echo "<hr/>";

    // Mostrar los datos de la tabla pluginpatroller
    echo "<h2>Datos de Instancia PluginPatroller</h2>";
    $pluginpatroller_records = $DB->get_records('pluginpatroller');
    foreach ($pluginpatroller_records as $record) {
        echo "<p>ID: {$record->id}</p>";
        echo "<p>Nombre: {$record->name}</p>";
        echo "<p>Formato de Introducción: {$record->introformat}</p>";
        echo "<p>Tiempo de Creación: " . date('d-m-Y H:i:s', $record->timecreated) . "</p>";
        echo "<p>Tiempo de Modificación: " . date('d-m-Y H:i:s', $record->timemodified) . "</p>";
        echo "<p>Última Actualización del API: {$record->apimodified}</p>";
        echo "<hr>";
    }
}

function get_commit_information_by_repo_name($repo_id, $repo_name = '', $courseid, $last_update = '1970-01-01T01:00:00Z')
{
    global $DB;
    // Get owner and repo from the database (values stored in $pluginpatroller)
    $token = get_config('pluginpatroller', 'token_patroller');  // Fetch the GitHub token from plugin settings in the database
    $owner = get_config('pluginpatroller', 'owner_patroller');  // Obtenemos el valor 'owner' de la base de datos
    // BEGIN GitHub API Script

    //Establish date for commit retrieval
    $date = date('Y-m-d') . 'T' . date('H:i:s') . 'Z';

    // URL de la API de GitHub para obtener commits por repositorio
    $commits_url = 'https://api.github.com/search/commits?q=repo:' . $owner . '/' . $repo_name . '+author-date:' . $last_update . '..' . $date . '+sort:author-date-desc';

    // Iniciar cURL
    $ch_commits = curl_init($commits_url);
    // Configurar las opciones de cURL
    curl_setopt($ch_commits, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_commits, CURLOPT_HTTPHEADER, [
        'Accept: application/vnd.github+json',
        'Authorization: Bearer ' . $token,   // Usar el token aquí
        'User-Agent: GitHub-API-Request'    // GitHub requiere un "User-Agent" en la solicitud
    ]);


    // Ejecutar la solicitud y obtener la respuesta
    $commits_response = curl_exec($ch_commits);
	
    // Array para guardar datos de commiters
    $commiter_array = [];

    // Comprobar si hubo errores en la solicitud
    if (curl_errno($ch_commits)) {
        echo 'Error al obtener commits: ' . curl_error($ch_commits) . "<br>";
    } else {
        //Crear variables para organizacion de datos
        $commit_data_array = ["last_commit" => "", "total_commits" => 0, "total_added" => 0, "total_deleted" => 0, "total_modified" => 0];
        // Decodificar la respuesta JSON
        $commits_data = json_decode($commits_response, true);

        foreach ($commits_data['items'] as $commit) {
            $commiter_name = $commit['author']['login'];
            if (!array_key_exists($commiter_name, $commiter_array)) {
                $commiter_array[$commiter_name] = $commit_data_array;
                $commiter_array[$commiter_name]["last_commit"] = explode(".", $commit["commit"]["author"]["date"])[0];
            }


            $commit_detail_url = "https://api.github.com/repos/$owner/$repo_name/commits/" . $commit['sha'];
            $ch_commit_detail = curl_init($commit_detail_url);
            curl_setopt($ch_commit_detail, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_commit_detail, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token,
                'Accept: application/vnd.github+json',
                'User-Agent: GitHub-API-Request'
            ]);

            $commit_detail_response = curl_exec($ch_commit_detail);

            if (curl_errno($ch_commit_detail)) {
                echo 'Error al obtener detalles del commit: ' . curl_error($ch_commit_detail) . "<br>";
            } else {
                $commit_detail_data = json_decode($commit_detail_response, true);

                if (isset($commit_detail_data['stats'])) {
                    $commiter_array[$commiter_name]['total_commits'] += 1;
                    $commiter_array[$commiter_name]['total_added'] += $commit_detail_data['stats']['additions'];
                    $commiter_array[$commiter_name]['total_deleted'] += $commit_detail_data['stats']['deletions'];
                    $commiter_array[$commiter_name]['total_modified'] += $commit_detail_data['stats']['total'];
                }
            }

            curl_close($ch_commit_detail);
        }
        curl_close($ch_commits);

        //Actualizar los registros de los estudiantes con la información apropiada
        $students = get_students_by_repoid($repo_id, $courseid);
        foreach ($commiter_array as $commiter_name => $commiter) {
            foreach ($students as $student) {
                if ($commiter_name == $student->alumno_github && $student->id_repos == $repo_id) {
                    $DB->update_record(
                        'alumnos_data_patroller',
                        [
                            'id' => $student->id,
                            'fecha_ultimo_commit' => $commiter['last_commit'],
                            //'cantidad_commits' => $student->cantidad_commits + $commiter['total_commits'],
                            //'lineas_agregadas' => $student->lineas_agregadas + $commiter['total_added'],
                            //'lineas_eliminadas' => $student->lineas_eliminadas + $commiter['total_deleted'],
                            //'lineas_modificadas' => $student->lineas_modificadas + $commiter['total_modified']
                            'cantidad_commits' => $commiter['total_commits'],
                            'lineas_agregadas' => $commiter['total_added'],
                            'lineas_eliminadas' => $commiter['total_deleted'],
                            'lineas_modificadas' =>  $commiter['total_modified'],
                          ],
                        $bulk = false
                    );
                    break;
                }
            }
        }
        return $commiter_array;
    }
}

function get_all_repositories_by_courseid($courseid)
{
    global $DB;

    $repositorios = $DB->get_records('repos_data_patroller', array('id_materia' => $courseid));
    $resultado = [];
    foreach ($repositorios as $repositorio) {
        $resultado[$repositorio->id] = $repositorio->nombre_repo;
    }
    return $resultado;
}

function get_students_by_repoid($repo_id, $courseid)
{
    global $DB;

	$resultado = $DB->get_records('alumnos_data_patroller', ['id_repos' => $repo_id, 'id_materia' => $courseid]);

    return $resultado;
}


function create_repository_by_repo_name($repo_name = '')
{
    $result = (bool)true;
    // Fetch the GitHub token from plugin settings in the database
    $token = get_config('pluginpatroller', 'token_patroller');
    // Get owner and repo from the database (values stored in $pluginpatroller)
    $owner = get_config('pluginpatroller', 'owner_patroller');  // Obtenemos el valor 'owner' de la base de datos


    $request_body_format = '{
    "name": "%s",
    "visibility": "private",
    "auto_init": true
    }';
    // BEGIN GitHub API Script
    // URL de la API de GitHub para crear repositorio
    $repo_creation_url = 'https://api.github.com/orgs/' . $owner . '/repos';
    $request_body = sprintf($request_body_format, $repo_name);

    // Iniciar cURL
    $ch_repos = curl_init($repo_creation_url);
    // Configurar las opciones de cURL
    curl_setopt($ch_repos, CURLOPT_POSTFIELDS, $request_body);
    curl_setopt($ch_repos, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_repos, CURLOPT_HTTPHEADER, [
        'Accept: application/vnd.github+json',
        'Authorization: Bearer ' . $token,   // Usar el token aquí
        'User-Agent: GitHub-API-Request'    // GitHub requiere un "User-Agent" en la solicitud
    ]);

    // Ejecutar la solicitud y obtener la respuesta
    curl_exec($ch_repos);

    // Comprobar si hubo errores en la solicitud
    if (curl_errno($ch_repos)) {
        echo 'Error al obtener commits: ' . curl_error($ch_repos) . "<br>";
    }
    if (curl_getinfo($ch_repos, CURLINFO_HTTP_CODE) == 422) {
        $result = (bool)false;
    }
    curl_close($ch_repos);
    return $result;
}

function delete_repository($repo_name) {

    // Token de acceso de GitHub
	$token = get_config('pluginpatroller', 'token_patroller');
    // Get owner and repo from the database (values stored in $pluginpatroller)
    $owner = get_config('pluginpatroller', 'owner_patroller');  // Obtenemos el valor 'owner' de la base de datos

    
    // URL de la API para eliminar el repositorio
    $delete_url = 'https://api.github.com/repos/' . $owner . '/' . $repo_name;
    
    // Inicializar cURL
    $ch = curl_init($delete_url);

    // Configurar las opciones de cURL
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE'); // Solicitud DELETE
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: token ' . $token,
        'User-Agent: GitHub-API-Request' // GitHub requiere un "User-Agent" en la solicitud
    ]);

    // Ejecutar la solicitud y obtener la respuesta
    $response = curl_exec($ch);

    // Comprobar si hubo errores en la solicitud
    if (curl_errno($ch)) {
        echo 'Error al eliminar el repositorio: ' . curl_error($ch);
    } else {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code == 204) {
            echo "Repositorio eliminado exitosamente.";
        } else {
            echo "Error al eliminar el repositorio. Código de respuesta HTTP: " . $http_code;
            echo "<br>Respuesta: " . $response;
        }
    }

    // Cerrar cURL
    curl_close($ch);
}

function get_sede_by_user($course, $user) {

	$sede = null;
	$groups = groups_get_all_groups($course->id, $user->id);
	if (!empty($groups)) {
		foreach ($groups as $group) {
			$parts = explode('-', $group->name);
			if (count($parts) == 2) {
				$sede = $parts[0];
			}
		}
	}
	return $sede;
}

function get_grupo_by_user($course, $user) {

	$grupo = null;
	$groups = groups_get_all_groups($course->id, $user->id);
	if (!empty($groups)) {
		foreach ($groups as $group) {
			$parts = explode('-', $group->name);
			if (count($parts) == 2) {
				$grupo = substr($parts[1], -1);
			}
		}
	}
	return $grupo;
}

function get_all_cursos_by_courseid($courseid) {
    global $DB;

$cursos = [];
$groups = groups_get_all_groups($courseid);
    foreach ($groups as $group) {
    if (preg_match('/^(BE|YA)/', $group->name)) { // Filtra grupos que empiezan con "BE" o "YA"
        $last_letter = substr($group->name, -1); // Obtiene la última letra del nombre del grupo
        $cursos[$last_letter] = $last_letter;
    }
}
return $cursos;
}


function filter_sede_curso($course) {
    echo '<div style="margin:15px">';

    // Selector de sede
    $options_sede = array(
        '' => get_string('all', 'moodle'), // Todos
        'YA' => 'YA',
        'BE' => 'BE'
    );
    echo '<label for="filterSede" style="margin-right: 15px;">' . get_string('filterbysede', 'pluginpatroller') . ':</label>';
    echo html_writer::select($options_sede, 'filterSede', '', null, array('id' => 'filterSede', 'onchange' => 'filterTable()', 'style' => 'margin-right: 55px; margin-left: 8px;'));

    // Selector de curso
    $options_curso = array_merge(get_all_cursos_by_courseid($course->id), ["" => "All"]);
    echo '<label for="filterCurso" style="margin-right: 15px;">' . get_string('filterbycurso', 'pluginpatroller') . ':</label>';
    echo html_writer::select($options_curso, 'filterCurso', '', null, array('id' => 'filterCurso', 'onchange' => 'filterTable()', 'style' => 'margin-right: 55px; margin-left: 8px;'));

    echo '</div>';

    // JavaScript de la función filterTable
    echo '<script>
        function filterTable() {
            var sedeFilter = document.getElementById("filterSede").value.toUpperCase();
            var cursoFilter = document.getElementById("filterCurso").value.toUpperCase();
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
    </script>';
}

function invite_students_by_repo_name_list($repo_list, $courseid)
{
    global $DB;
    $student_list = [];
    $result = [];
    $owner = get_config('pluginpatroller', 'owner_patroller');
    $token = get_config('pluginpatroller', 'token_patroller');

    foreach ($repo_list as $repo_id => $repo_name) {
        $student_list = get_students_by_repoid($repo_id, $courseid);
        foreach ($student_list as $student) {
            if ($student->invitacion_enviada == 0) {
                $student_invitation_url = 'https://api.github.com/repos/' . $owner . '/' . $repo_name . '/collaborators/' . $student->alumno_github;
                // Iniciar cURL
                $ch = curl_init($student_invitation_url);
                // Configurar las opciones de cURL
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Accept: application/vnd.github+json',
                    'Content-Length: 0',
                    'Authorization: Bearer ' . $token,   // Usar el token aquí
                    'User-Agent: GitHub-API-Request'    // GitHub requiere un "User-Agent" en la solicitud
                ]);
                curl_exec($ch);

                if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 201 && curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 204) {
                    $result[$student->nombre_alumno] = $repo_name;
                } else {
                    $DB->update_record(
                        'alumnos_data_patroller',
                        ['id' => $student->id, 'invitacion_enviada' => 1],
                        $bulk = false
                    );
                }
                curl_close($ch);
            } else {
                if ($student->id_repos !== $repo_id) $result[$student->id . "-" . $student->nombre_alumno] = $repo_name;
            }
        }
    }
    return $result;
}