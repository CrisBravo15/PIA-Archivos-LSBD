<?php
include('getconex.php');
session_start();
date_default_timezone_set('America/Monterrey');

// 1. Verificar sesión iniciada
if (!isset($_SESSION['logged_user'])) {
    header("Location: index.html");
    exit();
}

// 2. Procesar actualización usando el Procedimiento Almacenado sp_ModificarAsistencia (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_asistencia'])) {
    $id_asistencia = (int)$_POST['id_asistencia'];
    $fecha_base = $_POST['fecha_base'];
    $hora_entrada = $_POST['hora_entrada'];
    $hora_salida = $_POST['hora_salida'];

    // Formato DATETIME2(7): 'YYYY-MM-DD HH:MM:SS'
    $nuevaEntrada = $fecha_base . ' ' . $hora_entrada;
    $nuevaSalida = !empty($hora_salida) ? ($fecha_base . ' ' . $hora_salida) : null;

    // Llamada parametrizada al SP obligatorio (Req #12 y #15)
    $sql_upd = "{CALL dbo.sp_ModificarAsistencia(?, ?, ?)}";
    $params_upd = array($id_asistencia, $nuevaEntrada, $nuevaSalida);
    $stmt_upd = sqlsrv_query($conectar, $sql_upd, $params_upd);

    if ($stmt_upd) {
        echo '<script>alert("Registro actualizado con éxito."); location.href = "rh_panel.php";</script>';
        exit();
    } else {
        // Capturar mensaje de error de SQL Server (ej. si HoraSalida <= HoraEntrada)
        $errors = sqlsrv_errors();
        $msg_error = isset($errors[0]['message']) ? addslashes($errors[0]['message']) : "Error al actualizar el registro.";
        echo "<script>alert('Error: $msg_error');</script>";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel RH - Modificar horarios | Control de asistencia</title>
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
      <span class="session-label"><span class="status-indicator"></span> Administrador RH</span>
      <a class="header-link" href="menu.php">Volver al menú</a>
    </div>
  </header>

  <main class="page-container">
    <div class="page-title">
      <span class="overline">RECURSOS HUMANOS</span>
      <h1>Modificar horarios del personal</h1>
      <p>Edita directamente las horas de entrada o salida y presiona guardar en la fila correspondiente.</p>
    </div>

    <section class="panel table-panel" aria-label="Tabla editable de horarios">
      <div class="table-heading">
        <h2>Registro general de asistencia</h2>
        <p>Todos los empleados registrados en el sistema.</p>
      </div>

      <div class="table-scroll">
        <table class="attendance-table">
          <thead>
            <tr>
              <th scope="col">IdEmpleado</th>
              <th scope="col">Nombre</th>
              <th scope="col">Puesto</th>
              <th scope="col">Fecha</th>
              <th scope="col">Hora de entrada</th>
              <th scope="col">Hora de salida</th>
              <th scope="col">Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Llamada al SP que devuelve todas las asistencias de todos los empleados
            $sql = "{CALL dbo.sp_ConsultarTodasAsistencias}";
            $stmt = sqlsrv_query($conectar, $sql);

            if ($stmt) {
                while ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $id_asistencia = (int)$fila['IdAsistencia'];
                    $fecha_str = $fila['Fecha'] ? $fila['Fecha']->format('Y-m-d') : '';
                    $entrada_val = $fila['HoraEntrada'] ? $fila['HoraEntrada']->format('H:i:s') : '';
                    $salida_val = $fila['HoraSalida'] ? $fila['HoraSalida']->format('H:i:s') : '';
                    $nombre_completo = htmlspecialchars($fila['Nombre'] . ' ' . $fila['Apellido']);
                    $puesto = htmlspecialchars($fila['Puesto'] ?? 'Sin puesto');

                    echo "<tr>";
                    echo "<form method='post' action='rh_panel.php'>";
                    
                    echo "<input type='hidden' name='id_asistencia' value='" . $id_asistencia . "'>";
                    echo "<input type='hidden' name='fecha_base' value='" . $fecha_str . "'>";

                    echo "<td>" . (int)$fila['IdEmpleado'] . "</td>";
                    echo "<td>" . $nombre_completo . "</td>";
                    echo "<td>" . $puesto . "</td>";
                    echo "<td>" . $fecha_str . "</td>";
                    
                    echo "<td><input type='time' step='1' name='hora_entrada' value='" . $entrada_val . "' required></td>";
                    echo "<td><input type='time' step='1' name='hora_salida' value='" . $salida_val . "'></td>";
                    
                    echo "<td><button type='submit' class='button button-primary'>Guardar</button></td>";
                    
                    echo "</form>";
                    echo "</tr>";
                }
            }
            ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <footer class="footer">Sistema de control de asistencia · Módulo de Recursos Humanos</footer>
</body>
</html>