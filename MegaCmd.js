'use strict';

/**
 * MegaCmd Node.js Class
 * Una clase para interactuar con MEGAcmd (mega.nz) mediante comandos de consola.
 * Conversión de la clase PHP original a Node.js.
 */

const { execSync } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

// ---------- Helpers (equivalentes a funciones nativas de PHP) ----------

function basenamePosix(p) {
    return path.posix.basename(p);
}

function dirnamePosix(p) {
    return path.posix.dirname(p);
}

function extensionOf(name) {
    const ext = path.posix.extname(name);
    return ext ? ext.slice(1) : '';
}

function ctypeDigit(value) {
    return /^\d+$/.test(value);
}

function rtrimSep(str, sep) {
    let result = str;
    while (sep !== '' && result.endsWith(sep)) {
        result = result.slice(0, -sep.length);
    }
    return result;
}

class MegaCmd {
    #executablePath;
    #isWindows;
    #localBasePath;
    #workingDirectory;

    #errorCodes = {
        0: { error_type: 'MCMD_OK', message: 'Everything OK' },
        2: { error_type: 'MCMD_CUSTOM_UNKNOW', message: 'Failed to check email corresponds to link' },
        9: { error_type: 'MCMD_CUSTOM_UNKNOW', message: 'Failed to abort backup' },
        12: { error_type: 'MCMD_CONFIRM_NO', message: 'User response to confirmation is "no"' },
        51: { error_type: 'MCMD_EARGS', message: 'Wrong arguments' },
        52: { error_type: 'MCMD_INVALIDEMAIL', message: 'Invalid email' },
        53: { error_type: 'MCMD_NOTFOUND', message: 'Resource not found' },
        54: { error_type: 'MCMD_INVALIDSTATE', message: 'Invalid state' },
        55: { error_type: 'MCMD_INVALIDTYPE', message: 'Invalid type' },
        56: { error_type: 'MCMD_NOTPERMITTED', message: 'Operation not allowed' },
        57: { error_type: 'MCMD_NOTLOGGEDIN', message: 'Needs logging in' },
        58: { error_type: 'MCMD_NOFETCH', message: 'Nodes not fetched' },
        59: { error_type: 'MCMD_EUNEXPECTED', message: 'Unexpected failure' },
        60: { error_type: 'MCMD_REQCONFIRM', message: 'Confirmation required' },
        61: { error_type: 'MCMD_REQSTRING', message: 'String required' },
        62: { error_type: 'MCMD_PARTIALOUT', message: 'Partial output provided' },
        63: { error_type: 'MCMD_PARTIALERR', message: 'Partial error output provided' },
        64: { error_type: 'MCMD_EXISTS', message: 'Resource already exists' },
        71: { error_type: 'MCMD_REQRESTART', message: 'Restart required' },
    };

    /**
     * @param {string|null} execPath
     * @param {string|null} localBasePath
     * @param {string|null} workingDirectory
     */
    constructor(execPath = null, localBasePath = null, workingDirectory = null) {
        this.#isWindows = os.platform() === 'win32';

        if (execPath) {
            this.#executablePath = rtrimSep(execPath, path.sep) + path.sep;
        } else {
            if (this.#isWindows) {
                const localAppData = process.env.LOCALAPPDATA || '';
                const defaultPath = localAppData + path.sep + 'MEGAcmd' + path.sep;

                if (fs.existsSync(defaultPath + 'mega-help.bat')) {
                    this.#executablePath = defaultPath;
                } else {
                    this.#executablePath = '';
                }
            } else {
                this.#executablePath = '';
            }
        }

        this.#localBasePath = localBasePath
            ? rtrimSep(localBasePath, path.sep)
            : __dirname;

        this.#workingDirectory = workingDirectory
            ? rtrimSep(workingDirectory, path.sep)
            : this.#localBasePath;
    }

    /**
     * Escapa un argumento para uso seguro en shell (equivalente a escapeshellarg de PHP)
     */
    #escapeShellArg(arg) {
        if (this.#isWindows) {
            return '"' + String(arg).replace(/"/g, '""') + '"';
        }
        return "'" + String(arg).replace(/'/g, "'\\''") + "'";
    }

    /**
     * Ejecuta un comando de MEGAcmd
     * @param {string} command
     * @param {Array|string} args
     * @param {string} newCmdName
     * @returns {object}
     */
    exec(command, args = [], newCmdName = '') {
        let cmdName;
        if (newCmdName) {
            cmdName = newCmdName + command;
        } else {
            cmdName = 'mega-' + command;
        }

        const fullPath = this.#executablePath + cmdName;

        let fullCommand;
        if (this.#isWindows && this.#executablePath !== '' && fs.existsSync(fullPath + '.bat')) {
            fullCommand = '"' + fullPath + '.bat"';
        } else {
            fullCommand = fullPath;
        }

        if (!Array.isArray(args)) {
            args = [args];
        }

        args = args.filter((v) => v !== null && v !== undefined && v !== '');

        const escapedArgs = args.map((arg) => this.#escapeShellArg(String(arg)));

        const cmdLine = fullCommand + (escapedArgs.length ? ' ' + escapedArgs.join(' ') : '');

        const workingDir = this.#resolveWorkingDirectory();

        let finalCommand;
        if (this.#isWindows) {
            finalCommand = 'cd /d ' + this.#escapeShellArg(workingDir) + ' && ' + cmdLine + ' 2>&1';
        } else {
            finalCommand = 'cd ' + this.#escapeShellArg(workingDir) + ' && ' + cmdLine + ' 2>&1';
        }

        let rawOutput = '';
        let exitCode = 0;

        try {
            rawOutput = execSync(finalCommand, {
                shell: this.#isWindows ? 'cmd.exe' : '/bin/sh',
                encoding: 'utf8',
                maxBuffer: 1024 * 1024 * 10,
            });
        } catch (e) {
            rawOutput = (e.stdout || '') + (e.stderr || '');
            exitCode = e.status !== null && e.status !== undefined ? e.status : 1;
        }

        const outputLines = String(rawOutput)
            .split(/\r\n|\r|\n/)
            .map((l) => l.replace(/\s+$/, ''));
        // Elimina posibles líneas vacías al final, tal como hace exec() de PHP
        while (outputLines.length && outputLines[outputLines.length - 1] === '') {
            outputLines.pop();
        }
        const joinedOutput = outputLines.join(os.EOL);

        return {
            success: exitCode === 0 ? 0 : 1,
            exit_code: exitCode,
            error_message: (this.#errorCodes[exitCode] && this.#errorCodes[exitCode].message) || 'Unknown error',
            output: joinedOutput,
            cmd: finalCommand,
            working_directory: workingDir,
        };
    }

    #resolveWorkingDirectory() {
        if (this.#workingDirectory === '') {
            return this.#localBasePath;
        }

        if (this.#isAbsolutePath(this.#workingDirectory)) {
            return this.#workingDirectory;
        }

        return this.#localBasePath + path.sep + this.#workingDirectory;
    }

    setLocalBasePath(p) {
        this.#localBasePath = rtrimSep(p, path.sep);
        return this;
    }

    setWorkingDirectory(p) {
        this.#workingDirectory = rtrimSep(p, path.sep);
        return this;
    }

    getLocalBasePath() {
        return this.#localBasePath;
    }

    getWorkingDirectory() {
        return this.#workingDirectory;
    }

    #isAbsolutePath(p) {
        if (p === '') {
            return false;
        }

        if (this.#isWindows) {
            return /^[A-Za-z]:[\/\\]/.test(p) || p.startsWith('\\\\');
        }

        return p.startsWith('/');
    }

    #resolveLocalPath(p) {
        p = p.trim();

        if (p === '') {
            return this.#localBasePath;
        }

        p = p.split('/').join(path.sep).split('\\').join(path.sep);

        if (this.#isAbsolutePath(p)) {
            return p;
        }

        return this.#localBasePath + path.sep + p;
    }

    /**
     * ls -l --show-handles
     */

    /**
     * attr.md
     */
    attr() {
        return this.exec('attr');
    }
    // autocomplete() // not supported cmd

    /**
     * Crea un backup
     * Ejemplo $period
     * 1M -> 1 minuto
     * 1m -> 1 mes
     * 1s -> 1 segundo
     * 1h -> 1 hora
     * 1d -> 1 dia
     * 1y -> 1 año
     *  o 1m12d3h45m30s
     */
    backupCreate(localPath, remotePath, period, numBackups) {
        // falta --time-format=
        const localPathResolved = this.#resolveLocalPath(localPath);
        return this.exec('backup', [localPathResolved, remotePath, '--period=' + period, '--num-backups=' + numBackups]);
    }

    #parseBackupHistory(output) {
        let lines = output.split(/\r\n|\r|\n/).map((l) => l.trim());
        lines = lines.filter((line) => line !== '');

        const result = [];
        let current = null;
        let mode = null;

        for (const line of lines) {
            if (line.startsWith('TAG')) {
                continue;
            }

            let m = line.match(/^(\d+)\s+(.*?)\s+(\/\S+)\s+([A-Z]+)$/);
            if (m) {
                if (current !== null) {
                    result.push(current);
                }

                current = {
                    tag: parseInt(m[1], 10),
                    local: m[2],
                    remote: m[3],
                    status: m[4],
                    history: [],
                };
                mode = null;
                continue;
            }

            if (line.includes('HISTORY OF BACKUPS')) {
                mode = 'history';
                continue;
            }

            if (line.startsWith('NAME')) {
                continue;
            }

            if (mode === 'history' && current !== null) {
                m = line.match(/^(\S+)\s+(.+?)\s+(COMPLETE|FAILED|ACTIVE|INCOMPLETE|CANCELLED)\s+(\d+)\s+(\d+)$/);
                if (m) {
                    current.history.push({
                        name: m[1],
                        date: m[2].trim(),
                        status: m[3],
                        files: parseInt(m[4], 10),
                        folders: parseInt(m[5], 10),
                    });
                }
            }
        }

        if (current !== null) {
            result.push(current);
        }

        return result;
    }

    /**
     * Devuelve el historial de backups
     */
    backupHistory() {
        const res = this.exec('backup', ['-h']);

        if (res.success === 1) {
            return res;
        }
        const parsed = this.#parseBackupHistory(res.output);
        const output = { ...res };
        output.output = parsed;
        return output;
    }

    #parseBackupList(output) {
        const lines = output
            .split('\n')
            .map((l) => l.trim())
            .filter((l) => l !== '');

        const result = [];
        let current = null;
        let mode = null;

        for (const line of lines) {
            // ------------------------
            // NUEVO BACKUP (TAG)
            // ------------------------
            let m = line.match(/^(\d+)\s+(.*?)\s+(\/\S+)\s+(\w+)$/);
            if (m) {
                if (current !== null) {
                    result.push(current);
                }

                current = {
                    tag: parseInt(m[1], 10),
                    local: m[2],
                    remote: m[3],
                    status: m[4],
                    config: {},
                    current: {},
                };

                mode = 'config';
                continue;
            }

            // ------------------------
            // CONFIG
            // ------------------------
            if (line.startsWith('Max Backups:')) {
                current.config.max_backups = parseInt(line.split(':')[1].trim(), 10);
                continue;
            }

            if (line.startsWith('Period:')) {
                current.config.period = line.split(':')[1].replace(/"/g, '').trim();
                continue;
            }

            if (line.startsWith('Next backup scheduled for:')) {
                current.config.next = line.split(/:(.+)/)[1].trim();
                continue;
            }

            // ------------------------
            // CAMBIO A CURRENT
            // ------------------------
            if (line.includes('CURRENT/LAST BACKUP')) {
                mode = 'current';
                continue;
            }

            // ------------------------
            // CURRENT DATA
            // ------------------------
            if (mode === 'current') {
                m = line.match(/^(\d+)\/(\d+)\s+(\d+)\s+([\d.\/\sA-Z]+)\s+([\d.]+%)/);
                if (m) {
                    current.current = {
                        files_uploaded: parseInt(m[1], 10),
                        files_total: parseInt(m[2], 10),
                        folders_created: parseInt(m[3], 10),
                        size: m[4].trim(),
                        progress: m[5],
                    };
                }
            }
        }

        if (current !== null) {
            result.push(current);
        }

        return result;
    }

    /**
     * Devuelve la lista de backups
     */
    backupList() {
        const res = this.exec('backup -l');

        if (res.success === 1) {
            return res;
        }
        const parsed = this.#parseBackupList(res.output);
        const output = { ...res };
        output.output = parsed;
        return output;
    }

    /**
     * Elimina un backup por su tag id
     */
    backupDelete(tag) {
        return this.exec('backup -d', [tag]);
    }

    /**
     * Aborta un backup por su tag id
     */
    backupAbort(tag) {
        return this.exec('backup -a', [tag]);
    }

    /**
     * Cancela la cuenta de mega
     */
    cancel() {
        // return this.exec('cancel');
    } // DANGER -> do not use this

    /**
     * Devuelve el contenido de un archivo
     */
    cat(remotePath) {
        return this.exec('cat', [remotePath]);
    }

    /**
     * change directory -> dont work 'cd ..'
     */
    cd(remotePath = null) {
        if (remotePath) {
            return this.exec('cd', [remotePath]);
        }
        return this.exec('cd');
    }
    // clear() // not supported cmd

    /**
     * Ver codepage del terminal
     */
    codepage() {
        return this.exec('codepage');
    }

    completion() {
        return this.exec('completion');
    }

    /**
     * exported_folders_sdks MIN 0 MAX 20 -> descarga en paralelo
     * max_nodes_in_cache MAX QUANTITY    -> nodos, subnodos de carpetas
     * @param {string} key
     * @param {number} value
     */
    configure(key, value) {
        return this.exec('configure', [key, value]);
    }

    /**
     * Confirma la creación de la cuenta con link, email y password
     */
    confirm(link, email, password) {
        return this.exec('confirm', [link, email, password]);
    }

    /**
     * Confirma la cancelación de la cuenta con link y password
     */
    confirmcancel(link, password) {
        // return this.exec('confirmcancel', [link, password]);
    }

    /**
     * Copia archivos o carpetas
     */
    cp(remotePath, remoteDest) {
        return this.exec('cp', [remotePath, remoteDest]);
    }

    debug() {
        return this.exec('debug');
    }

    /**
     * Elimina versiones de un archivo
     */
    deleteversions(force = false, all = false) {
        return this.exec('deleteversions', [force ? '--force' : '', all ? '--all' : '']);
    }

    #parseDf(output) {
        let lines = output.split(/\r\n|\r|\n/).map((l) => l.trim());
        lines = lines.filter((line) => line !== '');

        const result = {
            spaces: {},
            used: {},
            versions_size: null,
        };

        for (const line of lines) {
            // ------------------------
            // CLOUD / INBOX / RUBBISH
            // ------------------------
            let m = line.match(/^(Cloud drive|Inbox|Rubbish bin):\s+(.+?)\s+in\s+(\d+)\s+file\(s\)\s+and\s+(\d+)\s+folder\(s\)$/);
            if (m) {
                const key = m[1].toLowerCase().replace(/ /g, '_');

                result.spaces[key] = {
                    size: m[2].trim(),
                    files: parseInt(m[3], 10),
                    folders: parseInt(m[4], 10),
                };
                continue;
            }

            // ------------------------
            // USED STORAGE
            // ------------------------
            m = line.match(/^USED STORAGE:\s+(.+?)\s+([\d.]+)%\s+of\s+(.+)$/);
            if (m) {
                result.used = {
                    size: m[1].trim(),
                    percentage: parseFloat(m[2]),
                    total: m[3].trim(),
                };
                continue;
            }

            // ------------------------
            // FILE VERSIONS
            // ------------------------
            m = line.match(/^Total size taken up by file versions:\s+(.+)$/);
            if (m) {
                result.versions_size = m[1].trim();
            }
        }

        return result;
    }

    /**
     * Muestra información de almacenamiento
     */
    df(showInGb = false) {
        const args = [];
        if (showInGb) {
            args.push('-h');
        }
        const res = this.exec('df', args);
        if (res.success === 1) {
            return res;
        }
        const parsed = this.#parseDf(res.output);
        const output = { ...res };
        output.output = parsed;
        return output;
    }

    #parseDu(output) {
        let lines = output.split(/\r\n|\r|\n/).map((l) => l.trim());
        lines = lines.filter((line) => line !== '');

        const result = {
            path: null,
            size: null,
            total: null,
        };

        for (const line of lines) {
            if (line.startsWith('FILENAME')) {
                continue;
            }

            if (/^-+$/.test(line)) {
                continue;
            }

            let m = line.match(/^Total storage used:\s+(.+)$/);
            if (m) {
                const value = m[1].trim().replace(/\s+/g, ' ');
                result.total = ctypeDigit(value) ? parseInt(value, 10) : value;
                continue;
            }

            m = line.match(/^(.+?):\s+(.+)$/);
            if (m) {
                const p = m[1].trim();
                const value = m[2].trim().replace(/\s+/g, ' ');

                result.path = p;
                result.size = ctypeDigit(value) ? parseInt(value, 10) : value;
                continue;
            }
        }

        return result;
    }

    /**
     * Devuelve el tamaño de una carpeta en KB
     */
    du(remotePath = '', showInKb = false) {
        const args = [];
        if (remotePath) {
            args.push(remotePath);
        }
        if (showInKb) {
            args.push('-h');
        }
        const res = this.exec('du', args);
        if (res.success === 1) {
            return res;
        }
        const parsed = this.#parseDu(res.output);
        const output = { ...res };
        output.output = parsed;
        return output;
    }

    errorcode(code) {
        return this.exec('errorcode', [code]);
    }

    /**
     * exclude -a "*.tmp" "*.log"
     */
    exclude(patterns) {} // deprecated
    // exit() // solo en MegaCMDShell.exe

    isExported(remotePath) {
        const res = this.exec('export', [remotePath]);
        const isExported = res.error_message === 'Resource already exists' || res.error_message === 'Everything OK';
        const output = { ...res };
        output.output = isExported ? 'true' + ' ' + res.output : 'false' + ' ' + res.output;
        return output;
    }

    #parseExportList(output) {
        let lines = output.split(/\r\n|\r|\n/).map((l) => l.trim());
        lines = lines.filter((line) => line !== '');

        const result = [];

        for (const line of lines) {
            // ------------------------
            // FOLDER
            // ------------------------
            let m = line.match(/^(.*?)\s+\(folder,\s+shared as exported permanent folder link:\s+(https:\/\/mega\.nz\/folder\/[^\s)]+)\)$/);
            if (m) {
                const [clean, key] = this.#splitMegaLink(m[2]);

                result.push({
                    name: m[1].trim(),
                    type: 'folder',
                    size: null,
                    link_full: m[2].trim(),
                    link_clean: clean,
                    decryption_key: key,
                    auth_key: null,
                });
                continue;
            }

            // ------------------------
            // FILE
            // ------------------------
            m = line.match(/^(.*?)\s+\((.+?),\s+shared as exported permanent file link:\s+(https:\/\/mega\.nz\/file\/[^\s)]+)(?:\s+AuthKey=([^\s)]+))?\)$/);
            if (m) {
                const [clean, key] = this.#splitMegaLink(m[3]);

                result.push({
                    name: m[1].trim(),
                    type: 'file',
                    size: m[2].trim(),
                    link_full: m[3].trim(),
                    link_clean: clean,
                    decryption_key: key,
                    auth_key: m[4] !== undefined ? m[4].trim() : null,
                });
            }
        }

        return result;
    }

    #splitMegaLink(url) {
        // ejemplo: https://mega.nz/file/ID#KEY
        const parts = url.split('#');

        const clean = parts[0];
        const key = parts[1] !== undefined ? parts[1] : null;

        return [clean, key];
    }

    exportList() {
        this.cd();
        const res = this.exec('export');
        if (res.success === 1) {
            return res;
        }
        const parsed = this.#parseExportList(res.output);
        const output = { ...res };
        output.output = parsed;
        return output;
    }

    /**
     * $remotePath -> path/file a exportar
     * $megaHosted -> true si quieres exportar y usar desde S4 MEGA
     * $password -> password para exportar   -> SOLO PRO
     * $expire -> tiempo de expiración en segundos -> SOLO PRO
     * Ejemplo $expire
     * 1M -> 1 minuto
     * 1m -> 1 mes
     * 1s -> 1 segundo
     * 1h -> 1 hora
     * 1d -> 1 dia
     * 1y -> 1 año
     */
    #parseExportSingle(output) {
        const line = output.trim();

        const m = line.match(/^Exported\s+(.+?):\s+(https:\/\/mega\.nz\/\S+)$/);
        if (!m) {
            return {};
        }

        const p = m[1].trim();
        const url = m[2].trim();

        const [clean, key] = this.#splitMegaLink(url);

        return {
            path: p,
            name: basenamePosix(p),
            type: url.includes('/folder/') ? 'folder' : 'file',
            link_full: url,
            link_clean: clean,
            decryption_key: key,
        };
    }

    exportAdd(remotePath, megaHosted = false, password = null, expire = null) {
        const args = ['-a', '-f', remotePath];
        if (megaHosted) {
            args.push('--mega-hosted');
        }
        if (password) {
            args.push('--password=' + password);
        }
        if (expire) {
            args.push('--expire=' + expire);
        }
        const res = this.exec('export', args);
        if (res.success === 1) {
            return res;
        }
        const parsed = this.#parseExportSingle(res.output);
        const output = { ...res };
        output.output = parsed;
        return output;
    }

    exportRemove(remotePath) {
        const res = this.exec('export', ['-d', remotePath]);
        if (res.success === 1) {
            return res;
        }
        const removed = res.error_message === 'Everything OK';
        const output = { ...res };
        output.output = removed ? 'true' : 'false';
        return output;
    }

    #buildMegaPathItem({ path: itemPath, type = null, size = null, handle = null, link = null, authKey = null }) {
        const name = basenamePosix(itemPath);
        let dir = dirnamePosix(itemPath);
        dir = dir === '.' ? null : dir;

        const extRaw = extensionOf(name);
        const ext = extRaw !== '' ? extRaw : null;

        let linkClean = null;
        let decryptionKey = null;

        if (link !== null) {
            const split = this.#splitMegaLink(link);
            linkClean = split[0];
            decryptionKey = split[1];
        }

        if (type === null) {
            if (size !== null) {
                type = 'file';
            } else if (ext !== null) {
                type = 'file';
            } else {
                type = 'folder';
            }
        }

        return {
            path: itemPath,
            name: name,
            dir: dir,
            ext: ext,
            handle: handle,
            type: type,
            size: size,
            is_exported: link !== null,
            link_full: link,
            link_clean: linkClean,
            decryption_key: decryptionKey,
            auth_key: authKey,
        };
    }

    #parseMegaPathList(output) {
        let lines = output.split(/\r\n|\r|\n/).map((l) => l.trim());
        lines = lines.filter((line) => line !== '');

        const result = [];

        for (const line of lines) {
            let m;

            // 1) path <H:handle> (folder, shared as exported permanent folder link: URL)
            m = line.match(/^(.*?)\s+<H:([^>]+)>\s+\(folder,\s+shared as exported permanent folder link:\s+(https:\/\/mega\.nz\/folder\/[^\s)]+)\)$/);
            if (m) {
                result.push(
                    this.#buildMegaPathItem({
                        path: m[1].trim(),
                        type: 'folder',
                        size: null,
                        handle: m[2].trim(),
                        link: m[3].trim(),
                        authKey: null,
                    })
                );
                continue;
            }

            // 2) path <H:handle> (size, shared as exported permanent file link: URL AuthKey=...)
            m = line.match(/^(.*?)\s+<H:([^>]+)>\s+\((.+?),\s+shared as exported permanent file link:\s+(https:\/\/mega\.nz\/file\/[^\s)]+)(?:\s+AuthKey=([^\s)]+))?\)$/);
            if (m) {
                result.push(
                    this.#buildMegaPathItem({
                        path: m[1].trim(),
                        type: 'file',
                        size: m[3].trim().replace(/\s+/g, ' '),
                        handle: m[2].trim(),
                        link: m[4].trim(),
                        authKey: m[5] !== undefined ? m[5].trim() : null,
                    })
                );
                continue;
            }

            // 3) path <H:handle> (folder)
            m = line.match(/^(.*?)\s+<H:([^>]+)>\s+\(folder\)$/);
            if (m) {
                result.push(
                    this.#buildMegaPathItem({
                        path: m[1].trim(),
                        type: 'folder',
                        size: null,
                        handle: m[2].trim(),
                    })
                );
                continue;
            }

            // 4) path <H:handle> (size)
            m = line.match(/^(.*?)\s+<H:([^>]+)>\s+\((.+)\)$/);
            if (m) {
                result.push(
                    this.#buildMegaPathItem({
                        path: m[1].trim(),
                        type: 'file',
                        size: m[3].trim().replace(/\s+/g, ' '),
                        handle: m[2].trim(),
                    })
                );
                continue;
            }

            // 5) path (folder, shared as exported permanent folder link: URL)
            m = line.match(/^(.*?)\s+\(folder,\s+shared as exported permanent folder link:\s+(https:\/\/mega\.nz\/folder\/[^\s)]+)\)$/);
            if (m) {
                result.push(
                    this.#buildMegaPathItem({
                        path: m[1].trim(),
                        type: 'folder',
                        size: null,
                        handle: null,
                        link: m[2].trim(),
                    })
                );
                continue;
            }

            // 6) path (size, shared as exported permanent file link: URL AuthKey=...)
            m = line.match(/^(.*?)\s+\((.+?),\s+shared as exported permanent file link:\s+(https:\/\/mega\.nz\/file\/[^\s)]+)(?:\s+AuthKey=([^\s)]+))?\)$/);
            if (m) {
                result.push(
                    this.#buildMegaPathItem({
                        path: m[1].trim(),
                        type: 'file',
                        size: m[2].trim().replace(/\s+/g, ' '),
                        handle: null,
                        link: m[3].trim(),
                        authKey: m[4] !== undefined ? m[4].trim() : null,
                    })
                );
                continue;
            }

            // 7) path (folder)
            m = line.match(/^(.*?)\s+\(folder\)$/);
            if (m) {
                result.push(
                    this.#buildMegaPathItem({
                        path: m[1].trim(),
                        type: 'folder',
                    })
                );
                continue;
            }

            // 8) path (size)
            m = line.match(/^(.*?)\s+\((.+)\)$/);
            if (m) {
                result.push(
                    this.#buildMegaPathItem({
                        path: m[1].trim(),
                        type: 'file',
                        size: m[2].trim().replace(/\s+/g, ' '),
                    })
                );
                continue;
            }

            // 9) solo path
            result.push(this.#buildMegaPathItem({ path: line }));
        }

        return result;
    }

    /**
     * Determina restricciones de tiempo, en la forma: [+-]TIMEVALUE
     * $mtime puede incluir horas(h), dias(d), minutos(M),
     * segundos(s), meses(m) o años(y)
     * Ejemplos:
     * "+1m12d3h" muestra archivos modificados antes de 1 mes, 12 dias y 3 horas desde el momento actual
     * "-3h" muestra archivos modificados en las últimas 3 horas
     * "-3d+1h" muestra archivos modificados en los últimos 3 dias antes de la última hora
     * $size puede incluir (B)ytes, (K)ilobytes, (M)egabytes, (G)igabytes y (T)erabytes
     * Ejemplos:
     * "+1m12k3B" muestra archivos más grandes que 1 Mega, 12 Kbytes y 3Bytes
     * "-3M" muestra archivos más pequeños que 3 Megabytes
     * "-4M+100K" muestra archivos más pequeños que 4 Mbytes y más grandes que 100 Kbytes
     *
     * $type
     * f -> archivo
     * d -> directorio
     */
    find(
        remotePath = '',
        detailed = false,
        pattern = '',
        type = '',
        mtime = '',
        size = '',
        showHandles = false,
        printHandles = false
    ) {
        const args = [];
        if (remotePath) {
            args.push(remotePath);
        }
        if (detailed) {
            args.push('-l');
        }
        if (pattern) {
            args.push('--pattern="' + pattern + '"');
        }
        if (type) {
            args.push('--type=' + type);
        }
        if (mtime) {
            args.push('--mtime=' + mtime);
        }
        if (size) {
            args.push('--size=' + size);
        }
        if (showHandles) {
            args.push('--show-handles');
        }
        if (printHandles) {
            args.push('--print-handles');
        }
        const res = this.exec('find', args);
        if (res.success === 1) {
            return res;
        }

        const parsed = this.#parseMegaPathList(res.output);

        const output = { ...res };
        output.output = parsed;

        return output;
    }
    /////// FUNCIONES INNECESARIAS ///////
    // ftp()
    // fuse_add()
    // fuse_config()
    // fuse_disable()
    // fuse_enable()
    // fuse_remove()
    // fuse_show()

    /**
     * Descarga un archivo/carpeta remoto o un link público
     *
     * @param {string} remotePath exportedlink|remotepath
     * @param {string|null} localPath carpeta de destino
     * @param {boolean} merge (-m) si la carpeta ya existe, el contenido se combinará
     * @param {boolean} queue (-q) descarga en cola: ejecutar en segundo plano
     * @param {boolean} ignoreQuotaWarn ignorar advertencia de exceso de cuota
     * @param {boolean} usePcre usar expresiones PCRE
     * @param {string|null} password Password para desencriptar el link protegido
     */
    get(remotePath, localPath = null, merge = false, queue = false, ignoreQuotaWarn = false, usePcre = false, password = null) {
        const args = [];
        if (merge) {
            args.push('-m');
        }
        if (queue) {
            args.push('-q');
        }
        if (ignoreQuotaWarn) {
            args.push('--ignore-quota-warn');
        }
        if (usePcre) {
            args.push('--use-pcre');
        }
        if (password !== null) {
            args.push('--password=' + password);
        }

        args.push(remotePath);

        if (localPath !== null) {
            args.push(localPath);
        }

        return this.exec('get', args);
    }

    /**
     * por defecto está ON
     */
    graphics(enable) {
        return this.exec('graphics', [enable ? 'on' : 'off']);
    }

    graphicsStatus() {
        return this.exec('graphics');
    }

    help() {
        return this.exec('help');
    }

    https() {
        return this.exec('https');
    }

    import(link, dest = '/', password = null) {
        const args = [];
        if (password !== null) {
            args.push('--password=' + password);
        }
        args.push(link);
        args.push(dest);
        return this.exec('import', args);
    }
    // invite() // innecesaria
    // ipc()

    /**
     * Para ver sesiones usa 'whoami -l'
     * y usa el sessionId
     */
    killsession(sid) {
        const res = this.exec('killsession', [sid]);
        if (res.success === 1) {
            res.output = 'false';
            return res;
        }

        res.output = 'true';
        return res;
    }

    /**
     * Se usará para subidas y descargas
     *
     * Si no se usa la consola interactiva, la carpeta local actual será
     * la de la shell que ejecuta los comandos mega
     */
    lcd(p) {
        return this.exec('lcd', [p]);
    }

    /**
     * Muestra el archivo de log
     */
    log() {
        return this.exec('log');
    }

    /**
     * Resumen de login
     */
    login(email, password, authCode = 0, authKey = 0, resume = false, otherPassword = '', sessionId = '') {
        this.logout(); // Asegurarse de cerrar sesión previa
        const args = [];
        if (authCode) {
            args.push('--auth-CODE=' + authCode);
        }
        if (authKey) {
            args.push('--auth-key=' + authKey);
        }
        if (resume) {
            args.push('--resume');
        }
        if (otherPassword) {
            args.push('--password=' + otherPassword);
        }
        if (sessionId) {
            return this.exec('login', [sessionId]);
        }
        const res = this.exec('login', [email, password]);
        if (res.success === 1) {
            return res;
        }
        res.output = 'true';
        return res;
    }

    logout() {
        const res = this.exec('logout');
        if (res.success === 1) {
            return res;
        }
        res.output = 'true';
        return res;
    }

    #parseLsLongWithHandles(output) {
        let lines = output.split(/\r\n|\r|\n/).map((l) => l.replace(/\s+$/, ''));
        lines = lines.filter((line) => line.trim() !== '');

        const result = [];

        for (let line of lines) {
            line = line.trim();

            if (line === '' || line.startsWith('FLAGS VERS')) {
                continue;
            }

            // Directorios: dep-    -            - 09Apr2026 17:06:55 H:1RdE1JLB commands
            let m = line.match(/^(\S+)\s+(-)\s+(-)\s+([0-9]{2}[A-Za-z]{3}[0-9]{4}\s+[0-9]{2}:[0-9]{2}:[0-9]{2})\s+H:([A-Za-z0-9]+)\s+(.+)$/);
            if (m) {
                const flags = m[1];
                const name = m[6];

                result.push({
                    flags: flags,
                    versions: null,
                    size: null,
                    date: m[4],
                    handle: m[5],
                    name: name,
                    path: name,
                    dir: null,
                    ext: extensionOf(name) || null,
                    type: 'folder',
                    is_exported: flags.includes('e'),
                });
                continue;
            }

            // Archivos: ----    1    703.00  B 26Mar2026 08:24:31 H:lU1Q2CrR attr.md
            // Archivos: ----    1      4.98 KB 26Mar2026 08:24:31 H:tRl2DYIK backup.md
            m = line.match(/^(\S+)\s+(\d+)\s+(\d+(?:\.\d+)?)\s+([KMGT]?B)\s+([0-9]{2}[A-Za-z]{3}[0-9]{4}\s+[0-9]{2}:[0-9]{2}:[0-9]{2})\s+H:([A-Za-z0-9]+)\s+(.+)$/);
            if (m) {
                const flags = m[1];
                const name = m[7];

                result.push({
                    flags: flags,
                    versions: parseInt(m[2], 10),
                    size: m[3] + ' ' + m[4],
                    date: m[5],
                    handle: m[6],
                    name: name,
                    path: name,
                    dir: null,
                    ext: extensionOf(name) || null,
                    type: 'file',
                    is_exported: flags.includes('e'),
                });
                continue;
            }

            // Si no coincide, lo dejamos marcado para debug
            result.push({
                raw: line,
                parse_error: true,
            });
        }

        return result;
    }

    /**
     * Muestra la carpeta local actual
     */
    lpwd() {
        return this.exec('lpwd');
    }

    /**
     * Lista el contenido de una carpeta remota
     */
    ls(p = null, detailed = false) {
        const args = [];
        if (p) {
            args.push(p);
        }
        if (detailed) {
            args.push('-hal');
            args.push('--show-handles');
        }

        const res = this.exec('ls', args);
        if (res.success === 1) {
            return res;
        }
        res.output = this.#parseLsLongWithHandles(res.output);

        return res;
    }

    /**
     * Muestra la masterkey de la clave de recuperación de la cuenta
     */
    masterkey() {
        // si es Windows usar MegaCliente.exe masterkey
        if (this.#isWindows) {
            return this.exec('Client.exe masterkey', [], 'Mega');
        }

        // si es linux
        return this.exec('exec masterkey');
    }

    #parseMediaInfoList(output) {
        let lines = output.split(/\r\n|\r|\n/).map((l) => l.trim());
        lines = lines.filter((line) => line !== '');

        const result = [];

        for (const line of lines) {
            if (line.startsWith('FILE')) {
                continue;
            }

            const m = line.match(/^(.+?)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)$/);
            if (m) {
                const p = m[1].trim();
                const widthRaw = m[2];
                const heightRaw = m[3];
                const fpsRaw = m[4];
                const playtimeRaw = m[5];

                const width = widthRaw === '---' ? null : parseInt(widthRaw, 10);
                const height = heightRaw === '---' ? null : parseInt(heightRaw, 10);
                const fps = fpsRaw === '---' ? null : parseInt(fpsRaw, 10);
                const playtime = playtimeRaw === '---' ? null : playtimeRaw;

                const name = basenamePosix(p);
                const extRaw = extensionOf(name);
                const ext = extRaw !== '' ? extRaw : null;

                result.push({
                    path: p,
                    name: name,
                    width: width,
                    height: height,
                    fps: fps,
                    playtime: playtime,
                    has_media_info: width !== null || height !== null || fps !== null || playtime !== null,
                    type: ext === null ? 'folder' : 'file',
                });
            }
        }

        return result;
    }

    /**
     * Obtiene info de medios de un archivo remoto, o varios argumentos
     */
    mediainfo(remotePath, ...otherPaths) {
        const args = [remotePath];
        for (const p of otherPaths) {
            args.push(p);
        }

        const res = this.exec('mediainfo', args);
        if (res.success === 1) {
            return res;
        }
        res.output = this.#parseMediaInfoList(res.output);
        return res;
    }

    /**
     * Crea una carpeta remota
     */
    mkdir(remotePath) {
        // cuenta de '/' mayor a 1
        const recursive = (remotePath.match(/\//g) || []).length > 1;

        const args = [remotePath];
        if (recursive) {
            args.push('-p');
        }
        return this.exec('mkdir', args);
    }

    folderExists(remotePath) {
        const res = this.find('', true);
        if (res.success === 1) {
            return res;
        }
        let exists = false;
        if (remotePath.startsWith('/')) {
            remotePath = remotePath.substring(1);
        }

        if (remotePath.endsWith('/')) {
            remotePath = remotePath.slice(0, -1);
        }
        res.debug = remotePath;
        for (const item of res.output) {
            if (item.path === remotePath && item.type === 'folder') {
                exists = true;
                break;
            }
        }
        res.output = exists ? 'true' : 'false';
        return res;
    }

    /**
     * Muestra las raíces del disco MEGA
     */
    mount() {
        return this.exec('mount');
    }

    /**
     * Mueve un archivo/carpeta remoto
     */
    mv(remoteSrcPath, remoteDestinationPath) {
        const res = this.exec('mv', [remoteSrcPath, remoteDestinationPath]);
        if (res.success === 1) {
            return res;
        }
        res.output = res.success === 0 ? 'true' : 'false';
        return res;
    }

    /**
     * Cambia el password de la cuenta
     */
    passwd(newPassword, authCode = null) {
        const args = [newPassword];
        if (authCode !== null) {
            args.push(authCode);
        }
        return this.exec('passwd', args);
    }

    /**
     * Ver o cambiar el preview de un archivo
     */
    preview(remotePathFile, newPreviewFile = null) {
        const args = [remotePathFile];
        if (newPreviewFile !== null) {
            args.push('-s' + newPreviewFile);
        }
        return this.exec('preview', args);
    }

    proxy(url, username = '', password = '', auto = false) {
        const args = [url];
        if (username !== '') {
            args.push('--username=' + username);
        }
        if (password !== '') {
            args.push('--password=' + password);
        }
        if (auto) {
            args.push('--auto');
        }
        return this.exec('proxy', [args]);
    }

    #parseTransferOutput(output) {
        let lines = output.split(/\r\n|\r|\n/).map((l) => l.trim());
        lines = lines.filter((l) => l !== '');

        const result = {
            status: null,
            path: null,
            progress: null,
        };

        for (const line of lines) {
            // Upload finished
            let m = line.match(/^Upload finished:\s+(.+)$/);
            if (m) {
                result.status = 'finished';
                result.path = m[1].trim();
                continue;
            }

            // Progress
            m = line.match(/\((\d+)\/(\d+)\s+([A-Z]+):\s+([\d.]+)\s+%\)/);
            if (m) {
                result.progress = {
                    transferred: parseInt(m[1], 10),
                    total: parseInt(m[2], 10),
                    unit: m[3],
                    percent: parseFloat(m[4]),
                };
                continue;
            }
        }

        return result;
    }

    psa() {} // innecesaria

    /**
     * Sube un archivo o carpeta local a MEGA
     */
    put(localPath, remotePath = '/') {
        const res = this.exec('put', [localPath, remotePath]);
        if (res.success === 1) {
            return res;
        }
        res.output = this.#parseTransferOutput(res.output);
        return res;
    }

    /**
     * Muestra la carpeta remota actual
     */
    pwd() {
        return this.exec('pwd');
    }

    /**
     * Sale del cmd actual y del servidor
     */
    quit() {
        return this.exec('quit');
    }

    /**
     * Recarga el árbol de directorios remoto
     */
    reload() {
        return this.exec('reload');
    }

    /**
     * Elimina un archivo o carpeta
     */
    rm(remotePath, recursive = false, force = false) {
        const args = [remotePath];
        if (recursive) {
            args.push('-r');
        }
        if (force) {
            args.push('-f');
        }
        return this.exec('rm', args);
    }

    session() {
        const res = this.exec('session');
        if (res.success === 1) {
            return res;
        }
        res.output = res.output.replace('Your (secret) session is: ', '');
        return res;
    }
    // share() // función innecesaria

    /**
     * Muestra emails afiliados
     */
    showpcr() {
        this.exec('showpcr');
    }

    /**
     * Registra un usuario con un email dado
     */
    signup(email, password, name = null) {
        const args = [email, password];
        if (name !== null) {
            args.push('--name=' + name);
        }
        return this.exec('signup', args);
    }

    #parseSpeedlimit(output) {
        let lines = output.split(/\r\n|\r|\n/).map((l) => l.trim());
        lines = lines.filter((line) => line !== '');

        const result = {
            upload: null,
            download: null,
        };

        for (const line of lines) {
            // Upload
            let m = line.match(/^Upload speed limit\s*=\s*(.+)$/i);
            if (m) {
                result.upload = this.#parseSpeedlimitValue(m[1]);
                continue;
            }

            // Download
            m = line.match(/^Download speed limit\s*=\s*(.+)$/i);
            if (m) {
                result.download = this.#parseSpeedlimitValue(m[1]);
                continue;
            }
        }

        return result;
    }

    #parseSpeedlimitValue(value) {
        value = value.trim().replace(/\s+/g, ' ');

        // ilimitado
        if (value.toLowerCase() === 'unlimited') {
            return {
                value: null,
                unit: null,
                raw: 'unlimited',
                unlimited: true,
            };
        }

        // 2048000 B/s
        let m = value.match(/^([\d.]+)\s+([A-Za-z/]+)$/);
        if (m) {
            return {
                value: !isNaN(m[1]) ? parseFloat(m[1]) : m[1],
                unit: m[2],
                raw: value,
                unlimited: false,
            };
        }

        // fallback
        return {
            value: value,
            unit: null,
            raw: value,
            unlimited: false,
        };
    }

    /**
     * Muestra/modifica los límites de velocidad de subida/descarga: velocidad o max conexiones
     */
    speedlimit(download = '', upload = '', uploadConnections = '', downloadConnections = '') {
        let args = [];
        if (download) {
            args.push('-d');
            args.push(download);
            let res = this.exec('speedlimit', args);
            if (res.success === 1) {
                return res;
            }
            res.output = this.#parseSpeedlimit(res.output);
            return res;
        }
        if (upload) {
            args.push('-d');
            args.push(download);
            let res = this.exec('speedlimit', args);
            if (res.success === 1) {
                return res;
            }
            res.output = this.#parseSpeedlimit(res.output);
            return res;
        }
        if (uploadConnections) {
            args.push('--upload-connections');
            args.push(uploadConnections);
            const res = this.exec('speedlimit', args);
            if (res.success === 1) {
                return res;
            }
            res.output = this.#parseSpeedlimit(res.output);
            return res;
        }
        if (downloadConnections) {
            args.push('--download-connections');
            args.push(downloadConnections);
            const res = this.exec('speedlimit', args);
            if (res.success === 1) {
                return res;
            }
            res.output = this.#parseSpeedlimit(res.output);
            return res;
        }

        const res = this.exec('speedlimit', args);
        if (res.success === 1) {
            return res;
        }
        res.output = this.#parseSpeedlimit(res.output);
        return res;
    }

    /**
     * Controla las sincronizaciones.
     * remotePath puede ser una carpeta y el contenido de localPath se copiará a remotePath
     */
    sync(localPath = null, remotePath = null, idOrPath = null, del = false, pause = false, enable = false) {
        const args = [];
        if (del) {
            args.push('-d');
        }
        if (pause) {
            args.push('-p');
        }
        if (enable) {
            args.push('-e');
        }

        if (localPath && remotePath) {
            args.push(localPath);
            args.push(remotePath);
        } else if (idOrPath) {
            args.push(idOrPath);
        }

        return this.exec('sync', args);
    }

    /**
     * Controla la configuración de sincronización.
     */
    sync_config(waitSeconds = false, maxAttempts = false) {
        const args = [];
        if (waitSeconds) {
            args.push('--delayed-uploads-wait-seconds');
        }
        if (maxAttempts) {
            args.push('--delayed-uploads-max-attempts');
        }
        return this.exec('sync-config', args);
    }

    /**
     * Gestiona filtros de exclusión para sincronizaciones
     */
    sync_ignore(idOrPath, filters = [], action = 'show') {
        const args = [];
        if (action !== 'show') {
            args.push('--' + action);
            for (const filter of filters) {
                args.push(filter);
            }
        } else {
            args.push('--show');
        }
        args.push(idOrPath);
        return this.exec('sync-ignore', args);
    }

    /**
     * Muestra todos los problemas con las sincronizaciones actuales
     */
    sync_issues(detail = null, limit = null, disablePathCollapse = false, enableWarning = false, disableWarning = false) {
        const args = [];
        if (detail) {
            args.push('--detail');
            args.push(detail);
        }
        if (limit !== null) {
            args.push('--limit=' + limit);
        }
        if (disablePathCollapse) {
            args.push('--disable-path-collapse');
        }
        if (enableWarning) {
            args.push('--enable-warning');
        }
        if (disableWarning) {
            args.push('--disable-warning');
        }
        return this.exec('sync-issues', args);
    }

    /**
     * Para descargar/subir el thumbnail de un archivo.
     */
    thumbnail(remotePath, localPath, set = false) {
        const args = [];
        if (set) {
            args.push('-s');
        }
        args.push(remotePath);
        args.push(localPath);
        return this.exec('thumbnail', args);
    }

    transfers() {
        return this.exec('transfers');
    }

    /**
     * Lista archivos en una ruta remota en forma de árbol
     */
    tree(remotePath = null) {
        const args = [];
        if (remotePath) {
            args.push(remotePath);
        }
        return this.exec('tree', args);
    }

    /**
     * Actualiza MEGAcmd
     */
    update(auto = null) {
        const args = [];
        if (auto) {
            args.push('--auto=' + auto.toUpperCase());
        }
        return this.exec('update', args);
    }

    /**
     * Lista/actualiza atributos de usuario
     */
    userattr(attribute = null, value = null, user = null, list = false) {
        const args = [];
        if (list) {
            args.push('--list');
        } else if (attribute && value) {
            args.push('-s');
            args.push(attribute);
            args.push(value);
        } else if (attribute) {
            args.push(attribute);
        }

        if (user) {
            args.push('--user=' + user);
        }
        return this.exec('userattr', args);
    }

    /**
     * Lista contactos
     */
    users(shared = false, hidden = false, names = false, del = null, timeFormat = null) {
        const args = [];
        if (shared) {
            args.push('-s');
        }
        if (hidden) {
            args.push('-h');
        }
        if (names) {
            args.push('-n');
        }
        if (del) {
            args.push('-d');
            args.push(del);
        }
        if (timeFormat) {
            args.push('--time-format=' + timeFormat);
        }
        return this.exec('users', args);
    }

    /**
     * Imprime la versión de MEGAcmd e info extra
     */
    version(changelog = false, extended = false) {
        const args = [];
        if (changelog) {
            args.push('-c');
        }
        if (extended) {
            args.push('-l');
        }
        return this.exec('version', args);
    }

    /**
     * Configura un servidor WEBDAV para servir una ubicación en MEGA
     */
    webdav(remotePath = null, del = false, all = false, port = null, isPublic = false, tls = false) {
        const args = [];
        if (del) {
            args.push('-d');
            if (all) {
                args.push('--all');
            } else if (remotePath) {
                args.push(remotePath);
            }
        } else {
            if (remotePath) {
                args.push(remotePath);
            }
            if (port) {
                args.push('--port=' + port);
            }
            if (isPublic) {
                args.push('--public');
            }
            if (tls) {
                args.push('--tls');
            }
        }
        return this.exec('webdav', args);
    }

    whoami() {
        const res = this.exec('whoami');
        if (res.success === 1) {
            return res;
        }

        res.output = res.output.replace('Account e-mail: ', '');
        return res;
    }

    #parseWhoamiFull(output) {
        let lines = output.split(/\r\n|\r|\n/).map((l) => l.trim());
        lines = lines.filter((line) => line !== '');

        const result = {
            account_email: null,
            available_storage: null,
            storage: {
                root: null,
                inbox: null,
                rubbish: null,
                file_versions_size: null,
            },
            pro_level: null,
            subscription_type: null,
            account_balance: null,
            sessions: [],
            active_sessions_count: null,
        };

        let currentSession = null;
        let markCurrentNextSession = false;

        for (const line of lines) {
            let m = line.match(/^Account e-mail:\s+(.+)$/);
            if (m) {
                result.account_email = m[1].trim();
                continue;
            }

            m = line.match(/^Available storage:\s+(.+)$/);
            if (m) {
                result.available_storage = m[1].trim().replace(/\s+/g, ' ');
                continue;
            }

            m = line.match(/^In ROOT:\s+(.+?)\s+in\s+(\d+)\s+file\(s\)\s+and\s+(\d+)\s+folder\(s\)$/);
            if (m) {
                result.storage.root = {
                    size: m[1].trim().replace(/\s+/g, ' '),
                    files: parseInt(m[2], 10),
                    folders: parseInt(m[3], 10),
                };
                continue;
            }

            m = line.match(/^In INBOX:\s+(.+?)\s+in\s+(\d+)\s+file\(s\)\s+and\s+(\d+)\s+folder\(s\)$/);
            if (m) {
                result.storage.inbox = {
                    size: m[1].trim().replace(/\s+/g, ' '),
                    files: parseInt(m[2], 10),
                    folders: parseInt(m[3], 10),
                };
                continue;
            }

            m = line.match(/^In RUBBISH:\s+(.+?)\s+in\s+(\d+)\s+file\(s\)\s+and\s+(\d+)\s+folder\(s\)$/);
            if (m) {
                result.storage.rubbish = {
                    size: m[1].trim().replace(/\s+/g, ' '),
                    files: parseInt(m[2], 10),
                    folders: parseInt(m[3], 10),
                };
                continue;
            }

            m = line.match(/^Total size taken up by file versions:\s+(.+)$/);
            if (m) {
                result.storage.file_versions_size = m[1].trim().replace(/\s+/g, ' ');
                continue;
            }

            m = line.match(/^Pro level:\s+(\d+)$/);
            if (m) {
                result.pro_level = parseInt(m[1], 10);
                continue;
            }

            m = line.match(/^Subscription type:\s*(.*)$/);
            if (m) {
                const value = m[1].trim();
                result.subscription_type = value !== '' ? value : null;
                continue;
            }

            m = line.match(/^Account balance:\s*(.*)$/);
            if (m) {
                const value = m[1].trim();
                result.account_balance = value !== '' ? value : null;
                continue;
            }

            if (line === 'Current Active Sessions:') {
                continue;
            }

            if (line === '* Current Session') {
                markCurrentNextSession = true;
                continue;
            }

            m = line.match(/^Session ID:\s+(.+)$/);
            if (m) {
                if (currentSession !== null) {
                    result.sessions.push(currentSession);
                }

                currentSession = {
                    session_id: m[1].trim(),
                    session_start: null,
                    most_recent_activity: null,
                    ip: null,
                    country: null,
                    user_agent: null,
                    is_current: !!markCurrentNextSession,
                };

                markCurrentNextSession = false;
                continue;
            }

            if (currentSession !== null) {
                m = line.match(/^Session start:\s+(.+)$/);
                if (m) {
                    currentSession.session_start = m[1].trim();
                    continue;
                }

                m = line.match(/^Most recent activity:\s+(.+)$/);
                if (m) {
                    currentSession.most_recent_activity = m[1].trim();
                    continue;
                }

                m = line.match(/^IP:\s+(.+)$/);
                if (m) {
                    currentSession.ip = m[1].trim();
                    continue;
                }

                m = line.match(/^Country:\s+(.+)$/);
                if (m) {
                    currentSession.country = m[1].trim();
                    continue;
                }

                m = line.match(/^User-Agent:\s+(.+)$/);
                if (m) {
                    currentSession.user_agent = m[1].trim();
                    continue;
                }
            }

            if (line === '-----') {
                continue;
            }

            m = line.match(/^(\d+)\s+active sessions opened$/);
            if (m) {
                result.active_sessions_count = parseInt(m[1], 10);
                continue;
            }
        }

        if (currentSession !== null) {
            result.sessions.push(currentSession);
        }

        return result;
    }

    sessions() {
        const res = this.exec('whoami', '-l');
        res.output = this.#parseWhoamiFull(res.output);
        return res;
    }
}

module.exports = MegaCmd;
