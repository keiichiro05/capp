<?php
session_start();
// Set Jakarta timezone
date_default_timezone_set('Asia/Jakarta');
// Check if user is logged in
if (!isset($_SESSION['username'], $_SESSION['idpegawai'])) {
    header("Location: ../index.php?status=Please Login First");
    exit();
}
require_once('../konekdb.php');
$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Check if user has access to Adminwarehouse module (using prepared statement)
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
$stmtPegawai = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$stmtPegawai->bind_param("i", $idpegawai);
$stmtPegawai->execute();
$resultPegawai = $stmtPegawai->get_result();
$pegawai = $resultPegawai->fetch_assoc();

// Check if tanggal column exists
$column_check = mysqli_query($mysqli, "SHOW COLUMNS FROM pemesanan LIKE 'tanggal'");
$date_column_exists = (mysqli_num_rows($column_check) > 0);

// Handle export requests
if (isset($_GET['export'])) {
    $export_type = $_GET['export'];
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    
    // Get data from database
    $query = "SELECT p.*, s.Nama as supplier_name 
            FROM pemesanan p
            LEFT JOIN supplier s ON p.id_supplier = s.id_supplier";
    
    if ($status_filter != '') {
        $query .= " WHERE p.status = '$status_filter'";
    }
    
    $query .= " ORDER BY p.tanggal DESC";
    $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
    
    $data = array();
    $headers = array('#', 'Order ID', 'Order Date', 'Item Name', 'Category', 'Quantity', 'Unit', 'Supplier', 'Branch', 'Status');
    
    if (mysqli_num_rows($result) > 0) {
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            // Determine status text
            $status = '';
            if ($row['status'] == '1') {
                $status = 'Accepted';
            } elseif ($row['status'] == '2') {
                $status = 'Declined';
            } elseif ($row['status'] == '0') {
                $status = 'Pending';
            }
            
            // Format date
            $order_date = 'N/A';
            if ($date_column_exists && !empty($row['tanggal'])) {
                $order_date = date('d M Y H:i', strtotime($row['tanggal']));
            } elseif (!empty($row['tanggal'])) {
                $order_date = date('d M Y H:i', strtotime($row['tanggal']));
            }
            
            $data[] = array(
                $no++,
                $row['id_pemesanan'],
                $order_date,
                $row['namabarang'],
                $row['kategori'],
                $row['jumlah'],
                $row['satuan'],
                ($row['supplier_name'] ? $row['supplier_name'] : $row['id_supplier']),
                $row['cabang'],
                $status
            );
        }
    }
    
    switch ($export_type) {
        case 'excel':
            exportExcel($headers, $data);
            break;
        case 'csv':
            exportCSV($headers, $data);
            break;
        case 'pdf':
            exportPDF($headers, $data);
            break;
    }
    exit;
}

function exportExcel($headers, $data) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename=request_history_'.date('Y-m-d').'.xls');
    
    echo '<table border="1">';
    echo '<tr>';
    foreach ($headers as $header) {
        echo '<th>'.htmlspecialchars($header).'</th>';
    }
    echo '</tr>';
    
    foreach ($data as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>'.htmlspecialchars($cell).'</td>';
        }
        echo '</tr>';
    }
    
    echo '</table>';
    exit;
}

function exportCSV($headers, $data) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=request_history_'.date('Y-m-d').'.csv');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM to fix UTF-8 in Excel
    fwrite($output, "\xEF\xBB\xBF");
    
    fputcsv($output, $headers);
    
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

function exportPDF($headers, $data) {
    require_once('../tcpdf/tcpdf.php');
    
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Warehouse System');
    $pdf->SetAuthor('Warehouse Manager');
    $pdf->SetTitle('Request History');
    $pdf->SetSubject('Request History Export');
    $pdf->SetKeywords('Request, History, Warehouse');
    
    $pdf->SetHeaderData('', 0, 'Request History', 'Generated on '.date('Y-m-d H:i:s'));
    $pdf->setHeaderFont(Array('helvetica', '', 10));
    $pdf->setFooterFont(Array('helvetica', '', 8));
    
    $pdf->SetDefaultMonospacedFont('courier');
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);
    $pdf->SetAutoPageBreak(TRUE, 25);
    
    $pdf->AddPage();
    
    $html = '<h2>Request History</h2>';
    $html .= '<table border="1" cellpadding="4">';
    $html .= '<thead><tr>';
    
    foreach ($headers as $header) {
        $html .= '<th style="background-color:#f2f2f2;font-weight:bold;">'.htmlspecialchars($header).'</th>';
    }
    
    $html .= '</tr></thead><tbody>';
    
    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= '<td>'.htmlspecialchars($cell).'</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('request_history_'.date('Y-m-d').'.pdf', 'D');
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
        
        /* Export buttons */
        .export-buttons {
            margin-bottom: 15px;
        }
        
        .export-buttons .btn {
            margin-right: 5px;
        }
        
        /* Status badges */
        .status-badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-accepted {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-declined {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        /* Filter form */
        .filter-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-form {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .total-records {
            margin-left: auto;
        }
        
        @media (max-width: 768px) {
            .filter-container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-form {
                margin-bottom: 10px;
                width: 100%;
            }
            
            .total-records {
                margin-left: 0;
                margin-top: 10px;
            }
        }
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
                    <li class="active">
                        <a href="daftarACC.php">
                            <i class="fa fa-undo"></i> <span>Request History</span>
                        </a>
                    </li>
                    <li>
                        <a href="stock.php">
                           <i class="fa fa-archive"></i> <span>Inventory</span>
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
                    Request History
                    <small>Warehouse Manager </small>
                </h1>
            </section>
            <section class="content">
                <?php
                if (isset($_SESSION['message'])) {
                    echo '<div class="alert alert-info alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            ' . $_SESSION['message'] . '
                        </div>';
                    unset($_SESSION['message']);
                }
                ?>
                <div class="order-history-container">
                    <div class="filter-container">
                        <div class="filter-form">
                            <div class="export-buttons">
                                <a href="?export=excel<?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?>" class="btn btn-success" title="Download as Excel"><i class="fa fa-file-excel-o"></i> Excel</a>
                                <a href="?export=csv<?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?>" class="btn btn-info" title="Download as CSV"><i class="fa fa-file-text-o"></i> CSV</a>
                                <a href="?export=pdf<?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?>" class="btn btn-danger" title="Download as PDF"><i class="fa fa-file-pdf-o"></i> PDF</a>
                            </div>
                            <form method="get" action="daftarACC.php" class="form-inline">
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="0" <?php echo (isset($_GET['status']) && $_GET['status'] == '0' ? 'selected' : ''); ?>>Pending</option>
                                    <option value="1" <?php echo (isset($_GET['status']) && $_GET['status'] == '1' ? 'selected' : ''); ?>>Accepted</option>
                                    <option value="2" <?php echo (isset($_GET['status']) && $_GET['status'] == '2' ? 'selected' : ''); ?>>Declined</option>
                                </select>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                                <?php if(isset($_GET['status'])): ?>
                                    <a href="daftarACC.php" class="btn btn-default">
                                        <i class="fa fa-times"></i> Clear
                                    </a>
                                <?php endif; ?>
                            </form>
                        </div>
                        <div class="total-records">
                            <?php
                            $count_query = "SELECT COUNT(*) as total FROM pemesanan";
                            if (isset($_GET['status']) && $_GET['status'] != '') {
                                $status = mysqli_real_escape_string($mysqli, $_GET['status']);
                                $count_query .= " WHERE status = '$status'";
                            }
                            $count_result = mysqli_query($mysqli, $count_query);
                            $count_row = mysqli_fetch_assoc($count_result);
                            echo "<span class='badge bg-blue'>{$count_row['total']} records found</span>";
                            ?>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Order ID</th>
                                    <th>Order Date</th>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Supplier</th>
                                    <th>Branch</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT p.*, s.Nama as supplier_name 
                                        FROM pemesanan p
                                        LEFT JOIN supplier s ON p.id_supplier = s.id_supplier";
                                
                                if (isset($_GET['status']) && $_GET['status'] != '') {
                                    $status = mysqli_real_escape_string($mysqli, $_GET['status']);
                                    $query .= " WHERE p.status = '$status'";
                                }
                                
                                $query .= " ORDER BY p.tanggal DESC";
                                
                                // Add simple pagination
                                $per_page = 5;
                                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                $start = ($page - 1) * $per_page;
                                $query .= " LIMIT $start, $per_page";
                                
                                $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
                                $no = $start + 1;
                                
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>
                                            <td>{$no}</td>
                                            <td>{$row['id_pemesanan']}</td>
                                            <td class='order-date'>";
                                        
                                        // Display date - use tanggal if exists, otherwise use tanggal
                                        if ($date_column_exists && !empty($row['tanggal'])) {
                                            echo date('d M Y H:i', strtotime($row['tanggal']));
                                        } elseif (!empty($row['tanggal'])) {
                                            echo date('d M Y H:i', strtotime($row['tanggal']));
                                        } else {
                                            echo 'N/A';
                                        }
                                        
                                        echo "</td>
                                            <td>{$row['namabarang']}</td>
                                            <td>{$row['kategori']}</td>
                                            <td>{$row['jumlah']}</td>
                                            <td>{$row['satuan']}</td>
                                            <td>".($row['supplier_name'] ? $row['supplier_name'] : $row['id_supplier'])."</td>
                                            <td>{$row['cabang']}</td>
                                            <td>";
                                        
                                        if ($row['status'] == '1') {
                                            echo "<span class='status-badge status-accepted'>Accepted</span>";
                                        } elseif ($row['status'] == '2') {
                                            echo "<span class='status-badge status-declined'>Declined</span>";
                                        } elseif ($row['status'] == '0') {
                                            echo "<span class='status-badge status-pending'>Pending</span>";
                                        }
                                        
                                        echo "</td>
                                            <td>";
                                        
                                        if ($row['status'] == '0') {
                                            echo "<a href='proses_pesanan.php?action=accept&id=" . $row['id_pemesanan'] . "' class='btn btn-success btn-xs' title='Accept'><i class='fa fa-check'></i></a>
                                                <a href='proses_pesanan.php?action=decline&id=" . $row['id_pemesanan'] . "' class='btn btn-danger btn-xs' title='Decline'><i class='fa fa-times'></i></a>";
                                        } else {
                                            echo "<span class='text-muted small'>Process completed</span>";
                                        }
                                        
                                        echo "</td>
                                        </tr>";
                                        $no++;
                                    }
                                } else {
                                    echo "<tr>
                                        <td colspan='11'>
                                            <div class='empty-state'>
                                                <i class='fa fa-inbox'></i>
                                                <h4>No Orders Found</h4>
                                                <p>There are no orders matching your criteria</p>
                                            </div>
                                        </td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Simple pagination -->
                    <div class="text-center">
                        <?php
                        $total_query = "SELECT COUNT(*) as total FROM pemesanan";
                        if (isset($_GET['status']) && $_GET['status'] != '') {
                            $status = mysqli_real_escape_string($mysqli, $_GET['status']);
                            $total_query .= " WHERE status = '$status'";
                        }
                        $total_result = mysqli_query($mysqli, $total_query);
                        $total_row = mysqli_fetch_assoc($total_result);
                        $total_pages = ceil($total_row['total'] / $per_page);
                        
                        if ($total_pages > 1) {
                            echo '<ul class="pagination">';
                            
                            // Previous button
                            if ($page > 1) {
                                $prev = $page - 1;
                                echo '<li><a href="daftarACC.php?page='.$prev.(isset($_GET['status']) ? '&status='.$_GET['status'] : '').'">«</a></li>';
                            }
                            
                            // Page numbers
                            for ($i = 1; $i <= $total_pages; $i++) {
                                $active = ($i == $page) ? 'class="active"' : '';
                                echo '<li '.$active.'><a href="daftarACC.php?page='.$i.(isset($_GET['status']) ? '&status='.$_GET['status'] : '').'">'.$i.'</a></li>';
                            }
                            
                            // Next button
                            if ($page < $total_pages) {
                                $next = $page + 1;
                                echo '<li><a href="daftarACC.php?page='.$next.(isset($_GET['status']) ? '&status='.$_GET['status'] : '').'">»</a></li>';
                            }
                            
                            echo '</ul>';
                        }
                        ?>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../js/AdminLTE/app.js" type="text/javascript"></script>
</body>
</html>