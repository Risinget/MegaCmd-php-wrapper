<?php
require_once 'MegaCmd.php';


// changed
// Crear instancia
$mega = new MegaCmd();

// NOTA: MEGAcmd necesita que el servidor esté corriendo en segundo plano.
// Si no está corriendo, puedes intentar iniciarlo antes o asegurarte de que esté activo.

header('Content-Type: text/plain');

echo "--- Estado de MEGAcmd ---\n";
echo $mega->exec('version');
echo "\n";

echo "--- ¿Quién soy? ---\n";
echo $mega->whoami() . "\n";

/* 
// Ejemplo de inicio de sesión:
echo "--- Iniciando sesión ---\n";
$resultado = $mega->login('tu_correo@gmail.com', 'tu_password');
echo $resultado . "\n";
*/

/*
// Ejemplo de subir un archivo:
echo "--- Subiendo archivo ---\n";
$mega->put('VIDEO.mp4', '/MiCarpetaEnMega/');
*/

//  Listar archivos:
echo "--- Listado de archivos ---\n";
$archivos = $mega->ls();
print_r($archivos);


echo "\n--- Fin del script ---\n";


echo $mega->login('','');
echo $mega->whoami() . "\n";