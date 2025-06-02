<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'], $_SESSION['idpegawai'])) {
    header("Location: ../index.php?status=Please Login First");
    exit();
}

require_once('../konekdb.php');

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Check if user has access to Adminwarehouse module (using prepared statement)
$stmt = $mysqli->prepare("SELECT COUNT(username) as usercount FROM authorization WHERE username = ? AND modul = 'Adminwarehouse'");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['usercount'] == "0") {
    header("Location: ../index.php?status=Access Declined");
    exit();
}

// Get employee data
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
    <title>Warehouse Manager | E-pharm</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- bootstrap 3.0.2 -->
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- font Awesome -->
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons -->
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <!-- Morris chart -->
    <link href="../css/morris/morris.css" rel="stylesheet" type="text/css" />
    <!-- jvectormap -->
    <link href="../css/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- fullCalendar -->
    <link href="../css/fullcalendar/fullcalendar.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker -->
    <link href="../css/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
    <!-- bootstrap wysihtml5 - text editor -->
    <link href="../css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
</head>
<?php

// Handle reject action
if(isset($_GET['reject']) && isset($_GET['no'])) {
    $no = intval($_GET['no']);
    
    // Get the order data first
    $getOrder = $mysqli->prepare("SELECT * FROM dariwarehouse WHERE no = ?");
    $getOrder->bind_param("i", $no);
    $getOrder->execute();
    $order = $getOrder->get_result()->fetch_assoc();
    
    if($order) {
        $currentDate = date('Y-m-d H:i:s');
        
        // Insert into pemesanan table with status=2 (rejected)
        $stmt = $mysqli->prepare("INSERT INTO pemesanan 
                        (code, namabarang, kategori, jumlah, satuan, id_supplier, tanggal, status, cabang) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, '2', ?)");
        $stmt->bind_param("sssissis", 
            $order['code'],
            $order['nama'],
            $order['kategori'],
            $order['jumlah'],
            $order['satuan'],
            $order['supplier'],
            $order['date_created'],
            $order['cabang']
        );
        
        if($stmt->execute()) {
            // Delete from dariwarehouse table
            $stmt2 = $mysqli->prepare("DELETE FROM dariwarehouse WHERE no = ?");
            $stmt2->bind_param("i", $no);
            
            if($stmt2->execute()) {
                $_SESSION['message'] = '<div class="alert alert-success">Order has been rejected and moved to the order database.</div>';
            } else {
                $_SESSION['message'] = '<div class="alert alert-danger">Failed to delete order from the list.</div>';
            }
        } else {
            $_SESSION['message'] = '<div class="alert alert-danger">Failed to move order to the order database.</div>';
        }
    } else {
        $_SESSION['message'] = '<div class="alert alert-danger">Order not found.</div>';
    }
    
    header("Location: index.php");
    exit();
}

// Handle accept action
if(isset($_GET['accept']) && isset($_GET['no'])) {
    $no = intval($_GET['no']);
    
    // Get the order data first
    $getOrder = $mysqli->prepare("SELECT * FROM dariwarehouse WHERE no = ?");
    $getOrder->bind_param("i", $no);
    $getOrder->execute();
    $order = $getOrder->get_result()->fetch_assoc();
    
    if($order) {
        $currentDate = date('Y-m-d H:i:s');
        
        // Insert into pemesanan table with status=1 (accepted)
        $stmt = $mysqli->prepare("INSERT INTO pemesanan 
                        (code, namabarang, kategori, jumlah, satuan, id_supplier, tanggal, status, cabang) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, '1', ?)");
        $stmt->bind_param("sssissis", 
            $order['code'],
            $order['nama'],
            $order['kategori'],
            $order['jumlah'],
            $order['satuan'],
            $order['supplier'],
            $currentDate,
            $order['cabang']
        );
        
        if($stmt->execute()) {
            // Delete from dariwarehouse table
            $stmt2 = $mysqli->prepare("DELETE FROM dariwarehouse WHERE no = ?");
            $stmt2->bind_param("i", $no);
            
            if($stmt2->execute()) {
                $_SESSION['message'] = '<div class="alert alert-success">Order has been accepted and moved to the order database.</div>';
            } else {
                $_SESSION['message'] = '<div class="alert alert-danger">Failed to delete order from the list.</div>';
            }
        } else {
            $_SESSION['message'] = '<div class="alert alert-danger">Failed to move order to the order database.</div>';
        }
    } else {
        $_SESSION['message'] = '<div class="alert alert-danger">Order not found.</div>';
    }
    
    header("Location: index.php");
    exit();
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
                    <li class="active">
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

        <!-- Right side column. Contains the navbar and content of the page -->
        <aside class="right-side">
            <section class="content-header">
                <h1>
                    List Request
                    <small>Warehouse Manager</small>
                </h1>
                <ol class="breadcrumb">
                </ol>
            </section>

            <!-- Main content -->
            <section class="content">
                <?php 
                if(isset($_SESSION['message'])) {
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                }
                ?>
                <p>List Request From Warehouse</p>
                <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Order Date</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th>Branch</th>
                            <th>Category</th>
                            <th>Action</th>
                        </tr>
            </div>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM dariwarehouse WHERE status = 0";
                        $hasil = mysqli_query($mysqli, $sql);

                        if (mysqli_num_rows($hasil) > 0) {
                            while ($baris = mysqli_fetch_array($hasil)) {
                                echo "<tr>
                                        <td>{$baris['no']}</td>
                                        <td>{$baris['date_created']}</td>
                                        <td>".htmlspecialchars($baris['code'])."</td>
                                        <td>".htmlspecialchars($baris['nama'])."</td>
                                        <td>{$baris['jumlah']}</td>
                                        <td>".htmlspecialchars($baris['satuan'])."</td>
                                        <td>".htmlspecialchars($baris['supplier'])."</td>
                                        <td><button class='btn btn-warning'>Pending</button></td>
                                        <td>".htmlspecialchars($baris['cabang'])."</td>
                                        <td>".htmlspecialchars($baris['kategori'])."</td>
                                        <td>
                                            <a href='index.php?accept=true&no={$baris['no']}' class='btn btn-primary'>Accept</a>
                                            <a href='index.php?reject=true&no={$baris['no']}' onclick='return confirm(\"Are you sure to reject this?\")' class='btn btn-danger'>Reject</a>
                                        </td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='11' style='text-align:center;'>No Order Requests</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>
        </aside>
    </div>

    <!-- jQuery 2.0.2 -->
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
    <!-- Bootstrap -->  
    <script src="../js/bootstrap.min.js" type="text/javascript"></script>
    <!-- AdminLTE App -->
    <script src="../js/AdminLTE/app.js" type="text/javascript"></script>
</body>
</html>
