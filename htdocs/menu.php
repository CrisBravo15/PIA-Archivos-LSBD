<?php
include('getconex.php');
session_start();
date_default_timezone_set('America/Monterrey');

if (!isset($_SESSION['logged_user'])) {
    header("Location: index.html");
    exit();
}

$id_empleado = (int)$_SESSION['logged_user'];
$puesto_txt = isset($_SESSION['puesto']) ? strtolower(trim($_SESSION['puesto'])) : '';
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
      // Consultar asistencias del empleado usando el SP permitido
      $fecha_hoy = date('Y-m-d');
      $sql_asist = "{CALL dbo.sp_ConsultarAsistenciasEmpleado(?)}";
      $stmt_asist = sqlsrv_query($conectar, $sql_asist, array($id_empleado));
      
      // Buscar si el registro más reciente corresponde al día de hoy
      $registro_hoy = null;
      if ($stmt_asist) {
          $primera_fila = sqlsrv_fetch_array($stmt_asist, SQLSRV_FETCH_ASSOC);
          if ($primera_fila && $primera_fila['Fecha']->format('Y-m-d') === $fecha_hoy) {
              $registro_hoy = $primera_fila;
          }
      }

      if ($registro_hoy == NULL) {
          echo '<a class="module-card" href="form_result_entrada.php">';
          echo '<span class="module-number">01</span>';
          echo '<span class="module-icon" aria-hidden="true">↓</span>';
          echo '<h2>Registrar entrada</h2>';
          echo '<p>Registrar la hora de ingreso del trabajador.</p>';
          echo '<span class="module-action">Acceder <span aria-hidden="true">→</span></span>';
          echo '</a>';
      } elseif ($registro_hoy['HoraSalida'] == NULL) {
          echo '<a class="module-card" href="form_result_salida.php">';
          echo '<span class="module-number">02</span>';
          echo '<span class="module-icon" aria-hidden="true">↑</span>';
          echo '<h2>Registrar salida</h2>';
          echo '<p>Registrar la hora de salida del trabajador.</p>';
          echo '<span class="module-action">Acceder <span aria-hidden="true">→</span></span>';
          echo '</a>';
      } else {
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
