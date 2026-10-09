# Agenda de Contactos — Sistema Web CRUD (PHP + MySQL/MariaDB)

Aplicación web para **Crear, Leer, Actualizar y Eliminar** contactos, desplegada en una **arquitectura de 2 niveles**:

```
 ┌──────────────────────┐         TCP 3306          ┌──────────────────────────┐
 │  Nodo HTTP           │ ───────────────────────▶  │  Nodo BD                 │
 │  10.10.10.20         │   PDO (sentencias         │  10.10.10.30             │
 │  Apache + PHP        │   preparadas)             │  MySQL / MariaDB         │
 │  /var/www/html/agenda│                           │  BD: agenda_db           │
 └──────────────────────┘                           └──────────────────────────┘
        ▲  HTTP :80
        │
   Navegador del usuario  →  http://10.10.10.20/agenda/
```

---

## 1. Características

- Backend en **PHP** con **PDO** y **sentencias preparadas reales** (`ATTR_EMULATE_PREPARES = false`).
- Frontend con **Bootstrap 5.3**, **Bootstrap Icons**, fuente **Inter** y **SweetAlert2** (todo por CDN).
- Avatares generados automáticamente con **ui-avatars.com** e imágenes de **Unsplash**.
- **Validaciones** en el cliente (HTML5 + Bootstrap) y en el servidor (PHP): campos obligatorios, longitudes, formato de email, teléfono, URL de LinkedIn, fecha no futura y listas cerradas de género y tipo de sangre.
- **Confirmación visual antes de eliminar** (ventana modal de SweetAlert2).
- Protección **CSRF** en todos los formularios, escape de salida contra **XSS**, eliminación solo por **POST**.
- Búsqueda por nombre, email o teléfono, y tarjetas de estadísticas por género.
- Diseño **responsivo** (las columnas secundarias se ocultan en pantallas pequeñas).

---

## 2. Estructura de archivos

```
agenda/
├── conexion.php            # Credenciales y conexión PDO al Nodo BD (10.10.10.30)
├── index.php               # READ   – Listado, búsqueda y estadísticas
├── crear.php               # CREATE – Formulario + INSERT
├── editar.php              # UPDATE – Formulario precargado + UPDATE
├── eliminar.php            # DELETE – Elimina por POST (con token CSRF)
├── database.sql            # Script SQL: BD, tabla, usuario/permisos y datos de prueba
├── README.md               # Este documento
├── includes/
│   ├── .htaccess           # Bloquea el acceso directo vía navegador
│   ├── funciones.php       # Sesión, CSRF, mensajes flash, validaciones y utilidades
│   ├── header.php          # Cabecera HTML, barra de navegación y alertas
│   ├── footer.php          # Pie de página y scripts JS
│   └── formulario.php      # Formulario reutilizado por crear.php y editar.php
└── assets/
    ├── css/estilos.css     # Estilos personalizados
    └── js/app.js           # Validación en cliente y confirmación de eliminación
```

> `instrucciones.md`, `skills-lock.json`, `.claude/` y `.agents/` no forman parte de la aplicación y **no** deben copiarse al servidor.

---

## 3. Usuarios, contraseñas y configuración de red preestablecidos

| Parámetro               | Valor                        |
|-------------------------|------------------------------|
| IP Nodo HTTP (Apache/PHP) | `10.10.10.20`              |
| IP Nodo BD (MySQL/MariaDB)| `10.10.10.30`              |
| Puerto MySQL/MariaDB    | `3306`                       |
| Base de datos           | `agenda_db`                  |
| Tabla                   | `contacto`                   |
| Usuario BD              | `sysadmin`                   |
| Contraseña BD           | `Uac_123456`                 |
| Hosts permitidos al usuario | `'%'` y `'10.10.10.20'`  |
| Charset                 | `utf8mb4`                    |
| URL de la aplicación    | `http://10.10.10.20/agenda/` |

Las credenciales están centralizadas en **`conexion.php`** (constantes `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`). Si cambia algún dato, solo modifique ese archivo.

---

## 4. Despliegue en el Nodo de Base de Datos (`10.10.10.30`)

> Ejemplos para Debian/Ubuntu. En RHEL/Rocky/Alma use `dnf` y los nombres de servicio indicados entre paréntesis.

### 4.1 Instalar el servidor

```bash
sudo apt update
sudo apt install -y mariadb-server        # o: sudo apt install -y mysql-server
sudo systemctl enable --now mariadb       # (mysqld en MySQL / RHEL)
```

### 4.2 Permitir conexiones remotas

Edite el parámetro `bind-address` para que escuche en la red:

- MariaDB: `/etc/mysql/mariadb.conf.d/50-server.cnf`
- MySQL: `/etc/mysql/mysql.conf.d/mysqld.cnf`
- RHEL: `/etc/my.cnf.d/mariadb-server.cnf`

```ini
[mysqld]
bind-address = 0.0.0.0        # o directamente 10.10.10.30
```

```bash
sudo systemctl restart mariadb            # o mysql
sudo ss -tlnp | grep 3306                 # Debe mostrar 0.0.0.0:3306 o 10.10.10.30:3306
```

### 4.3 Abrir el puerto 3306 solo para el Nodo HTTP

```bash
# UFW (Debian/Ubuntu)
sudo ufw allow from 10.10.10.20 to any port 3306 proto tcp

# firewalld (RHEL/Rocky/Alma)
sudo firewall-cmd --permanent --add-rich-rule='rule family="ipv4" source address="10.10.10.20" port port="3306" protocol="tcp" accept'
sudo firewall-cmd --reload
```

### 4.4 Ejecutar el script SQL

Copie `database.sql` al Nodo BD (por ejemplo, desde su equipo):

```bash
scp database.sql usuario@10.10.10.30:/tmp/
```

Ejecútelo como `root` de MySQL/MariaDB:

```bash
sudo mysql -u root -p < /tmp/database.sql
```

O desde la consola interactiva:

```sql
sudo mysql -u root -p
MariaDB [(none)]> SOURCE /tmp/database.sql;
```

El script realiza, en orden:

1. `CREATE DATABASE agenda_db` (utf8mb4).
2. `CREATE TABLE contacto` con los 8 campos requeridos.
3. Crea el usuario `sysadmin` (`'%'` y `'10.10.10.20'`) y le otorga `GRANT ALL PRIVILEGES ON agenda_db.*`.
4. Inserta **7 contactos de prueba**.
5. Muestra el total de registros para verificar.

> ⚠️ El script incluye `DROP TABLE IF EXISTS contacto;`: si se vuelve a ejecutar, **borra los datos existentes** y recarga los de prueba.

Verifique:

```bash
mysql -u sysadmin -p'Uac_123456' -e "SELECT id, nombres, email FROM agenda_db.contacto;"
```

---

## 5. Despliegue en el Nodo HTTP (`10.10.10.20`)

### 5.1 Instalar Apache, PHP y la extensión PDO MySQL

```bash
# Debian/Ubuntu
sudo apt update
sudo apt install -y apache2 php libapache2-mod-php php-mysql php-mbstring mariadb-client
sudo systemctl enable --now apache2

# RHEL/Rocky/Alma
# sudo dnf install -y httpd php php-mysqlnd php-mbstring mariadb
# sudo systemctl enable --now httpd php-fpm
```

Requisito: **PHP 7.4 o superior** (se usan funciones flecha). Verifique con:

```bash
php -v
php -m | grep -i pdo_mysql      # Debe mostrar: pdo_mysql
```

### 5.2 Copiar los archivos de la aplicación

Desde su equipo (en la carpeta del proyecto):

```bash
ssh usuario@10.10.10.20 "mkdir -p /tmp/agenda"
scp -r conexion.php index.php crear.php editar.php eliminar.php includes assets usuario@10.10.10.20:/tmp/agenda/
```

> Asegúrese de copiar también el archivo oculto `includes/.htaccess` (`scp -r` lo incluye).

En el Nodo HTTP:

```bash
sudo mkdir -p /var/www/html/agenda
sudo cp -r /tmp/agenda/. /var/www/html/agenda/
sudo chown -R www-data:www-data /var/www/html/agenda      # RHEL: apache:apache
sudo find /var/www/html/agenda -type d -exec chmod 755 {} \;
sudo find /var/www/html/agenda -type f -exec chmod 644 {} \;
```

### 5.3 Habilitar `.htaccess` (protección de `includes/`)

En `/etc/apache2/apache2.conf` (RHEL: `/etc/httpd/conf/httpd.conf`) cambie, para `/var/www/`:

```apache
<Directory /var/www/>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

```bash
sudo systemctl restart apache2      # RHEL: httpd
```

### 5.4 Permitir HTTP y la conexión saliente a la BD

```bash
# Debian/Ubuntu
sudo ufw allow 80/tcp

# RHEL/Rocky/Alma
sudo firewall-cmd --permanent --add-service=http && sudo firewall-cmd --reload
sudo setsebool -P httpd_can_network_connect_db 1    # SELinux: permite a Apache conectarse a MySQL remoto
```

### 5.5 Probar la conectividad con el Nodo BD

```bash
mysql -h 10.10.10.30 -P 3306 -u sysadmin -p'Uac_123456' agenda_db -e "SELECT COUNT(*) FROM contacto;"
```

### 5.6 Acceder a la aplicación

Abra en el navegador: **http://10.10.10.20/agenda/**

Deberían visualizarse inmediatamente los 7 contactos de prueba.

> El navegador del cliente necesita acceso a Internet para cargar Bootstrap, iconos, fuentes, SweetAlert2 y las imágenes externas (CDN). Sin Internet, la aplicación sigue funcionando pero sin estilos; la confirmación de eliminación usa entonces el `confirm()` nativo.

---

## 6. Uso

| Acción       | Cómo                                                                 |
|--------------|----------------------------------------------------------------------|
| **Listar**   | Página principal `index.php`.                                        |
| **Buscar**   | Escriba en el cuadro de búsqueda (nombre, email o teléfono).        |
| **Crear**    | Botón **Nuevo contacto** → complete el formulario → **Guardar**.     |
| **Editar**   | Icono ✏️ en la fila → modifique → **Actualizar**.                    |
| **Eliminar** | Icono 🗑️ en la fila → confirme en la ventana emergente.              |

Valores permitidos:

- **Género:** Masculino, Femenino, Otro.
- **Tipo de sangre:** A+, A-, B+, B-, AB+, AB-, O+, O-.
- **LinkedIn:** opcional; debe ser una URL de `linkedin.com`.
- **Email:** no se permiten correos duplicados entre contactos.

---

## 7. Solución de problemas

| Síntoma | Causa probable / Solución |
|---------|---------------------------|
| "No se pudo conectar a la base de datos" | Revise `bind-address`, el firewall del Nodo BD (puerto 3306) y que el servicio esté activo. En RHEL active `httpd_can_network_connect_db`. |
| `SQLSTATE[HY000] [1045] Access denied` | Usuario/contraseña o host no autorizado: vuelva a ejecutar la sección 3 de `database.sql`. |
| `could not find driver` | Falta la extensión: `sudo apt install php-mysql && sudo systemctl restart apache2`. |
| Página en blanco / error 500 | Revise `sudo tail -f /var/log/apache2/error.log` (RHEL: `/var/log/httpd/error_log`). |
| Error 500 por `.htaccess` | Módulo de autorización deshabilitado: `sudo a2enmod authz_core && sudo systemctl restart apache2`. |
| "Token de seguridad inválido" | La sesión expiró: recargue la página e intente nuevamente. |

---

## 8. Recomendaciones de seguridad para producción

- Cambiar la contraseña `Uac_123456` por una robusta y restringir el usuario solo a `'sysadmin'@'10.10.10.20'` (eliminar `'sysadmin'@'%'`).
- Otorgar solo `SELECT, INSERT, UPDATE, DELETE` en lugar de `ALL PRIVILEGES`.
- Habilitar HTTPS en Apache (por ejemplo, con certificados de Let's Encrypt o internos).
- Desactivar `display_errors` en `php.ini`.
