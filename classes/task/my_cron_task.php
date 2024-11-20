<?php
namespace mod_pluginpatroller\task;

defined('MOODLE_INTERNAL') || die();

//require_once('../../../../config.php');
//require_once('../../utils.php');
require_once($CFG->dirroot . '/config.php');
//require_once($CFG->dirroot . '/mod/pluginpatroller/utils.php');

class my_cron_task extends \core\task\scheduled_task {
	
    public function get_name() {
        // Nombre de la tarea que aparecerá en el administrador.
        return get_string('my_cron_task', 'mod_pluginpatroller');
    }

    public function execute() {
        global $DB;

        // Lógica que quieres ejecutar.
        mtrace("Ejecutando pluginpatroller cron...");
        
		try {
			// 1. Obtener todas las filas de repos_data_patroller.
			$repos = $DB->get_records('repos_data_patroller', null, '', 'id, id_materia');

			if (!empty($repos)) {
				//***************************************************************
				//***************************************************************
				foreach ($repos as $repo) {
					$repo_id = $repo->id;
					$repo_name = $repo->nombre_repo;
					$id_materia = $repo->id_materia;

					// Imprime los valores obtenidos (opcional, para depuración).
					mtrace("Procesando Repo ID: {$repo_id}, Repo name: {$repo_name}, ID Materia: {$id_materia}");

					// lógica que actualiza el dato de los repos
					update_commit_information($repo_id, $repo_name, $id_materia);
					
				}

				// 2. Actualizar last_api_refresh en la tabla pluginpatroller.
				mtrace("Actualizando last_api_refresh en pluginpatroller...");

				//***************************************************************
				//***************************************************************

				$new_refresh_time = $date = date('Y-m-d') . 'T' . date('H:i:s') . '-03:00';
				
				$plugin_instances = $DB->get_records('pluginpatroller', null, '', 'id, last_api_refresh');
				
				foreach ($plugin_instances as $plugin) {
					$plugin_id = $plugin->id;

					// Actualizar el campo last_api_refresh con la fecha/hora actual.
					$DB->update_record('pluginpatroller', [
						'id' => $plugin_id,
						'last_api_refresh' => $new_refresh_time
					]);

					// Imprime los valores actualizados (opcional, para depuración).
					mtrace("Actualizado Plugin ID: {$plugin_id}, Last API Refresh: {$new_refresh_time}");
				}
				//***************************************************************
				//***************************************************************
				
			} else {
				mtrace("No se encontraron repositorios en la tabla repos_data_patroller.");
			}
		} catch (Exception $e) {
			mtrace("Error al procesar: " . $e->getMessage());
		}
    }

	function update_commit_information($repo_id, $repo_name, $id_materia){
		global $DB;
		// Get owner and repo from the database (values stored in $pluginpatroller)
		$token = get_config('pluginpatroller', 'token_patroller');  // Fetch the GitHub token from plugin settings in the database
		$owner = get_config('pluginpatroller', 'owner_patroller');  // Obtenemos el valor 'owner' de la base de datos

		$last_update = '1970-01-01T01:00:00Z';
		
		// BEGIN GitHub API Script
		
		// URL de la API de GitHub para obtener commits por repositorio
		$commits_url = 'https://api.github.com/search/commits?per_page=100&q=repo:' . $owner . '/' . $repo_name . '+author-date:>=' . $last_update . '+sort:author-date-desc';
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
			mtrace('Error al obtener commits: ' . curl_error($ch_commits));
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
					mtrace('Error al obtener detalles del commit: ' . curl_error($ch_commit_detail));
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
			$students = get_students_by_repoid_and_courseid($repo_id, $id_materia);
			
			foreach ($commiter_array as $commiter_name => $commiter_info) {
				foreach ($students as $student) {
					if ($commiter_name == $student->alumno_github && $student->id_repos == $repo_id) {
						$DB->update_record(
							'alumnos_data_patroller',
							[
								'id' => $student->id,
								'fecha_ultimo_commit' => $commiter_info['last_commit'],
								'cantidad_commits' => $commiter_info['total_commits'],
								'lineas_agregadas' => $commiter_info['total_added'],
								'lineas_eliminadas' => $commiter_info['total_deleted'],
								'lineas_modificadas' => $commiter_info['total_modified']
							],
							$bulk = false
						);
						break;
					}
				}
			}
		}
	}

	function get_students_by_repoid_and_courseid($repo_id, $courseid){
		global $DB;

		$resultado = $DB->get_records('alumnos_data_patroller', ['id_repos' => $repo_id, 'id_materia' => $courseid]);

		return $resultado;
	}

}

