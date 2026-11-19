<?php
include('getconex.php');

// Puestos permitidos en el registro abierto. Los puestos de RH/administración NO se pueden elegir aquí:
// menu.php da acceso al Panel RH según el texto del puesto, así que esas cuentas se crean directo en SQL.
$PUESTOS_PERMITIDOS = array(
    'Operador de producción',
    'Técnico de mantenimiento',
    'Almacenista',
    'Supervisor de línea',
    'Seguridad industrial',
    'Personal de limpieza'
);

function volver($mensaje, $destino = 'registro.html') {
    echo '<script>alert(' . json_encode($mensaje, JSON_UNESCAPED_UNICODE) . '); location.href = ' . json_encode($destino) . ';</script>';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: registro.html");
    exit();
}

$usuario   = trim($_POST['usuario'] ?? '');
$nombres   = trim($_POST['nombres'] ?? '');
$apPat     = trim($_POST['apellidoPaterno'] ?? '');
$apMat     = trim($_POST['apellidoMaterno'] ?? '');
$fechaNac  = trim($_POST['fechaNac'] ?? '');
$correo    = trim($_POST['correo'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$puesto    = trim($_POST['puesto'] ?? '');
$password  = $_POST['password'] ?? '';
$confirmar = $_POST['confirmPassword'] ?? '';

// --- Validación de campos obligatorios y formato (del lado del servidor) ---
if ($usuario === '' || $nombres === '' || $apPat === '' || $apMat === '' ||
    $fechaNac === '' || $correo === '' || $puesto === '' || $password === '' || $confirmar === '') {
    volver("Todos los campos son obligatorios.");
}
if (!preg_match('/^[A-Za-z0-9_.]{4,30}$/', $usuario)) {
    volver("El usuario debe tener de 4 a 30 caracteres: letras, números, punto o guion bajo.");
}
if (mb_strlen($nombres) > 100 || mb_strlen($apPat) > 100 || mb_strlen($apMat) > 100) {
    volver("Nombre o apellidos demasiado largos.");
}
if (!preg_match('/^[\p{L}][\p{L} .\'-]*$/u', $nombres) ||
    !preg_match('/^[\p{L}][\p{L} .\'-]*$/u', $apPat) ||
    !preg_match('/^[\p{L}][\p{L} .\'-]*$/u', $apMat)) {
    volver("El nombre y los apellidos solo pueden contener letras.");
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 150) {
    volver("El correo electrónico no es válido.");
}
$fecha = DateTime::createFromFormat('Y-m-d', $fechaNac);
if (!$fecha || $fecha->format('Y-m-d') !== $fechaNac) {
    volver("La fecha de nacimiento no es válida.");
}
$hoy = new DateTime('today');
if ($fecha > $hoy || $fecha->diff($hoy)->y < 16 || $fecha->diff($hoy)->y > 100) {
    volver("La fecha de nacimiento no es válida para un trabajador (mínimo 16 años).");
}
if ($telefono !== '' && !preg_match('/^[0-9]{10}$/', $telefono)) {
    volver("El teléfono debe tener 10 dígitos (solo números).");
}
if (!in_array($puesto, $PUESTOS_PERMITIDOS, true)) {
    volver("Selecciona un puesto válido de la lista.");
}
if (mb_strlen($password) < 8 || mb_strlen($password) > 100) {
    volver("La contraseña debe tener entre 8 y 100 caracteres.");
}
if ($password !== $confirmar) {
    volver("Las contraseñas no coinciden.");
}

// --- Alta: empleado + usuario en una sola transacción (si falla algo, no queda nada a medias) ---
if (sqlsrv_begin_transaction($conectar) === false) {
    volver("Fallo en el sistema. Intente más tarde.");
}

$apellido = $apPat . ' ' . $apMat;

$sqlEmp    = "EXEC dbo.sp_RegistrarEmpleado @Nombre = ?, @Apellido = ?, @Correo = ?, @Puesto = ?, @FechaNacimiento = ?";
$paramsEmp = array($nombres, $apellido, $correo, $puesto, $fechaNac);
if ($telefono !== '') {
    $sqlEmp   .= ", @Telefono = ?";
    $paramsEmp[] = $telefono;
}
$stmt = sqlsrv_query($conectar, $sqlEmp, $paramsEmp);
if ($stmt === false) {
    $err = sqlsrv_errors();
    sqlsrv_rollback($conectar);
    $codigo = isset($err[0]['code']) ? (int)$err[0]['code'] : 0;
    if ($codigo === 50007) {
        volver("Ya existe un empleado registrado con ese correo electrónico.");
    }
    volver("No se pudo completar el registro. Intente más tarde.");
}
$fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($stmt);
$idEmpleado = $fila ? (int)$fila['IdEmpleadoCreado'] : 0;
if ($idEmpleado <= 0) {
    sqlsrv_rollback($conectar);
    volver("No se pudo completar el registro. Intente más tarde.");
}

// La contraseña se cifra dentro de sp_RegistrarUsuario (ENCRYPTBYPASSPHRASE); aquí nunca se guarda en claro.
$stmt2 = sqlsrv_query(
    $conectar,
    "{CALL dbo.sp_RegistrarUsuario(?, ?, ?)}",
    array($usuario, $password, $idEmpleado)
);
if ($stmt2 === false) {
    $err = sqlsrv_errors();
    sqlsrv_rollback($conectar);
    $codigo = isset($err[0]['code']) ? (int)$err[0]['code'] : 0;
    if ($codigo === 50002) {
        volver("Ese nombre de usuario ya está en uso. Elige otro.");
    }
    volver("No se pudo completar el registro. Intente más tarde.");
}
sqlsrv_free_stmt($stmt2);

if (sqlsrv_commit($conectar) === false) {
    sqlsrv_rollback($conectar);
    volver("No se pudo completar el registro. Intente más tarde.");
}

volver("Registro exitoso. Tu número de empleado es " . $idEmpleado . ". Ya puedes iniciar sesión.", "index.html");
