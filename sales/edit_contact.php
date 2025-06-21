<?php
include "konekdb.php";
session_start();

// Cek jika user belum login
if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

// Cek apakah parameter ID tersedia
if (!isset($_GET['id'])) {
    echo "Contact ID is missing.";
    exit();
}

$id = intval($_GET['id']); // Hindari SQL injection

// Ambil data contact berdasarkan ID
$result = mysqli_query($mysqli, "SELECT * FROM contact WHERE id = $id");
if (mysqli_num_rows($result) == 0) {
    echo "Contact not found.";
    exit();
}

$contact = mysqli_fetch_assoc($result);

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = mysqli_real_escape_string($mysqli, $_POST['first_name']);
    $account = mysqli_real_escape_string($mysqli, $_POST['account']);
    $email = mysqli_real_escape_string($mysqli, $_POST['email']);
    $job_title = mysqli_real_escape_string($mysqli, $_POST['job_title']);
    $department = mysqli_real_escape_string($mysqli, $_POST['department']);
    $phone = mysqli_real_escape_string($mysqli, $_POST['phone']);

    $update = mysqli_query($mysqli, "UPDATE contact SET
        first_name = '$first_name',
        account = '$account',
        email = '$email',
        job_title = '$job_title',
        department = '$department',
        phone = '$phone'
        WHERE id = $id");

    if ($update) {
        header("Location: contact.php?msg=updated");
        exit();
    } else {
        echo "Failed to update contact.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Contact</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="container">
    <h2>Edit Contact</h2>
    <form action="" method="POST">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($contact['first_name']); ?>" required>
        </div>
        <div class="form-group">
            <label>Account</label>
            <input type="text" name="account" class="form-control" value="<?= htmlspecialchars($contact['account']); ?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($contact['email']); ?>" required>
        </div>
        <div class="form-group">
            <label>Job Title</label>
            <input type="text" name="job_title" class="form-control" value="<?= htmlspecialchars($contact['job_title']); ?>">
        </div>
        <div class="form-group">
            <label>Department</label>
            <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($contact['department']); ?>">
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($contact['phone']); ?>">
        </div>
        <button type="submit" class="btn btn-success">Update Contact</button>
        <a href="contact.php" class="btn btn-secondary">Cancel</a>
    </form>
</body>
</html>
