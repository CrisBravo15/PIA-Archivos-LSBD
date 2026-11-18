<?php
    include("getconex.php");

    date_default_timezone_set('America/Monterrey');

    session_start();
    $matricula = $_SESSION['logged_user'];
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');

    $ins_sql = "INSERT INTO Asistencias (IdEmpleado,Fecha, HoraEntrada) VALUES ('$matricula','$fecha', '$hora')";
    $stmt= sqlsrv_query( $conectar, $ins_sql);

    if ( $stmt ){    
	    	echo '<script type="text/javascript">';
            echo ' alert("Movimiento realizado con exito.")';
            echo '</script>';
            echo "<script>setTimeout(\"location.href = 'menu.php';\",1);</script>";
	} else{   
            echo '<script type="text/javascript">';
            echo ' alert("Fallo en el sistema. Intente mas tarde.")';
            echo '</script>';
            echo "<script>setTimeout(\"location.href = 'menu.php';\",1);</script>";
	}
    sqlsrv_free_stmt($stmt);
?>