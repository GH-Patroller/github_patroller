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


function crear_repositorios()
{
    global $course; // Usa global para acceder a la variable $course
    echo "<h2>Crear Repositorios</h2>";

    echo "<br>Nombre de Materia: " . $course->shortname;
    echo "<br>Año: " . date("Y");
    echo "<br>Cuatrimestre: ";
    echo "<select name='cuatrimestre'>";
    echo "<option value='11'>1er Año - 1er Cuatrimestre (1º1º)</option>";
    echo "<option value='12'>1er Año - 2do Cuatrimestre (1º2º)</option>";
    echo "<option value='21'>2do Año - 1er Cuatrimestre (2º1º)</option>";
    echo "<option value='22'>2do Año - 2do Cuatrimestre (2º2º)</option>";
    echo "</select>";
    echo "<br>Sede:";
    echo "<br>Curso:";
    echo "<br>Grupos:";
}

function mostrar_alumnos_inscritos($context)
{
    global $DB; // Asegúrate de tener acceso global al DB si es necesario
    $enrolled_users = get_enrolled_users($context);

    // Comenzar la tabla
    echo '<table class="generaltable">';
    echo '<thead>';
    echo '<tr class="headerrow">';
    echo '<th>Usuario en Moodle</th>';
    echo '<th>Nombre Completo</th>';
    echo '<th>Correo</th>';
    echo '<th>Roles</th>';
    echo '<th>Grupo</th>'; // Nueva columna para el grupo
    echo '<th></th>'; // Nueva columna para el botón de guardar
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Iterar sobre cada usuario inscrito y agregar filas a la tabla
    foreach ($enrolled_users as $user) {
        // Obtener los roles del usuario en este contexto
        $roles = get_user_roles($context, $user->id, true);

        // Listar los roles en una cadena
        $role_names = array();
        foreach ($roles as $role) {
            $role_names[] = role_get_name($role, $context);
        }
        $roles_text = implode(', ', $role_names);

        // Crear la fila de la tabla
        echo '<tr>';
        echo '<td>' . $user->username . '</td>';
        echo '<td>' . $user->firstname . ' ' . $user->lastname . '</td>';
        echo '<td>' . $user->email . '</td>';
        echo '<td>' . $roles_text . '</td>';

        // Columna para el menú desplegable de grupo
        echo '<td>';
        echo '<select name="grupo_' . $user->id . '">';
        for ($i = 1; $i <= 10; $i++) {
            echo '<option value="' . $i . '">' . $i . '</option>';
        }
        echo '</select>';
        echo '</td>';

        // Columna para el botón de guardar
        echo '<td>';
        echo '<form method="post" action="guardar_grupo.php">';
        echo '<input type="hidden" name="userid" value="' . $user->id . '">';
        echo '<input type="hidden" name="courseid" value="' . $context->instanceid . '">';
        echo '<input type="submit" value="Guardar" class="btn btn-primary">';
        echo '</form>';
        echo '</td>';

        echo '</tr>';
    }

    // Cerrar la tabla
    echo '</tbody>';
    echo '</table>';
}

function mostrar_contributors_insights()
{
    global $DB; // Asegúrate de tener acceso global al DB
    // Mostrar los datos de la tabla data_patroller
    // Comenzar la tabla
    //DATOS MOCKEADOS (REEMPLAZAR CON UNA LLAMADA A LA TABLA REPOSITORIOS)
    $repositories = ['github_patroller'];
    echo "<h2>Datos de Alumnos </h2>";

    echo '<table class="generaltable">';
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

    foreach ($repositories as $repository) {
        $student_commits = get_all_commit_information_by_repo($repository);
        foreach ($student_commits as $student) {
            echo '<tr>';
            echo '<td>' . $student['commiter_github'] . '</td>';
            echo '<td>' . '' . '</td>';
            echo '<td>' . $student['last_commit'] . '</td>';
            echo '<td>' . $student['total_commits'] . '</td>';
            echo '<td>' . $student['total_added'] . '</td>';
            echo '<td>' . $student['total_deleted'] . '</td>';
            echo '<td>' . $student['total_modified'] . '</td>';
            echo '</tr>';
        }
    }
    // Cerrar la tabla
    echo '</tbody>';
    echo '</table>';
    echo "<hr>";
}

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

function get_all_commit_information_by_repo($repo = '')
{
    // Get owner and repo from the database (values stored in $pluginpatroller)
    $token = get_config('pluginpatroller', 'token_patroller');  // Fetch the GitHub token from plugin settings in the database
    $owner = get_config('pluginpatroller', 'owner_patroller');  // Obtenemos el valor 'owner' de la base de datos
    // BEGIN GitHub API Script

    //Establish date for commit retrieval
    $date = date('Y-m-d');

    // URL de la API de GitHub para obtener commits por repositorio
    $commits_url = 'https://api.github.com/search/commits?q=repo:' . $owner . '/' . $repo . '+author-date:<=' . $date . '+sort:author-date-desc';

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
        $commit_data_array = ["commiter_github" => "", "last_commit" => "", "total_commits" => 0, "total_added" => 0, "total_deleted" => 0, "total_modified" => 0];
        // Decodificar la respuesta JSON
        $commits_data = json_decode($commits_response, true);

        foreach ($commits_data['items'] as $commit) {
            $commiter_name = $commit['author']['login'];
            if (!array_key_exists($commiter_name, $commiter_array)) {
                $commiter_array[$commiter_name] = $commit_data_array;
                $commiter_array[$commiter_name]["commiter_github"] = $commiter_name;
                $commiter_array[$commiter_name]["last_commit"] = explode("T", $commit["commit"]["author"]["date"])[0];
            }


            $commit_detail_url = "https://api.github.com/repos/$owner/$repo/commits/" . $commit['sha'];
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
        return $commiter_array;
    }
}
