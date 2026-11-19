<?php
session_start();
include('getconex.php');

function volver($mensaje) {
    echo '<script>alert(' . json_encode($mensaje, JSON_UNESCAPED_UNICODE) . '); location.href = "index.html";</script>';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.html");
    exit();
}

$usuario    = trim($_POST['usuario'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';

if ($usuario === '' || $contrasena === '') {
    volver("Escribe tu usuario y tu contraseña.");
}
if (mb_strlen($usuario) > 100 || mb_strlen($contrasena) > 100) {
    volver("Usuario o contraseña incorrectos.");
}

// Login con procedimiento almacenado (Req #19): compara descifrando en memoria, sin tocar el dato cifrado.
$stmt = sqlsrv_query($conectar, "{CALL dbo.sp_ValidarLogin(?, ?)}", array($usuario, $contrasena));
if ($stmt === false) {
    volver("Fallo en el sistema. Intente más tarde.");
}
$fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($stmt);

if (!$fila || empty($fila['IdEmpleado'])) {
    volver("Usuario o contraseña incorrectos.");
}

// sp_ValidarLogin no devuelve el puesto: se obtiene con sp_ConsultarEmpleados (sin modificar la base de datos).
$puesto = '';
$stmtEmp = sqlsrv_query($conectar, "{CALL dbo.sp_ConsultarEmpleados(?)}", array(1));
if ($stmtEmp !== false) {
    while ($emp = sqlsrv_fetch_array($stmtEmp, SQLSRV_FETCH_ASSOC)) {
        if ((int)$emp['IdEmpleado'] === (int)$fila['IdEmpleado']) {
            $puesto = $emp['Puesto'] ?? '';
            break;
        }
    }
    sqlsrv_free_stmt($stmtEmp);
}

// Sesión con el contrato que usa el resto del sistema:
// logged_user = IdEmpleado (menu.php lo convierte a int), puesto = texto del puesto (menu.php decide si muestra Panel RH).
session_regenerate_id(true);
$_SESSION['valid_user']  = true;
$_SESSION['logged_user'] = (int)$fila['IdEmpleado'];
$_SESSION['usuario']     = $fila['NombreUsuario'];
$_SESSION['nombre']      = trim($fila['Nombre'] . ' ' . $fila['Apellido']);
$_SESSION['puesto']      = $puesto;

header("Location: menu.php");
exit();
