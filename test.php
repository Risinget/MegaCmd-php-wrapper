<?php

function test(): void
{
    /**
     * MegaCmd PHP Wrapper — Test exhaustivo con encadenamiento secuencial
     *
     * Cada grupo depende de los recursos creados por el grupo anterior.
     * Si un paso crítico falla, los tests posteriores que lo usen fallarán
     * con mensajes claros en lugar de errores crípticos.
     *
     * Orden de grupos:
     *   1.  Bootstrap            — objeto, whoami, attr
     *   2.  Estructura base      — mkdir, cd, pwd, reload
     *   3.  Archivos y subida    — put, cat
     *   4.  Backup               — backupCreate, backupList, backupHistory
     *   5.  Export / Import      — isExported, exportAdd, exportList, exportRemove
     *   6.  Copia y movimiento   — cp, mv, rm
     *   7.  Sesiones             — sessions, killsession, session
     *   8.  Info y diagnóstico   — df, du, tree, mediainfo, version, help
     *   9.  Configuración y red  — speedlimit, graphics, https, debug, log
     *   10. Misceláneos          — errorcode, masterkey, deleteversions
     *   11. Pendientes (stub)    — sync, users, userattr, proxy, webdav, update…
     */

    require_once 'MegaCmd.php';
    require_once 'config.php';

    echo "<pre>";
    echo "=== TEST EXHAUSTIVO MegaCmd — INICIO ===\n\n";

    // ──────────────────────────────────────────────────────────────
    // GRUPO 1 — Bootstrap
    // Sin dependencias previas. Verifica que la clase instancia y que
    // la sesión esté activa antes de cualquier operación.
    // ──────────────────────────────────────────────────────────────
    echo "──────────────────────────────────\n";
    echo "GRUPO 1 — Bootstrap\n";
    echo "──────────────────────────────────\n";

    // 1.1 Constructor
    $mega = new MegaCmd();
    echo "1.1 [__construct]  Objeto creado correctamente.\n";

    // 1.2 Verificar sesión activa (precondición para todo lo demás)
    $whoami = $mega->whoami();
    echo "1.2 [whoami]       " . print_r($whoami, true) . "\n";

    // 1.3 Atributos globales de la cuenta
    echo "1.3 [attr]         " . print_r($mega->attr(), true) . "\n";

    // Descomenta si necesitas hacer login explícito:
    // echo "1.4 [login]     " . print_r($mega->login($mega_email, $mega_password), true) . "\n";


    // ──────────────────────────────────────────────────────────────
    // GRUPO 2 — Estructura base
    // Depende de: Grupo 1 (sesión activa).
    // Crea las carpetas que usarán los grupos 3–6.
    // ──────────────────────────────────────────────────────────────
    echo "\n──────────────────────────────────\n";
    echo "GRUPO 2 — Estructura base\n";
    echo "──────────────────────────────────\n";

    // 2.1 Crear carpeta raíz de trabajo (usada por backup, export, cp, mv)
    echo "2.1 [mkdir /commands]       " . print_r($mega->mkdir('/commands'), true) . "\n";

    // 2.2 Crear estructura anidada para probar mkdir recursivo
    echo "2.2 [mkdir /prueba/newdir]  " . print_r($mega->mkdir('/prueba/newdir/asdsadasd'), true) . "\n";

    // 2.3 Crear carpeta para probar duplicados y folderExists
    $mkdirResult = $mega->mkdir('/pruebita23233');
    echo "2.3 [mkdir /pruebita23233]  " . print_r($mkdirResult, true) . "\n";
    echo "2.4 [mkdir /pruebita23233 (duplicado)] " . print_r($mega->mkdir('/pruebita23233'), true) . "\n";
    echo "2.5 [folderExists /pruebita23233]       " . print_r($mega->folderExists('/pruebita23233'), true) . "\n";

    // 2.6 Navegar a /prueba (pwd lo confirma)
    echo "2.6 [cd /prueba]   " . print_r($mega->cd('/prueba'), true) . "\n";
    echo "2.7 [pwd]          " . print_r($mega->pwd(), true) . "\n";

    // 2.8 Recargar metadatos remotos (limpiar caché)
    echo "2.8 [reload]       " . print_r($mega->reload(), true) . "\n";


    // ──────────────────────────────────────────────────────────────
    // GRUPO 3 — Archivos y subida
    // Depende de: Grupo 2 (carpeta /commands y /prueba existen).
    // Sube archivos que usan los grupos 4–6.
    // ──────────────────────────────────────────────────────────────
    echo "\n──────────────────────────────────\n";
    echo "GRUPO 3 — Archivos y subida\n";
    echo "──────────────────────────────────\n";

    // 3.1 Subir archivos de prueba a raíz
    echo "3.1 [put video2.mp4 /]  " . print_r($mega->put('video2.mp4', '/'), true) . "\n";
    echo "3.2 [put test.txt /]    " . print_r($mega->put('test.txt', '/'), true) . "\n";

    // 3.3 Leer contenido de archivo de texto (depende de que attr.md exista en la cuenta)
    echo "3.3 [cat attr.md]       " . print_r($mega->cat('attr.md'), true) . "\n";


    // ──────────────────────────────────────────────────────────────
    // GRUPO 4 — Backup
    // Depende de: Grupo 2 (/commands existe para usarlo como destino).
    // backupCreate → backupList → backupHistory → (backupAbort/backupDelete opcionales)
    // ──────────────────────────────────────────────────────────────
    echo "\n──────────────────────────────────\n";
    echo "GRUPO 4 — Backup\n";
    echo "──────────────────────────────────\n";

    // 4.1 Crear backup de /prueba hacia /commands con periodicidad diaria
    echo "4.1 [backupCreate]   " . print_r($mega->backupCreate('prueba', '/commands', '1d', 1), true) . "\n";

    // 4.2 Listar backups activos (necesario para obtener el tag en 4.4 / 4.5)
    $backupList = $mega->backupList();
    echo "4.2 [backupList]     " . print_r($backupList, true) . "\n";

    // 4.3 Historial de ejecuciones de backup
    echo "4.3 [backupHistory]  " . print_r($mega->backupHistory(), true) . "\n";

    // 4.4 Abortar backup (usa el tag del primer backup listado)
    // $tag = $backupList['output'][0]['tag'] ?? null;
    // if ($tag) {
    //     echo "4.4 [backupAbort tag=$tag]  " . print_r($mega->backupAbort($tag), true) . "\n";
    // }

    // 4.5 Eliminar backup permanentemente (descomenta solo si ya no se necesita)
    // if ($tag) {
    //     echo "4.5 [backupDelete tag=$tag] " . print_r($mega->backupDelete($tag), true) . "\n";
    // }


    // ──────────────────────────────────────────────────────────────
    // GRUPO 5 — Export / Import
    // Depende de: Grupo 3 (attr.md y /commands/commands existen).
    // isExported → exportAdd → exportList → exportRemove → import (opcional)
    // ──────────────────────────────────────────────────────────────
    echo "\n──────────────────────────────────\n";
    echo "GRUPO 5 — Export / Import\n";
    echo "──────────────────────────────────\n";

    // 5.1 Verificar estado de exportación antes de exportar
    echo "5.1 [isExported /attr.md (antes)]  " . print_r($mega->isExported('/attr.md'), true) . "\n";
    echo "5.2 [isExported /ruta_invalida]    " . print_r($mega->isExported('/ssadsad'), true) . "\n";

    // 5.3 Exportar archivo (primera vez y duplicado para ver idempotencia)
    echo "5.3 [exportAdd /attr.md]           " . print_r($mega->exportAdd('/attr.md'), true) . "\n";
    echo "5.4 [isExported /attr.md (después)]" . print_r($mega->isExported('/attr.md'), true) . "\n";
    echo "5.5 [exportAdd /attr.md (duplicado)]" . print_r($mega->exportAdd('/attr.md'), true) . "\n";
    echo "5.6 [exportAdd /commands/commands] " . print_r($mega->exportAdd('/commands/commands'), true) . "\n";

    // 5.7 Listar todos los exports activos (se usa para obtener link en import)
    $exportList = $mega->exportList();
    echo "5.7 [exportList]\n" . print_r($exportList, true) . "\n";

    // 5.8 Importar usando el link del primer export listado
    // $link = $exportList['output'][0]['link_full'] ?? null;
    // if ($link) {
    //     echo "5.8 [import $link → /commands/commands] " . print_r($mega->import($link, '/commands/commands'), true) . "\n";
    // }

    // 5.9 Revocar export de attr.md
    echo "5.9 [exportRemove /attr.md]        " . print_r($mega->exportRemove('/attr.md'), true) . "\n";


    // ──────────────────────────────────────────────────────────────
    // GRUPO 6 — Copia y movimiento
    // Depende de: Grupo 3 (attr.md existe), Grupo 2 (/commands existe).
    // cp → mv → rm (orden crítico: cp primero para tener origen al mover)
    // ──────────────────────────────────────────────────────────────
    echo "\n──────────────────────────────────\n";
    echo "GRUPO 6 — Copia y movimiento\n";
    echo "──────────────────────────────────\n";

    // 6.1 Copiar archivo a otra carpeta
    echo "6.1 [cp /attr.md → /commands/attrCopiado.md]  " . print_r($mega->cp('/attr.md', '/commands/attrCopiado.md'), true) . "\n";

    // 6.2 Renombrar/mover carpeta (usa /commands creada en Grupo 2)
    echo "6.2 [mv /commands → pruebitaRenombrada33333]   " . print_r($mega->mv('/commands', 'pruebitaRenombrada33333'), true) . "\n";

    // 6.3 Crear carpeta temporal y eliminarla (crea → rm)
    echo "6.3 [mkdir HOLAMUNDO_ANASHE]  " . print_r($mega->mkdir('HOLAMUNDO_ANASHE'), true) . "\n";
    echo "6.4 [rm HOLAMUNDO_ANASHE]     " . print_r($mega->rm('HOLAMUNDO_ANASHE', recursive: true, force: true), true) . "\n";


    // ──────────────────────────────────────────────────────────────
    // GRUPO 7 — Sesiones
    // Depende de: Grupo 1 (sesión activa).
    // sessions → killsession (usa un sid ficticio) → session (token actual)
    // ──────────────────────────────────────────────────────────────
    echo "\n──────────────────────────────────\n";
    echo "GRUPO 7 — Sesiones\n";
    echo "──────────────────────────────────\n";

    // 7.1 Listar sesiones activas
    echo "7.1 [sessions]         " . print_r($mega->sessions(), true) . "\n";

    // 7.2 Intentar matar sesión con sid ficticio (espera error controlado)
    echo "7.2 [killsession 'sid'] " . print_r($mega->killsession('sid'), true) . "\n";

    // 7.3 Obtener token de sesión actual
    echo "7.3 [session]          " . print_r($mega->session(), true) . "\n";


    // ──────────────────────────────────────────────────────────────
    // GRUPO 8 — Info y diagnóstico
    // Depende de: Grupos 2–3 (archivos y carpetas creados para du/tree/mediainfo).
    // No modifica estado remoto.
    // ──────────────────────────────────────────────────────────────
    echo "\n──────────────────────────────────\n";
    echo "GRUPO 8 — Info y diagnóstico\n";
    echo "──────────────────────────────────\n";

    echo "8.1 [df]                    \n" . print_r($mega->df(false), true) . "\n";
    echo "8.2 [du /prueba --verbose]  \n" . print_r($mega->du('/prueba', true), true) . "\n";
    echo "8.3 [tree]                  \n" . print_r($mega->tree(), true) . "\n";

    // mediainfo sobre tipos distintos: video, texto, carpeta
    echo "8.4 [mediainfo VIDEO.mp4, attr.md, commands/]\n" . print_r($mega->mediainfo('VIDEO.mp4', 'attr.md', 'commands/'), true) . "\n";

    echo "8.5 [version]               " . print_r($mega->version(), true) . "\n";
    echo "8.6 [help]                  " . print_r($mega->help(), true) . "\n";


    // ──────────────────────────────────────────────────────────────
    // GRUPO 9 — Configuración y red
    // Depende de: Grupo 1 (sesión activa). No necesita archivos remotos.
    // speedlimit: leer → modificar → restaurar (encadenado para no dejar estado sucio)
    // ──────────────────────────────────────────────────────────────
    echo "\n──────────────────────────────────\n";
    echo "GRUPO 9 — Configuración y red\n";
    echo "──────────────────────────────────\n";

    // 9.1 Leer límites actuales (estado base)
    echo "9.1 [speedlimit (leer)]                      " . print_r($mega->speedlimit(), true) . "\n";

    // 9.2 Modificar descarga a 2 MB/s
    echo "9.2 [speedlimit download=2048000]            " . print_r($mega->speedlimit(download: '2048000'), true) . "\n";

    // 9.3 Restaurar ambos a ilimitado
    echo "9.3 [speedlimit upload=unlimited,download=unlimited] " . print_r($mega->speedlimit(upload: 'unlimited', download: 'unlimited'), true) . "\n";

    // 9.4 Ajustar conexiones de subida y bajada
    echo "9.4 [speedlimit uploadConnections=10]        " . print_r($mega->speedlimit(uploadConnections: '10'), true) . "\n";
    echo "9.5 [speedlimit downloadConnections=10]      " . print_r($mega->speedlimit(downloadConnections: '10'), true) . "\n";

    // 9.6 Habilitar generación de thumbnails/previews
    echo "9.6 [graphics true]   " . print_r($mega->graphics(true), true) . "\n";

    // 9.7 Estado del protocolo HTTPS
    echo "9.7 [https]           " . print_r($mega->https(), true) . "\n";

    // 9.8 Modo debug (toggle)
    echo "9.8 [debug]           " . print_r($mega->debug(), true) . "\n";

    // 9.9 Nivel de log actual
    echo "9.9 [log]             " . print_r($mega->log(), true) . "\n";


    // ──────────────────────────────────────────────────────────────
    // GRUPO 10 — Misceláneos
    // Depende de: Grupo 1 (sesión), Grupos 2–3 (objetos en la cuenta).
    // No dependen entre sí; orden es por conveniencia.
    // ──────────────────────────────────────────────────────────────
    echo "\n──────────────────────────────────\n";
    echo "GRUPO 10 — Misceláneos\n";
    echo "──────────────────────────────────\n";

    // 10.1 Traducir código de error 0 → "OK"
    echo "10.1 [errorcode 0]      " . print_r($mega->errorcode(0), true) . "\n";

    // 10.2 Mostrar clave maestra (sensible — solo en entorno seguro)
    echo "10.2 [masterkey]        " . print_r($mega->masterkey(), true) . "\n";

    // 10.3 Eliminar versiones antiguas de archivos
    echo "10.3 [deleteversions]   " . print_r($mega->deleteversions(), true) . "\n";

    // 10.4 Confirmar enlace de cuenta (espera error porque los datos son ficticios)
    echo "10.4 [confirm]          " . print_r($mega->confirm('link', '[EMAIL_ADDRESS]', 'password'), true) . "\n";

    // 10.5 Find global con detalle y handles
    // echo "10.5 [find]          " . print_r($mega->find(detailed: true, showHandles: true), true) . "\n";

    // 10.6 Descargar archivo remoto a carpeta local
    // echo "10.6 [get /prueba fokin] " . print_r($mega->get('/prueba', 'fokin'), true) . "\n";


    // ──────────────────────────────────────────────────────────────
    // GRUPO 11 — Pendientes (stub)
    // Sin dependencias verificadas aún; comentados hasta implementar.
    // ──────────────────────────────────────────────────────────────
    echo "\n──────────────────────────────────\n";
    echo "GRUPO 11 — Pendientes (stub)\n";
    echo "──────────────────────────────────\n";

    // echo "11.01 [sync]         " . print_r($mega->sync(), true) . "\n";
    // echo "11.02 [sync_config]  " . print_r($mega->sync_config(), true) . "\n";
    // echo "11.03 [sync_ignore]  " . print_r($mega->sync_ignore('/path'), true) . "\n";
    // echo "11.04 [sync_issues]  " . print_r($mega->sync_issues(), true) . "\n";
    // echo "11.05 [users]        " . print_r($mega->users(), true) . "\n";
    // echo "11.06 [userattr]     " . print_r($mega->userattr(null, null, null, true), true) . "\n";
    // echo "11.07 [preview]      " . print_r($mega->preview('/file.jpg'), true) . "\n";
    // echo "11.08 [thumbnail]    " . print_r($mega->thumbnail('/path', '.'), true) . "\n";
    // echo "11.09 [webdav]       " . print_r($mega->webdav(), true) . "\n";
    // echo "11.10 [proxy]        " . print_r($mega->proxy('http://proxy:8080'), true) . "\n";
    // echo "11.11 [update]       " . print_r($mega->update(), true) . "\n";
    // echo "11.12 [showpcr]      " . print_r($mega->showpcr(), true) . "\n";
    // echo "11.13 [psa]          " . print_r($mega->psa(), true) . "\n";
    // echo "11.14 [passwd]       " . print_r($mega->passwd('newpassword'), true) . "\n";  // PELIGROSO
    // echo "11.15 [signup]       " . print_r($mega->signup('user@example.com', 'password'), true) . "\n";
    // echo "11.16 [quit]         " . print_r($mega->quit(), true) . "\n";
    // echo "11.17 [logout]       " . print_r($mega->logout(), true) . "\n";  // TERMINA SESIÓN

    echo "\n=== TEST EXHAUSTIVO MegaCmd — FIN ===\n";
    echo "</pre>";
}
