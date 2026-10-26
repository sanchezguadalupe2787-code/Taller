# D’Juniors — Entregables de liberación continua y despliegue

## Alcance

- Aplicación PHP existente para XAMPP local en Windows.
- Automatización mediante Jenkins Pipeline.
- Validación de sintaxis PHP, comprobación del entorno y prueba de humo.
- Despliegue local opcional a `C:\xampp\htdocs\taller_mecanico`.
- **No se importa, altera ni ejecuta ningún archivo SQL.** Se incluye `taller.sql` como copia de referencia sin cambios. El archivo preexistente `sql/taller_mecanico.sql` también se conserva tal como venía en el proyecto.

## Archivos entregados

- `Jenkinsfile`: pipeline con parámetro `DESPLEGAR_LOCAL` desactivado por defecto.
- `scripts/preparar-entorno.bat`: verifica PHP y extensiones PDO.
- `scripts/ejecutar-pruebas.bat`: lanza la prueba de humo.
- `scripts/desplegar.bat` y `scripts/desplegar.ps1`: copia los archivos de la aplicación al directorio de XAMPP, omite SQL y conserva `config/db.php` si ya existe en el destino.
- `tests/smoke_test.php`: valida versión PHP, extensiones y archivos esenciales; no necesita ni modifica la base de datos.

## Requisitos previos

1. Windows y XAMPP instalado en `C:\xampp` (si está en otra ruta, ajustar los scripts y `Jenkinsfile`).
2. Apache y MySQL de XAMPP disponibles para ejecutar la aplicación local.
3. Jenkins instalado como servicio o proceso con permiso para leer el workspace y escribir en `C:\xampp\htdocs`.
4. PHP 8.0 o superior con `PDO` y `pdo_mysql` habilitados.
5. Repositorio Git del proyecto accesible desde Jenkins.

## Configuración de Jenkins

1. Guarda estos archivos en la raíz del repositorio, junto a `index.php`, `config/` e `includes/`.
2. En Jenkins, crea **New Item → Pipeline**.
3. En Pipeline, selecciona **Pipeline script from SCM**, SCM **Git**, indica la URL del repositorio y la rama correspondiente.
4. Deja `Script Path` como `Jenkinsfile` y guarda.
5. Ejecuta **Build Now** con `DESPLEGAR_LOCAL` desactivado. Revisa que pasen las etapas de entorno, sintaxis y prueba de humo.
6. Solo después de revisar la consola, ejecuta **Build with Parameters** y activa `DESPLEGAR_LOCAL` para copiar la aplicación a XAMPP.

### Jenkins ejecutado como servicio de Windows

El usuario del servicio Jenkins necesita permisos de lectura sobre el workspace y escritura sobre `C:\xampp\htdocs`. Si no tiene permisos, configura el servicio con una cuenta local autorizada o ajusta los permisos de la carpeta. No ejecutes Jenkins como administrador permanentemente si no es necesario.

## Uso local

- Destino: `C:\xampp\htdocs\taller_mecanico`
- URL: `http://localhost/taller_mecanico`
- Comprueba que Apache y MySQL estén iniciados en el panel de XAMPP.
- Revisa `config/db.php` para confirmar las credenciales locales correctas. El script de despliegue preserva la configuración que ya exista en el destino.

## Pruebas y evidencias para la entrega

Conserva capturas de:

1. Configuración del job Pipeline en Jenkins.
2. Build exitoso con las etapas `Preparar entorno`, `Validar sintaxis PHP` y `Pruebas de entorno`.
3. Salida de `smoke_test.php` mostrando los mensajes `OK`.
4. Build con `DESPLEGAR_LOCAL` activado y mensaje de despliegue completado.
5. Sistema abierto desde `http://localhost/taller_mecanico`.

## Limitaciones conocidas

- La prueba de humo verifica entorno y archivos esenciales, no sustituye las pruebas funcionales de inicio de sesión, clientes, vehículos, órdenes, refacciones, pagos ni permisos.
- El pipeline no instala Jenkins, no inicia servicios de XAMPP y no crea/importa la base de datos.
- El despliegue copia archivos de aplicación y omite archivos `*.sql` y `.env`. La base de datos debe permanecer bajo administración manual y respaldo.
- El script preserva `config/db.php` existente en el destino. En la primera implementación, si el destino no tiene configuración, se copiará la versión del repositorio; revisa sus credenciales antes de usar el sistema.
- Para un despliegue local de demostración, el parámetro de despliegue permanece desactivado por defecto para evitar sobrescribir archivos accidentalmente.

## Reversión manual

Antes del primer despliegue, copia la carpeta `C:\xampp\htdocs\taller_mecanico` como respaldo. Si la nueva versión presenta errores, detén el uso de la aplicación y restaura esa copia. No restaures ni importes SQL como parte de la reversión, porque este pipeline no cambia la base de datos.
