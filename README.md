# Form2Mail-PHP API (PHP con Slim)

Form2Mail-PHP API es una aplicación RESTful ligera y segura, construida en PHP con el framework Slim, diseñada para enviar correos electrónicos de forma simple y eficiente. Integra PHPMailer para una conexión confiable con servidores SMTP, ofreciendo una solución escalable y fácil de implementar para tus necesidades de envío de emails.

Este proyecto es una adaptación en PHP inspirada en el original [Form2Mail](https://github.com/dansasser/form2mail), creado en Python. Me encantó su enfoque sencillo pero potente para enviar correos a través de una API, y esta versión busca replicar esa esencia con las fortalezas de PHP y Slim.

## Características
- Envío de correos electrónicos a través de SMTP (compatible con Gmail y otros proveedores).
- Autenticación mediante clave API (`X-API-KEY`).
- Soporte para HTTPS y CORS.
- Configuración mediante variables de entorno con `.env`.
- Respuestas en formato JSON para fácil integración.
- Validación de campos requeridos (`to`, `subject`, `body`) y formato de email para `to`.
- Cuerpo del correo generado como lista (`campo: valor`) con soporte para campos adicionales.

## Requisitos
- PHP 8.3 o superior.
- Composer para la gestión de dependencias.
- Servidor SMTP (ej: Gmail, SendGrid, etc.).
- Servidor web con soporte para PHP (LiteSpeed, Apache, Nginx).

## Instalación

### 1. Clonar el repositorio
```bash
git clone https://github.com/jure-ve/form2mail-php.git
cd form2mail-php
```

### 2. Instalar dependencias
```bash
composer install
```

### 3. Configurar variables de entorno
Crea un archivo `.env` en el directorio raíz basado en `example.env`:
```env
SMTP_HOST=smtp.gmail.com
SMTP_USERNAME=tu-correo@gmail.com
SMTP_PASSWORD=tu-contraseña-o-app-password
SMTP_PORT=587
API_KEY=tu-clave-secreta
APP_ENV=prod
CORS_ALLOWED_ORIGINS=http://dominio.com
```

**Nota:** Para Gmail con 2FA, genera una contraseña de aplicación.

### 4. Configuración del servidor
- Ubica los archivos en tu directorio de servidor (ej: `/var/www/dominio.com/public_html/`).
- Configura el Document Root para que apunte a `public/`.
- Añade este `.htaccess` en `public/` si usas Apache:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

- Para Nginx, usa esta configuración:
```nginx
server {
    listen 80;
    server_name tu-servidor.com;
    root /var/www/form2mail-php/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    # Opcional: Configuración de CORS
    add_header 'Access-Control-Allow-Origin' '*';
    add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS';
}
```

- Permisos recomendados:
```bash
chmod -R 755 public
chown -R www-data:www-data /ruta/de/tu/proyecto
```

### 5. Probar localmente
```bash
php -S localhost:8080 -t public
```

## Uso

**Endpoint principal:** `POST /api/send`

### Solicitud
El cuerpo debe incluir al menos `to`, `subject`, y `body`. Campos adicionales se incluirán en el correo como parte del cuerpo en formato `campo: valor`.

```bash
curl -X POST http://localhost:8080/api/send \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: tu-clave-secreta" \
  -d '{"to": "destinatario@ejemplo.com", "subject": "Asunto", "body": "Mensaje", "extra": "Dato adicional"}'
```

### Ejemplo de correo generado
```text
to: destinatario@ejemplo.com
subject: Asunto
body: Mensaje
extra: Dato adicional
```

### Respuestas
| Código | Ejemplo de respuesta |
|--------|-----------------------|
| 200 OK | ```json {"status": "success", "message": "Correo enviado correctamente"}``` |
| 400 Bad Request | ```json {"status": "error", "message": "El campo 'to' es obligatorio"}```<br>```json {"status": "error", "message": "El campo 'to' debe ser una dirección de correo electrónico válida"}```<br>```json {"status": "error", "message": "El cuerpo de la solicitud debe ser un JSON válido"}``` |
| 401 Unauthorized | ```json {"error": "No autorizado"}``` |
| 405 Method Not Allowed | ```json {"error": "Método no permitido"}``` |
| 500 Server Error | ```json {"status": "error", "message": "Error al enviar el correo: <detalle>"}``` |

## Estructura del proyecto
```
form2mail-php/
├── public/
│   ├── index.php       # Punto de entrada
│   └── .htaccess       # Reglas de reescritura
├── src/
│   ├── Controllers/
│   │   ├── MailController.php  # Lógica de envío
│   │   └── RootController.php  # Ruta raíz
│   ├── Services/
│   │   ├── MailService.php     # Envío de correos
│   │   └── ValidationService.php  # Validación de datos
│   └── Middleware/
│       ├── AuthMiddleware.php  # Autenticación API
│       └── JsonValidationMiddleware.php  # Validación JSON
├── vendor/             # Dependencias
├── example.env         # Ejemplo de archivo de Variables. Puedes cambiarlo y renonbrarlo a '.env'
├── composer.json       # Configuración de Composer
└── README.md           # Documentación
```

## Dependencias principales
| Librería | Uso |
|----------|-----|
| Slim Framework | Backend API |
| PHPMailer | Envío de emails |
| phpdotenv | Gestión de variables |
| PHP-DI | Inyección de dependencias |

## Contribuir
1. Haz fork del repositorio.
2. Crea una rama (`git checkout -b feature/nueva-funcionalidad`).
3. Haz commit de tus cambios (`git commit -am 'Añade funcionalidad X'`).
4. Haz push a la rama (`git push origin feature/nueva-funcionalidad`).
5. Abre un Pull Request.

## Licencia
MIT License - Ver [LICENSE](LICENSE.txt) para detalles.