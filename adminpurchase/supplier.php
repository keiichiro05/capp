<!DOCTYPE html>
<?php include('../konekdb.php');
session_start();
$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];
$cekuser = mysqli_query($mysqli, "SELECT count(username) as jmluser FROM authorization WHERE username = '$username' AND modul = 'Warehouse'");
$user = mysqli_fetch_array($cekuser);
$getpegawai = mysqli_query($mysqli, "SELECT * FROM pegawai where id_pegawai='$idpegawai'");
$pegawai = mysqli_fetch_array($getpegawai);
if ($user['jmluser'] == "0") {
    header("location:../index.php");
}; 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nama_kategori'])) {
    $nama_kategori = $_POST['nama_kategori'];
    
    // Periksa apakah nama kategori sudah ada
    $cek_kategori = mysqli_query($mysqli, "SELECT * FROM kategori WHERE nama_kategori = '$nama_kategori'");
    if (mysqli_num_rows($cek_kategori) > 0) {
        echo "<div class='alert alert-danger'>Category Available!</div>";
    } else {
        // Jika tidak ada, tambahkan kategori
        mysqli_query($mysqli, "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kategori')") or die(mysqli_error($mysqli));
        echo "<div class='alert alert-success'>Add New Category!</div>";
    }
}
?>

<html>
    <head>
        <meta charset="UTF-8">
        <title>Kategori - Warehouse</title>
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    </head>
    <body class="skin-blue">
        <header class="header">
            <a href="index.php" class="logo">E-pharm</a>
            <nav class="navbar navbar-static-top" role="navigation">
                <div class="navbar-right">
                    <ul class="nav navbar-nav">
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="glyphicon glyphicon-user"></i>
                                <span><?php echo $username; ?><i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="user-header bg-light-blue">
                                    <img src="img/<?php echo $pegawai['foto']; ?>" class="img-circle" alt="User Image" />
                                    <p>
                                        <?php echo $pegawai['Nama'] . " - " . $pegawai['Jabatan'] . " " . $pegawai['Departemen']; ?>
                                        <small>Member since <?php echo $pegawai['Tanggal_Masuk']; ?></small>
                                    </p>
                                </li>
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="profil.php" class="btn btn-default btn-flat">Profile</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="logout.php" class="btn btn-default btn-flat">Sign out</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="left-side sidebar-offcanvas">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- Sidebar user panel -->
                    <div class="user-panel">
                        <div class="pull-left image">
                          <img src="img/<?php echo $pegawai['foto'];?>" class="img-circle" alt="User Image" />
                               </div>
                        <div class="pull-left info">
                            <p>Hello, <?php echo $username;?></p>

                            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                        </div>
                    </div>
                    <!-- /.search form -->
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                     <ul class="sidebar-menu">
                        <li>
                            <a href="index.php">
                                <i class="fa fa-dashboard"></i> <span>Statistical</span>
                            </a>
                        </li>
                        <li>
                            <a href="product.php">
                                <i class="fa fa-list-alt"></i> <span>Category</span>
                            </a>
                        </li>
                        <li >
                            <a href="new_request.php">
                                <i class="fa fa-th"></i> <span>Order</span>
                            </a>
                        </li>
						<li class="active">
                            <a href="cuti.php">
                                <i class="fa fa-suitcase"></i> <span>Cuti</span>
                            </a>
                        </li>
                         <li>
                            <a href="mailbox.php">
                                <i class="fa fa-comments"></i> <span>Mailbox</span>
								
                            </a>
                        </li>
                    </ul>
                </section>
            </aside>
            <aside class="right-side">
                <section class="content-header">
                    <h1>Kategori</h1>
                </section>
                <section class="content">
                    <h3>Tambah Kategori</h3>
                    <form method="post">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" class="form-control" placeholder="Nama Kategori" name="nama_kategori" required>
                            </div>
                            <div class="col-md-2">
                                <input type="submit" class="btn btn-primary" value="Tambah">
                            </div>
                        </div>
                    </form>
                    <!-- Removed redundant form submission logic -->
                    <h3>Daftar Kategori</h3>
                    <table class="table table-striped table-bordered">
                        <tr>
                            <th>ID</th>
                            <th>Nama Kategori</th>
                            <th>Aksi</th>
                        </tr>
                        <?php
                        $sql = "SELECT * FROM kategori";
                        $hasil = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
                        while ($baris = mysqli_fetch_array($hasil)) {
                            echo "<tr>
                                <td>{$baris['id']}</td>
                                <td>{$baris['nama_kategori']}</td>
                                <td>
                                    <a href='edit_product.php?id={$baris['id']}' class='btn btn-warning'>Edit</a>
                                    <a href='hapus_product.php?id={$baris['id']}' class='btn btn-danger' onclick='return confirm(\"Yakin ingin menghapus kategori ini?\")'>Hapus</a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </table>
                </section>
            </aside>
        </div>
        <script src="../js/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>
    </body>
</html>
