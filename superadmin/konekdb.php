<?php
$server = "localhost";
$user = "root";
$password = "";
$mysqli = mysqli_connect($server, $user, $password, "E-pharm");

if (mysqli_connect_errno()) {
    echo "Gagal koneksi ke mysqli: " . mysqli_connect_error();
    exit();
}

?>