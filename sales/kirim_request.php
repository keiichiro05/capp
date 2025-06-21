<?php
include "konekdb.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

$iduser = $_SESSION['idpegawai'];
$usersql = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$iduser'");
$hasiluser = mysqli_fetch_array($usersql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_barang = $_POST['id_barang'];
    $nama = $_POST['nama'];
    $quantity = $_POST['quantity'];
    $reason = $_POST['reason'];

    // Insert ke warehouse (request produk)
    $insert_sql = "INSERT INTO warehouse (id_pegawai, id_barang, nama, quantity, reason) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($mysqli, $insert_sql);

    // Binding parameter dengan tipe: i = integer, s = string
    // id_pegawai (int), id_barang (int), nama (string), quantity (int), reason (string)
    mysqli_stmt_bind_param($stmt, "iisis", $iduser, $id_barang, $nama, $quantity, $reason);

    if (mysqli_stmt_execute($stmt)) {
        // Ambil id request terakhir jika diperlukan
        $last_request_id = mysqli_insert_id($mysqli);

        // Insert notifikasi ke warehouse_alert
        $alert_sql = "INSERT INTO warehouse_alert (id_barang, quantity, status) VALUES (?, ?, 'pending')";
        $stmt_alert = mysqli_prepare($mysqli, $alert_sql);
        mysqli_stmt_bind_param($stmt_alert, "ii", $id_barang, $quantity);
        mysqli_stmt_execute($stmt_alert);

        // Redirect dengan pesan sukses
        header("Location: kirim_request.php?message=success");
        exit();
    } else {
        $error = "Error: " . mysqli_error($mysqli);
    }
} else {
    // Kalau bukan POST, redirect ke form
    header("Location: product_request.php");
    exit();
}
?>
