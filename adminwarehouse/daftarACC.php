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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Warehouse</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- bootstrap 3.0.2 -->
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- font Awesome -->
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons -->
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <!-- DataTables -->
    <link href="../css/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />
    <style>
        .order-history-container {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }
        
        .filter-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .filter-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .order-history-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .order-history-table th {
            background-color:rgb(6, 57, 134);
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }
        
        .order-history-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .order-history-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .order-history-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 12px;
            display: inline-block;
        }
        
        .status-pending {
            background-color: #f39c12;
            color: white;
        }
        
        .status-accepted {
            background-color: #00a65a;
            color: white;
        }
        
        .status-declined {
            background-color: #dd4b39;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #777;
        }
        
        .empty-state i {
            font-size: 50px;
            margin-bottom: 15px;
            color: #ddd;
        }
        
        .empty-state h4 {
            margin: 10px 0;
            font-weight: 600;
        }
        
        .order-date {
            white-space: nowrap;
        }
        
        .total-records .badge {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 10px;
        }
    </style>    
</head>

<body class="skin-blue">
    <header class="header">
        <a href="index.html" class="logo">Admin Warehouse</a>
            <div class="navbar-right">
                <ul class="nav navbar-nav">
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="glyphicon glyphicon-user"></i>
                            <span><?php echo $username; ?><i class="caret"></i></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-header bg-light-blue">
                                <img src="img/<?php echo $pegawai['foto']; ?>" class="img-circle" alt="User Image" />
                                <p>
                                    <?php
                                    echo $pegawai['Nama'] . " - " . $pegawai['Jabatan'] . " " . $pegawai['Departemen']; ?>
                                    <small>Member since <?php echo "$pegawai[Tanggal_Masuk]"; ?></small>
                                </p>
                            </li>
                            <li class="user-body">
                                <div class="col-xs-4 text-center">
                                    <a href="#">Admin</a>
                                </div>
                                <div class="col-xs-4 text-center">
                                    <a href="#">Warehouse</a>
                                </div>
                                <div class="col-xs-4 text-center">
                                    <a href="#"></a>
                                </div>
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
                        <img src="img/<?php echo $pegawai['foto']; ?>" class="img-circle" alt="User Image" />
                    </div>
                    <div class="pull-left info">
                        <p>Hello, <?php echo $username; ?></p>
                        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                    </div>
                </div>
                <ul class="sidebar-menu">
                    <li>
                        <a href="dashboard.php">
                            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php">
                            <i class="fa fa-list"></i> <span>List Order</span>
                        </a>
                    </li>
                    <li class="active">
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
        </aside>

        <aside class="right-side">
            <section class="content-header">
                <h1>
                    Approved Orders
                    <small>Admin Warehouse</small>
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
                            <button id="exportExcel" class="btn btn-success" title="Download as Excel"><i class="fa fa-file-excel-o"></i> Excel</button>
                            <button id="exportCSV" class="btn btn-info" title="Download as CSV"><i class="fa fa-file-text-o"></i> CSV</button>
                            <button id="exportPDF" class="btn btn-danger" title="Download as PDF"><i class="fa fa-file-pdf-o"></i> PDF</button>
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
                        <table class="order-history-table">
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
    <!-- Export Buttons -->

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
        XLSX.writeFile(wb, "orders.xlsx");
    };

    // CSV Export
    document.getElementById('exportCSV').onclick = function() {
        var data = getTableData();
        var ws = XLSX.utils.aoa_to_sheet(data);
        var csv = XLSX.utils.sheet_to_csv(ws);
        var blob = new Blob([csv], {type: "text/csv"});
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = "orders.csv";
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
        doc.save("orders.pdf");
    };
    </script>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../js/AdminLTE/app.js" type="text/javascript"></script>
</body>
</html>