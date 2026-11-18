<?php
include('getconex.php');
session_start();
date_default_timezone_set('America/Monterrey');

// Verificar que exista una sesión iniciada
if (!isset($_SESSION['logged_user'])) {
    header("Location: index.html");
    exit();
}

$usuario_sesion = $_SESSION['logged_user'];

// Obtener IdEmpleado y Puesto del usuario logueado (soporta si guardan IdEmpleado o NombreUsuario en sesión)
if (is_numeric($usuario_sesion)) {
    $sql_emp = "SELECT IdEmpleado, Nombre, Apellido, Puesto FROM [Empleados] WHERE IdEmpleado = ?";
    $stmt_emp = sqlsrv_query($conectar, $sql_emp, array((int)$usuario_sesion));
} else {
    $sql_emp = "SELECT E.IdEmpleado, E.Nombre, E.Apellido, E.Puesto 
                FROM [Empleados] E
                INNER JOIN [UsuariosAplicacion] U ON E.IdEmpleado = U.IdEmpleado
                WHERE U.NombreUsuario = ?";
    $stmt_emp = sqlsrv_query($conectar, $sql_emp, array($usuario_sesion));
}

$empleado = $stmt_emp ? sqlsrv_fetch_array($stmt_emp, SQLSRV_FETCH_ASSOC) : null;
$id_empleado = $empleado ? (int)$empleado['IdEmpleado'] : 0;
$puesto_txt = $empleado ? strtolower(trim($empleado['Puesto'])) : '';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Menú principal del sistema de control de asistencia.">
  <title>Menú principal | Control de asistencia</title>
  <link rel="icon" href="img/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
  <header class="topbar">
    <a class="brand brand-header" href="menu.php">
      <span class="brand-symbol">CA</span>
      <span class="brand-name">CONTROL DE ASISTENCIA</span>
    </a>
    <div class="topbar-right">
      <a class="header-link" href="index.html">Cerrar sesión</a>
    </div>
  </header>

  <main class="page-container">
    <div class="dashboard-heading">
      <div class="page-title">
        <span class="overline">PANEL DE CONTROL</span>
        <h1>Menú principal</h1>
        <p>Selecciona el módulo al que deseas ingresar.</p>
      </div>
      <div class="date-box">
        <span class="date-label">FECHA</span>
        <strong id="currentDate"><?php echo date('Y-m-d H:i:s'); ?></strong>
      </div>
    </div>

    <section class="module-grid" aria-label="Módulos del sistema">
      <?php
      $fecha_hoy = date('Y-m-d');
      $sql_asist = "SELECT * FROM [Asistencias] WHERE IdEmpleado = ? AND Fecha = ?";
      $stmt_asist = sqlsrv_query($conectar, $sql_asist, array($id_empleado, $fecha_hoy));
      $fila = $stmt_asist ? sqlsrv_fetch_array($stmt_asist, SQLSRV_FETCH_ASSOC) : null;

      if ($fila == NULL) {
          // No ha registrado entrada ni salida hoy
          echo '<a class="module-card" href="form_result_entrada.php">';
          echo '<span class="module-number">01</span>';
          echo '<span class="module-icon" aria-hidden="true">↓</span>';
          echo '<h2>Registrar entrada</h2>';
          echo '<p>Registrar la hora de ingreso del trabajador.</p>';
          echo '<span class="module-action">Acceder <span aria-hidden="true">→</span></span>';
          echo '</a>';
      } elseif ($fila['HoraSalida'] == NULL) {
          // Ya registró entrada, falta registrar salida hoy
          echo '<a class="module-card" href="form_result_salida.php">';
          echo '<span class="module-number">02</span>';
          echo '<span class="module-icon" aria-hidden="true">↑</span>';
          echo '<h2>Registrar salida</h2>';
          echo '<p>Registrar la hora de salida del trabajador.</p>';
          echo '<span class="module-action">Acceder <span aria-hidden="true">→</span></span>';
          echo '</a>';
      } else {
          // Ya registró tanto entrada como salida hoy (Req #11)
          echo '<div class="module-card" style="opacity: 0.75; cursor: default;">';
          echo '<span class="module-number">✓</span>';
          echo '<span class="module-icon" aria-hidden="true">✔</span>';
          echo '<h2>Jornada completada</h2>';
          echo '<p>Ya registraste tu hora de entrada y salida el día de hoy.</p>';
          echo '<span class="module-action">Disponible mañana</span>';
          echo '</div>';
      }
      ?>

      <a class="module-card" href="tabla.php" data-module="consulta">
        <span class="module-number">03</span>
        <span class="module-icon" aria-hidden="true">≡</span>
        <h2>Consultar registros</h2>
        <p>Revisar el historial de entradas y salidas.</p>
        <span class="module-action">Acceder <span aria-hidden="true">→</span></span>
      </a>

      <?php
      // Bloque de RH: visible únicamente para administradores de Recursos Humanos (Req #12)
      if (strpos($puesto_txt, 'rh') !== false || strpos($puesto_txt, 'recursos humanos') !== false || strpos($puesto_txt, 'admin') !== false) {
          echo '<a class="module-card" href="rh_panel.php">';
          echo '<span class="module-number">04</span>';
          echo '<span class="module-icon" aria-hidden="true">⚙</span>';
          echo '<h2>Panel de RH</h2>';
          echo '<p>Consultar y modificar asistencias de todos los trabajadores.</p>';
          echo '<span class="module-action">Acceder <span aria-hidden="true">→</span></span>';
          echo '</a>';
      }
      ?>
    </section>
  </main>
  <footer class="footer">Sistema de control de asistencia · Interfaz de usuario</footer>
</body>
</html>
