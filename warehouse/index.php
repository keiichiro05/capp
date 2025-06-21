<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['username'], $_SESSION['idpegawai'])) {
    header("Location: ../index.php?status=Please Login First");
    exit();
}

require_once('../konekdb.php');

$username   = $_SESSION['username'];
$idpegawai  = $_SESSION['idpegawai'];

// Check session timeout (30 menit)
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
} elseif (time() - $_SESSION['last_activity'] > 1800) {
    session_unset();
    session_destroy();
    header("Location: ../index.php?status=Session expired");
    exit();
}
$_SESSION['last_activity'] = time();

// Check user access to Adminwarehouse module
$stmt = $mysqli->prepare("
    SELECT COUNT(*) as user_count 
    FROM authorization 
    WHERE username = ? AND modul = 'Warehouse'
");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (empty($user['user_count']) || $user['user_count'] == 0) {
    header("Location: ../index.php?status=Access Denied");
    exit();
}

// Get employee data
$stmtPegawai = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$stmtPegawai->bind_param("i", $idpegawai);
$stmtPegawai->execute();
$resultPegawai = $stmtPegawai->get_result();
$pegawai = $resultPegawai->fetch_assoc();

// Check if 'tanggal' column exists in pemesanan
$column_check = $mysqli->query("SHOW COLUMNS FROM pemesanan LIKE 'tanggal'");
$date_column_exists = ($column_check && $column_check->num_rows > 0);

date_default_timezone_set('Asia/Jakarta');
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour < 18) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}

// Fetch dashboard data
$queryOrders = mysqli_query($mysqli, "SELECT COUNT(*) as totalOrders FROM warehouse");
$ordersData = mysqli_fetch_array($queryOrders);
$totalOrders = $ordersData['totalOrders'];

$queryPending = mysqli_query($mysqli, "SELECT COUNT(*) as totalPending FROM dariwarehouse WHERE status = 0");
$pendingData = mysqli_fetch_array($queryPending);
$totalPending = $pendingData['totalPending'];

$queryAcc = mysqli_query($mysqli, "SELECT COUNT(*) as totalAcc FROM dariwarehouse WHERE status = 1");
$accData = mysqli_fetch_array($queryAcc);
$totalAcc = $accData['totalAcc'];

$queryStock = mysqli_query($mysqli, "SELECT SUM(stok) as totalStock FROM warehouse");
$stockData = mysqli_fetch_array($queryStock);
$totalStock = $stockData['totalStock'];

$queryLowStockCount = mysqli_query($mysqli, "SELECT COUNT(*) as lowStockCount FROM warehouse WHERE stok < reorder_level");
$lowStockCountData = mysqli_fetch_array($queryLowStockCount);
$lowStockCount = $lowStockCountData['lowStockCount'];

// Fetch low stock items for alerts
$lowStockItems = [];
$queryLowStockItems = mysqli_query($mysqli, "SELECT nama, stok, reorder_level FROM warehouse WHERE stok < reorder_level ORDER BY stok ASC");
while ($row = mysqli_fetch_assoc($queryLowStockItems)) {
    $lowStockItems[] = $row;
}

// Data for Stock Overview Bar Chart
$queryBar = mysqli_query($mysqli, "SELECT nama, stok, reorder_level FROM warehouse ORDER BY stok ASC LIMIT 10");
$barLabels = [];
$barStockData = [];
$barReorderData = [];
while ($row = mysqli_fetch_assoc($queryBar)) {
    $barLabels[] = $row['nama'];
    $barStockData[] = $row['stok'];
    $barReorderData[] = $row['reorder_level'];
}

// Data for Stock Distribution by Category Pie Chart
$queryPie = mysqli_query($mysqli, "SELECT kategori, SUM(stok) as total FROM warehouse GROUP BY kategori");
$pieLabels = [];
$pieData = [];
$pieColors = [];
$colorPalette = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#8AC24A', '#607D8B', '#E91E63', '#9C27B0'];
$i = 0;
// Data for Recent Orders
$queryRecentOrders = mysqli_query($mysqli, "SELECT * FROM dariwarehouse ORDER BY date_created DESC LIMIT 5");
$recentOrders = [];
while ($row = mysqli_fetch_assoc($queryRecentOrders)) {
    $recentOrders[] = $row;
}
?>
<!DOCTYPE html>
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
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet" />
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #1e5799 0%,#207cca 51%,#2989d8 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .dashboard-header h1 small {
            color: rgba(0, 35, 73, 0.8);
        }
        .greeting-card {
            background: rgba(255,255,255,0.1);
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .small-box {

            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .small-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .small-box:hover .icon {
            font-size: 80px;
        }
        .box {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .box-header {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .alert-item {
            border-left: 4px solid #dd4b39;
            margin-bottom: 8px;

        }
        .alert-item:hover {
            transform: translateX(5px);
            background-color: #f9f9f9;
        }
        .stock-critical {
            background-color: #f8d7da;
            border-left-color: #dc3545;
        }
        .stock-warning {
            background-color: #fff3cd;
            border-left-color: #ffc107;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .progress-sm {
            height: 10px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body class="skin-blue">
        <header class="header">
            <a href="index.php" class="logo">Admin Warehouse</a>
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
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <i class="glyphicon glyphicon-user"></i>
                                <span><?php echo htmlspecialchars($username); ?> <i class="caret"></i></span>
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
                            <a href="stock.php">
                                <i class="fa fa-folder"></i> <span>Stock</span>
                            </a>
                        </li>
                        <li>
                            <a href="movement.php">
                                <i class="fa fa-exchange"></i> <span>Movement</span>
                            </a>
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
<!-- JS dependencies for Bootstrap and jQuery -->
<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script>
    // Enable sidebar toggle
    $(function () {
        $('[data-toggle="offcanvas"]').click(function (e) {
            e.preventDefault();
            $('.row-offcanvas').toggleClass('active');
        });
        // Dropdowns
        $('.dropdown-toggle').dropdown();
    });
</script>
            <aside class="right-side">
            <section class="content-header custom-dashboard-header">
                <div class="row">
                    <div class="col-xs-12">
                        <h1><?php echo htmlspecialchars($greeting); ?>, <?php echo htmlspecialchars($username); ?>! <small>Welcome to Warehouse Dashboard</small></h1>
                        <p class="header-date-time"><i class="fa fa-calendar"></i> <?php echo date('l, F j, Y'); ?> <span class="pull-right"><i class="fa fa-clock-o"></i> <span id="live-clock"><?php echo date('H:i:s'); ?></span></span></p>
                    </div>
                </div>
            </section>
            <section class="content">
                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-yellow animate__animated animate__fadeIn">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($totalOrders); ?></h3>
                                <p>Inventory Stock</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-bag"></i>
                            </div>
                            <a href="stock.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-blue animate__animated animate__fadeIn animate__delay-0-5s">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($totalPending); ?></h3>
                                <p>Pending Request</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-clock"></i>
                            </div>
                            <a href="new_request.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <?php
                    // Use dariwarehouse table if history_order does not exist
                    $queryAcceptedHistory = mysqli_query($mysqli, "SELECT COUNT(*) as acceptedHistory FROM inventory_movement WHERE movement_type = 'inbound'");
                    $acceptedHistoryData = mysqli_fetch_array($queryAcceptedHistory);
                    $acceptedHistory = $acceptedHistoryData['acceptedHistory'];
                    ?>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-green animate__animated animate__fadeIn animate__delay-0-5s">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($acceptedHistory); ?></h3>
                                <p>Inbound Stock</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-checkmark-round"></i>
                            </div>
                            <a href="products.php?tab=movements#movements" target="_blank" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <?php
                    // Use dariwarehouse table if history_order does not exist
                    $queryDeclinedHistory = mysqli_query($mysqli, "SELECT COUNT(*) as DeclinedHistory FROM pemesanan WHERE status = 2");
                    $DeclinedHistoryData = mysqli_fetch_array($queryDeclinedHistory);
                    $DeclinedHistory = isset($DeclinedHistoryData['DeclinedHistory']) ? $DeclinedHistoryData['DeclinedHistory'] : 0;
                    ?>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-red animate__animated animate__fadeIn animate__delay-0-5s">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($DeclinedHistory); ?></h3>
                                <p>Declined Request</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-warning"></i>
                            </div>
                            <a href="history_request.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-purple animate__animated animate__fadeIn animate__delay-1-5s">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($totalStock); ?></h3>
                                <p>Total Inventory Items</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-cube"></i>
                            </div>
                            <a href="inventory.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>