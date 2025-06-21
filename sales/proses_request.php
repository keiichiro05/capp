<?php
include "konekdb.php";
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

// Ambil ID user
$iduser = $_SESSION['idpegawai'];

// Periksa apakah metode pengiriman POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $id_barang = $_POST['id_barang'];
    $nama = $_POST['nama'];
    $quantity = $_POST['quantity'];
    $reason = $_POST['reason'];

    // Validasi sederhana
    if (!empty($id_barang) && !empty($nama) && !empty($quantity) && !empty($reason)) {
        // Simpan ke tabel warehouse
        $sql = "INSERT INTO warehouse (id_barang, nama, quantity, reason) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($mysqli, $sql);
        mysqli_stmt_bind_param($stmt, "isis", $id_barang, $nama, $quantity, $reason);

        if (mysqli_stmt_execute($stmt)) {
            // Redirect ke halaman kirim_request.php dengan pesan sukses
            header("Location: kirim_request.php?message=success");
            exit();
        } else {
            echo "Failed to insert data: " . mysqli_error($mysqli);
        }
    } else {
        echo "Please fill in all fields.";
    }
} else {
    // Jika bukan POST, redirect ke form
    header("Location: kirim_request.php");
    exit();
}
?>
