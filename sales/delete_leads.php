<?php
include "konekdb.php";
session_start();

// Pastikan koneksi database ($mysqli) sudah terdefinisi
if (!isset($mysqli) || !$mysqli instanceof mysqli) {
    die("Koneksi database tidak tersedia.");
}

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

// Pastikan ada parameter id di URL
if (!isset($_GET['id'])) {
    header("Location: leads.php?error=missing_id");
    exit();
}

$lead_id = intval($_GET['id']);

// Hapus data dari tabel opportunity yang terkait dengan lead ini terlebih dahulu
$delete_opportunity = "DELETE FROM leads WHERE lead_id = ?";
$stmt1 = mysqli_prepare($mysqli, $delete_opportunity);
if ($stmt1) {
    mysqli_stmt_bind_param($stmt1, 'i', $lead_id);
    mysqli_stmt_execute($stmt1);
    mysqli_stmt_close($stmt1);
} else {
    echo "Gagal menyiapkan query untuk hapus opportunity: " . mysqli_error($mysqli);
    exit();
}

// Setelah itu, hapus dari tabel leads
$delete_lead = "DELETE FROM leads WHERE lead_id = ?";
$stmt2 = mysqli_prepare($mysqli, $delete_lead);

if ($stmt2) {
    mysqli_stmt_bind_param($stmt2, 'i', $lead_id);
    if (mysqli_stmt_execute($stmt2)) {
        // Redirect ke halaman leads dengan pesan sukses
        header("Location: leads.php?message=deleted");
        exit();
    } else {
        echo "Gagal menghapus lead: " . mysqli_error($mysqli);
    }
    mysqli_stmt_close($stmt2);
} else {
    echo "Gagal menyiapkan query untuk hapus lead: " . mysqli_error($mysqli);
}
?>
