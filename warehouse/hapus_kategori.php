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
$id = $_GET['id'];
if ($stmt = $mysqli->prepare("DELETE FROM kategori WHERE id = ?")) { // Replace 'correct_table_name' with the actual table name
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$stmt->close();
}

header("location:product.php");
?>