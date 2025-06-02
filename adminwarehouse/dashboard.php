<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'], $_SESSION['idpegawai'])) {
    header("Location: ../index.php?status=Silakan login dulu");
    exit();
}

require_once('../konekdb.php');

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Check if user has access to Adminwarehouse module
$stmt = $mysqli->prepare("SELECT COUNT(username) as jmluser FROM authorization WHERE username = ? AND modul = 'Adminwarehouse'");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['jmluser'] == "0") {
    header("Location: ../index.php?status=Akses ditolak");
    exit();
}

// Get employee data
$stmtPegawai = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$stmtPegawai->bind_param("i", $idpegawai);
$stmtPegawai->execute();
$resultPegawai = $stmtPegawai->get_result();
$pegawai = $resultPegawai->fetch_assoc();

// Time-based greeting
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
$queryOrders = mysqli_query($mysqli, "SELECT COUNT(*) as totalOrders FROM pemesanan");
$ordersData = mysqli_fetch_array($queryOrders);
$totalOrders = $ordersData['totalOrders'];

$queryPending = mysqli_query($mysqli, "SELECT COUNT(*) as totalPending FROM pemesanan WHERE status = 0");
$pendingData = mysqli_fetch_array($queryPending);
$totalPending = $pendingData['totalPending'];

$queryAcc = mysqli_query($mysqli, "SELECT COUNT(*) as totalAcc FROM pemesanan WHERE status = 1");
$accData = mysqli_fetch_array($queryAcc);
$totalAcc = $accData['totalAcc'];

$queryDecline = mysqli_query($mysqli, "SELECT COUNT(*) as totalDecline FROM pemesanan WHERE status = 2");
$DeclineData = mysqli_fetch_array($queryDecline);
$totalDecline = $DeclineData['totalDecline'];


$queryStock = mysqli_query($mysqli, "SELECT SUM(stok) as totalStock FROM warehouse");
$stockData = mysqli_fetch_array($queryStock);
$totalStock = $stockData['totalStock'];

$queryLowStockCount = mysqli_query($mysqli, "SELECT COUNT(*) as lowStockCount FROM warehouse WHERE stok < reorder_level");
$lowStockCountData = mysqli_fetch_array($queryLowStockCount);
$lowStockCount = $lowStockCountData['lowStockCount'];

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
$colorPalette = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#f8f9fc', '#5a5c69', '#3a3b45', '#2e59d9'];
$i = 0;
while ($row = mysqli_fetch_assoc($queryPie)) {
    $pieLabels[] = $row['kategori'];
    $pieData[] = $row['total'];
    $pieColors[] = $colorPalette[$i % count($colorPalette)];
    $i++;
}

// Data for Recent Orders
$queryRecentOrders = mysqli_query($mysqli, "SELECT * FROM dariwarehouse ORDER BY date_created DESC LIMIT 5");
$recentOrders = [];
while ($row = mysqli_fetch_assoc($queryRecentOrders)) {
    $recentOrders[] = $row;
}

// Data for Inbound Orders
$queryInboundOrders = mysqli_query($mysqli, "SELECT * FROM inbound_log ORDER BY tanggal DESC LIMIT 5");
$inboundOrders = [];
while ($row = mysqli_fetch_assoc($queryInboundOrders)) {
    $inboundOrders[] = $row;
}

// Data for Outbound Orders
$queryOutboundOrders = mysqli_query($mysqli, "SELECT * FROM outbound_log ORDER BY tanggal DESC LIMIT 5");
$outboundOrders = [];
while ($row = mysqli_fetch_assoc($queryOutboundOrders)) {
    $outboundOrders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Manager Dashboard</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --dark: #5a5c69;
            --light: #f8f9fc;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fc;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary) 0%, #224abe 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .dashboard-header h1 {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .dashboard-header h1 small {
            color: rgba(255,255,255,0.7);
            font-size: 16px;
            display: block;
            margin-top: 5px;
        }
        
        .header-date-time {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .header-date-time i {
            margin-right: 5px;
        }
        
        .small-box {
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
        }
        
        .small-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .small-box .inner {
            padding: 15px;
        }
        
        .small-box h3 {
            font-size: 28px;
            font-weight: 600;
            margin: 0 0 5px 0;
        }
        
        .small-box p {
            font-size: 15px;
            margin-bottom: 0;
        }
        
        .small-box .icon {
            font-size: 70px;
            position: absolute;
            right: 15px;
            top: 15px;
            transition: all 0.3s;
            opacity: 0.2;
        }
        
        .small-box:hover .icon {
            opacity: 0.3;
            transform: scale(1.1);
        }
        
        .small-box-footer {
            background: rgba(0,0,0,0.05);
            color: rgba(255,255,255,0.8);
            display: block;
            padding: 8px 0;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .small-box-footer:hover {
            background: rgba(0,0,0,0.1);
            color: white;
        }
        
        .box {
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            border: none;
            margin-bottom: 20px;
        }
        
        .box-header {
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            padding: 15px 20px;
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .box-header h3 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            display: inline-block;
        }
        
        .box-header .box-tools {
            position: absolute;
            right: 20px;
            top: 15px;
        }
        
        .box-body {
            padding: 20px;
            background-color: white;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .alert-item {
            border-left: 4px solid var(--danger);
            margin-bottom: 10px;
            border-radius: 6px;
            transition: all 0.3s;
            padding: 10px 15px;
        }
        
        .alert-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stock-critical {
            background-color: #f8d7da;
            border-left-color: var(--danger);
        }
        
        .stock-warning {
            background-color: #fff3cd;
            border-left-color: var(--warning);
        }
        
        .products-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .products-list .item {
            padding: 10px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .products-list .item:last-child {
            border-bottom: none;
        }
        
        .product-title {
            font-weight: 500;
            display: block;
            margin-bottom: 5px;
        }
        
        .product-description {
            font-size: 13px;
            color: #6c757d;
        }
        
        .sidebar-menu > li > a {
            border-radius: 5px;
            margin: 5px 10px;
        }
        
        .sidebar-menu > li.active > a {
            background-color: var(--primary);
            color: white;
        }
        
        .sidebar-menu > li > a:hover {
            background-color: rgba(78, 115, 223, 0.1);
        }
        
        .user-panel {
            padding: 15px;
        }
        
        .skin-blue .sidebar-menu > li:hover > a, 
        .skin-blue .sidebar-menu > li.active > a {
            color: white;
            background: var(--primary);
            border-left-color: var(--primary);
        }
        
        .info-box {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 15px;
        }
        
        .info-box-icon {
            border-radius: 8px 0 0 8px;
            display: block;
            float: left;
            height: 90px;
            width: 90px;
            text-align: center;
            font-size: 45px;
            line-height: 90px;
            background: rgba(0,0,0,0.2);
        }
        
        .info-box-content {
            padding: 15px;
            margin-left: 90px;
        }
        
        .info-box-text {
            display: block;
            font-size: 16px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .info-box-number {
            display: block;
            font-size: 22px;
            font-weight: 600;
        }
        
        .progress-description {
            display: block;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .bg-primary { background-color: var(--primary) !important; }
        .bg-success { background-color: var(--success) !important; }
        .bg-info { background-color: var(--info) !important; }
        .bg-warning { background-color: var(--warning) !important; }
        .bg-danger { background-color: var(--danger) !important; }
        .bg-purple { background-color: #6f42c1 !important; }
        
        .text-primary { color: var(--primary) !important; }
        .text-success { color: var(--success) !important; }
        .text-info { color: var(--info) !important; }
        .text-warning { color: var(--warning) !important; }
        .text-danger { color: var(--danger) !important; }
        
        .label-primary { background-color: var(--primary) !important; }
        .label-success { background-color: var(--success) !important; }
        .label-info { background-color: var(--info) !important; }
        .label-warning { background-color: var(--warning) !important; }
        .label-danger { background-color: var(--danger) !important; }
    </style>
</head>
<body class="skin-blue">
    <header class="header">
        <a href="#" class="logo">Warehouse Manager</a>
        <div class="navbar-right">
            <ul class="nav navbar-nav">
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="glyphicon glyphicon-user"></i>
                        <span><?php echo htmlspecialchars($username); ?> <i class="caret"></i></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header bg-light-blue">
                            <img src="../img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image" />
                            <p>
                                <?php echo htmlspecialchars($pegawai['Nama'] . " - " . $pegawai['Jabatan']); ?>
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
    </header>
    <div class="wrapper row-offcanvas row-offcanvas-left">
        <aside class="left-side sidebar-offcanvas">
            <section class="sidebar">
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="../img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image" />
                    </div>
                    <div class="pull-left info">
                        <p>Hello, <?php echo htmlspecialchars($username); ?></p>
                        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                    </div>
                </div>
                <ul class="sidebar-menu">
                    <li>
                        <a href="streamlit.php">
                            <i class="fa fa-signal"></i> <span>Analytics</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="dashboard.php">
                            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="list_request.php">
                            <i class="fa fa-list"></i> <span>List Request</span>
                        </a>
                    </li>
                    <li>
                        <a href="daftarACC.php">
                            <i class="fa fa-history"></i> <span>Request History</span>
                        </a>
                    </li>
                    <li>
                        <a href="stock.php">
                            <i class="fa fa-cubes"></i> <span>Inventory</span>
                        </a>
                    </li>
                    <li>
                        <a href="mailbox.php">
                            <i class="fa fa-envelope"></i> <span>Mailbox</span>
                        </a>
                    </li>
                </ul>
            </section>
        </aside>

        <aside class="right-side">
            <section class="content-header custom-dashboard-header">
                <div class="row">
                    <div class="col-xs-12">
                        <h1><?php echo htmlspecialchars($greeting); ?>, <?php echo htmlspecialchars($username); ?>! 
                            <small>Welcome to Warehouse Dashboard</small>
                        </h1>
                        <p class="header-date-time">
                            <i class="fa fa-calendar"></i> <?php echo date('l, F j, Y'); ?> 
                            <span class="pull-right"><i class="fa fa-clock-o"></i> <span id="live-clock"><?php echo date('H:i:s'); ?></span></span>
                        </p>
                    </div>
                </div>
            </section>
            
            <section class="content">
                <!-- Quick Stats Row -->
                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-primary animate__animated animate__fadeIn">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($totalOrders); ?></h3>
                                <p>Total Request</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-shopping-cart"></i>
                            </div>
                            <a href="index.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-warning animate__animated animate__fadeIn animate__delay">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($totalPending); ?></h3>
                                <p>Pending Request</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-clock-o"></i>
                            </div>
                            <a href="index.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-danger animate__animated animate__fadeIn animate__delay">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($totalDecline); ?></h3>
                                <p>Decline Request</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-exclamation-circle"></i>
                            </div>
                            <a href="index.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-success animate__animated animate__fadeIn animate__delay">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($totalAcc); ?></h3>
                                <p>Approved Request</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-check-circle"></i>
                            </div>
                            <a href="daftarACC.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-purple animate__animated animate__fadeIn animate__delay">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($totalStock); ?></h3>
                                <p>Total Inventory Items</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-cubes"></i>
                            </div>
                            <a href="stock.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Main Content Row -->
                <div class="row">
                    <!-- Stock Overview Chart -->
                    <div class="col-md-8">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-bar-chart text-primary"></i> Warehouse Stock Overview
                                    <small class="text-muted">(Top 10 Lowest Stock)</small>
                                </h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="chart-container">
                                    <canvas id="stockBarChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stock Alerts -->
                    <div class="col-md-4">
                        <div class="box box-danger">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-exclamation-triangle text-danger"></i> Stock Alerts
                                </h3>
                                <span class="label label-danger pull-right">
                                    <?php echo htmlspecialchars($lowStockCount); ?> Alerts
                                </span>
                            </div>
                            <div class="box-body">
                                <div class="info-box bg-red">
                                    <span class="info-box-icon">
                                        <i class="fa fa-exclamation-circle"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Critical Items</span>
                                        <span class="info-box-number"><?php echo htmlspecialchars($lowStockCount); ?></span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: <?php echo htmlspecialchars(min(100, ($totalStock > 0 ? ($lowStockCount / $totalStock) * 100 : 0))); ?>%"></div>
                                        </div>
                                        <span class="progress-description">Items below reorder level</span>
                                    </div>
                                </div>
                                
                                <ul class="list-group stock-alert-list">
                                    <?php
                                    // Fetch low stock items for alert list
                                    $lowStockItems = [];
                                    $queryLowStockItems = mysqli_query($mysqli, "SELECT nama, stok, reorder_level FROM warehouse WHERE stok < reorder_level ORDER BY stok ASC LIMIT 8");
                                    while ($row = mysqli_fetch_assoc($queryLowStockItems)) {
                                        $lowStockItems[] = $row;
                                    }
                                    ?>
                                    
                                    <?php if (!empty($lowStockItems)): ?>
                                        <?php foreach ($lowStockItems as $lowStock): ?>
                                            <?php
                                            $percent = $lowStock['reorder_level'] > 0 ? ($lowStock['stok'] / $lowStock['reorder_level']) * 100 : 0;
                                            $alertClass = $percent < 50 ? 'stock-critical' : 'stock-warning';
                                            ?>
                                            <li class="list-group-item alert-item <?php echo htmlspecialchars($alertClass); ?>">
                                                <div class="clearfix">
                                                    <strong><?php echo htmlspecialchars($lowStock['nama']); ?></strong>
                                                    <span class="pull-right text-danger">
                                                        <?php echo htmlspecialchars($lowStock['stok'] . '/' . $lowStock['reorder_level']); ?>
                                                    </span>
                                                </div>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar progress-bar-danger" style="width: <?php echo htmlspecialchars(min(100, $percent)); ?>%"></div>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="list-group-item list-group-item-success">
                                            <i class="fa fa-check-circle text-success"></i> All stock levels are good
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <div class="box-footer text-center">
                                <a href="stock.php" class="uppercase">View All Inventory</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Second Content Row -->
                <div class="row">
                    <!-- Stock Distribution Pie Chart -->
                    <div class="col-md-4">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-pie-chart text-info"></i> Stock Distribution
                                </h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="chart-container">
                                    <canvas id="stockPieChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Orders -->
                    <div class="col-md-4">
                        <div class="nav-tabs-custom">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-list-alt text-warning"></i> Recent Transactions
                                </h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <?php if (!empty($recentOrders)): ?>
                                    <ul class="products-list product-list-in-box">
                                        <?php foreach ($recentOrders as $order): ?>
                                            <?php
                                            $statusClass = $order['status'] == 1 ? 'success' : 'warning';
                                            $statusText = $order['status'] == 1 ? 'Approved' : 'Pending';
                                            ?>
                                            <li class="item">
                                                <div class="product-info">
                                                    <a href="order_detail.php?id=<?php echo htmlspecialchars($order['id']); ?>" class="product-title">
                                                        Order #<?php echo htmlspecialchars($order['id']); ?>
                                                        <span class="label label-<?php echo htmlspecialchars($statusClass); ?> pull-right">
                                                            <?php echo htmlspecialchars($statusText); ?>
                                                        </span>
                                                    </a>
                                                    <span class="product-description">
                                                        Placed on <?php echo htmlspecialchars(date('M j, Y', strtotime($order['date_created']))); ?>
                                                    </span>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <div class="alert alert-info">No recent orders found</div>
                                <?php endif; ?>
                            </div>
                            <div class="box-footer text-center">
                                <a href="index.php" class="uppercase">View All Orders</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Inbound/Outbound Orders -->
                    <div class="col-md-4">
                        <div class="nav-tabs-custom">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-list-alt text-warning"></i> Recent Transactions
                                </h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>  
                            <ul class="nav nav-tabs pull-right">
                                <li class="active"><a href="#inbound-tab" data-toggle="tab">Inbound</a></li>
                                <li><a href="#outbound-tab" data-toggle="tab">Outbound</a></li>
                                
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="inbound-tab">
                                    <?php if (!empty($inboundOrders)): ?>
                                        <ul class="products-list product-list-in-box">
                                            <?php foreach ($inboundOrders as $order): ?>
                                                <li class="item">
                                                    <div class="product-info">
                                                        <a href="#" class="product-title">
                                                            Inbound #<?php echo htmlspecialchars($order['id']); ?>
                                                            <span class="label label-info pull-right">Received</span>
                                                        </a>
                                                        <span class="product-description">
                                                            <?php echo htmlspecialchars(date('M j, Y', strtotime($order['tanggal']))); ?>
                                                        </span>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <div class="alert alert-info">No inbound orders found</div>
                                    <?php endif; ?>
                                </div>
                                <div class="tab-pane" id="outbound-tab">
                                    <?php if (!empty($outboundOrders)): ?>
                                        <ul class="products-list product-list-in-box">
                                            <?php foreach ($outboundOrders as $order): ?>
                                                <li class="item">
                                                    <div class="product-info">
                                                        <a href="#" class="product-title">
                                                            Outbound #<?php echo htmlspecialchars($order['id']); ?>
                                                            <span class="label label-primary pull-right">Shipped</span>
                                                        </a>
                                                        <span class="product-description">
                                                            <?php echo htmlspecialchars(date('M j, Y', strtotime($order['tanggal']))); ?>
                                                        </span>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <div class="alert alert-info">No outbound orders found</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="box-footer text-center">
                                <a href="#" class="uppercase">View All Transactions</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/AdminLTE/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Live clock
        function updateClock() {
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var seconds = now.getSeconds();
            
            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            
            document.getElementById('live-clock').textContent = hours + ':' + minutes + ':' + seconds;
            setTimeout(updateClock, 1000);
        }
        updateClock();
        
        // Charts
        document.addEventListener("DOMContentLoaded", function () {
            // Pie Chart
            const pieCtx = document.getElementById('stockPieChart').getContext('2d');
            const stockPieChart = new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($pieLabels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($pieData); ?>,
                        backgroundColor: <?php echo json_encode($pieColors); ?>,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 12,
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw || 0;
                                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    let percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
            
            // Bar Chart
            const barCtx = document.getElementById('stockBarChart').getContext('2d');
            const stockBarChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($barLabels); ?>,
                    datasets: [
                        {
                            label: 'Current Stock',
                            data: <?php echo json_encode($barStockData); ?>,
                            backgroundColor: 'rgba(78, 115, 223, 0.7)',
                            borderColor: 'rgba(78, 115, 223, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'Reorder Level',
                            data: <?php echo json_encode($barReorderData); ?>,
                            backgroundColor: 'rgba(231, 74, 59, 0.7)',
                            borderColor: 'rgba(231, 74, 59, 1)',
                            borderWidth: 1,
                            type: 'line',
                            fill: false,
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantity',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Products',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                afterLabel: function(context) {
                                    const datasetIndex = context.datasetIndex;
                                    if (datasetIndex === 0) {
                                        const reorderLevel = <?php echo json_encode($barReorderData); ?>[context.dataIndex];
                                        const currentStock = context.raw;
                                        if (currentStock < reorderLevel) {
                                            return '⚠️ Below reorder level by ' + (reorderLevel - currentStock);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            });
            
            // Smooth animations for elements
            $('.small-box').hover(
                function() {
                    $(this).find('.icon').css('font-size', '80px');
                },
                function() {
                    $(this).find('.icon').css('font-size', '70px');
                }
            );
        });
    </script>
</body>
</html>