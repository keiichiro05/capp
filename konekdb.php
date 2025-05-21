<?php
$server = "localhost";
$user = "root";
$password = "";
$mysqli = new mysqli($server, $user, $password, "e-pharm");

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}

?>
