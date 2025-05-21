<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'], $_SESSION['idpegawai'])) {
    header("Location: ../index.php?status=Please Login First");
    exit();
}

require_once('../konekdb.php');

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Cek apakah user memiliki hak akses ke modul Adminwarehouse (menggunakan prepared statement)
$stmt = $mysqli->prepare("SELECT COUNT(username) as jmluser FROM authorization WHERE username = ? AND modul = 'Adminwarehouse'");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['jmluser'] == "0") {
    header("Location: ../index.php?status=Access Declined");
    exit();
}
?>
<?php
$to = $_POST['nama'];
$from = $_POST['username'];
$pesan = $_POST['message'];

include '../config.php';

$nama1 = mysqli_fetch_array(mysqli_query($conn, "SELECT id_pegawai from pegawai WHERE nama='$to'"));
$nama2 = mysqli_fetch_array(mysqli_query($conn, "SELECT id_pegawai from authorization WHERE username='$from'"));
//echo $nama1[0].$nama2[0].$pesan;


if (isset($_POST['send'])) {
     $sql = "INSERT INTO pesan VALUES ('',$nama2[0],$nama1[0],'$pesan',NOW(),0,0)";
 }
else{
     $sql = "INSERT INTO pesan VALUES ('',$nama2[0],$nama1[0],'$pesan',NOW(),1,0)";
 }

$hasil = mysqli_query($conn, $sql);


if($hasil){
	print "<script>location = 'mailbox.php'; </script>";
}else{
	echo "Data gagal diubah.";
}



?>