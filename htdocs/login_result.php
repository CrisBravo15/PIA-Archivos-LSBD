<?php
	$usuario = $_POST['usuario'];

	session_start();
	$_SESSION['valid_user'] = true;
	$_SESSION['logged_user'] = $usuario;
	header("Location: menu.php");

?>