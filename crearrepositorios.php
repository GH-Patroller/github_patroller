<?php

require_once('../../config.php');

// Asegúrate de tener acceso global a la base de datos y a la salida de Moodle
global $DB, $PAGE, $OUTPUT;

// Obtener el ID del curso desde los parámetros de la URL
$id = required_param('id', PARAM_INT); // ID del curso o del módulo

// Obtener el módulo del curso (cm), el curso, y la instancia del plugin
$cm = get_coursemodule_from_id('pluginpatroller', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

// Configurar la página
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
$PAGE->set_url('/mod/pluginpatroller/crearrepositorios.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();

?>

<h2>Crear Repositorios</h2>

<!-- Formulario para seleccionar los valores -->
<br>Nombre de Materia: <span id="nombre_materia"><?php echo $course->shortname; ?></span>
<input type="hidden" id="input_materia" value="<?php echo $course->shortname; ?>">

<br>Año: <span id="ano"><?php echo date("Y"); ?></span>
<input type="hidden" id="input_ano" value="<?php echo date("Y"); ?>">

<br>Cuatrimestre: 
<select name='cuatrimestre' id="cuatrimestre" onchange="actualizarCampoConcatenado()">
    <option value='1º1º'>1er Año - 1er Cuatrimestre (1º1º)</option>
    <option value='1º2º'>1er Año - 2do Cuatrimestre (1º2º)</option>
    <option value='2º1º'>2do Año - 1er Cuatrimestre (2º1º)</option>
    <option value='2º2º'>2do Año - 2do Cuatrimestre (2º2º)</option>
</select>

<br>Sede: 
<select name='sede' id="sede" onchange="actualizarCampoConcatenado()">
    <option value='BEL'>BEL</option>
    <option value='YAT'>YAT</option>
</select>

<br>Curso: 
<select name='curso' id="curso" onchange="actualizarCampoConcatenado()">
    <option value='A'>A</option>
    <option value='B'>B</option>
    <option value='C'>C</option>
    <option value='D'>D</option>
    <option value='E'>E</option>
    <option value='F'>F</option>
</select>

<br>Grupos: 
<select name='grupo' id="grupo" onchange="actualizarCampoConcatenado()">
    <option value='1'>1</option>
    <option value='2'>2</option>
    <option value='3'>3</option>
</select>

<!-- Campo concatenado que se actualizará automáticamente -->
<br><br><b>Campo concatenado:</b> 
<input type="text" id="campo_concatenado" readonly style="width: 15%;">



<script>
    // Función para actualizar el campo concatenado
    function actualizarCampoConcatenado() {
        var nombreMateria = document.getElementById('input_materia').value;
        var ano = document.getElementById('input_ano').value;
        var cuatrimestre = document.getElementById('cuatrimestre').value;
        var sede = document.getElementById('sede').value;
        var curso = document.getElementById('curso').value;
        var grupo = document.getElementById('grupo').value;
        
        // Concatenar los valores
        var campoConcatenado = nombreMateria + ' - ' + ano + ' - ' + cuatrimestre + ' - ' + sede + ' - ' + curso + ' - ' + grupo;
        
        // Actualizar el valor del campo concatenado
        document.getElementById('campo_concatenado').value = campoConcatenado;
    }
    
    // Inicializar el campo concatenado con los valores actuales
    actualizarCampoConcatenado();
</script>

<?php
echo $OUTPUT->footer();
