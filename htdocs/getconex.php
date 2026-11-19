<?php
// Las advertencias informativas de SQL Server (cambio de BD o idioma) no se tratan como errores fatales.
sqlsrv_configure('WarningsReturnAsErrors', 0);

$serverName = 'MikelJonguitud';

$connectionInfo = array(
    'Database'     => 'BD_AsistenciaEmpleados',
    'UID'          => 'LSBDUSER',
    'PWD'          => 'C0ntr4s3n4*#',
    'Encrypt'      => 'no',
    'CharacterSet' => 'UTF-8'   // acentos y ñ correctos al guardar y leer
);

$conectar = sqlsrv_connect($serverName, $connectionInfo);

// Importante: este archivo NO imprime nada si la conexión funciona.
// Cualquier texto aquí rompe header("Location: ...") y session_start() en las páginas que lo incluyen.
if ($conectar === false) {
    error_log(print_r(sqlsrv_errors(), true));
    die('Error de conexión con la base de datos.');
}
