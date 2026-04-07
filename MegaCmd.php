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
    public function get($remotePath, $localPath = ".") {
        return $this->exec('get', [$remotePath, $localPath]);
    }
    public function graphics(){}
    public function help() {
        return $this->exec('help');
    }
    public function https(){}
    public function import($link, $dest = "/") {
        return $this->exec('import', [$link, $dest]);
    }
    public function invite(){}
    public function ipc(){}
    public function killsession(){}
    public function lcd(){}
    public function log(){}
    public function login($email, $password) {
        $this->logout(); // Asegurarse de cerrar sesión previa
        return $this->exec('login', [$email, $password]);
    }
    public function logout() {
        return $this->exec('logout');
    }

    public function lpwd(){}
    public function ls($path = null) {
        $args = [];
        if ($path) {
            $args[] = $path;
        }
        $output = $this->exec('ls', $args);
        if (!$output) return [];

        return array_filter(explode("\n", trim($output)));
    }
    public function masterkey(){}
    public function mediainfo(){}
    public function mkdir($path) {
        return $this->exec('mkdir', [$path]);
    }
    public function mount(){}
    public function mv(){}
    public function passwd(){}
    public function preview(){}

    public function proxy(){}
    public function psa(){}
    public function put($localPath, $remotePath = "/") {
        return $this->exec('put', [$localPath, $remotePath]);
    }
    public function pwd(){}
    public function quit(){}
    public function reload(){}
    public function rm($path) {
        return $this->exec('rm', [$path]);
    }
        public function session(){}
    public function share(){}
    public function showpcr(){}
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
