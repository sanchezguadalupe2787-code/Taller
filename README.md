# D'Juniors — Sistema de Gestión para Taller Mecánico

Aplicación web (PHP + MySQL) desarrollada como Producto Mínimo Viable (PMV)
según el documento de justificación y objetivos del proyecto, siguiendo la
metodología **Lean Startup** (Construir – Medir – Aprender).

Esta versión incorpora el rediseño visual **D'Juniors** (fondo negro, acentos
naranja, emblema de auto con llave), el logo oficial del taller, y funciona
como una **Progressive Web App (PWA)**: es responsiva, instalable tanto en
computadora como en teléfono, y conserva el acceso directo a los datos del
taller trabajando con el mismo código tanto en escritorio como en móvil.
Incluye además un **portal de clientes** independiente del panel de
administración, con permisos separados.

## Módulos incluidos

- **Usuarios (staff)**: niveles de acceso (administrador, recepcionista, mecánico).
- **Portal de clientes**: acceso propio para que cada cliente consulte, de forma
  restringida y de solo lectura, únicamente su información.
- **Clientes**: alta, edición, baja y consulta (incluye activar/desactivar el
  acceso de cada cliente al portal).
- **Vehículos**: asociados a cada cliente (marca, modelo, año, placas, etc.).
- **Órdenes de servicio**: problema reportado, diagnóstico, estado, fechas.
- **Servicios y reparaciones**: trabajos realizados, observaciones y recomendaciones.
- **Refacciones**: inventario básico (stock y precio) y refacciones usadas por orden.
- **Cotizaciones y pagos**: montos estimados, pagos, saldo pendiente.
- **Historial**: historial de servicios por vehículo (dentro del módulo de vehículos).
- **Reportes**: órdenes por estado, clientes con más órdenes, refacciones más
  usadas, ingresos mensuales, vehículos por marca.

## Permisos: administración vs. clientes

El sistema separa claramente dos tipos de sesión, con sus propias pantallas
y reglas de acceso:

| | **Staff** (administrador / recepcionista / mecánico) | **Clientes (portal)** |
|---|---|---|
| Inicia sesión en | `index.php`, con su usuario de empleado | `index.php`, con el usuario que le asigna el taller |
| Ve | Todos los clientes, vehículos, órdenes, refacciones, reportes | Únicamente **sus propios** vehículos, órdenes, cotizaciones y pagos |
| Puede | Crear/editar/eliminar según su rol, registrar servicios, refacciones, cotizaciones y pagos | Consultar su información (solo lectura) y editar sus propios datos de contacto y contraseña |
| Administrador únicamente | Gestiona usuarios del staff y **activa/desactiva el acceso al portal de cada cliente** (botón "Acceso al portal del cliente" en la ficha del cliente) | — |

Un cliente **no puede** entrar a ninguna pantalla del panel de staff
(`dashboard.php`, `clientes/`, `vehiculos/`, `ordenes/`, `refacciones/`,
`cotizaciones/`, `reportes/`, `usuarios/`) ni viceversa: cada petición pasa
por `requerirStaff()` o `requerirCliente()` en `includes/auth.php`, que
redirige automáticamente si el rol no corresponde.

### Cómo dar acceso al portal a un cliente

1. Inicia sesión como administrador.
2. Ve a **Clientes → (elige un cliente) → Ver**.
3. En el panel **"Acceso al portal del cliente"**, define un usuario (y
   opcionalmente una contraseña; si se deja en blanco, el sistema genera una).
4. Comparte el usuario y la contraseña con el cliente. Desde ahí puede
   desactivarse el acceso en cualquier momento desde la misma pantalla.

## La app como PWA (instalable en computadora y teléfono)

- La aplicación es **responsiva**: en pantallas angostas el menú superior se
  colapsa en un botón de hamburguesa y aparece una barra de navegación
  inferior tipo app; en escritorio se muestra el menú horizontal completo.
- Incluye `manifest.json` e íconos en todos los tamaños requeridos (incluido
  un ícono *maskable* para Android), por lo que el navegador ofrecerá
  **"Instalar aplicación"** (Chrome/Edge en Android y escritorio) o
  **"Agregar a pantalla de inicio"** (Safari en iOS).
- Un *service worker* (`service-worker.js`) cachea los archivos estáticos
  (CSS, JS, imágenes, íconos) para que la app cargue más rápido y muestre una
  pantalla de **"sin conexión"** (`offline.html`) si el teléfono o la
  computadora se quedan sin internet. Las páginas con datos del taller
  (órdenes, saldos, etc.) **nunca se cachean**, para evitar mostrar
  información desactualizada.
- Dentro de la app aparece un botón flotante **"Instalar app"** cuando el
  navegador detecta que la instalación es posible.

## Paleta de color (D'Juniors)

| Uso                              | Color      |
|-----------------------------------|-----------|
| Negro (fondo, header, nav, login) | `#0a0a0c` |
| Naranja (botones, acentos, logo)  | `#ff7a1a` |
| Naranja claro (hover)             | `#ff9a4d` |
| Fondo general                     | `#121214` |
| Verde (éxito)                     | `#2fbf71` |
| Rojo (alertas/errores)            | `#ef4444` |
| Ámbar (advertencia)               | `#f5a623` |

## Requisitos

- PHP 8.0 o superior con extensión PDO MySQL.
- MySQL o MariaDB.
- Servidor web (Apache, Nginx) o el servidor embebido de PHP.
- Para la instalación como app (PWA), se recomienda servir el sitio por
  **HTTPS** (o `localhost`, que los navegadores tratan como seguro); sin
  HTTPS el *service worker* y el botón de instalación no funcionarán.

## Instalación

1. **Crear la base de datos**: importa el archivo `sql/taller_mecanico.sql`:

   ```
   mysql -u root -p < sql/taller_mecanico.sql
   ```

   > **¿Ya tienes una base de datos previa del sistema?** Agrega las columnas
   > nuevas del portal de clientes con:
   > ```sql
   > ALTER TABLE clientes
   >   ADD COLUMN usuario VARCHAR(50) NULL UNIQUE,
   >   ADD COLUMN password VARCHAR(255) NULL,
   >   ADD COLUMN portal_activo TINYINT(1) NOT NULL DEFAULT 0,
   >   ADD COLUMN fecha_ultimo_acceso DATETIME NULL;
   > ```

2. **Configurar la conexión**: edita `config/db.php` con los datos de tu
   servidor (host, usuario, contraseña).

3. **Levantar el servidor**:

   - Con el servidor embebido de PHP (para pruebas rápidas):
     ```
     php -S localhost:8000
     ```
     y abre `http://localhost:8000` en el navegador.

   - O coloca la carpeta `taller_mecanico` dentro de la carpeta pública de
     tu servidor (por ejemplo `htdocs` en XAMPP, o `www` en WampServer) y
     accede desde `http://localhost/taller_mecanico`.

   - Para probar la instalación como app desde un teléfono, el sitio debe
     ser accesible por HTTPS desde internet (por ejemplo con un hosting, o
     un túnel como ngrok durante pruebas).

4. **Iniciar sesión**:

   - **Administrador (staff)**:
     - Usuario: `admin`
     - Contraseña: `admin123`

   - **Cliente de prueba (portal)**:
     - Usuario: `juan.perez`
     - Contraseña: `cliente123`

   Se recomienda crear un nuevo usuario administrador y desactivar o cambiar
   la contraseña de estos usuarios de ejemplo después de la primera prueba.

## Estructura de carpetas

```
taller_mecanico/
├── config/          Configuración de conexión a la base de datos
├── includes/        Cabecera (con logo D'Juniors), pie de página y control de sesión/roles
├── css/             Hoja de estilos con la paleta D'Juniors (negro/naranja)
├── js/              Script general (menú, validaciones, PWA, mostrar/ocultar contraseña)
├── assets/          Logo e íconos de la app (incluye los íconos PWA en todos los tamaños)
├── sql/             Script de creación de la base de datos y datos de ejemplo
├── clientes/        Módulo de clientes (CRUD + activar/desactivar acceso al portal)
├── vehiculos/        Módulo de vehículos (CRUD + historial)
├── ordenes/         Módulo de órdenes de servicio (con servicios, refacciones,
│                    cotizaciones y pagos integrados)
├── refacciones/     Módulo de inventario de refacciones
├── cotizaciones/    Vista general de cotizaciones y pagos
├── reportes/        Reportes básicos del sistema
├── usuarios/        Administración de usuarios (solo administradores)
├── portal/          Portal de clientes: panel propio, de solo lectura, por cliente
│                    (index, vehículos, vehículo, órdenes, orden, perfil)
├── uploads/         Carpeta reservada para archivos futuros (fotos, etc.)
├── manifest.json    Manifiesto de la PWA (nombre, colores, íconos)
├── service-worker.js  Service worker: caché de archivos estáticos y modo sin conexión
├── offline.html     Pantalla mostrada cuando no hay conexión
├── index.php        Página de inicio de sesión única (staff y clientes)
├── logout.php       Cierre de sesión
└── dashboard.php    Panel principal del staff con estadísticas
```

## Notas sobre el alcance (versión PMV)

Conforme al documento de proyecto, esta primera versión **no incluye**:
facturación electrónica, diagnóstico automático de fallas, integración con
escáneres automotrices, venta en línea de refacciones, pagos en línea, ni
integración con sistemas externos. La aplicación móvil se entrega como una
PWA instalable (sin necesidad de tiendas de aplicaciones); una app nativa
independiente (iOS/Android) podrá evaluarse como mejora futura a partir de
la retroalimentación obtenida durante la validación del PMV con usuarios
reales (ciclo Construir – Medir – Aprender).
