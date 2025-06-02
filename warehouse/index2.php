<?php 
// From C:\xampp\htdocs\capp\warehouse\index2.php
// To reach C:\xampp\htdocs\capp\konekdb.php
include('../konekdb.php');
session_start();

$username = $_SESSION['username'] ?? null;
$idpegawai = $_SESSION['idpegawai'] ?? null;

if(!isset($_SESSION['username'])){
    header("location:../index.php?status=please login first"); // Assuming login page is capp/index.php
    exit();
}
if (isset($_SESSION['idpegawai'])) {
    $idpegawai = $_SESSION['idpegawai'];
} else {
    header("location:../index.php?status=please login first"); // Assuming login page is capp/index.php
    exit();
}
$cekuser = mysqli_query($mysqli, "SELECT count(username) as jmluser FROM authorization WHERE username = '$username' AND modul = 'Warehouse'");
$user = mysqli_fetch_assoc($cekuser);

$getpegawai = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$idpegawai'");
$pegawai = mysqli_fetch_array($getpegawai);

if ($user['jmluser'] == "0") {
    header("location:../index.php"); // Assuming access denied redirect to capp/index.php
    exit;
}

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
$queryOrders = mysqli_query($mysqli, "SELECT COUNT(*) as totalOrders FROM dariwarehouse");
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

// Include header and sidebar
include('../partials/header.php'); // Path is from warehouse/ to capp/partials/
include('../partials/sidebar.php'); // Path is from warehouse/ to capp/partials/
?>

<div class="dashboard-header animate__animated animate__fadeIn">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1><?php echo htmlspecialchars($greeting); ?>, <?php echo htmlspecialchars($username); ?>!</h1>
            <small>Warehouse Management Dashboard</small>
        </div>
        <div class="col-md-4 text-md-end">
            <p class="header-date-time mb-0">
                <i class="far fa-calendar-alt me-2"></i> <?php echo date('l, F j, Y'); ?>
                <span class="ms-3"><i class="far fa-clock me-2"></i> <span id="live-clock"><?php echo date('H:i:s'); ?></span></span>
            </p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card orders animate__animated animate__fadeInUp">
            <i class="fas fa-clipboard-list"></i>
            <div class="count"><?php echo $totalOrders; ?></div>
            <div class="title">Total Requests</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card pending animate__animated animate__fadeInUp animate__delay-1s">
            <i class="fas fa-hourglass-half"></i>
            <div class="count"><?php echo $totalPending; ?></div>
            <div class="title">Pending Requests</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card approved animate__animated animate__fadeInUp animate__delay-2s">
            <i class="fas fa-check-circle"></i>
            <div class="count"><?php echo $totalAcc; ?></div>
            <div class="title">Approved Requests</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stock animate__animated animate__fadeInUp animate__delay-3s">
            <i class="fas fa-box-open"></i>
            <div class="count"><?php echo $totalStock; ?></div>
            <div class="title">Total Stock Items</div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-4">
        <div class="card animate__animated animate__fadeIn">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-exclamation-triangle me-2 text-warning"></i> Stock Alerts</span>
                <span class="badge bg-danger"><?php echo $lowStockCount; ?> items</span>
            </div>
            <div class="card-body">
                <?php if (count($lowStockItems) > 0): ?>
                    <?php foreach ($lowStockItems as $item): ?>
                        <?php 
                        $isCritical = $item['stok'] < ($item['reorder_level'] * 0.5);
                        $alertClass = $isCritical ? 'critical' : 'warning';
                        ?>
                        <div class="alert-item <?php echo $alertClass; ?>">
                            <div>
                                <strong><?php echo htmlspecialchars($item['nama']); ?></strong>
                                <div class="text-muted small">Current: <?php echo $item['stok']; ?> | Reorder: <?php echo $item['reorder_level']; ?></div>
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <p>No stock alerts</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-4 animate__animated animate__fadeIn">
            <div class="card-header">
                <i class="fas fa-history me-2"></i> Recent Requests
            </div>
            <div class="card-body recent-orders p-0">
                <?php if (count($recentOrders) > 0): ?>
                    <?php foreach ($recentOrders as $order): ?>
                        <div class="order-item">
                            <div class="d-flex justify-content-between">
                                <strong>#<?php echo htmlspecialchars($order['id']); ?></strong>
                                <span class="order-status <?php echo $order['status'] == 1 ? 'status-approved' : 'status-pending'; ?>">
                                    <?php echo $order['status'] == 1 ? 'Approved' : 'Pending'; ?>
                                </span>
                            </div>
                            <div class="text-muted small"><?php echo htmlspecialchars($order['nama_barang']); ?></div>
                            <div class="d-flex justify-content-between mt-2">
                                <small><?php echo date('M j, H:i', strtotime($order['date_created'])); ?></small>
                                <small>Qty: <?php echo htmlspecialchars($order['jumlah']); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No recent requests</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center">
                <a href="history_order.php" class="btn btn-sm btn-outline-primary">View All Requests</a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card animate__animated animate__fadeIn">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i> Stock Overview
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <i class="fas fa-chart-pie me-2"></i> Stock by Category
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="pieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <i class="fas fa-tasks me-2"></i> Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="product.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> Add Product
                            </a>
                            <a href="order.php" class="btn btn-outline-primary">
                                <i class="fas fa-clipboard-list me-2"></i> New Request
                            </a>
                            <a href="stock.php" class="btn btn-outline-primary">
                                <i class="fas fa-boxes me-2"></i> View Inventory
                            </a>
                            <a href="mailbox.php" class="btn btn-outline-primary">
                                <i class="fas fa-envelope me-2"></i> Check Mailbox
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../partials/footer.php'); ?>

<script>
    // Charts
    document.addEventListener('DOMContentLoaded', function() {
        // Bar Chart
        const barCtx = document.getElementById('barChart');
        if (barCtx) { // Check if the element exists
            const barChart = new Chart(barCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($barLabels); ?>,
                    datasets: [
                        {
                            label: 'Current Stock',
                            data: <?php echo json_encode($barStockData); ?>,
                            backgroundColor: 'rgba(67, 97, 238, 0.7)',
                            borderColor: 'rgba(67, 97, 238, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Reorder Level',
                            data: <?php echo json_encode($barReorderData); ?>,
                            backgroundColor: 'rgba(247, 37, 133, 0.7)',
                            borderColor: 'rgba(247, 37, 133, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    }
                }
            });
        }
        
        // Pie Chart
        const pieCtx = document.getElementById('pieChart');
        if (pieCtx) { // Check if the element exists
            const pieChart = new Chart(pieCtx.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($pieLabels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($pieData); ?>,
                        backgroundColor: <?php echo json_encode($pieColors); ?>, // Use pieColors
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
        }
    });
</script>