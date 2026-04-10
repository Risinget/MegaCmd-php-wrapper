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
echo "2. [exec] Prueba ('whoami'): " . print_r($mega->whoami(), true) . "\n"; // IN linux.txt


// echo '2.1 [LOGIN]' . print_r($mega->login($mega_email, $mega_password),true) . "\n";
// 3. attr
echo "3. [attr]: " . print_r( $mega->attr(),true) . "\n"; // IN linux.txt

// 4. autocomplete
// echo "4. [autocomplete]: " . $mega->autocomplete() . "\n";  unsupported cmd

echo '9.0 [mkdir]'. print_r($mega->mkdir('commands'),true) . "\n"; // IN linux.txt

echo "9. [backupCreate]: ". print_r($mega->backupCreate('prueba','/commands','1d',1), true); // IN linux.txt

// 5. backupHistory
echo "5. [backupHistory]: " . print_r($mega->backupHistory(),true) . "\n"; // IN linux.txt

// 6. backupList
echo "6. [backupList]: " . print_r($mega->backupList(),true) . "\n"; // IN linux.txt

// 8. backupAbort
echo "8. [backupAbort]: " . print_r($mega->backupAbort($mega->backupList()['output'][0]['tag']),true) . "\n"; // IN linux.txt

// 7. backupDelete
echo "7. [backupDelete]: " . print_r($mega->backupDelete($mega->backupList()['output'][0]['tag']),true) . "\n"; // IN linux.txt

// 9. backup

// 10. cancel
// echo "10. [cancel]: // \$mega->cancel(); (DANGER / Disabled)\n";

// 11. cat
echo "11. [cat]: " . print_r($mega->cat('attr.md'),true) . "\n"; // IN linux.txt

// 12. cd
echo "12. [cd]: " . print_r($mega->cd('/prueba'),true) . "\n"; // IN linux.txt


// 14. codepage
// echo "14. [codepage]: " . print_r($mega->codepage(),true) . "\n"; // not exist

// 15. completion
// echo "15. [completion]: " . print_r($mega->completion(),true) . "\n"; // not exist

// 16. configure
// echo "16. [configure]:  ".print_r($mega->configure('max_nodes_in_cache',20),true)."\n"; // not in windows // IN linux.txt



// 17. confirm
echo "17. [confirm]: " . print_r($mega->confirm('link','[EMAIL_ADDRESS]','password'),true)."\n"; // IN linux.txt

// 18. confirmcancel
// echo "18. [confirmcancel]: // \$mega->confirmcancel(string \$link, string \$password);\n"; // DANGEROUS

// 19. cp
echo "19. [cp]: // " . print_r($mega->cp('/attr.md', '/commands/attrCopiado.md'),true) . "\n"; // IN linux.txt

// 20. debug
echo "20. [debug]: " . print_r($mega->debug(),true) . "\n"; // IN linux.txt

// 21. deleteversions
echo "21. [deleteversions]: " . print_r($mega->deleteversions(),true) . "\n"; // IN linux.txt

// 22. df
echo "22. [df]:\n" . print_r($mega->df(false),true) . "\n"; // IN linux.txt

// 23. du
echo "23. [du]:\n" . print_r($mega->du('/prueba',true),true) . "\n"; // IN linux.txt

// 24. errorcode
echo "24. [errorcode](0): " . print_r($mega->errorcode(0),true) . "\n"; // IN linux.txt

// 25. exclude
// echo "25. [exclude]: // Deprecated / Unused\n";

// 26. export
echo "26. [isExport]: " . print_r($mega->isExported('/attr.md'),true) . "\n"; // IN linux.txt
echo "26. [isExport]: " . print_r($mega->isExported('/ssadsad'),true) . "\n"; // IN linux.txt
echo "26. [export]: " . print_r($mega->exportAdd('/attr.md'),true) . "\n"; // IN linux.txt
echo "26. [isExport]: " . print_r($mega->isExported('/attr.md'),true) . "\n"; // IN linux.txt
echo "26. [export]: " . print_r($mega->exportAdd('/attr.md'),true) . "\n"; // IN linux.txt
echo "26. [export]: " . print_r($mega->exportAdd('/commands/commands'),true) . "\n"; // IN linux.txt


// 27. exportList
echo "27. [exportList]:\n" . print_r($mega->exportList(),true) . "\n"; // IN linux.txt

// 29. exportRemove
echo "29. [exportRemove]: " . print_r($mega->exportRemove('/attr.md'),true) . "\n"; // IN linux.txt




// 30. find
// echo "30. [find]: " . print_r($mega->find(detailed: true, showHandles:true), true) . "\n"; // IN linux.txt

// 31. get
// echo "31. [get]: " . print_r($mega->get('/prueba', 'fokin'), true) . "\n"; // IN linux.txt

// 32. graphics
echo "32. [graphics]: " . print_r($mega->graphics(true), true) . "\n"; // IN linux.txt


// 34. help
echo "34. [help]: " . print_r($mega->help(), true) . "\n"; // IN linux.txt

// 35. https
echo "35. [https]: " . print_r($mega->https(), true) . "\n"; // IN linux.txt

// 36. import

// echo "36. [import]: " . print_r($mega->import($mega->exportList()['output'][0]['link_full'], '/commands/commands'), true) . "\n"; // IN linux.txt

// 37. killsession



echo "37. [session]: " . print_r($mega->sessions(), true) . "\n"; // IN linux.txt

echo "37. [killsession]: " . print_r($mega->killsession('sid'), true) . "\n"; // IN linux.txt

// 38. lcd
// echo "38. [lcd]: " . print_r($mega->lcd('.'), true) . "\n"; // IN linux.txt // probar mas tarde

// 39. log
echo "39. [log]: " . print_r($mega->log(), true) . "\n"; // IN linux.txt

// 40. login
// echo "40. [login]: " . print_r($mega->login('user@example.com', 'password'), true) . "\n"; // IN linux.txt

// 41. logout
// echo "41. [logout]: " . print_r($mega->logout(), true) . "\n"; // IN linux.txt
// 42. lpwd
// echo "42. [lpwd]: " . print_r($mega->lpwd(), true) . "\n"; // IN linux.txt

// 43. ls
echo "43. [ls]: "; print_r($mega->ls()); echo "\n"; // IN linux.txt
exit;

// 44. masterkey
echo "44. [masterkey]: " . print_r($mega->masterkey('.'), true) . "\n";

// 45. mediainfo
echo "45. [mediainfo]: " . print_r($mega->mediainfo('/prueba'), true) . "\n"; // IN linux.txt

// 46. mkdir
echo "46. [mkdir]: " . print_r($mega->mkdir('/prueba/newdir'), true) . "\n"; // IN linux.txt

// 47. mount
echo "47. [mount]: " . print_r($mega->mount(), true) . "\n"; // IN linux.txt

// 48. mv
echo "48. [mv]: " . print_r($mega->mv('/src', '/dest'), true) . "\n"; // IN linux.txt

// 49. passwd
echo "49. [passwd]: " . print_r($mega->passwd('newpassword'), true) . "\n"; // IN linux.txt

// 50. preview
echo "50. [preview]: " . print_r($mega->preview('/file.jpg'), true) . "\n"; // IN linux.txt

// 51. proxy
echo "51. [proxy]: " . print_r($mega->proxy('http://proxy:8080'), true) . "\n"; // IN linux.txt

// 52. psa
echo "52. [psa]: " . print_r($mega->psa(), true) . "\n";

// 53. put
echo "53. [put]: " . print_r($mega->put('local.txt', '/'), true) . "\n"; // IN linux.txt

// 54. pwd
echo "54. [pwd]: " . print_r($mega->pwd(), true) . "\n"; // IN linux.txt

// 55. quit
echo "55. [quit]: " . print_r($mega->quit(), true) . "\n"; // IN linux.txt

// 56. reload
echo "56. [reload]: " . print_r($mega->reload(), true) . "\n"; // IN linux.txt

// 57. rm
echo "57. [rm]: " . print_r($mega->rm('/path', true), true) . "\n"; // IN linux.txt

// 58. session
echo "58. [session]: " . print_r($mega->session(), true) . "\n"; // IN linux.txt

// 59. showpcr
echo "59. [showpcr]: " . print_r($mega->showpcr(), true) . "\n"; // IN linux.txt

// 60. signup
echo "60. [signup]: " . print_r($mega->signup('user@example.com', 'password'), true) . "\n"; // IN linux.txt

// 61. speedlimit
echo "61. [speedlimit]: " . print_r($mega->speedlimit(), true) . "\n"; // IN linux.txt

// 62. sync
echo "62. [sync]: " . print_r($mega->sync(), true) . "\n"; // IN linux.txt

// 63. sync_config
echo "63. [sync_config]: " . print_r($mega->sync_config(), true) . "\n"; // IN linux.txt

// 64. sync_ignore
echo "64. [sync_ignore]: " . print_r($mega->sync_ignore('/path'), true) . "\n"; // IN linux.txt

// 65. sync_issues
echo "65. [sync_issues]: " . print_r($mega->sync_issues(), true) . "\n"; // IN linux.txt

// 66. thumbnail
echo "66. [thumbnail]: " . print_r($mega->thumbnail('/path', '.'), true) . "\n"; // IN linux.txt

// 67. transfers
echo "67. [transfers]: " . print_r($mega->transfers(), true) . "\n"; // IN linux.txt

// 68. tree
echo "68. [tree]: " . print_r($mega->tree(), true) . "\n"; // IN linux.txt

// 69. update
echo "69. [update]: " . print_r($mega->update(), true) . "\n";

// 70. userattr
echo "70. [userattr]: " . print_r($mega->userattr(null, null, null, true), true) . "\n"; // IN linux.txt

// 71. users
echo "71. [users]: " . print_r($mega->users(), true) . "\n"; // IN linux.txt

// 72. version
echo "72. [version]: " . print_r($mega->version(), true) . "\n"; // IN linux.txt

// 73. webdav
echo "73. [webdav]: " . print_r($mega->webdav(), true) . "\n"; // IN linux.txt

// 74. whoami
echo "74. [whoami]: " . print_r($mega->whoami(), true) . "\n"; // IN linux.txt

echo "\n--- TEST FINALIZADO ---";
echo "</pre>";

}


