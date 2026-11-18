<?php
    include("getconex.php");
    
    date_default_timezone_set('America/Monterrey');

    session_start();
    $id_empleado = $_SESSION['logged_user'];
    $hora = date('H:i:s');

    
    $upd_sql = "{CALL dbo.sp_RegistrarSalida( ?, ?)}";
    $params = array($id_empleado,$hora);
    $stmt= sqlsrv_query( $conectar, $upd_sql, $params);

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
?>
