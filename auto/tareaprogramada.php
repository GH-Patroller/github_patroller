<?php
// Obtener la hora actual en formato "Hora:Minuto:Segundo"
$hora_actual = date("H:i:s");

// Especificar el nombre del archivo
$nombre_archivo = "hora_actual.txt";

// Abrir el archivo en modo "append" para agregar una nueva línea al final
file_put_contents($nombre_archivo, $hora_actual . PHP_EOL, FILE_APPEND);

// Confirmación
echo "Hora '$hora_actual' agregada al archivo '$nombre_archivo'.";
?>
