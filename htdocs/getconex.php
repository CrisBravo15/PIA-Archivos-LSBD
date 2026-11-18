<?php
ini_set('sqlsrv.WarningsReturnAsErrors', 0);
$serverName = 'LTBK-3806';

$connectionInfo = array('Database'=>'BD_AsistenciaEmpleados','UID'=>'LSBDUSER','PWD'=>'C0ntr4s3n4*#');

$conectar = sqlsrv_connect($serverName, $connectionInfo);

if($conectar){

    /*Todo bien*/

} else{

echo 'Hubo un error al conectarse a la base de datos. A continuación, los errores: <br>';

die(print_r(sqlsrv_errors(),true));

}

?>