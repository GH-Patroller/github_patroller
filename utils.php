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

function get_commit_information_by_repo_name($repo_name = '', $last_update = '1970-01-01T01:00:00Z')
{
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
        return $commiter_array;
    }
}

function get_all_repositories()
{
    global $DB;

    $repositorios = $DB->get_records('repos_data_patroller', array());
    $resultado = [];
    foreach ($repositorios as $repositorio) {
        $resultado[$repositorio->id] = $repositorio->nombre_repo;
    }
    return $resultado;
}

function get_students_by_repoid($repo_id)
{
    global $DB;

    $resultado = $DB->get_records(
        'alumnos_data_patroller',
        ['id_repos' => $repo_id],
        '',
        '*'
    );

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

function invite_students_by_repo_name_list($repo_list)
{
    global $DB;
    $student_list = [];
    $result = [];
    $owner = get_config('pluginpatroller', 'owner_patroller');
    $token = get_config('pluginpatroller', 'token_patroller');

    foreach ($repo_list as $repo_id => $repo_name) {
        $student_list = get_students_by_repoid($repo_id);
        foreach ($student_list as $student) {
            if ($student->invitacion_enviada == 0 || count($repo_list) == 1) {
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
