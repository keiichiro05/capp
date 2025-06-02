<?php
session_start();
require_once('../konekdb.php');

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Check if export request
if (isset($_GET['export'])) {
    $exportType = $_GET['export'];
    $cabang_filter = isset($_GET['warehouse']) ? $_GET['warehouse'] : '';
    exportData($exportType, $cabang_filter);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['username'], $_SESSION['idpegawai'])) {
    header("Location: ../index.php?status=Please Login First");
    exit();
}

// Check user authorization
$username = $_SESSION['username'];
$stmt = $mysqli->prepare("SELECT COUNT(username) as jmluser FROM authorization WHERE username = ? AND modul = 'Adminwarehouse'");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['jmluser'] == "0") {
    header("Location: ../index.php?status=Access Declined");
    exit();
}

// Get employee data
$idpegawai = $_SESSION['idpegawai'];
$stmtPegawai = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$stmtPegawai->bind_param("i", $idpegawai);
$stmtPegawai->execute();
$resultPegawai = $stmtPegawai->get_result();
$pegawai = $resultPegawai->fetch_assoc();

// Set warehouse filter
$cabang_filter = '';
if (isset($_GET['warehouse']) && !empty($_GET['warehouse'])) {
    $cabang_filter = htmlspecialchars($_GET['warehouse']);
}

function exportData($type, $cabang_filter = '') {
    global $mysqli;
    
    // Get data from database
    $sql = "SELECT * FROM warehouse";
    $params = [];
    $types = "";
    
    if (!empty($cabang_filter)) {
        $sql .= " WHERE cabang = ?";
        $params[] = $cabang_filter;
        $types .= "s";
    }
    
    $stmt = $mysqli->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepare data for export
    $data = [];
    $headers = ['Code', 'Name', 'Qty', 'Category', 'Unit', 'Stock Min', 'Warehouse'];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            $row['code'] ?? '',
            $row['Nama'] ?? '',
            $row['Stok'] ?? '',
            $row['Kategori'] ?? '',
            $row['Satuan'] ?? '',
            $row['reorder_level'] ?? '',
            $row['cabang'] ?? ''
        ];
    }

    // Export based on type
    switch ($type) {
        case 'excel':
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename=stock_export_' . date('Y-m-d') . '.xls');
            
            echo '<table border="1">';
            echo '<tr>';
            foreach ($headers as $header) {
                echo '<th>' . htmlspecialchars($header) . '</th>';
            }
            echo '</tr>';
            
            foreach ($data as $row) {
                echo '<tr>';
                foreach ($row as $cell) {
                    echo '<td>' . htmlspecialchars($cell) . '</td>';
                }
                echo '</tr>';
            }
            
            echo '</table>';
            break;
            
        case 'pdf':
            // Simple PDF fallback without TCPDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename=stock_export_' . date('Y-m-d') . '.pdf');
            
            $html = '<h1>Stock Export</h1>';
            $html .= '<table border="1" cellpadding="5">';
            $html .= '<tr>';
            foreach ($headers as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr>';
            
            foreach ($data as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                }
                $html .= '</tr>';
            }
            
            $html .= '</table>';
            echo $html;
            break;
            
        case 'csv':
        default:
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=stock_export_' . date('Y-m-d') . '.csv');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, $headers);
            
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
            
            fclose($output);
    }
    exit;
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
                    <li>
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
                    <li class=active>
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
            <section class="content-header">
                <h1>
                    Stock Management
                    <small>Warehouse Manager</small>
                </h1>
                <ol class="breadcrumb">
                </ol>
            </section>

            <section class="content">
                <?php 
                if (isset($_SESSION['message'])) {
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                }
                ?>
                
                <div class="filter-container">
                    <div class="filter-form">
                        <a href="?export=excel&warehouse=<?= urlencode($cabang_filter) ?>" class="btn btn-success" title="Download as Excel">
                            <i class="fa fa-file-excel-o"></i> Excel
                        </a>
                        <a href="?export=csv&warehouse=<?= urlencode($cabang_filter) ?>" class="btn btn-info" title="Download as CSV">
                            <i class="fa fa-file-text-o"></i> CSV
                        </a>
                        <a href="?export=pdf&warehouse=<?= urlencode($cabang_filter) ?>" class="btn btn-danger" title="Download as PDF">
                            <i class="fa fa-file-pdf-o"></i> PDF
                        </a>
                        <form method="get" action="stock.php" class="form-inline">
                            <select name="warehouse" class="form-control">
                                <option value="">All Warehouse</option>
                                <option value="Ambon" <?php echo ($cabang_filter == 'Ambon' ? 'selected' : ''); ?>>Ambon</option>
                                <option value="Cikarang" <?php echo ($cabang_filter == 'Cikarang' ? 'selected' : ''); ?>>Cikarang</option>
                                <option value="Medan" <?php echo ($cabang_filter == 'Medan' ? 'selected' : ''); ?>>Medan</option>
                                <option value="Blitar" <?php echo ($cabang_filter == 'Blitar' ? 'selected' : ''); ?>>Blitar</option>
                                <option value="Surabaya" <?php echo ($cabang_filter == 'Surabaya' ? 'selected' : ''); ?>>Surabaya</option>
                            </select>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-filter"></i> Filter
                            </button>
                            <?php if (!empty($cabang_filter)): ?>
                                <a href="stock.php" class="btn btn-default">
                                    <i class="fa fa-times"></i> Clear
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="total-records">
                        <?php
                        $count_query = "SELECT COUNT(*) as total FROM warehouse";
                        $params = [];
                        $types = "";
                        
                        if (!empty($cabang_filter)) {
                            $count_query .= " WHERE cabang = ?";
                            $params[] = $cabang_filter;
                            $types .= "s";
                        }
                        
                        $stmt = $mysqli->prepare($count_query);
                        if (!empty($params)) {
                            $stmt->bind_param($types, ...$params);
                        }
                        $stmt->execute();
                        $count_result = $stmt->get_result();
                        $count_row = $count_result->fetch_assoc();
                        echo "<span class='badge bg-blue'>" . htmlspecialchars($count_row['total']) . " records found</span>";
                        ?>
                    </div>
                </div>
                
                <h1>Stock List</h1>
                <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Product Id</th>
                            <th>Item Name</th>
                            <th>Stock</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Stock Min</th>
                            <th>Warehouse</th>
                        </tr>
                    </div>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM warehouse";
                        $params = [];
                        $types = "";
                        
                        if (!empty($cabang_filter)) {
                            $sql .= " WHERE cabang = ?";
                            $params[] = $cabang_filter;
                            $types .= "s";
                        }
                        
                        $stmt = $mysqli->prepare($sql);
                        if (!empty($params)) {
                            $stmt->bind_param($types, ...$params);
                        }
                        $stmt->execute();
                        $hasil = $stmt->get_result();

                        if ($hasil->num_rows > 0) {
                            while ($baris = $hasil->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($baris['Code'] ?? '') . "</td>
                                        <td>" . htmlspecialchars($baris['Nama'] ?? '') . "</td>
                                        <td>" . htmlspecialchars($baris['Stok'] ?? '') . "</td>
                                        <td>" . htmlspecialchars($baris['Kategori'] ?? '') . "</td>
                                        <td>" . htmlspecialchars($baris['Satuan'] ?? '') . "</td>
                                        <td>" . htmlspecialchars($baris['reorder_level'] ?? '') . "</td>
                                        <td>" . htmlspecialchars($baris['cabang'] ?? '') . "</td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' style='text-align:center;'>No Available Stock</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>
        </aside>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../js/AdminLTE/app.js" type="text/javascript"></script>
</body>
</html>