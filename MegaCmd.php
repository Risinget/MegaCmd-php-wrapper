<?php

/**
 * MegaCmd PHP Class
 * Una clase para interactuar con MEGAcmd (mega.nz) mediante comandos de consola.
 */
class MegaCmd {
    private $executablePath;
    private $isWindows;

    public function __construct($path = null) {
        $this->isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
        if ($path) {
            $this->executablePath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        } else {
            if ($this->isWindows) {
                // Ruta típica de instalación del usuario
                $localAppData = getenv('LOCALAPPDATA');
                $defaultPath = $localAppData . DIRECTORY_SEPARATOR . 'MEGAcmd' . DIRECTORY_SEPARATOR;
                if (file_exists($defaultPath . 'mega-help.bat')) {
                    $this->executablePath = $defaultPath;
                } else {
                    $this->executablePath = ""; // Confiar en el PATH
                }
            } else {
                $this->executablePath = "";
            }
        }
    }

    public function exec($command, $args = []) {
        $cmdName = "mega-" . $command;
        $fullPath = $this->executablePath . $cmdName;

        // En Windows, los comandos de MEGAcmd suelen ser .bat que llaman al server
        if ($this->isWindows && $this->executablePath !== "" && file_exists($fullPath . ".bat")) {
            $fullCommand = 'cmd /c "' . $fullPath . '.bat"';
        } else {
            $fullCommand = $fullPath;
        }
        
        // Escapar argumentos
        $escapedArgs = array_map('escapeshellarg', $args);
        $cmdLine = $fullCommand . " " . implode(" ", $escapedArgs) . " 2>&1";

        $output = shell_exec($cmdLine);
        return $output;
    }

    
    /**
     * ls -l --show-handlesw
     */

    /**
     * attr.md
     * @return bool|string|null
     */
    public function attr(){ return $this->exec('attr');}
    public function autocomplete(){ return $this->exec('autocomplete');}
    /**
     * Return backups history
     */
    public function backupHistory(){ 
        // No backup configured.
        if(str_contains('No backup configured.', $this->exec('backup -h'))){
            return ['No backup configured.'];
        }
        return $this->exec('backup -h');
     }
    /**
     * Return backups list
     */
    public function backupList(){ 
        if(str_contains('No backup configured.', $this->exec('backup -l'))){
            return ['No backup configured.'];
        }
        return $this->exec('backup -l'); 
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
    public function backup(string $localPath, string $remotePath, string $period, int $numBackups){
        // falta --time-format=
        return $this->exec('backup', [$localPath, $remotePath, '--period='.$period, '--num-backups='.$numBackups]);
    }

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
    public function clear(){ return $this->exec('clear');}
    /**
     * See codepage interpreter terminal
     */
    public function codepage(){ return $this->exec('codepage');}
    public function completion(){ return $this->exec('completion');}
    public function configure(string $key, int $value){ 
        // exported_folders_sdks MIN 0 MAX 20 -> paralel download
        // max_nodes_in_cache MAX QUANTITY    -> nodes, subnodes from folders
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
    /**
     * Show storage information
     * @return bool|string|null
     */
    public function df(){ return $this->exec('df');}
    /**
     * Show disk usage
     * @return bool|string|null
     */
    public function du(){ return $this->exec('du');}
    public function errorcode(int $errorcode){
        return $this->exec('errorcode', [$errorcode]);
    }
    /**
     * exclude -a "*.tmp" "*.log"
     */
    public function exclude(array $patterns){} // deprecated
    // public function exit(){ return $this->exec('exit');} // only on MegaCMDShell.exe
    public function export($path) {
        return $this->exec('export', [$path]);
    }

    public function exportList(){
        return $this->exec('export');
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
    public function exportAdd(string $remotePath, bool $megaHosted = false, string $password = null, string $expire = null){
        $args = ['-a '.$remotePath];
        if($megaHosted){
            $args[] = '--mega-hosted';
        }
        if($password){
            $args[] = '--password='.$password;
        }
        if($expire){
            $args[] = '--expire='.$expire;
        }
        return $this->exec('export', $args);
    }

    public function exportRemove(string $remotePath){
        return $this->exec('export', ['-d '.$remotePath]);
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
     */
    public function find(string $remotePath, string $pattern = "", string $type = "", string $mtime = "", string $size = "", bool $showHandles = false, bool $printHandles = false){
        $args = [];
        if($remotePath){
            $args[] = $remotePath;
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
        return $this->exec('find', $args);
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
        return $this->exec('killsession', [$sid]);
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
        return $this->exec('login', [$email, $password]);
    }
    public function logout() {
        return $this->exec('logout');
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
        return str_replace('Account e-mail: ', '',trim($this->exec('whoami')));
    }
    public function sessions(){
        return trim($this->exec('whoami', '-l'));
    }
}
