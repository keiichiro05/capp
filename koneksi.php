<?php
$namahost = "localhost";
$username = "root";
$password = ""; //password mysqli anda
$database = "e-pharm"; //database anda
$koneksi=mysqli_connect($namahost,$username,$password) or die("Failed");
mysqli_select_db($database) or die("Database not exist");
?>