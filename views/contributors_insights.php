<?php
require_once('../../config.php');
require_once('utils.php');

function show_students_commits_table($context)
{
    global $DB; // Asegúrate de tener acceso global al DB
    $repository = optional_param('repository', '', PARAM_TAGLIST);
    $url = new moodle_url('/mod/pluginpatroller/view.php', array('id' => $context->instanceid, 'tab' => 'tab1'));
    $student_commits = $repository != '' ? get_commit_information_by_repo($repository) : [];
    $repositories = get_all_repositories();
    $options_repos = array(
        '' => '-- select a repository --',
    );
    foreach ($repositories as $repository) {
        $options_repos[$repository] = $repository;
    };
    echo '<div>';

    echo '<label for="repository_select"> Repositorios </label>';
    echo html_writer::select($options_repos, 'repository_select', '', null, array('id' => 'repository_select', 'onchange' => 'filterTable()'));

    echo '</div>';
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
    echo '</tbody>';
    echo '</table>';
?>
    <script>
        function onRepositoryChange(event) {
            var repository_selected = document.getElementById('repository_select').value;
            window.location.href = <?php $url . 'AAAAAAAAAAAAAAAAA' ?>;
        }
    </script>
<?php
    echo '<hr>';
}
?>