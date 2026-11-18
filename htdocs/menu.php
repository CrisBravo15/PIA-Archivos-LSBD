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
    <a class="brand brand-header" href="menu.html">
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
        <strong id="currentDate"><?php date_default_timezone_set('America/Monterrey'); echo date('Y-m-d H:i:s')?></strong>
      </div>
    </div>
    <section class="module-grid" aria-label="Módulos del sistema">
      <?php
          include('getconex.php');
          session_start();
          date_default_timezone_set('America/Monterrey');

          $sql = "SELECT * FROM [Asistencias] WHERE IdEmpleado = '{$_SESSION['logged_user']}' AND Fecha = '". date('Y-m-d') ."'";
          $stmt = sqlsrv_query($conectar, $sql);

          $fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

          if($fila == NULL){
            echo '<a class="module-card" href="form_result_entrada.php">';
            echo '<span class="module-number">01</span>';
            echo '<span class="module-icon" aria-hidden="true">↓</span>';
            echo '<h2>Registrar entrada</h2>';
            echo '<p>Registrar la hora de ingreso del trabajador.</p>';
            echo '<span class="module-action">Acceder <span aria-hidden="true">→</span></span>';
            echo '</a>';
          } elseif ($fila['HoraSalida'] == NULL){
            echo '<a class="module-card" href="form_result_salida.php">';
            echo '<span class="module-number">02</span>';
            echo '<span class="module-icon" aria-hidden="true">↑</span>';
            echo '<h2>Registrar salida</h2>';
            echo '<p>Registrar la hora de salida del trabajador.</p>';
            echo '<span class="module-action">Acceder <span aria-hidden="true">→</span></span>';
            echo '</a>';
          }
        ?>
        <a class="module-card" href="tabla.php" data-module="consulta">
          <span class="module-number">03</span>
          <span class="module-icon" aria-hidden="true">≡</span>
          <h2>Consultar registros</h2>
          <p>Revisar el historial de entradas y salidas.</p>
          <span class="module-action">Acceder <span aria-hidden="true">→</span></span>
        </a>
    </section>
  </main>
  <footer class="footer">Sistema de control de asistencia · Interfaz de usuario</footer>
</body>
</html>
