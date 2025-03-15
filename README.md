# Mini Blog de Noticias PHP

Un simple pero funcional blog de noticias que utiliza News API para mostrar titulares de diferentes categorías, con autores aleatorios proporcionados por Random User API.

## 📋 Requisitos

- PHP 7.0 o superior
- Extensión cURL habilitada (recomendado) o `allow_url_fopen` activado
- Clave de API de [News API](https://newsapi.org/) (gratuita para desarrollo)
- Servidor web (Apache, Nginx, o el servidor integrado de PHP)

## 🚀 Instalación

1. **Clonar o descargar** el proyecto en tu directorio web:

```bash
git clone [url-del-repositorio] php_blog_mini
```

O simplemente descarga y descomprime el archivo ZIP en tu directorio web.

2. **Configurar la API key**:

Crea un archivo `.env` en el directorio raíz del proyecto con el siguiente contenido:

```
NEWS_API_KEY=tu_clave_api_aquí
```

Reemplaza `tu_clave_api_aquí` con tu clave de API de [News API](https://newsapi.org/).

## ⚙️ Configuración

El blog viene preconfigurado para mostrar noticias de la categoría "general". Si deseas cambiar esto, modifica la variable `$category` en `index.php`:

```php
$category = 'technology'; // Cambia a: business, entertainment, general, health, science, sports, technology
```

También puedes activar/desactivar el modo de depuración modificando:

```php
$debug = true; // Cambia a false para desactivar información de depuración
```

## 🖥️ Uso

1. **Iniciar el servidor** (si no estás usando Apache/Nginx):

```bash
cd php_blog_mini
php -S localhost:8000
```

2. **Acceder al blog** a través de tu navegador:

```
http://localhost:8000
```

3. **Navegar por las páginas** usando la paginación en la parte inferior de la página.

## 📂 Estructura de archivos

- `index.php` - Archivo principal que muestra la interfaz del blog
- `functions.php` - Contiene todas las funciones auxiliares para API y manipulación de datos
- `.env` - Almacena las claves de API y otras variables sensibles

## ✨ Características

- Visualización de noticias por categorías
- Paginación de resultados
- Manejo de errores de API
- Modo de depuración para solucionar problemas
- Datos de muestra cuando la API no está disponible
- Diseño responsive con Bootstrap 5
- Autores aleatorios para cada artículo

## ❓ Solución de problemas

**No se muestran noticias reales**:
- Verifica que tu clave API sea válida y esté correctamente configurada en el archivo `.env`
- Asegúrate de que PHP pueda hacer solicitudes HTTP (cURL o allow_url_fopen)
- Revisa la información de depuración si el modo debug está activado

**Problemas de CORS**:
- News API puede tener restricciones de CORS en el plan gratuito. El código intenta manejar esto usando datos de muestra.

## 📝 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo LICENSE.md para más detalles.
