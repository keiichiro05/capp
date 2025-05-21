<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'], $_SESSION['idpegawai'])) {
    header("Location: ../index.php?status=Silakan login dulu");
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
    header("Location: ../index.php?status=Akses ditolak");
    exit();
}

// Ambil data pegawai
$stmtPegawai = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$stmtPegawai->bind_param("i", $idpegawai);
$stmtPegawai->execute();
$resultPegawai = $stmtPegawai->get_result();
$pegawai = $resultPegawai->fetch_assoc();
?>
<!DOCTYPE html>
    <html>
    <head>
    <meta charset="UTF-8">
    <title>Admin Warehouse Dashboard</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />
</head>
<body class="skin-blue">
    <header class="header">
        <a href="#" class="logo">Admin Warehouse</a>
            <div class="navbar-right">
                <ul class="nav navbar-nav">
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="glyphicon glyphicon-user"></i>
                            <span><?php echo $username; ?> <i class="caret"></i></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-header bg-light-blue">
                                <img src="../img/<?php echo $pegawai['foto']; ?>" class="img-circle" alt="User Image" />
                                <p>
                                    <?php echo $pegawai['Nama'] . " - " . $pegawai['Jabatan']; ?>
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
        <aside class="left-side sidebar-offcanvas">
            <section class="sidebar">
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="../img/<?php echo $pegawai['foto']; ?>" class="img-circle" alt="User Image" />
                    </div>
                    <div class="pull-left info">
                        <p>Hello, <?php echo $username; ?></p>
                        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                    </div>
                </div>
                                    <!-- sidebar menu: : style can be found in sidebar.less -->
                                    <ul class="sidebar-menu">
                    <li class="active">
                            <a href="dashboard.php">
                                <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="index.php">
                                <i class="fa fa-list"></i> <span>List Order</span>
                            </a>
                        </li>
                        <li>
                            <a href="daftarACC.php">
                                <i class="fa fa-th"></i> <span>Order History</span>
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
                <!-- /.sidebar -->
            </section>
        </aside>
        <aside class="right-side">
            <section class="content-header">
                <h1>Dashboard <small>Admin Warehouse</small></h1>
            </section>
            <section class="content">
                <div class="row">
                    <?php
                    $queryOrders = mysqli_query($mysqli, "SELECT COUNT(*) as totalOrders FROM dariwarehouse");
                    $ordersData = mysqli_fetch_array($queryOrders);
                    $totalOrders = $ordersData['totalOrders'];
                    ?>  
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-yellow">
                            <div class="inner">
                                <h3><?php echo $totalOrders; ?></h3>
                                <p>Total Orders</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-shopping-cart"></i>
                            </div>
                            <a href="index.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <?php
                    $queryPending = mysqli_query($mysqli, "SELECT COUNT(*) as totalPending FROM dariwarehouse WHERE status = 0");
                    $pendingData = mysqli_fetch_array($queryPending);
                    $totalPending = $pendingData['totalPending'];

                    $queryAcc = mysqli_query($mysqli, "SELECT COUNT(*) as totalAcc FROM dariwarehouse WHERE status = 1");
                    $accData = mysqli_fetch_array($queryAcc);
                    $totalAcc = $accData['totalAcc'];
                    
                    $queryStock = mysqli_query($mysqli, "SELECT SUM(stok) as totalStock FROM warehouse");
                    $stockData = mysqli_fetch_array($queryStock);
                    $totalStock = $stockData['totalStock'];
                    ?>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-blue">
                            <div class="inner">
                                <h3><?php echo $totalPending; ?></h3>
                                <p>Pending Orders</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-clock-o"></i>
                            </div>
                            <a href="index.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3><?php echo $totalAcc; ?></h3>
                                <p>Approved Orders</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-check"></i>
                            </div>
                            <a href="approved.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-purple">
                            <div class="inner">
                            <h3><?php echo $totalStock; ?></h3>
                            <p>Inventory Stock</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-home"></i>
                            </div>
                            <a href="daftarACC.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-red">
                            <div class="inner">
                                <?php
                                $queryLowStockCount = mysqli_query($mysqli, "SELECT COUNT(*) as lowStockCount FROM warehouse WHERE stok < reorder_level");
                                $lowStockCountData = mysqli_fetch_array($queryLowStockCount);
                                $lowStockCount = $lowStockCountData['lowStockCount'];
                                ?>
                                <h3><?php echo $lowStockCount; ?></h3>
                                <p>Low Stock</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-bar-chart"></i>
                            </div>
                            <a href="index  .php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="box box-primary">
                            <div class="box-header">
                                <h3 class="box-title">Warehouse Stock Distribution</h3>
                            </div>
                            <div class="box-body">
                                <canvas id="stockPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        const ctx = document.getElementById('stockPieChart').getContext('2d');
                        <?php
                        $queryPie = mysqli_query($mysqli, "SELECT nama, stok, kategori FROM warehouse");
                        $labels = [];
                        $data = [];
                        while ($row = mysqli_fetch_assoc($queryPie)) {
                            $labels[] = $row['nama'] . " (" . $row['kategori'] . ")";
                            $data[] = $row['stok'];
                        }
                        ?>
                        const stockPieChart = new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: <?php echo json_encode($labels); ?>,
                                datasets: [{
                                    data: <?php echo json_encode($data); ?>,
                                    backgroundColor: [
                                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                                    ]
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    }
                                }
                            }
                        });
                    });
                </script>

                <div class="col-lg-6">
                    <div class="box box-warning">
                        <div class="box-header">
                            <h3 class="box-title">Low Stock Alerts</h3>
                        </div>
                        <div class="box-body">
                            <ul class="list-group">
                                <?php
                                $queryLowStock = mysqli_query($mysqli, "SELECT nama, stok, reorder_level FROM warehouse WHERE stok < reorder_level");
                                while ($lowStock = mysqli_fetch_assoc($queryLowStock)) {
                                    echo '<li class="list-group-item list-group-item-danger">';
                                    echo '<strong>' . $lowStock['nama'] . '</strong>: Stock (' . $lowStock['stok'] . ') is below reorder level (' . $lowStock['reorder_level'] . ')';
                                    echo '</li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
                
            </section>
        