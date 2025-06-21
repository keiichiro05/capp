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

// Handle stock transfer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transfer'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $transfer_type = $_POST['transfer_type'];
    $notes = mysqli_real_escape_string($mysqli, $_POST['notes']);
    
    // Get current stock
    $stmt = $mysqli->prepare("SELECT Stok FROM warehouse WHERE no = ?");
    $stmt->bind_param("s", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if ($product) {
        $current_stock = $product['Stok'];
        
        if ($transfer_type === 'out' && $current_stock < $quantity) {
            $_SESSION['message'] = '<div class="alert alert-danger">Insufficient stock for transfer out!</div>';
        } else {
            // Update warehouse stock
            $new_stock = $transfer_type === 'in' ? $current_stock + $quantity : $current_stock - $quantity;
            
            $update_stmt = $mysqli->prepare("UPDATE warehouse SET Stok = ? WHERE no = ?");
            $update_stmt->bind_param("is", $new_stock, $product_id);
            $update_stmt->execute();
            
            // Record the transfer in transaction history
            $insert_stmt = $mysqli->prepare("INSERT INTO stock_transactions (product_id, quantity, transfer_type, notes, user_id, transaction_date) 
                                           VALUES (?, ?, ?, ?, ?, NOW())");
            $insert_stmt->bind_param("iissi", $product_id, $quantity, $transfer_type, $notes, $idpegawai);
            $insert_stmt->execute();
            
            $_SESSION['message'] = '<div class="alert alert-success">Stock transfer successful!</div>';
        }
    } else {
        $_SESSION['message'] = '<div class="alert alert-danger">Product not found!</div>';
    }
    
    header("Location: stock.php");
    exit();
}

function exportData($type, $cabang_filter = '') {
    global $mysqli;
    
    // Get data from database
    $sql = "SELECT no, Code, Nama, Stok, Kategori, Satuan, reorder_level, cabang FROM warehouse";
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
    $headers = ['ID', 'Code', 'Name', 'Stock', 'Category', 'Unit', 'Reorder Level', 'Warehouse'];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            $row['no'] ?? '',
            $row['Code'] ?? '',
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
    <title>Warehouse Manager | E-pharm</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>

        /* Sidebar */
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
        
        /* Info Box */
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
        
        /* Color Classes */
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
        <!-- Sidebar -->
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
                            <i class="fa fa-undo"></i> <span>Request History</span>
                        </a>
                    </li>
                    <li class="active">
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
                Stock Management
                <small>Warehouse Manager</small>
            </h1>
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
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Item Name</th>
                            <th>Stock</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Reorder Level</th>
                            <th>Warehouse</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT no, Code, Nama, Stok, Kategori, Satuan, reorder_level, cabang FROM warehouse";
                        $params = [];
                        $types = "";
                        
                        if (!empty($cabang_filter)) {
                            $sql .= " WHERE cabang = ?";
                            $params[] = $cabang_filter;
                            $types .= "s";
                        }
                        
                        $sql .= " ORDER BY Nama ASC";
                        
                        $stmt = $mysqli->prepare($sql);
                        if (!empty($params)) {
                            $stmt->bind_param($types, ...$params);
                        }
                        $stmt->execute();
                        $hasil = $stmt->get_result();

                        if ($hasil->num_rows > 0) {
                            while ($baris = $hasil->fetch_assoc()) {
                                $row_class = '';
                                if ($baris['Stok'] <= 0) {
                                    $row_class = 'stock-critical';
                                } elseif ($baris['Stok'] <= $baris['reorder_level']) {
                                    $row_class = 'stock-warning';
                                }
                                
                                echo "<tr class='$row_class'>
                                        <td>" . htmlspecialchars($baris['no'] ?? '') . "</td>
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
                            echo "<tr><td colspan='9' style='text-align:center;'>No Available Stock</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </aside>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../js/AdminLTE/app.js" type="text/javascript"></script>
    
    <script>
        $(document).ready(function() {
            // Handle transfer button click
            $('.transfer-btn').click(function() {
                var productId = $(this).data('id');
                var productName = $(this).data('name');
                var currentStock = $(this).data('stock');
                
                $('#transfer_product_id').val(productId);
                $('#transfer_product_name').text(productName);
                $('#transfer_current_stock').text(currentStock);
                $('#transfer_quantity').val(1).attr('max', currentStock);
                
                $('.transfer-modal').modal('show');
            });
            
            // Change max value when transfer type changes
            $('input[name="transfer_type"]').change(function() {
                var currentStock = parseInt($('#transfer_current_stock').text());
                if ($(this).val() === 'out') {
                    $('#transfer_quantity').attr('max', currentStock);
                } else {
                    $('#transfer_quantity').removeAttr('max');
                }
            });
        });
    </script>
</body>
</html>