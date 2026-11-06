# ana-podologia-auditoria

Sistema de Gestión Clínica - Podología Ana Ramírez (v2.0).

Sistema integral para gestión de expedientes clínicos podológicos, antecedentes de salud, valoración biomecánica, citas, control de acceso por roles (RBAC estricto) y auditoría inmutable de cambios.

## Ejecución aislada con Docker

Requisitos: Docker Engine y Docker Compose. Desde la raíz del proyecto:

```bash
# Solo si todavía no existe .env:
cp .env.example .env
# Sustituir las claves de ejemplo por claves propias antes de iniciar.
docker compose up -d --build --wait
```

La página se abre en **http://localhost:8088**. En este equipo ya se generó
un `.env` privado con claves aleatorias; consérvalo para los siguientes inicios.
Si necesitas otro puerto, cambia `APP_PORT` en `.env` y vuelve a ejecutar
`docker compose up -d --wait`.

El proyecto Compose se llama `podoana-auditoria`. Utiliza PHP/Apache, una instancia
MariaDB propia, una red interna para la base de datos y volúmenes exclusivos para
datos y archivos subidos. Solo la web publica un puerto, limitado a `127.0.0.1`.
No utiliza el MySQL del equipo ni modifica los demás proyectos Docker.

La primera ejecución importa `sql/schema.sql` **únicamente en el volumen nuevo**.
El esquema incluye las tablas y los roles, sin usuarios, pacientes ni historiales
precargados. Después, `demo-init` agrega cuatro cuentas para explorar los perfiles:

| Perfil | DNI de acceso | Contraseña en `.env` |
| :--- | :--- | :--- |
| SuperAdmin | `90000001` | `DEMO_SUPERADMIN_PASSWORD` |
| Administradora | `90000002` | `DEMO_ADMIN_PASSWORD` |
| Especialista | `90000003` | `DEMO_ESPECIALISTA_PASSWORD` |
| Recepcionista | `90000004` | `DEMO_RECEPCIONISTA_PASSWORD` |

Estas cuentas de demostración están listas para ingresar, sin cambio inicial
obligatorio. El inicializador conserva las cuentas existentes y sus contraseñas
al reiniciar. Usa datos ficticios para las pruebas. Los estilos e iconos cargan
desde CDN y requieren conexión a Internet en el navegador.

```bash
docker compose ps -a          # Estado de este proyecto
docker compose logs web      # Registro de la aplicación
docker compose stop          # Detenerlo conservando contenedores y datos
docker compose up -d --wait   # Volver a iniciarlo
docker compose down          # Quitar sus contenedores; conserva los volúmenes
```

Evita `docker compose down -v` si quieres conservar los datos de prueba.
El código se copia en la imagen: después de editar PHP, CSS o JavaScript,
ejecuta `docker compose up -d --build --wait`.

Referencias: [PHP en Docker](https://docs.docker.com/guides/php/) y
[comprobaciones de salud de MariaDB](https://mariadb.com/docs/server/server-management/automated-mariadb-deployment-and-administration/docker-and-mariadb/docker-official-image-frequently-asked-questions).

---

## 1. Identidad Visual de Marca Integrada
El sistema incorpora la línea gráfica oficial de la clínica:
- **Logo Corporativo Oficial**: Silueta del pie con detalle botánico y firma de la Dra. Ana Ramírez en [`public/assets/img/logo.jpg`](file:///c:/Users/Usuario/Documents/PodoAna/public/assets/img/logo.jpg).
- **Paleta de Colores Clínicos**:
  - Azul Oscuro Médico: `#0A2540`
  - Azul Clínico Dominante: `#005B94`
  - Verde Menta / Salud: `#2EC4B6`
  - Acentos Cian: `#00B4D8`
  - Fondo Ergonómico: `#F8FAFC`

---

## 2. Esquema de Roles y Permisos (RBAC de 3 Niveles)

| Nivel | Rol / Perfil | Credenciales por Defecto | Permisos y Restricciones |
| :--- | :--- | :--- | :--- |
| **Nivel 1** | **SuperAdmin** *(Técnico / Creador)* | Credenciales entregadas de forma privada | **Acceso exclusivo al panel técnico, salud de BD y logs globales.** |
| **Nivel 2** | **Dueña / Admin** | Credenciales entregadas de forma privada | **Control del negocio y gestión de empleados.** Edición de datos secundarios y borrado lógico, con auditoría. |
| **Nivel 3** | **Especialista (Podólogo)** | Credenciales asignadas por administración | **Creación de pacientes, fichas y atenciones.** No puede modificar ni eliminar registros guardados. |
| **Nivel 3** | **Recepcionista** | Credenciales asignadas por administración | **Registro de pacientes y agenda de turnos.** |

---

## 3. Auditoría, Trazabilidad Forense y Borrado Lógico

1. **Trazabilidad de Creación**: Cada paciente, cita o historia clínica guarda de forma automática e indeleble:
   - `creado_por_usuario_id`
   - `fecha_creacion`
   - `hora_creacion`
2. **Historial Inmutable de Modificaciones (`historial_cambios_pacientes`)**:
   - Cuando el Administrador actualiza datos secundarios (teléfono, domicilio, calzado, antecedentes), el valor anterior **NO se sobrescribe a ciegas**.
   - Se registra: `campo_modificado`, `valor_anterior`, `valor_nuevo`, `modificado_por_usuario_id`, `fecha_hora` y `motivo_cambio`.
3. **Borrado Lógico (*Soft Delete*)**: Los registros nunca se destruyen con `DELETE FROM`. Se marcan con `estado = 'inactivo'`, `eliminado_en = NOW()` y `eliminado_por_usuario_id`.
4. **Logs del Sistema (`logs_sistema`)**: Registra intentos de acceso no autorizados, bloqueos RBAC y operaciones del sistema visibles exclusivamente para el SuperAdmin.

---

## 4. Estructura del Código

```text
PodoAna/
├── config/
│   ├── app.php                    # Helpers de escape XSS con e(), sesiones y mensajes flash
│   ├── auth.php                   # Control de acceso RBAC, autenticación y registro de logs
│   └── database.php               # Conexión PDO Singleton desacoplada
├── public/
│   ├── assets/
│   │   ├── css/custom.css         # Estilos complementarios
│   │   ├── img/logo.jpg           # Logo oficial de la clínica
│   │   └── js/app.js              # Prevención de doble envío táctil y lógica de alertas
│   └── index.php                  # Front Controller y enrutador central
├── sql/
│   └── schema.sql                 # Script DDL completo (MySQL 8 / MariaDB) con auditoría y semillas
├── src/
│   ├── controllers/
│   │   ├── AuthController.php     # Flujo de login seguro con password_verify() y logout
│   │   └── PacienteController.php # CRUD con inyección de creador y bloqueos RBAC
│   ├── models/
│   │   └── Paciente.php           # Modelo con transacciones PDO y tabla historial_cambios
│   └── views/
│       ├── admin/
│       │   └── sistema.php        # Panel técnico exclusivo para SuperAdmin (salud BD y logs)
│       ├── auth/
│       │   └── login.php          # Pantalla de inicio de sesión sin selector público de perfiles
│       ├── layout/
│       │   ├── header.php         # Navbar corporativa, badges de rol y cierre de sesión
│       │   └── footer.php         # Pie de página y scripts
│       └── pacientes/
│           ├── detalle.php        # Ficha con tarjeta de trazabilidad e historial inmutable
│           ├── editar.php         # Formulario de datos secundarios con bloqueo DNI/Nombres
│           ├── listado.php        # Directorio con badges médicos y enlaces al expediente
│           └── registro.php       # Formulario táctil con alertas dinámicas
├── uploads/
│   └── pacientes/
├── iniciar.bat                    # Lanzador rápido de un clic para Windows
└── README.md                      # Esta documentación
```

---

## 5. Guía de Ejecución Local

### En Windows (con XAMPP):
1. Asegúrate de tener **MySQL** activo en el XAMPP Control Panel.
2. Haz doble clic en **`iniciar.bat`** (o ejecuta `C:\xampp\php\php.exe -S 0.0.0.0:8000 -t public`).
3. Abre en tu navegador: **`http://localhost:8000`**

### En macOS (Terminal):
1. Importar la base de datos:
   ```bash
   mysql -u root -p < sql/schema.sql
   ```
2. Obtener la IP local de tu Mac:
   ```bash
   ipconfig getifaddr en0
   ```
3. Arrancar el servidor integrado:
   ```bash
   php -S 0.0.0.0:8000 -t public
   ```
4. Acceder desde cualquier computadora o tablet iPad/Android en la misma red Wi-Fi:
   ```text
   http://TU_IP_LOCAL:8000
   ```

---

## 6. Acceso y perfiles

El formulario de ingreso solicita DNI y contraseña, pero no muestra perfiles ni rellena credenciales de prueba. El DNI por sí solo no autentica: se valida también una contraseña almacenada como hash. Las cuentas de acceso deben aprovisionarse de forma privada y solo compartirse con su titular. La administración de usuarios dentro de la aplicación aún no está implementada.
