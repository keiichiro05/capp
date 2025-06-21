<?php 
include('../konekdb.php');
session_start();
$username = $_SESSION['username'] ?? null;
$idpegawai = $_SESSION['idpegawai'] ?? null;

// Set active submenu for sidebar highlighting
$active_submenu = isset($_GET['submenu']) ? $_GET['submenu'] : 'movement';

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

// Get filter values
$cabang_filter = isset($_GET['cabang']) ? mysqli_real_escape_string($mysqli, $_GET['cabang']) : '';
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 100;
$offset = ($current_page - 1) * $limit;
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Warehouse Inventory Management</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
        <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" /> 
        <style>
            .box-primary {
                border-top-color: #3c8dbc;
            }
            .label-success {
                background-color: #00a65a;
            }
            .label-danger {
                background-color: #dd4b39;
            }
            .table-responsive {
                overflow-x: auto;
            }
            .input-group-btn .btn {
                padding: 5px 10px;
            }
            .pagination > li > a {
                padding: 5px 10px;
            }
            .text-success {
                color: #00a65a;
                font-weight: bold;
            }
            .text-danger {
                color: #dd4b39;
                font-weight: bold;
            }
            .treeview-menu > li > a {
                padding: 5px 5px 5px 25px;
            }
            .sidebar-menu > li.active > a {
                border-left-color: #3c8dbc;
                font-weight: bold;
            }
        </style>
    </head>
    <body class="skin-blue">
        <header class="header">
            <a href="index.html" class="logo">Admin Warehouse</a>
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
                        <li>
                            <a href="index.php">
                                <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="stock.php">
                                <i class="fa fa-folder"></i> <span>Stock</span>
                            </a>
                        </li>
                 <li class="treeview active">
                    <a href="#">
                        <i class="fa fa-exchange"></i> <span>Movement</span>
                        <i class=""></i>
                    </a>
                        <ul class="treeview-menu" style="<?php echo in_array($active_submenu, ['movement','movement-history','inbound','outbound']) ? 'display: block;' : ''; ?>">
                        <li class="<?php echo $active_submenu == 'movement' ? 'active' : ''; ?>">
                            <a href="movement.php?submenu=movement"><i class="fa fa-th"></i>All Movement</a>

                        <li class="<?php echo $active_submenu == 'movement-history' ? 'active' : ''; ?>">
                            <a href="movement_history.php?submenu=movement-history"><i class="fa fa-undo"></i>Movement History</a>
                        </li>
                        <li class="<?php echo $active_submenu == 'outbound' ? 'active' : ''; ?>">
                            <a href="movement_outbound.php?submenu=outbound"><i class="fa fa-sign-out "></i> Outbound</a>
                        </li>
                        <li class="<?php echo $active_submenu == 'outbound' ? 'active' : ''; ?>">
                            <a href="movement_outbound?submenu=unit"><i class="fa fa-sign-in "></i> Inbound</a>
                        </li>
                    </ul>
        </li>
                        <li>
                            <a href="product.php">
                                <i class="fa fa-list-alt"></i> <span>Products</span>
                            </a>
                        </li>
                        <li>
                            <a href="new_request.php">
                                <i class="fa fa-plus-square"></i> <span>New Request</span>
                            </a>
                        </li>
                        <li>
                            <a href="history_request.php">
                                <i class="fa fa-archive"></i> <span>Request History</span>
                            </a>
                        </li>
                         <li>
                            <a href="sales_request.php">
                                <i class="fa fa-retweet"></i> <span>Sales Request</span>
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
                    <h1>
                        Inventory Movements
                        <small>Track and manage inventory movements</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Inventory Movements</li>
                    </ol>
                </section>

                <section class="content">
                    <div class="row">
                        <div class="col-md-12">
                            <?php if(isset($_SESSION['success'])): ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title">All Inventory Movements</h3>
                                    <div class="box-tools pull-right">
                                        <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <!-- Filter Form -->
                                    <div class="row">
                                        <div class="col-md-12">
                                            <form method="get" action="movement.php" class="form-inline">
                                                <div class="form-group">
                                                    <label for="cabang">Warehouse: </label>
                                                    <select name="cabang" class="form-control input-sm">
                                                        <option value="">All</option>
                                                        <?php
                                                        $warehouse_query = mysqli_query($mysqli, "SELECT nama FROM list_warehouse ORDER BY nama ASC");
                                                        while ($wh = mysqli_fetch_assoc($warehouse_query)): ?>
                                                            <option value="<?php echo htmlspecialchars($wh['nama']); ?>" <?php echo ($cabang_filter == $wh['nama'] ? 'selected' : ''); ?>>
                                                                <?php echo htmlspecialchars($wh['nama']); ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-sm" style="margin-left:10px;">
                                                    <i class="fa fa-filter"></i> Filter
                                                </button>
                                                <a href="movement.php" class="btn btn-default btn-sm" style="margin-left:10px;">
                                                    <i class="fa fa-times"></i> Clear
                                                </a>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive" style="margin-top:20px;">
                                        <table class="table table-bordered table-striped" id="movementsTable">
                                            <thead>
                                                <tr>
                                                    <th>Product ID</th>
                                                    <th>Item Name</th>
                                                    <th>Current Stock</th>
                                                    <th>Category</th>
                                                    <th>Unit</th>
                                                    <th>Reorder Level</th>
                                                    <th>Warehouse</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sql = "SELECT * FROM warehouse WHERE 1=1";
                                                if ($cabang_filter != '') {
                                                    $sql .= " AND cabang = '$cabang_filter'";
                                                }
                                                $sql .= " ORDER BY Code DESC";
                                                
                                                $hasil = $mysqli->query($sql);
                                                if ($hasil && $hasil->num_rows > 0) {
                                                    while ($baris = $hasil->fetch_assoc()) {
                                                        echo "<tr>
                                                            <td>" . htmlspecialchars($baris['Code']) . "</td>
                                                            <td>" . htmlspecialchars($baris['Nama']) . "</td>
                                                            <td>" . htmlspecialchars($baris['Stok']) . "</td>
                                                            <td>" . htmlspecialchars($baris['Kategori']) . "</td>
                                                            <td>" . htmlspecialchars($baris['Satuan']) . "</td>
                                                            <td>" . htmlspecialchars($baris['reorder_level']) . "</td>
                                                            <td>" . htmlspecialchars($baris['cabang']) . "</td>
                                                            <td>
                                                                <div class='btn-group'>
                                                                    <a href='movement_inbound.php?product_id=" . htmlspecialchars($baris['Code']) . "' class='btn btn-success btn-xs' title='Inbound'>
                                                                        <i class='fa fa-plus'></i>
                                                                    </a>
                                                                    <a href='movement_outbound.php?product_id=" . htmlspecialchars($baris['Code']) . "' class='btn btn-danger btn-xs' title='Outbound'>
                                                                        <i class='fa fa-minus'></i>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='8' class='text-center'>No products found</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>

        <!-- JavaScript Libraries -->
        <script src="../js/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <script src="../js/AdminLTE/app.js" type="text/javascript"></script>
    </body>
</html> 