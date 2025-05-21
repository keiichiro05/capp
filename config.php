<?php
// Updated code using mysqli_connect()
$host = "localhost";
$user = "root";
$pass = "";
$db = "e-pharm";
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}