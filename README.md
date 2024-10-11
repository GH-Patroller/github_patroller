
# 🎓 GitPatroller Plugin para Moodle

[![Moodle Version](https://img.shields.io/badge/Moodle-3.11%2B-brightgreen)](https://moodle.org/) ![GitHub All Releases](https://img.shields.io/github/downloads/yourusername/gitpatroller/total) ![GitHub issues](https://img.shields.io/github/issues/yourusername/gitpatroller)

### Monitorea la actividad de los estudiantes en repositorios de GitHub, optimizando el trabajo en equipo y facilitando el seguimiento académico.

## 📝 Descripción

GitPatroller es un plugin desarrollado para Moodle que permite a los educadores monitorear automáticamente la actividad de los estudiantes en repositorios de GitHub. Su propósito principal es mejorar la transparencia en el trabajo en equipo y ayudar a los profesores a hacer un seguimiento preciso del progreso de sus alumnos. 

A través de un dashboard intuitivo, GitPatroller recopila y muestra métricas clave como:
- 📊 Número de commits.
- 👤 Usuarios que realizaron los commits.
- 📅 Fechas del último commit.
- ➕ Líneas agregadas, eliminadas y modificadas.

## 🚀 Características Principales

- **Monitoreo en tiempo real**: Obtén datos actualizados sobre la actividad en los repositorios.
- **Integración con GitHub**: Crea repositorios automáticamente para cada curso y grupo.
- **Panel de control intuitivo**: Visualiza la actividad de cada estudiante de forma clara y simple.
- **Filtrado y búsqueda**: Filtra los datos por alumno, grupo o repositorio para un análisis más preciso.
- **Invitaciones automáticas**: Envía invitaciones a los estudiantes para unirse a los repositorios mediante un archivo Excel.

## 📚 Documentación

1. **Requisitos del sistema**:
   - Moodle 3.11 o superior
   - Acceso a la API de GitHub
   - PHP 7.4 o superior
   - MySQL 5.7 o superior

2. **Instalación**:
   - Clona el repositorio en el directorio `mod/` de Moodle:
     ```bash
     git clone https://github.com/yourusername/gitpatroller.git mod/gitpatroller
     ```
   - Navega a la sección de administración de Moodle y finaliza la instalación.

3. **Configuración**:
   - Configura el plugin desde el panel de administración de Moodle.
   - Asegúrate de conectar tu cuenta de GitHub para habilitar la recolección de datos.

4. **Uso**:
   - Una vez instalado, crea un repositorio para cada curso directamente desde el plugin.
   - Sube un archivo Excel con los datos de los estudiantes para enviar las invitaciones a GitHub.

## 🔧 Configuración avanzada

Para configurar opciones avanzadas, como los intervalos de recolección de datos o la personalización del dashboard, consulta la [documentación completa](https://github.com/yourusername/gitpatroller/wiki).

## 🛠️ Desarrollo

Si deseas colaborar con el proyecto, sigue estos pasos para configurar tu entorno local:

1. Clona el repositorio:
   ```bash
   git clone https://github.com/yourusername/gitpatroller.git
   ```

2. Instala las dependencias necesarias:
   ```bash
   composer install
   ```

3. Configura las variables de entorno para conectar con tu API de GitHub.

## ✅ Criterios de Aceptación

- Integración completa con Moodle y GitHub.
- Recolección automática y confiable de datos de actividad.
- Creación automática de repositorios para cada curso.
- Funcionalidades de filtrado y visualización de datos operativas.
- Documentación completa disponible.

## 🚫 Exclusiones

- No se incluye soporte técnico posterior al despliegue.
- Las métricas avanzadas de análisis de commits no forman parte de esta versión.

## 📈 Roadmap

- [ ] Mejorar el diseño del dashboard para incluir gráficos interactivos.
- [ ] Integración con otras plataformas de control de versiones.
- [ ] Soporte multilenguaje.
- [ ] Herramientas adicionales para la visualización de la participación en proyectos de grupo.

## 🧑‍💻 Contribuciones

¡Las contribuciones son bienvenidas! Si tienes ideas para mejorar el plugin o has encontrado algún problema, no dudes en abrir un issue o enviar un pull request.

## ⚖️ Licencia

Este proyecto está bajo la licencia MIT. Puedes ver más detalles en el archivo [LICENSE](LICENSE).
