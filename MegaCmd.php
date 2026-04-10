<?php

/**
 * MegaCmd PHP Class
 * Una clase para interactuar con MEGAcmd (mega.nz) mediante comandos de consola.
 */
class MegaCmd {
    private string $executablePath;
    private bool $isWindows;
    private string $localBasePath;
    private string $workingDirectory;


    private array $errorCodes = [
        0 => [
            'error_type'=>'MCMD_OK',
            'message'=>'Everything OK'
        ],              // Everything OK
        2 => [
            'error_type'=>'MCMD_CUSTOM_UNKNOW',
            'message'=>'Failed to check email corresponds to link'
        ],              // Failed to check email corresponds to link
        9 => [
            'error_type'=>'MCMD_CUSTOM_UNKNOW',
            'message'=>'Failed to abort backup'
        ],              // Failed to abort backup

        12 => [
            'error_type'=>'MCMD_CONFIRM_NO',
            'message'=>'User response to confirmation is "no"'
        ],    // User response to confirmation is "no"
        51 => [
            'error_type'=>'MCMD_EARGS',
            'message'=>'Wrong arguments'
        ],         // Wrong arguments
        52 => [
            'error_type'=>'MCMD_INVALIDEMAIL',
            'message'=>'Invalid email'
        ],  // Invalid email
        53 => [
            'error_type'=>'MCMD_NOTFOUND',
            'message'=>'Resource not found'
        ],      // Resource not found
        54 => [
            'error_type'=>'MCMD_INVALIDSTATE',
            'message'=>'Invalid state'
        ],  // Invalid state
        55 => [
            'error_type'=>'MCMD_INVALIDTYPE',
            'message'=>'Invalid type'
        ],   // Invalid type
        56 => [
            'error_type'=>'MCMD_NOTPERMITTED',
            'message'=>'Operation not allowed'
        ],  // Operation not allowed
        57 => [
            'error_type'=>'MCMD_NOTLOGGEDIN',
            'message'=>'Needs logging in'
        ],   // Needs logging in
        58 => [
            'error_type'=>'MCMD_NOFETCH',
            'message'=>'Nodes not fetched'
        ],       // Nodes not fetched
        59 => [
            'error_type'=>'MCMD_EUNEXPECTED',
            'message'=>'Unexpected failure'
        ],   // Unexpected failure
        60 => [
            'error_type'=>'MCMD_REQCONFIRM',
            'message'=>'Confirmation required'
        ],    // Confirmation required
        61 => [
            'error_type'=>'MCMD_REQSTRING',
            'message'=>'String required'
        ],     // String required
        62 => [
            'error_type'=>'MCMD_PARTIALOUT',
            'message'=>'Partial output provided'
        ],    // Partial output provided
        63 => [
            'error_type'=>'MCMD_PARTIALERR',
            'message'=>'Partial error output provided'
        ],    // Partial error output provided
        64 => [
            'error_type'=>'MCMD_EXISTS',
            'message'=>'Resource already exists'
        ],        // Resource already exists
        71 => [
            'error_type'=>'MCMD_REQRESTART',
            'message'=>'Restart required'
        ],    // Restart required
    ];
    public function __construct(
        ?string $path = null,
        ?string $localBasePath = null,
        ?string $workingDirectory = null
    ) {
        $this->isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if ($path) {
            $this->executablePath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        } else {
            if ($this->isWindows) {
                $localAppData = getenv('LOCALAPPDATA');
                $defaultPath = $localAppData . DIRECTORY_SEPARATOR . 'MEGAcmd' . DIRECTORY_SEPARATOR;

                if (file_exists($defaultPath . 'mega-help.bat')) {
                    $this->executablePath = $defaultPath;
                } else {
                    $this->executablePath = '';
                }
            } else {
                $this->executablePath = '';
            }
        }

        $this->localBasePath = $localBasePath
            ? rtrim($localBasePath, DIRECTORY_SEPARATOR)
            : __DIR__;

        $this->workingDirectory = $workingDirectory
            ? rtrim($workingDirectory, DIRECTORY_SEPARATOR)
            : $this->localBasePath;
    }

    public function exec(string $command, array|string $args = []): array
    {
        $cmdName = 'mega-' . $command;
        $fullPath = $this->executablePath . $cmdName;

        if ($this->isWindows && $this->executablePath !== '' && file_exists($fullPath . '.bat')) {
            $fullCommand = '"' . $fullPath . '.bat"';
        } else {
            $fullCommand = $fullPath;
        }

        if (!is_array($args)) {
            $args = [$args];
        }

        $args = array_values(array_filter($args, fn($v) => $v !== null && $v !== ''));

        $escapedArgs = array_map(
            fn($arg) => escapeshellarg((string)$arg),
            $args
        );

        $cmdLine = $fullCommand . (count($escapedArgs) ? ' ' . implode(' ', $escapedArgs) : '');

        $workingDir = $this->resolveWorkingDirectory();

        if ($this->isWindows) {
            $finalCommand = 'cd /d ' . escapeshellarg($workingDir) . ' && ' . $cmdLine . ' 2>&1';
        } else {
            $finalCommand = 'cd ' . escapeshellarg($workingDir) . ' && ' . $cmdLine . ' 2>&1';
        }

        $output = [];
        $exitCode = 0;

        exec($finalCommand, $output, $exitCode);

        return [
            'success' => $exitCode === 0 ? 0 : 1,
            'exit_code' => $exitCode,
            'error_message' => $this->errorCodes[$exitCode]['message'] ?? 'Unknown error',
            'output' => implode(PHP_EOL, $output),
            'cmd' => $finalCommand,
            'working_directory' => $workingDir,
        ];
    }

    private function resolveWorkingDirectory(): string
    {
        if ($this->workingDirectory === '') {
            return $this->localBasePath;
        }

        if ($this->isAbsolutePath($this->workingDirectory)) {
            return $this->workingDirectory;
        }

        return $this->localBasePath . DIRECTORY_SEPARATOR . $this->workingDirectory;
    }

    public function setLocalBasePath(string $path): self
    {
        $this->localBasePath = rtrim($path, DIRECTORY_SEPARATOR);
        return $this;
    }

    public function setWorkingDirectory(string $path): self
    {
        $this->workingDirectory = rtrim($path, DIRECTORY_SEPARATOR);
        return $this;
    }

    public function getLocalBasePath(): string
    {
        return $this->localBasePath;
    }

    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }

    private function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if ($this->isWindows) {
            return preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1
                || str_starts_with($path, '\\\\');
        }

        return str_starts_with($path, '/');
    }

    private function resolveLocalPath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return $this->localBasePath;
        }

        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return $this->localBasePath . DIRECTORY_SEPARATOR . $path;
    }

    
    /**
     * ls -l --show-handlesw
     */

    /**
     * attr.md
     * @return bool|string|null
     */
    public function attr(){ return $this->exec('attr');}
    // public function autocomplete(){ return $this->exec('autocomplete');}  // not suported cmd
    /**
     * Return backups history
     */



    public function backupCreate(string $localPath, string $remotePath, string $period, int $numBackups){
        // falta --time-format=

        $localPathResolved = $this->resolveLocalPath($localPath);
        return $this->exec('backup', [$localPathResolved, $remotePath, "--period=".$period, "--num-backups=".$numBackups]);
    }


 private function parseBackupHistory(string $output): array
{
    $lines = preg_split('/\r\n|\r|\n/', $output);
    $lines = array_map('trim', $lines);
    $lines = array_values(array_filter($lines, fn($line) => $line !== ''));

    $result = [];
    $current = null;
    $mode = null;

    foreach ($lines as $line) {
        if (str_starts_with($line, 'TAG')) {
            continue;
        }

        if (preg_match('/^(\d+)\s+(.*?)\s+(\/\S+)\s+([A-Z]+)$/', $line, $m)) {
            if ($current !== null) {
                $result[] = $current;
            }

            $current = [
                'tag' => (int)$m[1],
                'local' => $m[2],
                'remote' => $m[3],
                'status' => $m[4],
                'history' => [],
            ];
            $mode = null;
            continue;
        }

        if (str_contains($line, 'HISTORY OF BACKUPS')) {
            $mode = 'history';
            continue;
        }

        if (str_starts_with($line, 'NAME')) {
            continue;
        }

        if ($mode === 'history' && $current !== null) {
            if (preg_match('/^(\S+)\s+(.+?)\s+(COMPLETE|FAILED|ACTIVE|INCOMPLETE|CANCELLED)\s+(\d+)\s+(\d+)$/', $line, $m)) {
                $current['history'][] = [
                    'name' => $m[1],
                    'date' => trim($m[2]),
                    'status' => $m[3],
                    'files' => (int)$m[4],
                    'folders' => (int)$m[5],
                ];
            }
        }
    }

    if ($current !== null) {
        $result[] = $current;
    }

    return $result;
}

    public function backupHistory()
    {
        $res = $this->exec('backup', ['-h']);

        // return $res;
        if ($res['success'] == 1) {
            return $res;
        }
        $parsed = $this->parseBackupHistory($res['output']);
        $output = $res;
        $output['output'] = $parsed;
        return $output;
    }
    /**
     * Return backups list
     */

 private function parseBackupList(string $output): array
{
    $lines = array_filter(array_map('trim', explode("\n", $output)));

    $result = [];
    $current = null;
    $mode = null;

    foreach ($lines as $line) {

        // ------------------------
        // NUEVO BACKUP (TAG)
        // ------------------------
        if (preg_match('/^(\d+)\s+(.*?)\s+(\/\S+)\s+(\w+)$/', $line, $m)) {

            // guardar el anterior
            if ($current !== null) {
                $result[] = $current;
            }

            $current = [
                'tag' => (int)$m[1],
                'local' => $m[2],
                'remote' => $m[3],
                'status' => $m[4],
                'config' => [],
                'current' => []
            ];

            $mode = 'config';
            continue;
        }

        // ------------------------
        // CONFIG
        // ------------------------
        if (str_starts_with($line, 'Max Backups:')) {
            $current['config']['max_backups'] = (int)trim(explode(':', $line)[1]);
            continue;
        }

        if (str_starts_with($line, 'Period:')) {
            $current['config']['period'] = trim(str_replace('"', '', explode(':', $line)[1]));
            continue;
        }

        if (str_starts_with($line, 'Next backup scheduled for:')) {
            $current['config']['next'] = trim(explode(':', $line, 2)[1]);
            continue;
        }

        // ------------------------
        // CAMBIO A CURRENT
        // ------------------------
        if (str_contains($line, 'CURRENT/LAST BACKUP')) {
            $mode = 'current';
            continue;
        }

        // ------------------------
        // CURRENT DATA
        // ------------------------
        if ($mode === 'current' &&
            preg_match('/^(\d+)\/(\d+)\s+(\d+)\s+([\d\.\/\sA-Z]+)\s+([\d\.]+%)/', $line, $m)
        ) {
            $current['current'] = [
                'files_uploaded' => (int)$m[1],
                'files_total' => (int)$m[2],
                'folders_created' => (int)$m[3],
                'size' => trim($m[4]),
                'progress' => $m[5],
            ];
        }
    }

    // guardar último bloque
    if ($current !== null) {
        $result[] = $current;
    }

    return $result;
}

    public function backupList(){ 
        $res =  $this->exec('backup -l');
        
        if ($res['success'] == 1) {
            return $res;
        }
        $parsed = $this->parseBackupList($res['output']);
        $output = $res;
        $output['output'] = $parsed;
        return $output;
        
    }
    /**
     * Delete a backup by tag id
     */
    public function backupDelete(int $tag){ return $this->exec('backup -d', [$tag]); }
    /**
     * Abort a backup by tag id
     */
    public function backupAbort(int $tag){ return $this->exec('backup -a', [$tag]); }
    /**
     * Create a backup 
     * Example $period
     * 1M -> 1 minute
     * 1m -> 1 month
     * 1s -> 1 second
     * 1h -> 1 hour
     * 1d -> 1 day
     * 1y -> 1 year
     *  or 1m12d3h45m30s
     */


    /**
    * Cancel your account mega
    */
    public function cancel(){ 
        // $this->exec('cancel');
    } // DANGER -> do not use this 

    /**
     * Return content of a file
     */
    public function cat($remotePath){ return $this->exec('cat', [$remotePath]);}
    /**
     * change directory -> dont work 'cd ..'
     */
    public function cd($remotePath = null){
        if($remotePath){
            return $this->exec('cd', [$remotePath]);
        }
        return $this->exec('cd');
    }
    // public function clear(){ return $this->exec('clear');} // not supported cmd
    /**
     * See codepage interpreter terminal
     */
    public function codepage(){ return $this->exec('codepage');}
    public function completion(){ return $this->exec('completion');}

    /**
     * exported_folders_sdks MIN 0 MAX 20 -> paralel download
      * max_nodes_in_cache MAX QUANTITY    -> nodes, subnodes from folders
     * @param string $key
     * @param int $value
     * @return array
     */
    public function configure(string $key, int $value){ 
        
        return $this->exec('configure', [$key, $value]);
    }
    /**
     * Confirm account creation with link, email and password
     */
    public function confirm(string $link, string $email, string $password){ return $this->exec('confirm', [$link, $email, $password]);}
    /**
     * Confirm account cancel with link and password
     */
    public function confirmcancel(string $link, string $password){ 
        // return $this->exec('confirmcancel', [$link, $password]);
    }
    /**
     * Copy files or folders
     * @param string $remotePath
     * @param string $remoteDest
     * @return bool|string|null
     */
    public function cp(string $remotePath, string $remoteDest){
        return $this->exec('cp', [$remotePath, $remoteDest]);
    }
    public function debug(){ return $this->exec('debug');}
    /**
     * Delete versions of a file
     * @param bool $force
     * @param bool $all
     * @return bool|string|null
     */
    public function deleteversions(bool $force = false, bool $all = false){
        return $this->exec('deleteversions', [$force ? '--force' : '', $all ? '--all' : '']);
    }


    private function parseDf(string $output): array
{
    $lines = preg_split('/\r\n|\r|\n/', $output);
    $lines = array_map('trim', $lines);
    $lines = array_values(array_filter($lines, fn($line) => $line !== ''));

    $result = [
        'spaces' => [],
        'used' => [],
        'versions_size' => null
    ];

    foreach ($lines as $line) {

        // ------------------------
        // CLOUD / INBOX / RUBBISH
        // ------------------------
        if (preg_match('/^(Cloud drive|Inbox|Rubbish bin):\s+(.+?)\s+in\s+(\d+)\s+file\(s\)\s+and\s+(\d+)\s+folder\(s\)$/', $line, $m)) {

            $key = strtolower(str_replace(' ', '_', $m[1]));

            $result['spaces'][$key] = [
                'size' => trim($m[2]),
                'files' => (int)$m[3],
                'folders' => (int)$m[4],
            ];
        }

        // ------------------------
        // USED STORAGE
        // ------------------------
        elseif (preg_match('/^USED STORAGE:\s+(.+?)\s+([\d\.]+)%\s+of\s+(.+)$/', $line, $m)) {
            $result['used'] = [
                'size' => trim($m[1]),
                'percentage' => (float)$m[2],
                'total' => trim($m[3]),
            ];
        }

        // ------------------------
        // FILE VERSIONS
        // ------------------------
        elseif (preg_match('/^Total size taken up by file versions:\s+(.+)$/', $line, $m)) {
            $result['versions_size'] = trim($m[1]);
        }
    }

    return $result;
}



    /**
     * Show storage information
     * @return bool|string|null
     */
    public function df($showInGb = false)
    {
        $args = [];
        if($showInGb){
            $args[] = '-h';
        }
        $res = $this->exec('df', $args);
        if ($res['success'] == 1) {
            return $res;
        }
        $parsed = $this->parseDf($res['output']);
        $output = $res;
        $output['output'] = $parsed;
        return $output;
    }

    private function parseDu(string $output): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $output);
        $lines = array_map('trim', $lines);
        $lines = array_values(array_filter($lines, fn($line) => $line !== ''));

        $result = [
            'path' => null,
            'size' => null,
            'total' => null,
        ];

        foreach ($lines as $line) {
            if (str_starts_with($line, 'FILENAME')) {
                continue;
            }

            if (preg_match('/^-+$/', $line)) {
                continue;
            }

            if (preg_match('/^Total storage used:\s+(.+)$/', $line, $m)) {
                $value = preg_replace('/\s+/', ' ', trim($m[1]));
                $result['total'] = ctype_digit($value) ? (int)$value : $value;
                continue;
            }

            if (preg_match('/^(.+?):\s+(.+)$/', $line, $m)) {
                $path = trim($m[1]);
                $value = preg_replace('/\s+/', ' ', trim($m[2]));

                $result['path'] = $path;
                $result['size'] = ctype_digit($value) ? (int)$value : $value;
                continue;
            }
        }

        return $result;
    }

    /**
     * Return size of a folder used in KB
     * @param string $remotePath
     * @param bool $showInKb
     * @return array
     */
    public function du(string $remotePath = '', $showInKb = false){
        $args = [];
        if($remotePath){
            $args[] = $remotePath;
        }
        if($showInKb){
            $args[] = '-h';
        }
        $res = $this->exec('du', $args);
        if ($res['success'] == 1) {
            return $res;
        }
        $parsed = $this->parseDu($res['output']);
        $output = $res;
        $output['output'] = $parsed;
        return $output;
    }
    public function errorcode(int $errorcode){
        return $this->exec('errorcode', [$errorcode]);
    }
    /**
     * exclude -a "*.tmp" "*.log"
     */
    public function exclude(array $patterns){} // deprecated
    // public function exit(){ return $this->exec('exit');} // only on MegaCMDShell.exe


    public function isExported($remotePath){
        $res = $this->exec('export', [$remotePath]);
        $isExported = $res['error_message'] == 'Resource already exists' || $res['error_message'] == 'Everything OK';
        $output = $res;
        $output['output'] = $isExported ? 'true' . ' ' . $res['output']: 'false' . ' ' . $res['output'];
        return $output;
    }


  private function parseExportList(string $output): array
{
    $lines = preg_split('/\r\n|\r|\n/', $output);
    $lines = array_map('trim', $lines);
    $lines = array_values(array_filter($lines, fn($line) => $line !== ''));

    $result = [];

    foreach ($lines as $line) {

        // ------------------------
        // FOLDER
        // ------------------------
        if (preg_match(
            '/^(.*?)\s+\(folder,\s+shared as exported permanent folder link:\s+(https:\/\/mega\.nz\/folder\/[^\s\)]+)\)$/',
            $line,
            $m
        )) {

            [$clean, $key] = $this->splitMegaLink($m[2]);

            $result[] = [
                'name' => trim($m[1]),
                'type' => 'folder',
                'size' => null,

                'link_full' => trim($m[2]),
                'link_clean' => $clean,
                'decryption_key' => $key,

                'auth_key' => null,
            ];
            continue;
        }

        // ------------------------
        // FILE
        // ------------------------
        if (preg_match(
            '/^(.*?)\s+\((.+?),\s+shared as exported permanent file link:\s+(https:\/\/mega\.nz\/file\/[^\s\)]+)(?:\s+AuthKey=([^\s\)]+))?\)$/',
            $line,
            $m
        )) {

            [$clean, $key] = $this->splitMegaLink($m[3]);

            $result[] = [
                'name' => trim($m[1]),
                'type' => 'file',
                'size' => trim($m[2]),

                'link_full' => trim($m[3]),
                'link_clean' => $clean,
                'decryption_key' => $key,

                'auth_key' => isset($m[4]) ? trim($m[4]) : null,
            ];
        }
    }

    return $result;
}

    private function splitMegaLink(string $url): array
    {
        // ejemplo: https://mega.nz/file/ID#KEY
        $parts = explode('#', $url);

        $clean = $parts[0];
        $key = $parts[1] ?? null;

        return [$clean, $key];
    }


    public function exportList(){
        $this->cd();
        $res = $this->exec('export');
        if ($res['success'] == 1) {
            return $res;
        }
        $parsed = $this->parseExportList($res['output']);
        $output = $res;
        $output['output'] = $parsed;
        return $output;
    }

    /**
     * $remotePath -> path/file to export
     * $megaHosted -> true if you want to export and usable from S4 MEGA
     * $password -> password to export   -> ONLY PRO
     * $expire -> expire time in seconds -> ONLY PRO
     * Example $expire
     * 1M -> 1 minute
     * 1m -> 1 month
     * 1s -> 1 second
     * 1h -> 1 hour
     * 1d -> 1 day
     * 1y -> 1 year
     */
    private function parseExportSingle(string $output): array
    {
        $line = trim($output);

        if (!preg_match('/^Exported\s+(.+?):\s+(https:\/\/mega\.nz\/\S+)$/', $line, $m)) {
            return [];
        }

        $path = trim($m[1]);
        $url = trim($m[2]);

        [$clean, $key] = $this->splitMegaLink($url);

        return [
            'path' => $path,
            'name' => basename($path),
            'type' => str_contains($url, '/folder/') ? 'folder' : 'file',

            'link_full' => $url,
            'link_clean' => $clean,
            'decryption_key' => $key,
        ];
    }
    public function exportAdd(string $remotePath, bool $megaHosted = false, string $password = null, string $expire = null){

        $args = ['-a', '-f', $remotePath];
        if($megaHosted){
            $args[] = '--mega-hosted';
        }
        if($password){
            $args[] = '--password='.$password;
        }
        if($expire){
            $args[] = '--expire='.$expire;
        }
        $res = $this->exec('export', $args);
        if ($res['success'] == 1) {
            return $res;
        }
        $parsed = $this->parseExportSingle($res['output']);
        $output = $res;
        $output['output'] = $parsed;
        return $output;
    }

    public function exportRemove(string $remotePath){
        
        
        $res = $this->exec('export', ['-d', $remotePath]);
        if ($res['success'] == 1) {
            return $res;
        }
        $removed = $res['error_message'] == 'Everything OK';
        $output = $res;
        $output['output'] = $removed ? 'true' : 'false';
        return $output;


    }
    
private function buildMegaPathItem(
    string $path,
    ?string $type = null,
    ?string $size = null,
    ?string $handle = null,
    ?string $link = null,
    ?string $authKey = null
): array {
    $name = basename($path);
    $dir = dirname($path);
    $dir = $dir === '.' ? null : $dir;

    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $ext = $ext !== '' ? $ext : null;

    $linkClean = null;
    $decryptionKey = null;

    if ($link !== null) {
        $split = $this->splitMegaLink($link);
        $linkClean = $split['link_clean'];
        $decryptionKey = $split['decryption_key'];
    }

    if ($type === null) {
        if ($size !== null) {
            $type = 'file';
        } elseif ($ext !== null) {
            $type = 'file';
        } else {
            $type = 'folder';
        }
    }

    return [
        'path' => $path,
        'name' => $name,
        'dir' => $dir,
        'ext' => $ext,
        'handle' => $handle,
        'type' => $type,
        'size' => $size,
        'is_exported' => $link !== null,
        'link_full' => $link,
        'link_clean' => $linkClean,
        'decryption_key' => $decryptionKey,
        'auth_key' => $authKey,
    ];
}

private function parseMegaPathList(string $output): array
{
    $lines = preg_split('/\r\n|\r|\n/', $output);
    $lines = array_map('trim', $lines);
    $lines = array_values(array_filter($lines, fn($line) => $line !== ''));

    $result = [];

    foreach ($lines as $line) {
        // 1) path <H:handle> (folder, shared as exported permanent folder link: URL)
        if (preg_match(
            '/^(.*?)\s+<H:([^>]+)>\s+\(folder,\s+shared as exported permanent folder link:\s+(https:\/\/mega\.nz\/folder\/[^\s\)]+)\)$/',
            $line,
            $m
        )) {
            $result[] = $this->buildMegaPathItem(
                path: trim($m[1]),
                type: 'folder',
                size: null,
                handle: trim($m[2]),
                link: trim($m[3]),
                authKey: null
            );
            continue;
        }

        // 2) path <H:handle> (size, shared as exported permanent file link: URL AuthKey=...)
        if (preg_match(
            '/^(.*?)\s+<H:([^>]+)>\s+\((.+?),\s+shared as exported permanent file link:\s+(https:\/\/mega\.nz\/file\/[^\s\)]+)(?:\s+AuthKey=([^\s\)]+))?\)$/',
            $line,
            $m
        )) {
            $result[] = $this->buildMegaPathItem(
                path: trim($m[1]),
                type: 'file',
                size: preg_replace('/\s+/', ' ', trim($m[3])),
                handle: trim($m[2]),
                link: trim($m[4]),
                authKey: isset($m[5]) ? trim($m[5]) : null
            );
            continue;
        }

        // 3) path <H:handle> (folder)
        if (preg_match('/^(.*?)\s+<H:([^>]+)>\s+\(folder\)$/', $line, $m)) {
            $result[] = $this->buildMegaPathItem(
                path: trim($m[1]),
                type: 'folder',
                size: null,
                handle: trim($m[2])
            );
            continue;
        }

        // 4) path <H:handle> (size)
        if (preg_match('/^(.*?)\s+<H:([^>]+)>\s+\((.+)\)$/', $line, $m)) {
            $result[] = $this->buildMegaPathItem(
                path: trim($m[1]),
                type: 'file',
                size: preg_replace('/\s+/', ' ', trim($m[3])),
                handle: trim($m[2])
            );
            continue;
        }

        // 5) path (folder, shared as exported permanent folder link: URL)
        if (preg_match(
            '/^(.*?)\s+\(folder,\s+shared as exported permanent folder link:\s+(https:\/\/mega\.nz\/folder\/[^\s\)]+)\)$/',
            $line,
            $m
        )) {
            $result[] = $this->buildMegaPathItem(
                path: trim($m[1]),
                type: 'folder',
                size: null,
                handle: null,
                link: trim($m[2])
            );
            continue;
        }

        // 6) path (size, shared as exported permanent file link: URL AuthKey=...)
        if (preg_match(
            '/^(.*?)\s+\((.+?),\s+shared as exported permanent file link:\s+(https:\/\/mega\.nz\/file\/[^\s\)]+)(?:\s+AuthKey=([^\s\)]+))?\)$/',
            $line,
            $m
        )) {
            $result[] = $this->buildMegaPathItem(
                path: trim($m[1]),
                type: 'file',
                size: preg_replace('/\s+/', ' ', trim($m[2])),
                handle: null,
                link: trim($m[3]),
                authKey: isset($m[4]) ? trim($m[4]) : null
            );
            continue;
        }

        // 7) path (folder)
        if (preg_match('/^(.*?)\s+\(folder\)$/', $line, $m)) {
            $result[] = $this->buildMegaPathItem(
                path: trim($m[1]),
                type: 'folder'
            );
            continue;
        }

        // 8) path (size)
        if (preg_match('/^(.*?)\s+\((.+)\)$/', $line, $m)) {
            $result[] = $this->buildMegaPathItem(
                path: trim($m[1]),
                type: 'file',
                size: preg_replace('/\s+/', ' ', trim($m[2]))
            );
            continue;
        }

        // 9) solo path
        $result[] = $this->buildMegaPathItem(
            path: $line
        );
    }

    return $result;
}
    /**
    *Determines time constrains, in the form: [+-]TIMEVALUE
    * $MTIME may include hours(h), days(d), minutes(M),
    *seconds(s), months(m) or years(y)
    *Examples:
    *"+1m12d3h" shows files modified before 1 month, 12 days and 3 hours the current moment
    *"-3h" shows files modified within the last 3 hours
    *"-3d+1h" shows files modified in the last 3 days prior to the last hour
    * $SIZE may include (B)ytes, (K)ilobytes, (M)egabytes, (G)igabytes & (T)erabytes
    * Examples:
    * "+1m12k3B" shows files bigger than 1 Mega, 12 Kbytes and 3Bytes
    * "-3M" shows files smaller than 3 Megabytes
    * "-4M+100K" shows files smaller than 4 Mbytes and bigger than 100 Kbytes
    *
    * $type
    * f -> file
    * d -> directory
    */
    public function find(string $remotePath = '', bool $detailed = false,string $pattern = "", string $type = "", string $mtime = "", string $size = "", bool $showHandles = false, bool $printHandles = false){
        $args = [];
        if($remotePath){
            $args[] = $remotePath;
        }
        if($detailed){
            $args[] = '-l';

        }
        if($pattern){
            $args[] = '--pattern="'.$pattern.'"';
        }
        if($type){
            $args[] = '--type='.$type;
        }   
        if($mtime){
            $args[] = '--mtime='.$mtime;
        }
        if($size){
            $args[] = '--size='.$size;
        }
        if($showHandles){
            $args[] = '--show-handles';
        }
        if($printHandles){
            $args[] = '--print-handles';
        }
        $res = $this->exec('find', $args);
        if ($res['success'] == 1) {
            return $res;
        }

        $parsed = $this->parseMegaPathList($res['output']);

        $output = $res;
        $output['output'] = $parsed;

        return $output;
    }
    /////// UNNECESARY FUNCTIONS ///////
    // public function ftp(){} 
    // public function fuse_add(){} 
    // public function fuse_config(){} 
    // public function fuse_disable(){} 
    // public function fuse_enable(){}
    // public function fuse_remove(){}
    // public function fuse_show(){}
    /**
     * Downloads a remote file/folder or a public link
     * 
     * @param string $remotePath exportedlink|remotepath
     * @param string|null $localPath destination folder
     * @param bool $merge (-m) if the folder already exists, the contents will be merged
     * @param bool $queue (-q) queue download: execute in the background
     * @param bool $ignoreQuotaWarn ignore quota surpassing warning
     * @param bool $usePcre use PCRE expressions
     * @param string|null $password Password to decrypt the password-protected link
     * @return bool|string|null
     */
    public function get(string $remotePath, ?string $localPath = null, bool $merge = false, bool $queue = false, bool $ignoreQuotaWarn = false, bool $usePcre = false, ?string $password = null) {
        $args = [];
        if ($merge) {
            $args[] = '-m';
        }
        if ($queue) {
            $args[] = '-q';
        }
        if ($ignoreQuotaWarn) {
            $args[] = '--ignore-quota-warn';
        }
        if ($usePcre) {
            $args[] = '--use-pcre';
        }
        if ($password !== null) {
            $args[] = '--password=' . $password;
        }
        
        $args[] = $remotePath;
        
        if ($localPath !== null) {
            $args[] = $localPath;
        }
        
        return $this->exec('get', $args);
    }
    /**
     * default is ON
     * @param bool $enable
     * @return array
     */
    public function graphics(bool $enable){
        return $this->exec('graphics', [$enable ? 'on' : 'off']);
    }

    public function graphicsStatus(){
        return $this->exec('graphics');
    }
    public function help() {
        return $this->exec('help');
    }
    public function https(){ return $this->exec('https');}
    public function import($link, $dest = "/", $password = null) {
        $args = [];
        if ($password !== null) {
            $args[] = '--password=' . $password;
        }
        $args[] = $link;
        $args[] = $dest;
        return $this->exec('import', $args);
    }
    // public function invite(){} // unnecesary
    // public function ipc(){}

    /**
     * To See sessions use 'whoami -l'
     * and use the sessionId
     * @param string $sid
     * @return bool|string|null
     */
    public function killsession(string $sid){

        $res =  $this->exec('killsession', [$sid]);
        if($res['success'] == 1){
            $res['output'] = 'false';
            return $res;
        }
        
        $res['output'] = 'true';
        return $res;
    } 

    /**
     * It will be used for uploads and downloads
*
*If not using interactive console, the current local folder will be
 *that of the shell executing mega comands
     * @param string $path
     * @return bool|string|null
     */
    public function lcd(string $path){
        return $this->exec('lcd', [$path]);
    }
    /**
     * Shows the log file
     * @return bool|string|null
     */
    public function log(){ return $this->exec('log');}

    /**
     * Summary of login
     * @param string $email
     * @param string $password
     * @return bool|string|null
     */
    public function login(string $email, string $password, int $authCode = 0, int $authKey = 0, bool $resume = false, string $otherPassword = '', string $sessionId = '') {
        $this->logout(); // Asegurarse de cerrar sesión previa
        $args = [];
        if($authCode){
            $args[] = '--auth-CODE='.$authCode;
        }
        if($authKey){
            $args[] = '--auth-key='.$authKey;
        }
        if($resume){
            $args[] = '--resume';
        }
        if($otherPassword){
            $args[] = '--password='.$otherPassword;
        }
        if($sessionId){
            return $this->exec('login', [$sessionId]);
        }
        $res = $this->exec('login', [$email, $password]);
        if($res['success'] == 1){
            return $res;
        }
        $res['output'] = 'true';
        return $res;
    }
    public function logout() {
        $res = $this->exec('logout');
        if($res['success'] == 1){
            return $res;
        }
        $res['output'] = 'true';
        return $res;
    }

    /**
     * Prints the current local folder
     * @return bool|string|null
     */
    public function lpwd(){ return $this->exec('lpwd');}
    /**
     * Lists the contents of a remote folder
     * @param string|null $path
     * @return array|string|null
     */
    public function ls($path = null) {
        $args = [];
        if ($path) {
            $args[] = $path;
        }
        $output = $this->exec('ls', $args);
        if (!$output) return [];

        return array_filter(explode("\n", trim($output)));
    }

    /**
     * Show the masterkey of the account recovery key
     * @param string $localpatToSave
     * @return bool|string|null
     */
    public function masterkey(string $localpatToSave){ return $this->exec('masterkey', [$localpatToSave]);}

    /**
     * Get media info of a remote file
     * @param string $remotePath
     * @return bool|string|null
     */
    public function mediainfo(string $remotePath){ return $this->exec('mediainfo', [$remotePath]); }
    
    /**
     * Creates a remote folder
     * @param string $path
     * @return bool|string|null
     */
    public function mkdir($remotePath) {
        return $this->exec('mkdir', [$remotePath]);
    }

    /**
     * Show of roots of the MEGA cloud drive
     * @return bool|string|null
     */
    public function mount(){ return $this->exec('mount');}
    
    /**
     * Moves a remote file/folder
     * @param string $remoteSrcPath
     * @param string $remoteDestinationPath
     * @return bool|string|null
     */

    /**
     * move files or folders
     * @param string $remoteSrcPath
     * @param string $remoteDestinationPath
     * @return bool|string|null
     */
    public function mv(string $remoteSrcPath, string $remoteDestinationPath){ return $this->exec('mv', [$remoteSrcPath, $remoteDestinationPath]);}
    
    /**
     * change password account
     * @return void
     */
    public function passwd(string $newPassword, int $authCode = null){
        $args = [$newPassword];
        if ($authCode !== null) {
            $args[] = $authCode;
        }
        return $this->exec('passwd', $args);
    }

    /**
     * See preview or change preview of a file
     * @return void
     */
    public function preview($remotePathFile, $newPreviewFile = null){
        $args = [$remotePathFile];
        if ($newPreviewFile !== null) {
            $args[] = '-s'.$newPreviewFile;
        }
        return $this->exec('preview', $args);
    }


    public function proxy(string $url, string $username = '', string $password = '', bool $auto = false ){
        $args = [$url];
        if ($username !== '') {
            $args[] = '--username='.$username;
        }
        if ($password !== '') {
            $args[] = '--password='.$password;
        }
        if ($auto) {
            $args[] = '--auto';
        }
        return $this->exec('proxy',[$args]);
    } 
    public function psa(){} // innecesary
    /**
     * Uploads a local file or folder to MEGA
     * @param string $localPath
     * @param string $remotePath
     * @return bool|string|null
     */
    public function put($localPath, $remotePath = "/") {
        return $this->exec('put', [$localPath, $remotePath]);
    }

    /**
     * Prints the current remote folder
     * @return bool|string|null
     */
    public function pwd(){ return $this->exec('pwd');}

    /**
     * exit current cmd and server
     * @return void
     */
    public function quit(){ return $this->exec('quit');}

    /**
     * Reloads the remote directory tree
     * @return void
     */
    public function reload(){ return $this->exec('reload');}
    
    /**
     * Removes a file or folder
     * @param string $remotePath
     * @return bool|string|null
     */
    public function rm($remotePath, bool $recursive = false, bool $force = false) {
        $args = [$remotePath];
        if ($recursive) {
            $args[] = '-r';
        }
        if ($force) {
            $args[] = '-f';
        }
        return $this->exec('rm', $args);
    }


    public function session(){ 
        return $this->exec('session');
    }
    // public function share(){} // unnecesary function
    
    /**
     * Show afiliated emails 
     * @return void
     */
    public function showpcr(){
        $this->exec('showpcr');
    }


    /**
     * Register as user with a given email
     * 
     * @param string $email
     * @param string $password
     * @param string|null $name
     * @return bool|string|null
     */
    public function signup(string $email, string $password, ?string $name = null) {
        $args = [$email, $password];
        if ($name !== null) {
            $args[] = '--name=' . $name;
        }
        return $this->exec('signup', $args);
    }
    /**
     * Displays/modifies upload/download rate limits: either speed or max connections
     * 
     * @param string|null $newLimit New limit to set (e.g., "1M", "100K", or connections count)
     * @param bool $download Set/Read download speed limit (-d)
     * @param bool $upload Set/Read upload speed limit (-u)
     * @param bool $uploadConnections Set/Read max number of connections for an upload transfer
     * @param bool $downloadConnections Set/Read max number of connections for a download transfer
     * @param bool $humanReadable Human readable output (-h)
     * @return bool|string|null
     */
    public function speedlimit(?string $newLimit = null, bool $download = false, bool $upload = false, bool $uploadConnections = false, bool $downloadConnections = false, bool $humanReadable = false) {
        $args = [];
        if ($download) {
            $args[] = '-d';
        }
        if ($upload) {
            $args[] = '-u';
        }
        if ($uploadConnections) {
            $args[] = '--upload-connections';
        }
        if ($downloadConnections) {
            $args[] = '--download-connections';
        }
        if ($humanReadable) {
            $args[] = '-h';
        }
        if ($newLimit !== null) {
            $args[] = $newLimit;
        }
        return $this->exec('speedlimit', $args);
    }
    /**
     * Controls synchronizations.
     * remotePath can be a folder and the content of localPath be copied to remotePath
     * not included as Folder 
     * @param string|null $localPath
     * @param string|null $remotePath
     * @param string|null $idOrPath ID or local path for actions
     * @param bool $delete Delete synchronization (-d)
     * @param bool $pause Pause synchronization (-p)
     * @param bool $enable Enable synchronization (-e)
     * @return bool|string|null
     */
    public function sync(?string $localPath = null, ?string $remotePath = null, ?string $idOrPath = null, bool $delete = false, bool $pause = false, bool $enable = false) {
        $args = [];
        if ($delete) { $args[] = '-d'; }
        if ($pause) { $args[] = '-p'; }
        if ($enable) { $args[] = '-e'; }
        
        if ($localPath && $remotePath) {
            $args[] = $localPath;
            $args[] = $remotePath;
        } elseif ($idOrPath) {
            $args[] = $idOrPath;
        }
        
        return $this->exec('sync', $args);
    }

    /**
     * Controls sync configuration.
     * 
     * @param bool $waitSeconds Show delayed-uploads-wait-seconds
     * @param bool $maxAttempts Show delayed-uploads-max-attempts
     * @return bool|string|null
     */
    public function sync_config(bool $waitSeconds = false, bool $maxAttempts = false) {
        $args = [];
        if ($waitSeconds) { $args[] = '--delayed-uploads-wait-seconds'; }
        if ($maxAttempts) { $args[] = '--delayed-uploads-max-attempts'; }
        return $this->exec('sync-config', $args);
    }

    /**
     * Manages ignore filters for syncs
     * 
     * @param string $idOrPath ID, localpath or 'DEFAULT'
     * @param array $filters List of filters
     * @param string $action show|add|add-exclusion|remove|remove-exclusion
     * @return bool|string|null
     */
    public function sync_ignore(string $idOrPath, array $filters = [], string $action = 'show') {
        $args = [];
        if ($action !== 'show') {
            $args[] = '--' . $action;
            foreach ($filters as $filter) {
                $args[] = $filter;
            }
        } else {
            $args[] = '--show';
        }
        $args[] = $idOrPath;
        return $this->exec('sync-ignore', $args);
    }

    /**
     * Show all issues with current syncs
     * 
     * @param string|null $detail ID or '--all'
     * @param int|null $limit Row count limit
     * @param bool $disablePathCollapse disable-path-collapse
     * @param bool $enableWarning enable-warning
     * @param bool $disableWarning disable-warning
     * @return bool|string|null
     */
    public function sync_issues(?string $detail = null, ?int $limit = null, bool $disablePathCollapse = false, bool $enableWarning = false, bool $disableWarning = false) {
        $args = [];
        if ($detail) {
            $args[] = '--detail';
            $args[] = $detail;
        }
        if ($limit !== null) {
            $args[] = '--limit=' . $limit;
        }
        if ($disablePathCollapse) { $args[] = '--disable-path-collapse'; }
        if ($enableWarning) { $args[] = '--enable-warning'; }
        if ($disableWarning) { $args[] = '--disable-warning'; }
        return $this->exec('sync-issues', $args);
    }

    /**
     * To download/upload the thumbnail of a file.
     * 
     * @param string $remotePath
     * @param string $localPath
     * @param bool $set Set thumbnail (-s)
     * @return bool|string|null
     */
    public function thumbnail(string $remotePath, string $localPath, bool $set = false) {
        $args = [];
        if ($set) { $args[] = '-s'; }
        $args[] = $remotePath;
        $args[] = $localPath;
        return $this->exec('thumbnail', $args);
    }

    public function transfers() {
        return $this->exec('transfers');
    }

    /**
     * Lists files in a remote path in a nested tree decorated output
     * 
     * @param string|null $remotePath
     * @return bool|string|null
     */
    public function tree(?string $remotePath = null) {
        $args = [];
        if ($remotePath) { $args[] = $remotePath; }
        return $this->exec('tree', $args);
    }

    /**
     * Updates MEGAcmd
     * 
     * @param string|null $auto ON|OFF|query
     * @return bool|string|null
     */
    public function update(?string $auto = null) {
        $args = [];
        if ($auto) {
            $args[] = '--auto=' . strtoupper($auto);
        }
        return $this->exec('update', $args);
    }

    /**
     * Lists/updates user attributes
     * 
     * @param string|null $attribute
     * @param string|null $value
     * @param string|null $user User email
     * @param bool $list List valid attributes
     * @return bool|string|null
     */
    public function userattr(?string $attribute = null, ?string $value = null, ?string $user = null, bool $list = false) {
        $args = [];
        if ($list) {
            $args[] = '--list';
        } elseif ($attribute && $value) {
            $args[] = '-s';
            $args[] = $attribute;
            $args[] = $value;
        } elseif ($attribute) {
            $args[] = $attribute;
        }
        
        if ($user) {
            $args[] = '--user=' . $user;
        }
        return $this->exec('userattr', $args);
    }

    /**
     * List contacts
     * 
     * @param bool $shared Show shared folders (-s)
     * @param bool $hidden Show all contacts (-h)
     * @param bool $names Show users names (-n)
     * @param string|null $delete Delete contact email
     * @param string|null $timeFormat Time format
     * @return bool|string|null
     */
    public function users(bool $shared = false, bool $hidden = false, bool $names = false, ?string $delete = null, ?string $timeFormat = null) {
        $args = [];
        if ($shared) { $args[] = '-s'; }
        if ($hidden) { $args[] = '-h'; }
        if ($names) { $args[] = '-n'; }
        if ($delete) {
            $args[] = '-d';
            $args[] = $delete;
        }
        if ($timeFormat) {
            $args[] = '--time-format=' . $timeFormat;
        }
        return $this->exec('users', $args);
    }

    /**
     * Prints MEGAcmd versioning and extra info
     * 
     * @param bool $changelog Show changelog (-c)
     * @param bool $extended Show extended info (-l)
     * @return bool|string|null
     */
    public function version(bool $changelog = false, bool $extended = false) {
        $args = [];
        if ($changelog) { $args[] = '-c'; }
        if ($extended) { $args[] = '-l'; }
        return $this->exec('version', $args);
    }

    /**
     * Configures a WEBDAV server to serve a location in MEGA
     * 
     * @param string|null $remotePath
     * @param bool $delete Stop serving (-d)
     * @param bool $all Stop serving all locations (--all)
     * @param int|null $port Port number
     * @param bool $public Allow access from outside localhost
     * @param bool $tls Serve with TLS
     * @return bool|string|null
     */
    public function webdav(?string $remotePath = null, bool $delete = false, bool $all = false, ?int $port = null, bool $public = false, bool $tls = false) {
        $args = [];
        if ($delete) {
            $args[] = '-d';
            if ($all) {
                $args[] = '--all';
            } elseif ($remotePath) {
                $args[] = $remotePath;
            }
        } else {
            if ($remotePath) { $args[] = $remotePath; }
            if ($port) { $args[] = '--port=' . $port; }
            if ($public) { $args[] = '--public'; }
            if ($tls) { $args[] = '--tls'; }
        }
        return $this->exec('webdav', $args);
    }
    public function whoami() {
        // $res = $this->exec('whoami');
        // if($res['success']){
        //     return str_replace('Account e-mail: ', '',$res['output']);
        // }
        // return $res;
        return $this->exec('whoami');
    }


    private function parseWhoamiFull(string $output): array
{
    $lines = preg_split('/\r\n|\r|\n/', $output);
    $lines = array_map('trim', $lines);
    $lines = array_values(array_filter($lines, fn($line) => $line !== ''));

    $result = [
        'account_email' => null,
        'available_storage' => null,
        'storage' => [
            'root' => null,
            'inbox' => null,
            'rubbish' => null,
            'file_versions_size' => null,
        ],
        'pro_level' => null,
        'subscription_type' => null,
        'account_balance' => null,
        'sessions' => [],
        'active_sessions_count' => null,
    ];

    $currentSession = null;
    $markCurrentNextSession = false;

    foreach ($lines as $line) {
        if (preg_match('/^Account e-mail:\s+(.+)$/', $line, $m)) {
            $result['account_email'] = trim($m[1]);
            continue;
        }

        if (preg_match('/^Available storage:\s+(.+)$/', $line, $m)) {
            $result['available_storage'] = preg_replace('/\s+/', ' ', trim($m[1]));
            continue;
        }

        if (preg_match('/^In ROOT:\s+(.+?)\s+in\s+(\d+)\s+file\(s\)\s+and\s+(\d+)\s+folder\(s\)$/', $line, $m)) {
            $result['storage']['root'] = [
                'size' => preg_replace('/\s+/', ' ', trim($m[1])),
                'files' => (int)$m[2],
                'folders' => (int)$m[3],
            ];
            continue;
        }

        if (preg_match('/^In INBOX:\s+(.+?)\s+in\s+(\d+)\s+file\(s\)\s+and\s+(\d+)\s+folder\(s\)$/', $line, $m)) {
            $result['storage']['inbox'] = [
                'size' => preg_replace('/\s+/', ' ', trim($m[1])),
                'files' => (int)$m[2],
                'folders' => (int)$m[3],
            ];
            continue;
        }

        if (preg_match('/^In RUBBISH:\s+(.+?)\s+in\s+(\d+)\s+file\(s\)\s+and\s+(\d+)\s+folder\(s\)$/', $line, $m)) {
            $result['storage']['rubbish'] = [
                'size' => preg_replace('/\s+/', ' ', trim($m[1])),
                'files' => (int)$m[2],
                'folders' => (int)$m[3],
            ];
            continue;
        }

        if (preg_match('/^Total size taken up by file versions:\s+(.+)$/', $line, $m)) {
            $result['storage']['file_versions_size'] = preg_replace('/\s+/', ' ', trim($m[1]));
            continue;
        }

        if (preg_match('/^Pro level:\s+(\d+)$/', $line, $m)) {
            $result['pro_level'] = (int)$m[1];
            continue;
        }

        if (preg_match('/^Subscription type:\s*(.*)$/', $line, $m)) {
            $value = trim($m[1]);
            $result['subscription_type'] = $value !== '' ? $value : null;
            continue;
        }

        if (preg_match('/^Account balance:\s*(.*)$/', $line, $m)) {
            $value = trim($m[1]);
            $result['account_balance'] = $value !== '' ? $value : null;
            continue;
        }

        if ($line === 'Current Active Sessions:') {
            continue;
        }

        if ($line === '* Current Session') {
            $markCurrentNextSession = true;
            continue;
        }

        if (preg_match('/^Session ID:\s+(.+)$/', $line, $m)) {
            if ($currentSession !== null) {
                $result['sessions'][] = $currentSession;
            }

            $currentSession = [
                'session_id' => trim($m[1]),
                'session_start' => null,
                'most_recent_activity' => null,
                'ip' => null,
                'country' => null,
                'user_agent' => null,
                'is_current' => $markCurrentNextSession ? true : false,
            ];

            $markCurrentNextSession = false;
            continue;
        }

        if ($currentSession !== null && preg_match('/^Session start:\s+(.+)$/', $line, $m)) {
            $currentSession['session_start'] = trim($m[1]);
            continue;
        }

        if ($currentSession !== null && preg_match('/^Most recent activity:\s+(.+)$/', $line, $m)) {
            $currentSession['most_recent_activity'] = trim($m[1]);
            continue;
        }

        if ($currentSession !== null && preg_match('/^IP:\s+(.+)$/', $line, $m)) {
            $currentSession['ip'] = trim($m[1]);
            continue;
        }

        if ($currentSession !== null && preg_match('/^Country:\s+(.+)$/', $line, $m)) {
            $currentSession['country'] = trim($m[1]);
            continue;
        }

        if ($currentSession !== null && preg_match('/^User-Agent:\s+(.+)$/', $line, $m)) {
            $currentSession['user_agent'] = trim($m[1]);
            continue;
        }

        if ($line === '-----') {
            continue;
        }

        if (preg_match('/^(\d+)\s+active sessions opened$/', $line, $m)) {
            $result['active_sessions_count'] = (int)$m[1];
            continue;
        }
    }

    if ($currentSession !== null) {
        $result['sessions'][] = $currentSession;
    }

    return $result;
}
    public function sessions(){
        $res = $this->exec('whoami', '-l');
        if($res['success'] == 1){
        }
        $res['output'] = $this->parseWhoamiFull($res['output']);
        return $res;
    }
}
