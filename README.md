# MegaCmd PHP Wrapper

Una potente y completa envoltura (wrapper) de PHP para interactuar con **MEGAcmd**, la interfaz de línea de comandos oficial de MEGA.nz. Este proyecto permite gestionar tus archivos en la nube de MEGA directamente desde scripts de PHP, soportando tanto sistemas **Windows 10/11** como **Linux**.

---

## 🚀 Características Principales

- **Multiplataforma**: Detección automática y soporte nativo para entornos Windows (.bat) y Linux.
- **Cobertura Total**: Soporte para casi todos los comandos de MEGAcmd (`ls`, `put`, `get`, `mkdir`, `backup`, `export`, etc.).
- **Análisis de Salida (Parsing)**: Convierte la salida de texto plano de la consola en arrays estructurados de PHP para un manejo sencillo.
- **Gestión de Sesiones**: Control de inicio/cierre de sesión y visualización de sesiones activas.
- **Copias de Seguridad**: Interfaz para crear, listar y gestionar backups automatizados.
- **Diseño Extensible**: Base sólida para añadir nuevos comandos o personalizar los existentes.

---

## 📋 Requisitos Prolegómenos

Para que este wrapper funcione, debes tener instalado **MEGAcmd** en tu sistema:

- **Windows**: Descarga e instala el instalador oficial desde [mega.nz/cmd](https://mega.nz/cmd). Por defecto, el wrapper busca en `%LOCALAPPDATA%\MEGAcmd`.
- **Linux**: Instala el paquete correspondiente a tu distribución. Asegúrate de que los comandos `mega-*` estén disponibles en el `PATH` o especifica la ruta en el constructor.

---

## 🛠️ Instalación y Configuración

1. Clone o descargue los archivos `MegaCmd.php` y `config.php` en su proyecto.
2. Configure sus credenciales en `config.php`:

```php
<?php
$mega_email = 'tu_correo@example.com';
$mega_password = 'tu_password_segura';
```

---

## 💻 Uso Básico

```php
require_once 'MegaCmd.php';
require_once 'config.php';

// Instanciar la clase
$mega = new MegaCmd();

// Verificar estado de la sesión
$info = $mega->whoami();
print_r($info);

// Crear una carpeta remota
$mega->mkdir('/MiProyectoBackup');

// Subir un archivo
$mega->put('archivo_local.zip', '/MiProyectoBackup/');

// Listar archivos
$lista = $mega->ls('/MiProyectoBackup');
print_r($lista);
```

---

## 🧪 Pruebas Exhaustivas

El proyecto incluye un script `test.php` que realiza un recorrido secuencial por todos los grupos de comandos:

1.  **Bootstrap**: Identidad y atributos.
2.  **Estructura**: Directorios y navegación.
3.  **Archivos**: Subida y lectura.
4.  **Backup**: Creación y gestión de respaldos.
5.  **Export/Import**: Enlaces públicos.
6.  **Transferencias**: Copia, movimiento y borrado.
7.  **Y mucho más...**

Para ejecutar los tests, simplemente utiliza el servidor local o la CLI de PHP:
`php -f test.php`

---

## 📜 Comandos Soportados (Resumen)

El wrapper incluye implementación o soporte para:

| Comando | Descripción |
| :--- | :--- |
| `login` / `logout` | Gestión de acceso a la cuenta. |
| `ls`, `cd`, `pwd` | Navegación y listado de archivos. |
| `put`, `get` | Subida y descarga de ficheros. |
| `mkdir`, `rm`, `cp`, `mv` | Manipulación de archivos y carpetas. |
| `backup` | Configuración de tareas de respaldo. |
| `export` | Creación de enlaces públicos de descarga. |
| `df`, `du` | Información de espacio y uso de disco. |
| `sessions` | Control de sesiones activas en la cuenta. |

---

## 🔗 Referencias y Créditos

- **GitHub de Referencia**: [Risinget/MegaCmd-php-wrapper](https://github.com/Risinget/MegaCmd-php-wrapper)
- **Documentación Oficial de MEGAcmd**: [meganz/MEGAcmd](https://github.com/meganz/MEGAcmd)

---

> [!IMPORTANT]
> Asegúrate de que el servidor de MEGAcmd (`mega-cmd-server`) esté en ejecución en segundo plano para que el wrapper pueda comunicarse con la API de MEGA correctamente.
