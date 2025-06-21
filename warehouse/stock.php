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

// Get filter values
$cabang_filter = isset($_GET['cabang']) ? mysqli_real_escape_string($mysqli, $_GET['cabang']) : '';

// Handle exports
if (isset($_GET['export'])) {
    $export_type = $_GET['export'];
    
    $query = "SELECT p.*, s.Nama as supplier_name 
              FROM warehouse p
              LEFT JOIN supplier s ON p.Supplier = s.Nama
              WHERE 1=1";
    
    if ($cabang_filter != '') {
        $query .= " AND p.cabang = '$cabang_filter'";
    }
    
    $query .= " ORDER BY p.tanggal DESC";
    
    $result = mysqli_query($mysqli, $query);
    
    $headers = array('No', 'Code', 'Name Product', 'Stock', 'Category', 'Supplier', 'Unit', 'Reorder', 'Warehouse', 'PIC', 'Date Add');
    $rows = array();
    $no = 1;
    
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = array(
            $no++,
            $row['Code'],
            $row['Nama'],
            $row['Stok'],
            $row['Kategori'],
            $row['supplier_name'] ? $row['supplier_name'] : 'N/A',
            $row['Satuan'],
            $row['reorder_level'],
            $row['cabang'],
            $row['pic'],
            $row['Tanggal']
        );
    }
    
    $filename = 'stock_export_' . date('Ymd_His');
    
    if ($export_type == 'excel') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');
        
        echo '<table border="1">';
        echo '<tr>';
        foreach ($headers as $header) {
            echo '<th>'.$header.'</th>';
        }
        echo '</tr>';
        
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>'.$cell.'</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
        exit;
    }
    elseif ($export_type == 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="'.$filename.'.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    elseif ($export_type == 'pdf') {
        require_once('../tcpdf/tcpdf.php');
        
        $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Warehouse System');
        $pdf->SetTitle('Stock Report');
        $pdf->SetSubject('Stock Report');
        
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);
        
        // Title
        $pdf->Cell(0, 10, 'Stock Report', 0, 1, 'C');
        $pdf->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'C');
        $pdf->Ln(5);
        
        // Table header
        $html = '<table border="1" cellpadding="5">
                <tr style="background-color:#f2f2f2;">';
        foreach ($headers as $header) {
            $html .= '<th>'.$header.'</th>';
        }
        $html .= '</tr>';
        
        // Table rows
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>'.$cell.'</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($filename.'.pdf', 'D');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Warehouse Branch</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
        <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" /> 
        <style>
            /* Custom CSS for Order History */
            .order-history-container {
                background: #fff;
                border-radius: 10px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                overflow: hidden;
                margin-bottom: 30px;
            }

            .order-history-header {
                background: linear-gradient(135deg, #3498db, #2980b9);
                color: white;
                padding: 15px 20px;
                border-bottom: 1px solid rgba(255,255,255,0.2);
            }

            .order-history-header h3 {
                margin: 0;
                font-weight: 600;
                font-size: 18px;
                display: inline-block;
            }

            .filter-container {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 15px 20px;
                background-color: #f8f9fa;
                border-bottom: 1px solid #eee;
            }

            .filter-form {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .filter-form .form-control {
                min-width: 180px;
                border-radius: 4px;
                border: 1px solid #ddd;
                box-shadow: none;
            }

            .filter-form .btn {
                border-radius: 4px;
            }

            .order-history-table {
                width: 100%;
                border-collapse: collapse;
            }

            .order-history-table th {
                background-color: #2c3e50;
                color: white;
                font-weight: 600;
                padding: 12px 15px;
                text-align: left;
                position: sticky;
                top: 0;
            }

            .order-history-table td {
                padding: 12px 15px;
                border-bottom: 1px solid #eee;
                vertical-align: middle;
            }

            .order-history-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            .order-history-table tr:hover {
                background-color: #f1f1f1;
            }

            /* Barcode Styles */
            .barcode-cell {
                text-align: center;
            }
            
            .barcode-container {
                display: inline-block;
                padding: 5px;
                background: white;
                border: 1px solid #ddd;
                border-radius: 4px;
                position: relative;
            }
            
            .barcode-img {
                height: 40px;
                width: auto;
                max-width: 150px;
                image-rendering: crisp-edges;
            }
            
            .barcode-text {
                font-size: 10px;
                font-family: monospace;
                margin-top: 3px;
                display: block;
            }

            /* Responsive Adjustments */
            @media (max-width: 768px) {
                .filter-container {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 10px;
                }
                
                .filter-form {
                    width: 100%;
                    flex-direction: column;
                }
                
                .filter-form .form-control,
                .filter-form .btn {
                    width: 100%;
                }
                
                .order-history-table {
                    display: block;
                    overflow-x: auto;
                }
            }

            /* Pagination Styles */
            .pagination-container {
                display: flex;
                justify-content: center;
                padding: 15px;
                background-color: #f8f9fa;
                border-top: 1px solid #eee;
            }

            .pagination {
                margin: 0;
            }

            .pagination > li > a,
            .pagination > li > span {
                color: #2c3e50;
                border: 1px solid #ddd;
                margin: 0 2px;
                border-radius: 4px !important;
            }

            .pagination > li.active > a,
            .pagination > li.active > span {
                background: linear-gradient(135deg, #3498db, #2980b9);
                border-color: #2980b9;
                color: white;
            }

            /* Empty State */
            .empty-state {
                padding: 40px 20px;
                text-align: center;
                color: #7f8c8d;
            }

            .empty-state i {
                font-size: 50px;
                margin-bottom: 20px;
                color: #bdc3c7;
            }

            .empty-state h4 {
                margin-bottom: 10px;
                color: #2c3e50;
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
                        <li class="active">
                            <a href="stock.php">
                                <i class="fa fa-folder"></i> <span>Stock</span>
                            </a>
                        </li>
                        <li>
                            <a href="movement.php">
                                <i class="fa fa-exchange"></i> <span>Movement</span>
                            </a>
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
                        Stock 
                        <small>View and Manage Stock</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Stock</li>
                    </ol>
                </section>

                <section class="content">
                    <div class="order-history-container">
                        <div class="filter-container">
                            <div class="filter-form">
                                <a href="stock.php?export=excel" class="btn btn-success" title="Download as Excel">
                                    <i class="fa fa-file-excel-o"></i> Excel
                                </a>
                                <a href="stock.php?export=csv" class="btn btn-info" title="Download as CSV">
                                    <i class="fa fa-file-text-o"></i> CSV
                                </a>
                                <a href="stock.php?export=pdf" class="btn btn-danger" title="Download as PDF">
                                    <i class="fa fa-file-pdf-o"></i> PDF
                                </a>
                                
                                <form method="get" action="stock.php" class="form-inline">
                                    <?php
                                    // Ambil daftar warehouse dari tabel list_warehouse
                                    $warehouse_query = mysqli_query($mysqli, "SELECT nama FROM list_warehouse ORDER BY nama ASC");
                                    ?>
                                    <select name="cabang" class="form-control">
                                        <option value="">All Warehouse</option>
                                        <?php while ($wh = mysqli_fetch_assoc($warehouse_query)): ?>
                                            <option value="<?php echo htmlspecialchars($wh['nama']); ?>" <?php echo ($cabang_filter == $wh['nama'] ? 'selected' : ''); ?>>
                                                <?php echo htmlspecialchars($wh['nama']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                            
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-filter"></i> Filter
                                    </button>
                                    <?php if(isset($_GET['cabang'])): ?>
                                        <a href="stock.php" class="btn btn-default">
                                            <i class="fa fa-times"></i> Clear
                                        </a>
                                    <?php endif; ?>
                                </form>
                            </div>
                            <div class="total-records">
                                <?php
                                $count_query = "SELECT COUNT(*) as total FROM warehouse WHERE 1=1";
                                if ($cabang_filter != '') {
                                    $count_query .= " AND cabang = '$cabang_filter'";
                                }
                                
                                $count_result = mysqli_query($mysqli, $count_query);
                                $count_row = mysqli_fetch_assoc($count_result);
                                echo "<span class='badge bg-blue'>{$count_row['total']} records found</span>";
                                ?>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="order-history-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Code</th>
                                        <th>Name Product</th>
                                        <th>Stock</th>
                                        <th>Category</th>
                                        <th>Supplier</th>
                                        <th>Unit</th>
                                        <th>Min Stock</th>
                                        <th>Warehouse</th>
                                        <th>PIC</th>
                                        <th>Added Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT p.*, s.Nama as supplier_name 
                                            FROM warehouse p
                                            LEFT JOIN supplier s ON p.Supplier = s.Nama
                                            WHERE 1=1";
                                    
                                    if ($cabang_filter != '') {
                                        $query .= " AND p.cabang = '$cabang_filter'";
                                    }
                                    
                                    $query .= " ORDER BY p.tanggal DESC";
                                    
                                    $per_page = 15;
                                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                    $start = ($page - 1) * $per_page;
                                    $query .= " LIMIT $start, $per_page";
                                    
                                    $result = mysqli_query($mysqli, $query);
                                    $no = $start + 1;
                                    
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<tr>
                                                <td>{$no}</td>
                                                <td class='barcode-cell'>
                                                    <div class='barcode-container'>
                                                        <img src='https://barcode.tec-it.com/barcode.ashx?data=".urlencode($row['Code'])."&code=Code128&dpi=96' 
                                                            class='barcode-img' 
                                                            alt='".htmlspecialchars($row['Code'])."'
                                                            title='".htmlspecialchars($row['Code'])."'>
                                                        <span class='barcode-text'>".htmlspecialchars($row['Code'])."</span>
                                                    </div>
                                                </td>
                                                <td>".htmlspecialchars($row['Nama'])."</td>
                                                <td>".htmlspecialchars($row['Stok'])."</td>
                                                <td>".htmlspecialchars($row['Kategori'])."</td>
                                                <td>".htmlspecialchars($row['supplier_name'] ? $row['supplier_name'] : 'N/A')."</td>
                                                <td>".htmlspecialchars($row['Satuan'])."</td>
                                                <td>".htmlspecialchars($row['reorder_level'])."</td>
                                                <td>".htmlspecialchars($row['cabang'])."</td>
                                                <td>".htmlspecialchars($row['pic'])."</td>
                                                <td>".htmlspecialchars($row['Tanggal'])."</td>
                                            <td><a href='details.php?code=".urlencode($row['Code'])."' class='btn btn-xs btn-info'>
                                                    <i class='fa fa-eye'></i> Details
                                                </a>
                                            </td>
                                            </tr>";
                                            $no++;
                                        }
                                    } else {
                                        echo "<tr>
                                            <td colspan='11'>
                                                <div class='empty-state'>
                                                    <i class='fa fa-inbox'></i>
                                                    <h4>No Stock Data Found</h4>
                                                    <p>There are no items matching your criteria</p>
                                                </div>
                                            </td>
                                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="pagination-container">
                            <ul class="pagination">
                                <?php
                                $total_pages = ceil($count_row['total'] / $per_page);
                                
                                if ($page > 1) {
                                    echo "<li><a href='stock.php?page=".($page-1).
                                        ($cabang_filter ? "&cabang=$cabang_filter" : "").
                                        "'>&laquo;</a></li>";
                                }
                                
                                for ($i = 1; $i <= $total_pages; $i++) {
                                    $active = ($i == $page) ? "active" : "";
                                    echo "<li class='$active'><a href='stock.php?page=$i".
                                        ($cabang_filter ? "&cabang=$cabang_filter" : "").
                                        "'>$i</a></li>";
                                }
                                
                                if ($page < $total_pages) {
                                    echo "<li><a href='stock.php?page=".($page+1).
                                        ($cabang_filter ? "&cabang=$cabang_filter" : "").
                                        "'>&raquo;</a></li>";
                                }
                                ?>
                            </ul>
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