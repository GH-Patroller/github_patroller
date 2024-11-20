<?php
// Este archivo debe estar ubicado en el directorio db/tasks.php de tu plugin.
// Se utiliza para definir tareas programadas (cron) para tu plugin.

$tasks = array(
    array(
        'classname' => 'mod_pluginpatroller\task\my_cron_task',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    )
);