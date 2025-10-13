# Módulo de Reporte Diario

## Descripción General

El módulo de Reporte Diario permite a los usuarios generar y gestionar reportes diarios de las clínicas, incluyendo información sobre ventas, nuevos pacientes y referencias. Este módulo se integra en el proyecto existente para proporcionar una interfaz fácil de usar para la visualización y análisis de datos diarios de las clínicas.

## Requisitos Previos

- PHP 8.2.12
- CodeIgniter 4.3.6
- MySQL 5.2.1

## Instalación

1. Clona el repositorio:

   ```bash
   git clone https://github.com/JSebasRC/rubymed-crm.git

   ```

2. Estructura del Proyecto

- app/Controllers/Daily_report.php: Controlador del módulo de reporte diario.
- app/Models/Daily_report_model.php: Modelo de datos para los reportes diarios.
- app/Views/daily_report: Vista principal del módulo.

3. Configura la base de datos en app/Config/Database.php:

De acuerdo al entorno en que se este trabajando, si es en local, usar la siguiente configuración:

public $default = [
'DSN' => '',
'hostname' => 'localhost',
'username' => 'root',
'password' => '',
'database' => 'crm',
'DBDriver' => 'MySQLi',
'DBPrefix' => 'crm\_',
'pConnect' => false,
'DBDebug' => (ENVIRONMENT !== 'production'),
'charset' => 'utf8',
'DBCollat' => 'utf8_general_ci',
'swapPre' => '',
'encrypt' => false,
'compress' => false,
'strictOn' => false,
'failover' => [],
'port' => 3306,
];

Si se usa en el entorno de producción, se debe modificar el usarname, password, database de acuerdo a la configuración de la base datos:

public $default = [
'DSN' => '',
'hostname' => 'localhost',
'username' => 'utcbmibktgmh5',
'password' => 'Oyarcegroup2024',
'database' => 'dbencrwo0tnhtn',
'DBDriver' => 'MySQLi',
'DBPrefix' => 'crm\_',
'pConnect' => false,
'DBDebug' => (ENVIRONMENT !== 'production'),
'charset' => 'utf8',
'DBCollat' => 'utf8_general_ci',
'swapPre' => '',
'encrypt' => false,
'compress' => false,
'strictOn' => false,
'failover' => [],
'port' => 3306,
];

4. Uso
   Accede al módulo desde el navegador:

https://rubymedfc.com/crm/index.php/daily_report

5. Crear un reporte diario

- Accede a la página del módulo desde el menu lateral izquierdo, dando click a la opcion reporte diario.
- Da click en añadir reporte, llena todos los campos del formulario, da click en guardar.

6. Licencia
   Este proyecto está licenciado bajo la Oyarce Groun 2024.
