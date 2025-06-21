<?php
include "konekdb.php";
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

// Pastikan ada parameter ID yang dikirim melalui GET
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Lakukan query delete
    $query = "DELETE FROM contact WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirect kembali ke halaman contact.php dengan pesan sukses
        header("Location: contact.php?msg=deleted");
    } else {
        // Redirect dengan pesan error jika gagal
        header("Location: contact.php?msg=error");
    }

    $stmt->close();
} else {
    // Jika tidak ada ID, redirect ke halaman contact.php
    header("Location: contact.php");
}

$mysqli->close();
?>
