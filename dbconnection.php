<?php
	
	require("config.php");

	//Crear conexión
	$sqlconnection = new mysqli($servername, $username, $password,$dbname);

	//Comprobar la conexión
	if ($sqlconnection->connect_error) {
    	die("Connection failed: " . $sqlconnection->connect_error);
	}
	
?>