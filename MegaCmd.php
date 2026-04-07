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
     * attr.md
     * @return bool|string|null
     */
    public function attr(){ return $this->exec('attr');}
    public function autocomplete(){ return $this->exec('autocomplete');}
    /**
     * Return backups history
     */
    public function backupHistory(){ return $this->exec('backup -h'); }
    /**
     * Return backups list
     */
    public function backupList(){ return $this->exec('backup -l'); }
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


    public function signup(){}
    public function speedlimit(){}
    public function sync(){}
    public function sync_config(){}
    public function sync_ignore(){}
    public function sync_issues(){}
    public function thumbnail(){}
    public function transfers() {
        return $this->exec('transfers');
    }
    public function tree(){}
    public function update(){}
    public function userattr(){}
    public function users(){}
    public function version(){}
    public function webdav(){}
    public function whoami() {
        return trim($this->exec('whoami'));
    }
}
