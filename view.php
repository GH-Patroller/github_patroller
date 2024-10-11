<?php

require_once('../../config.php');
require_once('lib.php');
require_once('mod_form.php');

// Set necessary parameters
$id = required_param('id', PARAM_INT); // Course module ID

if ($id) {
    $cm = get_coursemodule_from_id('pluginpatroller', $id, 0, false, MUST_EXIST);
    $course = get_course($cm->course);
    $pluginpatroller = $DB->get_record('pluginpatroller', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    print_error('Course module ID is required.');
}

require_login($course, true, $cm);

$PAGE->set_url('/mod/pluginpatroller/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($pluginpatroller->name));
$PAGE->set_heading(format_string($course->fullname));
echo $OUTPUT->header();

// Fetch the GitHub token from plugin settings in the database
$token = get_config('pluginpatroller', 'github_token');

// Get owner and repo from the database (values stored in $pluginpatroller)
$owner = $pluginpatroller->owner;  // Obtenemos el valor 'owner' de la base de datos
$repo = $pluginpatroller->repo;    // Obtenemos el valor 'repo' de la base de datos

// BEGIN GitHub API Script

// URL to get the collaborators from the GitHub API
$url = "https://api.github.com/repos/$owner/$repo/collaborators";

// Initialize cURL session
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: token ' . $token,
    'User-Agent: GitHub-API-Request'
]);

// Execute the cURL request
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch) . "<br>";
} else {
    $data = json_decode($response, true);
    if (isset($data['message'])) {
        echo "Error: " . $data['message'] . "<br>";
    } else {
        foreach ($data as $collaborator) {
            echo "Colaborador: " . $collaborator['login'] . "<br>";
            echo "URL: " . $collaborator['html_url'] . "<br>";

            $commits_url = "https://api.github.com/repos/$owner/$repo/commits";
            $ch_commits = curl_init();
            curl_setopt($ch_commits, CURLOPT_URL, $commits_url);
            curl_setopt($ch_commits, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_commits, CURLOPT_HTTPHEADER, [
                'Authorization: token ' . $token,
                'User-Agent: GitHub-API-Request'
            ]);

            // Execute the cURL request for commits
            $commits_response = curl_exec($ch_commits);
            $commits_data = json_decode($commits_response, true);

            if (curl_errno($ch_commits)) {
                echo 'Error al obtener los commits: ' . curl_error($ch_commits) . "<br>";
            } else {
                $num_commits = count($commits_data);
                echo "Número de commits: " . $num_commits . "<br>";

                $total_added = 0;
                $total_deleted = 0;
                $total_modified = 0;

                foreach ($commits_data as $commit) {
                    $commit_detail_url = "https://api.github.com/repos/$owner/$repo/commits/" . $commit['sha'];
                    $ch_commit_detail = curl_init();
                    curl_setopt($ch_commit_detail, CURLOPT_URL, $commit_detail_url);
                    curl_setopt($ch_commit_detail, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch_commit_detail, CURLOPT_HTTPHEADER, [
                        'Authorization: token ' . $token,
                        'User-Agent: GitHub-API-Request'
                    ]);

                    $commit_detail_response = curl_exec($ch_commit_detail);

                    if (curl_errno($ch_commit_detail)) {
                        echo 'Error al obtener detalles del commit: ' . curl_error($ch_commit_detail) . "<br>";
                    } else {
                        $commit_detail_data = json_decode($commit_detail_response, true);

                        if (isset($commit_detail_data['stats'])) {
                            $total_added += $commit_detail_data['stats']['additions'];
                            $total_deleted += $commit_detail_data['stats']['deletions'];
                            $total_modified += $commit_detail_data['stats']['total'];
                        }
                    }
                    curl_close($ch_commit_detail);
                }

                echo "Líneas agregadas: " . $total_added . "<br>";
                echo "Líneas eliminadas: " . $total_deleted . "<br>";
                echo "Líneas modificadas: " . $total_modified . "<br>";
            }

            curl_close($ch_commits);
            echo "-------------------------------" . "<br>";
        }
    }
}
curl_close($ch);

// END GitHub API Script

echo $OUTPUT->footer();
?>
