# Podología Ana Ramírez

Sistema web para la gestión de pacientes, expedientes clínicos y citas de un
consultorio podológico. Centraliza los antecedentes de salud, las atenciones y
la valoración biomecánica, con acceso por perfiles y registro de cambios.

## Funcionalidades

### Pacientes y expedientes

- Registro de identificación, contacto, domicilio, ocupación y contacto de emergencia.
- Antecedentes de diabetes, hipertensión, problemas circulatorios, tratamientos,
  alergias y otras condiciones de salud.
- Información sobre calzado, uso de plantillas y actividad física.
- Directorio con búsqueda por documento, nombre, apellido o teléfono.
- Consulta del expediente, corrección de datos secundarios e impresión de la ficha.
- Retirada de expedientes mediante borrado lógico, conservando sus registros.

### Atenciones clínicas

Cada atención se vincula con un paciente y un profesional responsable. Permite
registrar motivo de consulta, diagnóstico, procedimiento, evolución de la lesión,
recomendaciones, notas internas y fecha sugerida para la siguiente revisión.

La valoración incluye escala de dolor, tipo de pisada, marcha y apoyo, exploración
de piel y uñas, valoración vascular y exploración neurológica. Las atenciones
aparecen en el expediente del paciente. Las correcciones y retiradas realizadas
por administración conservan un historial de cambios.

### Agenda de citas

La agenda permite consultar los turnos de una fecha, reservar y editar citas,
asignar profesionales y registrar el motivo y las notas de cada visita. Comprueba
que las reservas de un mismo profesional no se superpongan.

Los estados disponibles son **pendiente**, **en atención**, **finalizado** y
**cancelado**. La cancelación conserva el registro de la reserva.

### Personal y accesos

La administración puede crear cuentas, actualizar datos del personal, asignar
perfiles, renovar contraseñas y desactivar accesos. Las cuentas creadas desde
este módulo deben cambiar su contraseña inicial al ingresar. La desactivación
impide nuevos ingresos y bloquea el acceso en las sesiones abiertas.

## Perfiles de usuario

| Perfil | Funciones principales |
| --- | --- |
| SuperAdmin | Gestión de pacientes, atenciones, agenda y cuentas de todos los perfiles; acceso al panel técnico, información de tablas y registros del sistema. |
| Administración | Corrección y retirada de expedientes y atenciones, gestión de agenda y administración de cuentas de especialistas y recepción. |
| Especialista | Registro de pacientes y atenciones; consulta de expedientes y actualización del estado de sus propias citas. |
| Recepción | Registro y consulta de pacientes, reserva y edición de citas y actualización de sus estados. |

El ingreso se realiza con **DNI y contraseña**. Los permisos se comprueban en el
servidor según el perfil de la cuenta.

## Registro de cambios

El sistema conserva la autoría y las fechas de creación de pacientes, citas y
atenciones. Las modificaciones de pacientes registran el campo afectado, su valor
anterior, el nuevo valor, el usuario y el motivo del cambio.

Los cambios de citas, atenciones y cuentas del personal se almacenan en una
auditoría adicional. Los ingresos, operaciones y acciones denegadas se registran
en el historial del sistema, consultable desde el panel técnico.

## Tecnologías

- PHP 8.4 y PDO MySQL.
- MySQL o MariaDB para la persistencia de información.
- Apache en el entorno Docker.
- Plantillas PHP, Tailwind CSS y Font Awesome.
- Docker Compose para ejecutar los servicios y administrar los volúmenes.

Los estilos y los iconos se cargan desde CDN y requieren conexión a Internet en
el navegador.

## Instalación con Docker

Requisitos: Docker Engine y Docker Compose.

1. Clone el repositorio y entre en su directorio.
2. Cree el archivo de configuración:

   ```bash
   cp .env.example .env
   ```

3. Complete las contraseñas y revise los parámetros de conexión de `.env`.
4. Inicie los servicios:

   ```bash
   docker compose up -d --build --wait
   ```

La aplicación estará disponible en **http://localhost:8088**. El puerto se puede
cambiar con `APP_PORT`.

La configuración de ejemplo utiliza MariaDB dentro de Docker. En el primer
arranque se crea el esquema en el volumen nuevo y se aprovisionan las cuentas
iniciales de los cuatro perfiles. Sus contraseñas se definen en las variables
`DEMO_SUPERADMIN_PASSWORD`, `DEMO_ADMIN_PASSWORD`,
`DEMO_ESPECIALISTA_PASSWORD` y `DEMO_RECEPCIONISTA_PASSWORD`, con un mínimo de
12 caracteres. El aprovisionamiento conserva las cuentas existentes al reiniciar.

### Variables de configuración

| Variable | Descripción |
| --- | --- |
| `APP_PORT` | Puerto HTTP publicado en el equipo. Valor predeterminado: `8088`. |
| `DB_HOST` | Servidor de la base de datos. En Docker local: `db`. |
| `DB_PORT` | Puerto MySQL. Valor predeterminado: `3306`. |
| `DB_NAME` | Nombre de la base de datos. |
| `DB_USER` | Usuario utilizado por la aplicación. |
| `DB_PASS` | Contraseña de conexión; en Docker, si está vacía, se utiliza `DB_PASSWORD`. |
| `DB_CHARSET` | Juego de caracteres. Valor predeterminado: `utf8mb4`. |
| `DB_PASSWORD` | Contraseña del usuario de la instancia MariaDB local. |
| `DB_ROOT_PASSWORD` | Contraseña de administración de la instancia MariaDB local. |
| `DEMO_*_PASSWORD` | Contraseñas de aprovisionamiento inicial de los cuatro perfiles. |

El archivo `.env` contiene la configuración privada y está excluido del
repositorio. Docker proporciona las variables a PHP sin incluir ese archivo en
la imagen.

### Conexión a un servidor MySQL externo

Configure `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` y `DB_PASS` en `.env` con los
datos del servidor. La base debe existir y estar vacía antes de inicializarla.
Para crear las tablas en la base seleccionada:

```bash
mysql -h SERVIDOR -P PUERTO -u USUARIO -p NOMBRE_BASE < sql/instalar.sql
```

Después, ejecute `docker compose up -d --build --wait`. PHP utilizará el servidor
indicado por `DB_HOST`; el servicio MariaDB local se mantiene como dependencia
del entorno Compose.

`sql/instalar.sql` crea las tablas y los roles sin instrucciones de borrado.
`sql/schema.sql` elimina y vuelve a crear las tablas de la base local
`podoana_db`; se utiliza únicamente para reinicializar entornos de desarrollo.

## Ejecución con PHP

Se necesita PHP 8.4 con PDO MySQL y una base de datos inicializada. Complete
`.env` con un servidor accesible desde el equipo y una contraseña en `DB_PASS`.
Para una base instalada en el mismo equipo, utilice `DB_HOST=127.0.0.1`.

El aprovisionamiento inicial de cuentas se puede ejecutar desde la raíz:

```bash
php docker/seed-demo.php
```

Para iniciar el servidor de desarrollo:

```bash
php -S 127.0.0.1:8000 -t public
```

La aplicación estará disponible en **http://localhost:8000**. En Windows también
se incluye `iniciar.bat`, que utiliza PHP de XAMPP cuando está disponible.

## Estructura del proyecto

```text
config/             Configuración, conexión PDO, sesiones y permisos
src/controllers/    Procesamiento de solicitudes
src/models/         Consultas y operaciones de base de datos
src/views/          Pantallas de acceso, pacientes, gestión y administración
public/index.php    Punto de entrada y enrutamiento por acción
public/assets/      Estilos, JavaScript e imágenes
sql/                Esquema de la base de datos
docker/             Configuración de Apache y scripts de instalación
uploads/pacientes/  Directorio reservado para archivos de pacientes
compose.yaml        Definición de servicios Docker
Dockerfile          Imagen de PHP y Apache
```

### Tablas principales

| Tabla | Información almacenada |
| --- | --- |
| `roles` | Perfiles y niveles de acceso. |
| `usuarios` | Cuentas, datos del personal y estado de acceso. |
| `pacientes` | Identificación, contacto y antecedentes de salud. |
| `historias_clinicas` | Atenciones, procedimientos y valoraciones. |
| `citas` | Reservas, horarios, profesionales y estados. |
| `historial_cambios_pacientes` | Modificaciones de los datos del paciente. |
| `auditoria_modulos` | Cambios en citas, atenciones y cuentas. |
| `logs_sistema` | Eventos de acceso y operaciones del sistema. |

## Operación y mantenimiento

```bash
docker compose ps -a        # Consultar el estado de los servicios
docker compose logs web    # Consultar el registro de la aplicación
docker compose stop        # Detener los servicios
docker compose up -d --wait # Iniciar los servicios
docker compose down        # Retirar los contenedores y conservar los volúmenes
```

Después de modificar el código, reconstruya la imagen con
`docker compose up -d --build --wait`.

Los datos locales se conservan en volúmenes Docker. La opción `down -v` elimina
esos volúmenes. Las copias de respaldo de un servidor MySQL externo se gestionan
sobre ese servidor.

Para comprobar la sintaxis de los archivos PHP:

```bash
rg --files -g '*.php' -0 | xargs -0 -n 1 php -l
```
