<?php
session_start();
$idpegawai=$_SESSION['idpegawai'];
if(!isset($_SESSION['username'])){
	header("location:../index.php?status=please login first");
	exit();
	}
?>
<?php
include('../konekdb.php');
session_start();

// Check authorization
$username = $_SESSION['username'];
$cekuser = mysqli_query($mysqli, "SELECT count(username) as jmluser FROM authorization WHERE username = '$username' AND modul = 'Adminwarehouse'");
$user = mysqli_fetch_array($cekuser);
if($user['jmluser'] == "0") {
    header("location:../index.php");
    exit();
}

// Validasi koneksi database
if (!$mysqli || $mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// Validasi parameter 'No'
if (isset($_GET['No']) && is_numeric($_GET['No'])) {
    $No = (int)$_GET['No'];

    // Mulai transaksi untuk memastikan konsistensi data
    if (!$mysqli->begin_transaction()) {
        die("Failed to start transaction: " . $mysqli->error);
    }

    try {
        // Update status menjadi '2' di tabel pemesanan
        $stmt = $mysqli->prepare("UPDATE pemesanan SET status = '2' WHERE No = ?");
        if ($stmt) {
            $stmt->bind_param("i", $No);
            $stmt->execute();
            $stmt->close();
        } else {
            throw new Exception("Error preparing update query: " . $mysqli->error);
        }

        // Commit transaksi
        $mysqli->commit();

        // Redirect setelah selesai
        header("Location: daftarACC.php");
        exit;
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        $mysqli->rollback();
        die("Transaction failed: " . $e->getMessage());
    }
} else {
    // Jika parameter 'No' tidak valid
    header("Location: daftarACC.php?error=invalid_parameter");
    exit;
}
?>
