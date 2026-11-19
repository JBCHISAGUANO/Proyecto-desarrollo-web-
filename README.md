# 📒 Agenda personal de contactos (PHP + MySQL)

Aplicación web que permite a cada usuario **registrarse, iniciar sesión y administrar su propia agenda de contactos**. Está desarrollada en PHP puro con PDO y MySQL, aplicando prácticas básicas de seguridad: contraseñas con hash, sesiones seguras, validación en el servidor y aislamiento de los datos de cada usuario.

## Funcionalidades

- Registro de usuarios con validación de correo único.
- Inicio y cierre de sesión con manejo de sesiones.
- Panel privado con nombre, correo y lista de contactos del usuario.
- Crear, editar y eliminar contactos (la eliminación pide confirmación).
- Cada usuario solo puede ver, editar y eliminar **sus propios** contactos.

## Tecnologías utilizadas

| Componente | Detalle |
|---|---|
| Lenguaje | PHP 8.1 o superior |
| Base de datos | MySQL 5.7+ / MariaDB 10.3+ (InnoDB, utf8mb4) |
| Acceso a datos | PDO con sentencias preparadas |
| Interfaz | HTML5 y CSS3 (sin frameworks) |
| Servidor local | XAMPP, WAMP, Laragon o el servidor integrado de PHP |

## Requisitos

- PHP 8.1+ con las extensiones `pdo_mysql` y `mbstring` (vienen activas en XAMPP/Laragon).
- MySQL o MariaDB.
- Un navegador web moderno.

## Instalación

1. **Clonar el repositorio** (o copiar la carpeta del proyecto):
   ```bash
   git clone https://github.com/TU_USUARIO/agenda-contactos.git
   ```
2. **Ubicar el proyecto** en la carpeta pública del servidor, por ejemplo:
   - XAMPP: `C:\xampp\htdocs\agenda-contactos`
   - Laragon: `C:\laragon\www\agenda-contactos`
3. **Crear la base de datos**: abrir phpMyAdmin → pestaña *Importar* → seleccionar `sql/agenda.sql` → *Continuar*. También se puede hacer desde consola:
   ```bash
   mysql -u root -p < sql/agenda.sql
   ```
4. **Configurar la conexión** en `config/config.php` (por defecto: host `localhost`, base `agenda_contactos`, usuario `root`, sin contraseña). Alternativamente, definir las variables de entorno `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASS`.
5. **Abrir la aplicación** en el navegador: `http://localhost/agenda-contactos/`

### Alternativa sin Apache (servidor integrado de PHP)

```bash
php -S localhost:8000 -t .
```
y abrir `http://localhost:8000`.

## Estructura del proyecto

```
agenda-contactos/
├── assets/css/style.css        Estilos
├── config/config.php           Parámetros de conexión
├── includes/
│   ├── bootstrap.php           Sesión, PDO, CSRF, validaciones y control de acceso
│   ├── header.php / footer.php Plantilla común
│   └── form_contacto.php       Formulario reutilizado (crear / editar)
├── sql/agenda.sql              Script de la base de datos
├── index.php                   Redirige según el estado de la sesión
├── registro.php                Registro de usuarios
├── login.php / logout.php      Autenticación
├── panel.php                   Panel privado con la lista de contactos
├── contacto_nuevo.php          Crear contacto
├── contacto_editar.php         Editar contacto
└── contacto_eliminar.php       Confirmar y eliminar contacto
```

## Base de datos

- **usuarios**: `id`, `nombre`, `correo` (único), `password` (hash), `fecha_registro`.
- **contactos**: `id`, `id_usuario` (clave foránea hacia `usuarios.id`, con `ON DELETE CASCADE`), `nombre`, `telefono`, `correo`, `ciudad`.

## Medidas de seguridad implementadas

| Medida | Dónde |
|---|---|
| `password_hash()` al registrar y `password_verify()` al iniciar sesión | `registro.php`, `login.php` |
| Validación de todos los formularios en el servidor | `registro.php`, `includes/bootstrap.php` |
| Verificación de sesión activa en cada página protegida (`requiere_login()`) | `panel.php`, `contacto_*.php` |
| Consultas filtradas por `id_usuario` (impide ver/editar/borrar contactos ajenos cambiando la URL) | `contacto_del_usuario()` y las sentencias `UPDATE`/`DELETE` |
| Mensajes de error genéricos (“Correo o contraseña incorrectos”, “El contacto no existe o no está disponible”) | `login.php`, `bootstrap.php` |
| Sentencias preparadas (contra inyección SQL) | Todo el acceso a datos |
| Escape de salida con `htmlspecialchars` (contra XSS) | Función `e()` |
| Token CSRF en todos los formularios POST | `csrf_campo()` / `csrf_valido()` |
| `session_regenerate_id()` al iniciar sesión (contra fijación de sesión) | `iniciar_sesion_usuario()` |
| Cookie de sesión `HttpOnly` y `SameSite=Lax`; expiración por inactividad (30 min) | `bootstrap.php` |
| Eliminación solo por POST tras una pantalla de confirmación | `contacto_eliminar.php` |

## Autor

Josue Chisaguano — Semestre 5 / Desarrollo Web — 2026
