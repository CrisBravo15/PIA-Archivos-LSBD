<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tabla de horarios | Control de asistencia</title>
  <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
  <header class="topbar">
    <a class="brand brand-header" href="menu.html">
      <span class="brand-symbol">CA</span>
      <span class="brand-name">CONTROL DE ASISTENCIA</span>
    </a>
    <div class="topbar-right">
      <span class="session-label"><span class="status-indicator"></span> Sistema de asistencia</span>
      <a class="header-link" href="menu.php">Volver al menú</a>
    </div>
  </header>

  <main class="page-container">
    <div class="page-title">
      <span class="overline">REGISTROS DEL PERSONAL</span>
      <h1>Consultar horarios</h1>
      <p>Consulta de entradas y salidas de los trabajadores.</p>
    </div>

    <section class="panel table-panel" aria-label="Tabla de horarios">
      <div class="table-heading">
        <h2>Registro de asistencia</h2>
        <p>Resumen de horarios registrados.</p>
      </div>

      <div class="table-scroll">
        <table class="attendance-table">
          <thead>
            <tr>
              <th scope="col">IdEmpleado</th>
              <th scope="col">Fecha</th>
              <th scope="col">Hora de entrada</th>
              <th scope="col">Hora de salida</th>
            </tr>
          </thead>
          <tbody>
            <?php
            include('getconex.php');
            session_start(); 
            $sql = "SELECT * FROM [Asistencias] WHERE IdEmpleado = '{$_SESSION['logged_user']}'";
            $stmt = sqlsrv_query($conectar, $sql);

            while ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $fila['IdEmpleado'] . "</td>";
            echo "<td>" . $fila['Fecha']->format('Y-m-d') . "</td>";
            echo "<td>" . $fila['HoraEntrada']->format('H:i') . "</td>";
            echo "<td>" . $fila['HoraSalida']?->format('H:i') . "</td>";
            echo "</tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <footer class="footer">Sistema de control de asistencia · Interfaz de usuario</footer>
</body>
</html>