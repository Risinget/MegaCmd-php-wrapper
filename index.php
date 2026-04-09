<?php 


function test(){
/**
 * MegaCmd PHP Wrapper - Full Exhaustive Class Order Test
 * Este script prueba TODAS las funciones de la clase MegaCmd en el orden exacto en que están definidas.
 */

require_once 'MegaCmd.php';

require_once 'config.php';

echo "<pre>"; // Para visualización en navegador
echo "--- INICIANDO TEST EXHAUSTIVO EN ORDEN DE CLASE ---\n\n";

// 1. __construct
$mega = new MegaCmd();
echo "1. [__construct] Objeto creado correctamente.\n";

// 2. exec
echo "2. [exec] Prueba ('whoami'): " . print_r($mega->whoami(), true) . "\n";


// echo '2.1 [LOGIN]' . print_r($mega->login($mega_email, $mega_password),true) . "\n";
// 3. attr
echo "3. [attr]: " . print_r( $mega->attr(),true) . "\n";

// 4. autocomplete
// echo "4. [autocomplete]: " . $mega->autocomplete() . "\n";  unsupported cmd

echo '9.0 [mkdir]'. print_r($mega->mkdir('commands'),true) . "\n";

echo "9. [backupCreate]: ". print_r($mega->backupCreate('prueba','/commands','1d',1), true);

// 5. backupHistory
echo "5. [backupHistory]: " . print_r($mega->backupHistory(),true) . "\n";

// 6. backupList
echo "6. [backupList]: " . print_r($mega->backupList(),true) . "\n";

// 8. backupAbort
echo "8. [backupAbort]: " . print_r($mega->backupAbort($mega->backupList()['output'][0]['tag']),true) . "\n";

// 7. backupDelete
echo "7. [backupDelete]: " . print_r($mega->backupDelete($mega->backupList()['output'][0]['tag']),true) . "\n";

// 9. backup

// 10. cancel
// echo "10. [cancel]: // \$mega->cancel(); (DANGER / Disabled)\n";

// 11. cat
echo "11. [cat]: " . print_r($mega->cat('attr.md'),true) . "\n";

// 12. cd
echo "12. [cd]: " . print_r($mega->cd('/prueba'),true) . "\n";


// 14. codepage
// echo "14. [codepage]: " . print_r($mega->codepage(),true) . "\n"; // not exist

// 15. completion
// echo "15. [completion]: " . print_r($mega->completion(),true) . "\n"; // not exist

// 16. configure
// echo "16. [configure]:  ".print_r($mega->configure('max_nodes_in_cache',20),true)."\n"; // not in windows



// 17. confirm
echo "17. [confirm]: " . print_r($mega->confirm('link','[EMAIL_ADDRESS]','password'),true)."\n";

// 18. confirmcancel
// echo "18. [confirmcancel]: // \$mega->confirmcancel(string \$link, string \$password);\n"; // DANGEROUS

// 19. cp
echo "19. [cp]: // " . print_r($mega->cp('/attr.md', '/commands/attrCopiado.md'),true) . "\n";

// 20. debug
echo "20. [debug]: " . print_r($mega->debug(),true) . "\n";

// 21. deleteversions
echo "21. [deleteversions]: " . print_r($mega->deleteversions(),true) . "\n";

// 22. df
echo "22. [df]:\n" . print_r($mega->df(false),true) . "\n";

// 23. du
echo "23. [du]:\n" . print_r($mega->du('/prueba',true),true) . "\n";

// 24. errorcode
echo "24. [errorcode](0): " . print_r($mega->errorcode(0),true) . "\n";

// 25. exclude
// echo "25. [exclude]: // Deprecated / Unused\n";

// 26. export
echo "26. [isExport]: " . print_r($mega->isExported('/attr.md'),true) . "\n";
echo "26. [isExport]: " . print_r($mega->isExported('/ssadsad'),true) . "\n";
echo "26. [export]: " . print_r($mega->exportAdd('/attr.md'),true) . "\n";
echo "26. [isExport]: " . print_r($mega->isExported('/attr.md'),true) . "\n";
echo "26. [export]: " . print_r($mega->exportAdd('/attr.md'),true) . "\n";


// 27. exportList
echo "27. [exportList]:\n" . print_r($mega->exportList(),true) . "\n";

// 29. exportRemove
echo "29. [exportRemove]: " . print_r($mega->exportRemove('/attr.md'),true) . "\n";



exit;

// 30. find
echo "30. [find]: // \$mega->find(string \$remotePath ...);\n";

// 31. get
echo "31. [get]: // \$mega->get(string \$remotePath, ?string \$localPath ...);\n";

// 32. graphics
echo "32. [graphics]: // \$mega->graphics(bool \$enable);\n";

// 33. graphicsStatus
echo "33. [graphicsStatus]: " . $mega->graphicsStatus() . "\n";

// 34. help
echo "34. [help]: Fragmento:\n" . substr($mega->help(), 0, 100) . "...\n";

// 35. https
echo "35. [https]: " . $mega->https() . "\n";

// 36. import
echo "36. [import]: // \$mega->import(\$link, \$dest = '/', \$password = null);\n";

// 37. killsession
echo "37. [killsession]: // \$mega->killsession(string \$sid);\n";

// 38. lcd
echo "38. [lcd]: // \$mega->lcd(string \$path);\n";

// 39. log
echo "39. [log]: Fragmento:\n" . substr($mega->log(), 0, 100) . "...\n";

// 40. login
echo "40. [login]: // \$mega->login(string \$email, string \$password ...);\n";

// 41. logout
echo "41. [logout]: // \$mega->logout();\n";

// 42. lpwd
echo "42. [lpwd]: " . $mega->lpwd() . "\n";

// 43. ls
echo "43. [ls]: "; print_r($mega->ls()); echo "\n";

// 44. masterkey
echo "44. [masterkey]: // \$mega->masterkey(string \$localpatToSave);\n";

// 45. mediainfo
echo "45. [mediainfo]: // \$mega->mediainfo(string \$remotePath);\n";

// 46. mkdir
echo "46. [mkdir]: // \$mega->mkdir(\$remotePath);\n";

// 47. mount
echo "47. [mount]:\n" . $mega->mount() . "\n";

// 48. mv
echo "48. [mv]: // \$mega->mv(string \$remoteSrcPath, string \$remoteDestinationPath);\n";

// 49. passwd
echo "49. [passwd]: // \$mega->passwd(string \$newPassword ...);\n";

// 50. preview
echo "50. [preview]: // \$mega->preview(\$remotePathFile, \$newPreviewFile = null);\n";

// 51. proxy
echo "51. [proxy]: // \$mega->proxy(string \$url ...);\n";

// 52. psa
echo "52. [psa]: " . $mega->psa() . "\n";

// 53. put
echo "53. [put]: // \$mega->put(\$localPath, \$remotePath = '/');\n";

// 54. pwd
echo "54. [pwd]: " . $mega->pwd() . "\n";

// 55. quit
echo "55. [quit]: // \$mega->quit();\n";

// 56. reload
echo "56. [reload]: " . $mega->reload() . "\n";

// 57. rm
echo "57. [rm]: // \$mega->rm(\$remotePath, bool \$recursive = false ...);\n";

// 58. session
echo "58. [session]:\n" . $mega->session() . "\n";

// 59. showpcr
echo "59. [showpcr]: " . $mega->showpcr() . "\n";

// 60. signup
echo "60. [signup]: // \$mega->signup(string \$email, string \$password ...);\n";

// 61. speedlimit
echo "61. [speedlimit]:\n" . $mega->speedlimit() . "\n";

// 62. sync
echo "62. [sync]:\n" . $mega->sync() . "\n";

// 63. sync_config
echo "63. [sync_config]:\n" . $mega->sync_config() . "\n";

// 64. sync_ignore
echo "64. [sync_ignore]: // \$mega->sync_ignore(string \$idOrPath ...);\n";

// 65. sync_issues
echo "65. [sync_issues]:\n" . $mega->sync_issues() . "\n";

// 66. thumbnail
echo "66. [thumbnail]: // \$mega->thumbnail(string \$remotePath, string \$localPath ...);\n";

// 67. transfers
echo "67. [transfers]:\n" . $mega->transfers() . "\n";

// 68. tree
echo "68. [tree]: Fragmento:\n" . substr($mega->tree(), 0, 100) . "...\n";

// 69. update
echo "69. [update]: " . $mega->update() . "\n";

// 70. userattr
echo "70. [userattr]: " . $mega->userattr(null, null, null, true) . "\n";

// 71. users
echo "71. [users]:\n" . $mega->users() . "\n";

// 72. version
echo "72. [version]:\n" . $mega->version() . "\n";

// 73. webdav
echo "73. [webdav]:\n" . $mega->webdav() . "\n";

// 74. whoami
echo "74. [whoami]: " . $mega->whoami() . "\n";

echo "\n--- TEST FINALIZADO ---";
echo "</pre>";

}



// mega-attr
// mega-backup
// mega-cancel
// mega-cat
// mega-cd
// mega-cmd
// mega-cmd-server
// mega-configure
// mega-confirm
// mega-confirmcancel
// mega-cp
// mega-debug
// mega-deleteversions
// mega-df
// mega-du
// mega-errorcode
// mega-exclude
// mega-exec
// mega-export
// mega-find
// mega-ftp
// mega-fuse-add
// mega-fuse-config
// mega-fuse-disable
// mega-fuse-enable
// mega-fuse-remove
// mega-fuse-show
// mega-get
// mega-graphics
// mega-help
// mega-https
// mega-import
// mega-invite
// mega-ipc
// mega-killsession
// mega-lcd
// mega-log
// mega-login
// mega-logout
// mega-lpwd
// mega-ls
// mega-mediainfo
// mega-mkdir
// mega-mount
// mega-mv
// mega-passwd
// mega-permissions
// mega-preview
// mega-proxy
// mega-put
// mega-pwd
// mega-quit
// mega-reload
// mega-rm
// mega-session
// mega-share
// mega-showpcr
// mega-signup
// mega-speedlimit
// mega-sync
// mega-sync-config
// mega-sync-ignore
// mega-sync-issues
// mega-thumbnail
// mega-transfers
// mega-tree
// mega-userattr
// mega-users
// mega-version
// mega-webdav
// mega-whoami