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
?>
<!DOCTYPE html>
<html>
<head>
    <title>Streamlit Integration</title>
</head>
<body>
    <iframe src="http://192.168.0.111:8501" width="100%" height="800px" style="border:none;"></iframe>
</body>
</html>
