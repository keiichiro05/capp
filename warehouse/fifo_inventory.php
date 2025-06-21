
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
?>
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

            /* Status Styles */
            .status-badge {
                display: inline-block;
                padding: 5px 10px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .status-pending {
                background-color: #f39c12;
                color: white;
            }

            .status-accepted {
                background-color: #2ecc71;
                color: white;
            }

            .status-declined {
                background-color: #e74c3c;
                color: white;
            }

            /* Date Styles */
            .order-date {
                font-family: monospace;
                color: #555;
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

            /* Animation for table rows */
            @keyframes fadeInRow {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .order-history-table tbody tr {
                animation: fadeInRow 0.3s ease-out forwards;
                animation-delay: calc(var(--row-index) * 0.05s);
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
            
            /* Sidebar Fix */
            .sidebar {
                display: block !important;
            }
            
            .left-side {
                width: 220px;
            }
            
            .right-side {
                margin-left: 220px;
            }
            
            @media (max-width: 767px) {
                .left-side {
                    width: 0;
                }
                
                .right-side {
                    margin-left: 0;
                }
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
                            <a  class="active" href="fifo_inventory.php">
                                <i class="fa fa-exchange"></i> <span>Stock Transfer</span>
                            </a>
                        </li>
                        <li>
                            <a href="product.php">
                                <i class="fa fa-list-alt"></i> <span>Products</span>
                            </a>
                        </li>
                        <li>
                            <a href="new_request.php">
                                <i class="fa fa-th"></i> <span>Request</span>
                            </a>
                        </li>
                        <li>
                            <a href="fifo_inventory.php">
                                <i class="fa fa-archive"></i> <span>Request History</span>
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
                        Request History
                        <small>View and Manage Request History</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li>Order History</li>
                    </ol>
                </section>

                <section class="content">
                    <div class="order-history-container">
                        <div class="filter-container">
                            <div class="filter-form">
                                    <button id="exportExcel" class="btn btn-success" title="Download as Excel"><i class="fa fa-file-excel-o"></i> Excel</button>
                                    <button id="exportCSV" class="btn btn-info" title="Download as CSV"><i class="fa fa-file-text-o"></i> CSV</button>
                                    <button id="exportPDF" class="btn btn-danger" title="Download as PDF"><i class="fa fa-file-pdf-o"></i> PDF</button>
                            
                                <form method="get" action="fifo_inventory.php" class="form-inline">
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
                                        <a href="fifo_inventory.php" class="btn btn-default">
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
                                        <th>Satuan</th>
                                        <th>Reorder</th>
                                        <th>Warehouse</th>
                                        <th>PIC</th>
                                         <th>Tanggal</th>
                                   
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT p.*, s.Nama as supplier_name 
                                            FROM warehouse p
                                            LEFT JOIN supplier s ON p.Supplier = s.id_supplier
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
                                            echo "<tr style='--row-index: {$no};'>
                                                <td>{$no}</td>
                                                <td>{$row['Code']}</td>
                                                <td>{$row['Nama']}</td>
                                                <td>{$row['Stok']}</td>
                                                <td>{$row['Kategori']}</td>
                                                 <td>".($row['supplier_name'] ? $row['supplier_name'] : 'N/A')."</td>
                                                <td>{$row['Satuan']}</td>
                                                <td>{$row['reorder_level']}</td>
                                                 <td>{$row['cabang']}</td>
                                                 <td>{$row['pic']}</td>
                                                  <td>{$row['Tanggal']}</td>
                                               
                                               
                                                <td>";
                                       
                                            echo "</td>
                                            </tr>";
                                            $no++;
                                        }
                                    } else {
                                        echo "<tr>
                                            <td colspan='11'>
                                                <div class='empty-state'>
                                                    <i class='fa fa-inbox'></i>
                                                    <h4>No Order History Found</h4>
                                                    <p>There are no orders matching your criteria</p>
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
                                    echo "<li><a href='fifo_inventory.php?page=".($page-1).
                                        ($cabang_filter ? "&cabang=$cabang_filter" : "").
                                    
                                        "'>&laquo;</a></li>";
                                }
                                
                                for ($i = 1; $i <= $total_pages; $i++) {
                                    $active = ($i == $page) ? "active" : "";
                                    echo "<li class='$active'><a href='fifo_inventory.php?page=$i".
                                        ($cabang_filter ? "&cabang=$cabang_filter" : "").
                                  
                                        "'>$i</a></li>";
                                }
                                
                                if ($page < $total_pages) {
                                    echo "<li><a href='fifo_inventory.php?page=".($page+1).
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
        <!-- SheetJS & jsPDF CDN -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

        <script>
        function getTableData() {
            var table = document.querySelector('.order-history-table');
            var data = [];
            var rows = table.querySelectorAll('tr');
            for (var i = 0; i < rows.length; i++) {
                var row = [], cols = rows[i].querySelectorAll('th,td');
                for (var j = 0; j < cols.length; j++) {
                    row.push(cols[j].innerText.trim());
                }
                data.push(row);
            }
            return data;
        }

        // Excel Export
        document.getElementById('exportExcel').onclick = function() {
            var data = getTableData();
            var ws = XLSX.utils.aoa_to_sheet(data);
            var wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Orders");
            XLSX.writeFile(wb, "history-orders.xlsx");
        };

        // CSV Export
        document.getElementById('exportCSV').onclick = function() {
            var data = getTableData();
            var ws = XLSX.utils.aoa_to_sheet(data);
            var csv = XLSX.utils.sheet_to_csv(ws);
            var blob = new Blob([csv], {type: "text/csv"});
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = "history-orders.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };

        // PDF Export
        document.getElementById('exportPDF').onclick = function() {
            var data = getTableData();
            var doc = new jspdf.jsPDF('l', 'pt', 'a4');
            doc.text("Order List", 40, 30);
            doc.autoTable({
                head: [data[0]],
                body: data.slice(1),
                startY: 50,
                styles: {fontSize: 8}
            });
            doc.save("history-orders.pdf");
        };
        </script>
        <script src="../js/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <script src="../js/AdminLTE/app.js" type="text/javascript"></script>
    </body>
</html>
