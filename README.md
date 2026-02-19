# web-impresiones-3d

Tienda web de **impresiones 3D** hecha en **PHP**, con un **front público** (catálogo + ficha + compra + contacto) y un **panel de administración** protegido por login (compras, clientes y mensajes).

> Persistencia **sin base de datos**: se guarda todo en **JSON** y contenido informativo en **XML**.

---

## ✨ Funcionalidades

### Front (público)
- **Catálogo** de productos (lee `data/productos_enriched.json`).
- **Ficha de producto** por `slug` (y compatibilidad por `ref`/`id`).
- **Compra**:
  - Selección de **cantidad** y **color**
  - Formulario de **datos del cliente**
  - Guarda:
    - clientes en `data/clientes.json` (upsert por email)
    - compras en `data/compras_ficticias.json`
  - Redirección **PRG** a pantalla de finalización (evita reenvío del POST).
- **Contacto**:
  - Configuración visual desde `data/contacto.xml`
  - Guardado de mensajes en `data/contactos_recibidos.json`
- **Nosotros**:
  - Contenido desde `data/nosotros.xml`
- Cabecera y footer **modularizados** (`front/inc/header.php` y `front/inc/footer.php`).

### Admin (panel)
- **Login por sesión** (rutas protegidas con `require_admin()`).
- **Dashboard** con contadores y última compra (`admin/index.php`).
- **Compras**:
  - Listado con búsqueda (`admin/compras.php`)
  - Ficha de compra y cambio de **estado** (`admin/compra.php`)
- **Clientes**:
  - Listado + búsqueda + detalle (`admin/clientes.php`)
- **Contactos**:
  - Listado + lectura + eliminación con confirmación (`admin/contactos.php`)
- Acciones sensibles con **CSRF token** (estado de compra y borrado de mensajes).

---

## 🧱 Estructura del proyecto

```txt
├── admin
│   ├── assets
│   │   ├── admin.css
│   │   └── login.css
│   ├── inc
│   │   ├── auth.php
│   │   ├── auth_config.php
│   │   ├── footer.php
│   │   └── header.php
│   ├── clientes.php
│   ├── compra.php
│   ├── compras.php
│   ├── contactos.php
│   ├── index.php
│   ├── login.php
│   └── logout.php
├── data
│   ├── clientes.json
│   ├── compras_ficticias.json
│   ├── contacto.xml
│   ├── contactos_recibidos.json
│   ├── nosotros.xml
│   └── productos_enriched.json
└── front
    ├── assets
    │   ├── compra.css
    │   ├── contacto.css
    │   ├── finalizacion.css
    │   ├── index.css
    │   ├── nosotros.css
    │   ├── producto.css
    │   └── styles.css
    ├── inc
    │   ├── footer.php
    │   └── header.php
    ├── static
    │   ├── (imágenes, héroes y productos)
    ├── compra.php
    ├── contacto.php
    ├── finalizacion.php
    ├── index.php
    ├── nosotros.php
    └── producto.php
````

---

## 🚀 Cómo ejecutar en local (XAMPP)

### Requisitos

* **PHP 8+** recomendado (usa `declare(strict_types=1)` y `random_bytes()`).
* Servidor local tipo **XAMPP** (Apache + PHP).

### Pasos

1. Copia la carpeta del proyecto dentro de:

   * `C:\xampp\htdocs\web-impresiones-3d`
2. Asegura permisos de escritura en la carpeta:

   * `data/` (para guardar clientes, compras y contactos)
3. Inicia **Apache** en XAMPP.
4. Abre el front:

   * `http://localhost/web-impresiones-3d/front/index.php`
5. Abre el admin:

   * `http://localhost/web-impresiones-3d/admin/login.php`

### Credenciales del admin (demo)

Se definen en `admin/inc/auth_config.php`:

* **Usuario:** `piero7ov`
* **Contraseña:** `piero7ov`

> Nota: es un proyecto didáctico; las credenciales están en texto plano para simplificar.

---

## 🔁 Flujo de la web

### 1) Catálogo → Ficha

* `front/index.php`

  * Lee `data/productos_enriched.json`
  * Renderiza tarjetas y enlaza a:

    * `front/producto.php?slug=...` (principal)
    * o fallback por `ref`/`id`

### 2) Ficha → Compra

* `front/producto.php`

  * Carga catálogo y busca producto por `slug`/`ref`/`id`
  * Muestra detalles + bullets/tags
  * Formulario **POST** a `front/compra.php` con:

    * `action=start`
    * `slug/ref/id`, `qty`, `color`

### 3) Compra → Confirmación

* `front/compra.php`

  * Muestra formulario de cliente
  * Al confirmar (**POST** `action=confirm`):

    1. Valida campos obligatorios + email
    2. **Upsert cliente por email** en `data/clientes.json`
    3. Guarda compra en `data/compras_ficticias.json` con `order_id`
    4. Redirige a `front/finalizacion.php?order=...` (patrón PRG)

### 4) Finalización

* `front/finalizacion.php`

  * Lee `data/compras_ficticias.json`
  * Busca por `order_id` y muestra resumen

### 5) Contacto

* `front/contacto.php`

  * Lee configuración de `data/contacto.xml`
  * Guarda mensajes en `data/contactos_recibidos.json`

### 6) Admin

* Todas las rutas del panel ejecutan:

  * `require_admin()` (si no hay sesión → `login.php`)
* `admin/compra.php`:

  * Cruza `compras_ficticias.json` con `clientes.json`
  * Permite cambiar **estado** (con CSRF)

---

## 🧩 Funciones clave

### Front

* `h($s)` → sanitiza salida HTML (previene XSS).
* `load_products($jsonPath)` → carga y valida `productos_enriched.json`.
* `find_product($products, $slug, $ref, $id)` → localiza un producto.
* `clamp_int()` → asegura cantidad dentro de rango.
* `read_json_list()` / `write_json_list()` → lectura/escritura JSON con `LOCK_EX`.
* `upsert_client()` → crea/actualiza cliente por email.
* `new_order_id()` / `new_client_id()` → IDs únicos.

### Admin

* `is_admin_logged()` / `require_admin()` → control de sesión y protección de rutas.
* `try_login()` / `admin_logout()` → login/logout.
* `load_json_list()` → carga datasets aunque vengan como lista u objeto con keys típicas.
* `get_field()` / `set_field()` → acceso/actualización por rutas tipo `compra.estado`.
* CSRF simple con `$_SESSION["csrf_admin"]` y `hash_equals()`.

---

## 🗃️ Formato de datos

### `data/productos_enriched.json`

* `products[]` contiene campos como:

  * `nombre`, `short_desc`, `descripcion`
  * `precio`, `material`, `tamano`, `categoria`
  * `slug`, `imagen`
  * `bullets[]`, `tags[]`
  * `seo_title`, `seo_description`
* Incluye metadata (`generated_at`, info de Ollama/modelo) para trazabilidad.

### `data/clientes.json`

Lista plana:

* `id`, `created_at`, `updated_at`
* `nombre`, `email`, `telefono`, `direccion`, `notas`

### `data/compras_ficticias.json`

Lista plana:

* `order_id`, `created_at`, `cliente_id`
* `compra`: `{ product_slug, product_name, material, tamano, categoria, precio_unit, qty, color, total, (estado opcional) }`
* En algunas entradas puede existir `estado` también a nivel raíz (dependiendo de la edición/ejemplos).

### `data/contactos_recibidos.json`

Lista plana:

* `created_at`, `nombre`, `email`, `asunto`, `mensaje`

---

## 🛡️ Seguridad (en el contexto del proyecto)

* Sanitización de salida con `htmlspecialchars` (`h()`).
* Escritura de JSON con `LOCK_EX`.
* Acciones críticas del admin con **CSRF token**.
* Login por sesión con redirección controlada mediante `next`.

---

## 👤 Autor

**Desarrollado por Piero Olivares · PieroDev**

