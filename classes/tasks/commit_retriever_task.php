<?php

class commit_retriever_task
{
    public function __construct() {}
    // public function get_name()
    // {
    //     return get_string('repo_retriever_task', 'mod_pluginpatroller');
    // }

    public function execute($repo = '')
    {
        global $DB;

        // $data = $this->get_custom_data();
        // $course_id = $data->course_id;
        // $installation_id = $data->installation_id;

        // Fetch the GitHub token from plugin settings in the database
        $token = get_config('pluginpatroller', 'github_token');
        // Get owner and repo from the database (values stored in $pluginpatroller)
        $owner = 'GHPatroller';  // Obtenemos el valor 'owner' de la base de datos

        // BEGIN GitHub API Script

        //Establish date for commit retrieval
        $date = date('Y-m-d');

        // URL de la API de GitHub para obtener commits por repositorio
        $commits_url = 'https://api.github.com/search/commits?q=repo:' . $owner . '/' . $repo . '+author-date:<=' . $date;
        echo $commits_url;

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
            $commit_data_array = ["total_commits" => 0, "total_added" => 0, "total_deleted" => 0, "total_modified" => 0];
            // Decodificar la respuesta JSON
            $commits_data = json_decode($commits_response, true);

            foreach ($commits_data['items'] as $commit) {
                $commiter_name = $commit['author']['login'];
                if (!array_key_exists($commiter_name, $commiter_array)) {
                    $commiter_array[$commiter_name] = $commit_data_array;
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
}
