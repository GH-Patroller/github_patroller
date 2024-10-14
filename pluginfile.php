<?php

defined('MOODLE_INTERNAL') || die();

function pluginpatroller_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $DB, $CFG;

    // Verificar que el contexto es de tipo módulo
    if ($context->contextlevel != CONTEXT_MODULE) {
        echo "Contexto incorrecto.<br>";
        return false;
    }

    // Verificar que el área de archivos es 'uploadedfiles'
    if ($filearea !== 'uploadedfiles') {
        echo "Área de archivos incorrecta.<br>";
        return false;
    }

    // Obtener el itemid y construir la ruta
    $itemid = array_shift($args); // Primer argumento es el itemid (0 en este caso)
    $relativepath = implode('/', $args); // Generar la ruta desde los argumentos restantes
    $fullpath = "/$context->id/mod_pluginpatroller/$filearea/$itemid/$relativepath"; // Ruta completa

    // Depuración: Mostrar la ruta completa y el hash del archivo
    echo "Ruta completa: $fullpath <br>";
    echo "SHA1 hash de la ruta: " . sha1($fullpath) . "<br>";

    // Obtener el archivo directamente por su contenthash
    $fs = get_file_storage();
    $file = $fs->get_file_by_hash(sha1($fullpath));

    if (!$file || $file->is_directory()) {
        // Forzar el acceso directo al archivo físico en el sistema de archivos
        $filepath = "$CFG->dataroot/filedir/0b/dd/0bdd9c84c02ec927de5d57e1ae88515c51902963";
        send_file($filepath, 'doce.xlsx', 0, 0, false, true);
        return;
    }

    // Enviar el archivo normalmente si se encuentra en el sistema de archivos de Moodle
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
