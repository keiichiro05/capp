<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php?status=Silakan login dulu");
    exit();
}
?>
<?php
$server = "localhost";
$user = "root";
$password = "";
$mysqli = new mysqli($server, $user, $password, "E-pharm");

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}

?>