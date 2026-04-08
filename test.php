<?php
/**
 * MegaCmd PHP Wrapper - Full Exhaustive Class Order Test
 * Este script prueba TODAS las funciones de la clase MegaCmd en el orden exacto en que están definidas.
 */

require_once 'MegaCmd.php';

echo "<pre>"; // Para visualización en navegador
echo "--- INICIANDO TEST EXHAUSTIVO EN ORDEN DE CLASE ---\n\n";

// 1. __construct
$mega = new MegaCmd();
echo "1. [__construct] Objeto creado correctamente.\n";

// 2. exec
echo "2. [exec] Prueba ('whoami'): " . $mega->whoami() . "\n";

// 3. attr
echo "3. [attr]: " . $mega->attr() . "\n";

// 4. autocomplete
echo "4. [autocomplete]: " . $mega->autocomplete() . "\n";

// 5. backupHistory
echo "5. [backupHistory]: " . $mega->backupHistory() . "\n";

// 6. backupList
echo "6. [backupList]: " . $mega->backupList() . "\n";

// 7. backupDelete
echo "7. [backupDelete]: // \$mega->backupDelete(int \$tag);\n";

// 8. backupAbort
echo "8. [backupAbort]: // \$mega->backupAbort(int \$tag);\n";

// 9. backup
echo "9. [backup]: // \$mega->backup(string \$localPath, string \$remotePath, string \$period, int \$numBackups);\n";

// 10. cancel
echo "10. [cancel]: // \$mega->cancel(); (DANGER / Disabled)\n";

// 11. cat
echo "11. [cat]: // \$mega->cat(string \$remotePath);\n";

// 12. cd
echo "12. [cd]: " . $mega->cd() . "\n";

// 13. clear
echo "13. [clear]: " . $mega->clear() . "\n";

// 14. codepage
echo "14. [codepage]: " . $mega->codepage() . "\n";

// 15. completion
echo "15. [completion]: " . $mega->completion() . "\n";

// 16. configure
echo "16. [configure]: // \$mega->configure(string \$key, int \$value);\n";

// 17. confirm
echo "17. [confirm]: // \$mega->confirm(string \$link, string \$email, string \$password);\n";

// 18. confirmcancel
echo "18. [confirmcancel]: // \$mega->confirmcancel(string \$link, string \$password);\n";

// 19. cp
echo "19. [cp]: // \$mega->cp(string \$remotePath, string \$remoteDest);\n";

// 20. debug
echo "20. [debug]: " . $mega->debug() . "\n";

// 21. deleteversions
echo "21. [deleteversions]: " . $mega->deleteversions() . "\n";

// 22. df
echo "22. [df]:\n" . $mega->df() . "\n";

// 23. du
echo "23. [du]:\n" . $mega->du() . "\n";

// 24. errorcode
echo "24. [errorcode](0): " . $mega->errorcode(0) . "\n";

// 25. exclude
echo "25. [exclude]: // Deprecated / Unused\n";

// 26. export
echo "26. [export]: " . $mega->export(null) . "\n";

// 27. exportList
echo "27. [exportList]:\n" . $mega->exportList() . "\n";

// 28. exportAdd
echo "28. [exportAdd]: // \$mega->exportAdd(string \$remotePath ...);\n";

// 29. exportRemove
echo "29. [exportRemove]: // \$mega->exportRemove(string \$remotePath);\n";

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
