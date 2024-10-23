<?php

require_once('../../../config.php');

global $DB;

$sede_options = ['YA' => 'Yatay', 'BE' => 'Belgrano'];
$cursos = [
    ['sede' => 'YA', 'curso' => '1A', 'grupos' => 4],
    ['sede' => 'YA', 'curso' => '1B', 'grupos' => 4],
    ['sede' => 'BE', 'curso' => '2A', 'grupos' => 4],
    ['sede' => 'BE', 'curso' => '2B', 'grupos' => 4],
    ['sede' => 'YA', 'curso' => '3A', 'grupos' => 4]
];

$counter = 1;
foreach ($cursos as $curso) {
    for ($i = 1; $i <= $curso['grupos']; $i++) {
        $record = new stdClass();
        $record->sede = $curso['sede'];
        $record->curso = $curso['curso'];
        $record->num_grupo = $i; // Ahora es solo el número
        $record->nombre_repo = 'repo_' . strtolower($curso['curso']) . '_g' . $i;
        $record->nombre_alumno = 'Alumno ' . $counter;
        $record->mail_alumno = 'alumno' . $counter . '@example.com';
        $record->alumno_github = 'github_user_' . $counter;
        $record->cantidad_commits = rand(10, 50);
        $record->lineas_agregadas = rand(50, 200);
        $record->lineas_eliminadas = rand(5, 50);
        $record->lineas_modificadas = rand(10, 100);
        $record->fecha_ultimo_commit = time(); // Marca de tiempo actual

        // Insertar el registro en la base de datos
        $DB->insert_record('data_patroller', $record);
        $counter++;
    }
}
