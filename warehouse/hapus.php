<?php
include "konekdb.php";
session_start();

if(!isset($_SESSION['username'])){
	header("location:../index.php?status=please login first");
	exit();
	}
if (isset($_SESSION['idpegawai'])) {
    $idpegawai = $_SESSION['idpegawai'];
} else {
    header("location:../index.php?status=please login first");
    exit();
}
$no=$_GET['no'];
mysqli_query($mysqli,"DELETE FROM dariwarehouse WHERE No='$no'");
header("location:new_request.php");
?>