<?php
include('../konekdb.php');
session_start();

if(!isset($_SESSION['username'])) {
    header("location:../index.php?status=please login first");
    exit();
}

if (isset($_GET['id']) && isset($_GET['reason'])) {
    $id = mysqli_real_escape_string($mysqli, $_GET['id']);
    $reason = mysqli_real_escape_string($mysqli, $_GET['reason']);
    $username = $_SESSION['username'];
    $current_time = date('Y-m-d H:i:s');
    
    mysqli_query($mysqli, "UPDATE sales_request SET 
        status = 'rejected',
        rejected_by = '$username',
        rejected_at = '$current_time',
        rejection_reason = '$reason'
        WHERE id_barang='$id'");
    
    header("Location: sales_request.php?status=rejected");
    exit();
} else {
    header("Location: sales_request.php?status=reject_failed");
    exit();
}
?>