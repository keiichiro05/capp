<?php
session_start();

if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
} elseif (time() - $_SESSION['last_activity'] > 1800) {
    session_unset();
    session_destroy();
    header("Location: ../index.php?status=Session expired");
    exit();
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php?status=Please login first.");
    exit();
}
?>
