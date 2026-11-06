# Implementación para práctica

La aplicación utiliza MySQL por PDO y carga la conexión desde variables de
entorno o desde el archivo privado `.env`. El instalador exige una base vacía;
no ejecuta el script de recreación sobre la base remota.

## Decisiones que pueden revisarse en clase

La implementación es deliberadamente directa. Las citas, atenciones y cuentas
comparten el controlador `GestionController` y el modelo `Gestion`, que tienen
varias responsabilidades. Los formularios se escriben directamente en plantillas
PHP con parte de la composición repetida. Las listas de estados, perfiles y
campos se repiten entre validaciones, formularios y consultas. Los datos viajan
como arreglos y cadenas sin objetos de dominio.

Las pantallas de personal y los selectores consultan listas completas sin
paginación; parte de la validación vuelve a consultar usuarios. Esto produce
más consultas y carga de datos de la necesaria, especialmente con una base
remota. No hay una herramienta de migraciones: el esquema local y el instalador
se mantienen en dos archivos. Los registros operativos y las auditorías detalladas
se guardan en tablas diferentes y requieren consultas separadas para revisarlos.

Estas decisiones dan material para practicar separación de responsabilidades,
reducción de duplicación, paginación, rendimiento y evolución del esquema. Se
mantienen las consultas preparadas, las contraseñas con hash, las restricciones
por perfil, los registros de auditoría y los borrados lógicos.

## Datos sintéticos

El inicializador carga cuatro cuentas, 36 pacientes, 72 atenciones y 72 citas.
Los nombres, documentos, teléfonos, direcciones y antecedentes son inventados.
Los correos usan `example.test`. Las pruebas funcionales añaden un expediente,
una atención, una cita y una cuenta de prueba que se conservan como inactivos o
cancelados, junto con su historial, para revisar el borrado lógico.

Las claves de las cuatro cuentas se leen de `DEMO_*_PASSWORD` en `.env`. Las
cuentas creadas en Personal y accesos deben cambiar su contraseña al ingresar.
El archivo privado `output/accesos-practica.txt` permite consultar los accesos de
esta copia sin agregarlos al repositorio.

## Validación

La verificación incluye sintaxis PHP, instalación en MySQL, inicio de sesión de
los cuatro perfiles, persistencia de todos los antecedentes, registro y corrección
de atenciones, valoración biomecánica, impresión, reserva y cancelación de citas,
rechazo de solapamientos, alta y desactivación de personal, cambio de contraseña,
revocación de sesiones, permisos denegados y conservación de auditorías.

Los scripts y resultados de ejecución se guardan localmente en `output/`, excluido
de Git. Las capturas de escritorio y móvil están en `output/playwright/`.

Resultado en esta copia: 42 verificaciones HTTP y de persistencia correctas,
29 archivos PHP sin errores de sintaxis, instalador bloqueado sobre base ya
poblada y carga de datos ficticios sin duplicación. Las tres pantallas nuevas se
revisaron en 1440 × 1000 y 390 × 844 píxeles.
