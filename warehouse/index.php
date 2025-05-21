<!DOCTYPE html>
<?php 
include('../konekdb.php');
session_start();

$username = $_SESSION['username'] ?? null;
$idpegawai = $_SESSION['idpegawai'] ?? null;

if(!isset($_SESSION['username'])){
	header("location:../index.php?status=please login first");
	exit();
	}
if (isset($_SESSION['idpegawai'])) {
    $idpegawai = $_SESSION['idpegawai'];
} else {
    header("location:../index.php?status=please login first");
    exit();
}
$cekuser = mysqli_query($mysqli, "SELECT count(username) as jmluser FROM authorization WHERE username = '$username' AND modul = 'Warehouse'");
$user = mysqli_fetch_assoc($cekuser);

$getpegawai = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$idpegawai'");
$pegawai = mysqli_fetch_array($getpegawai);

if ($user['jmluser'] == "0") {
    header("location:../index.php");
    exit;
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Warehouse Management System</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
        <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />
    </head>
    <body class="skin-blue">
        <header class="header">
            <a href="index.php" class="logo">PSN</a>
            <nav class="navbar navbar-static-top" role="navigation">
                <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <div class="navbar-right">
                    <ul class="nav navbar-nav">
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="glyphicon glyphicon-user"></i>
                                <span><?php echo htmlspecialchars($username); ?><i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="user-header bg-light-blue">
                                    <img src="img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image" />
                                    <p>
                                        <?php 
                                        echo htmlspecialchars($pegawai['Nama']) . " - " . htmlspecialchars($pegawai['Jabatan']) . " " . htmlspecialchars($pegawai['Departemen']); ?>
                                        <small>Member since <?php echo htmlspecialchars($pegawai['Tanggal_Masuk']); ?></small>
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
            <aside class="left-side sidebar-offcanvas">
                <section class="sidebar">
                    <div class="user-panel">
                        <div class="pull-left image">
                            <img src="img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image" />
                        </div>
                        <div class="pull-left info">
                            <p>Hello, <?php echo htmlspecialchars($username); ?></p>
                            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                        </div>
                    </div>
                    <ul class="sidebar-menu">
                        <li class="active">
                            <a href="index.php">
                                <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="kategori.php">
                                <i class="fa fa-list-alt"></i> <span>Categories</span>
                            </a>
                        </li>
                        <li>
                            <a href="order.php">
                                <i class="fa fa-th"></i> <span>Orders</span>
                            </a>
                        </li>
                        <li>
                            <a href="history_order.php">
                                <i class="fa fa-history"></i> <span>Order History</span>
                            </a>
                        </li>
                        <li>
                            <a href="cuti.php">
                                <i class="fa fa-suitcase"></i> <span>Leave</span>
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
                    <h1>Warehouse Management <small>Blitar Branch</small></h1>
                </section>
                <section class="content">
                    <div class="form-container">
                        <h3>Stock Management</h3>
                        <form method="post">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Item Name</label>
                                        <input type="text" class="form-control" placeholder="Enter item name..." name="nama">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Category</label>
                                        <input type="text" class="form-control" placeholder="Enter category..." name="kategori">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" class="form-control" placeholder="Enter quantity..." name="stok">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-container">
                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $nama = mysqli_real_escape_string($mysqli, $_POST['nama'] ?? '');
                            $stok = mysqli_real_escape_string($mysqli, $_POST['stok'] ?? '');
                            $kategori = mysqli_real_escape_string($mysqli, $_POST['kategori'] ?? '');
                            $edit_id = mysqli_real_escape_string($mysqli, $_POST['edit_id'] ?? '');
                            $delete_id = mysqli_real_escape_string($mysqli, $_POST['delete_id'] ?? '');

                            if (!empty($delete_id)) {
                                $stmt = $mysqli->prepare("DELETE FROM warehouse WHERE id_barang=?");
                                $stmt->bind_param("i", $delete_id);
                                if ($stmt->execute()) {
                                    echo "<div class='alert alert-success'>Item deleted successfully!</div>";
                                } else {
                                    echo "<div class='alert alert-danger'>Error deleting item: " . htmlspecialchars($stmt->error) . "</div>";
                                }
                                $stmt->close();
                            } elseif (!empty($edit_id) && !empty($stok)) {
                                $stmt = $mysqli->prepare("UPDATE warehouse SET Stok=? WHERE id_barang=?");
                                $stmt->bind_param("ii", $stok, $edit_id);
                                if ($stmt->execute()) {
                                    echo "<div class='alert alert-success'>Stock updated successfully!</div>";
                                } else {
                                    echo "<div class='alert alert-danger'>Error updating stock: " . htmlspecialchars($stmt->error) . "</div>";
                                }
                                $stmt->close();
                            } elseif (!empty($nama) && !empty($stok)) {
                                $stmt = $mysqli->prepare("SELECT COUNT(*) FROM warehouse WHERE Nama=?");
                                $stmt->bind_param("s", $nama);
                                $stmt->execute();
                                $stmt->bind_result($count);
                                $stmt->fetch();
                                $stmt->close();

                                if ($count > 0) {
                                    $stmt = $mysqli->prepare("UPDATE warehouse SET Stok=?, Kategori=? WHERE Nama=?");
                                    $stmt->bind_param("iss", $stok, $kategori, $nama);
                                    if ($stmt->execute()) {
                                        echo "<div class='alert alert-success'>Item updated successfully!</div>";
                                    } else {
                                        echo "<div class='alert alert-danger'>Error updating item: " . htmlspecialchars($stmt->error) . "</div>";
                                    }
                                    $stmt->close();
                                } else {
                                    $stmt = $mysqli->prepare("INSERT INTO warehouse (Nama, Stok, Kategori) VALUES (?, ?, ?)");
                                    $stmt->bind_param("sis", $nama, $stok, $kategori);
                                    if ($stmt->execute()) {
                                        echo "<div class='alert alert-success'>New item added successfully!</div>";
                                    } else {
                                        echo "<div class='alert alert-danger'>Error adding item: " . htmlspecialchars($stmt->error) . "</div>";
                                    }
                                    $stmt->close();
                                }
                            }
                        }

                        $sql = "SELECT * FROM warehouse";
                        $hasil = mysqli_query($mysqli, $sql);

                        if ($hasil) {
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-bordered table-striped">';
                            echo '<thead><tr><th>ID</th><th>Item Name</th><th>Stock</th><th>Category</th><th>Actions</th></tr></thead>';
                            echo '<tbody>';
                            while ($baris = mysqli_fetch_assoc($hasil)) {
                                echo "<tr>
                                    <td>" . htmlspecialchars($baris['id_barang']) . "</td>
                                    <td>" . htmlspecialchars($baris['Nama']) . "</td>
                                    <td>" . htmlspecialchars($baris['Stok']) . "</td>
                                    <td>" . htmlspecialchars($baris['Kategori']) . "</td>
                                    <td>
                                        <form method='post' class='form-inline'>
                                            <input type='hidden' name='edit_id' value='" . htmlspecialchars($baris['id_barang']) . "'>
                                            <div class='input-group'>
                                                <input type='number' name='stok' class='form-control input-sm' placeholder='New Qty' required style='width: 80px;'>
                                                <span class='input-group-btn'>
                                                    <button type='submit' class='btn btn-action btn-edit'>Update</button>
                                                </span>
                                            </div>
                                        </form>
                                        <form method='post' style='display:inline-block; margin-left:5px;'>
                                            <input type='hidden' name='delete_id' value='" . htmlspecialchars($baris['id_barang']) . "'>
                                            <button type='submit' class='btn btn-action btn-delete'>Delete</button>
                                        </form>
                                    </td>
                                </tr>";
                            }
                            echo "</tbody>";
                            echo "</table>";
                            echo "</div>";
                            mysqli_free_result($hasil);
                        } else {
                            echo "<div class='alert alert-danger'>Error fetching data: " . mysqli_error($mysqli) . "</div>";
                        }
                        ?>
                    </div>
                </section>
            </aside>
        </div>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <script src="../js/AdminLTE/app.js" type="text/javascript"></script>
    </body>
</html>