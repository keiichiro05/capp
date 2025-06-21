<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'], $_SESSION['idpegawai'])) {
    header("Location: ../list_request.php?status=Please Login First");
    exit();
}

require_once('../konekdb.php');

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Check if user has access to Adminwarehouse module
$stmt = $mysqli->prepare("SELECT COUNT(username) as usercount FROM authorization WHERE username = ? AND modul = 'Adminwarehouse'");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['usercount'] == "0") {
    header("Location: ../list_request.php?status=Access Declined");
    exit();
}

// Get employee data
$stmtPegawai = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$stmtPegawai->bind_param("i", $idpegawai);
$stmtPegawai->execute();
$resultPegawai = $stmtPegawai->get_result();
$pegawai = $resultPegawai->fetch_assoc();

// Handle accept action
if (isset($_GET['accept']) && isset($_GET['no'])) {
    $no = intval($_GET['no']);
    $getOrder = $mysqli->prepare("SELECT * FROM dariwarehouse WHERE no = ?");
    $getOrder->bind_param("i", $no);
    $getOrder->execute();
    $order = $getOrder->get_result()->fetch_assoc();

    if ($order) {
        $currentDate = date('Y-m-d H:i:s');
        $stmt = $mysqli->prepare("INSERT INTO pemesanan 
            (namabarang, kategori, jumlah, satuan, id_supplier, tanggal, status, cabang) 
            VALUES (?, ?, ?, ?, ?, ?, '1', ?)");
        $stmt->bind_param("sssissi",
            $order['nama'],
            $order['kategori'],
            $order['jumlah'],
            $order['satuan'],
            $order['supplier'],
            $currentDate,
            $order['cabang']
        );
        if ($stmt->execute()) {
            $stmt2 = $mysqli->prepare("DELETE FROM dariwarehouse WHERE no = ?");
            $stmt2->bind_param("i", $no);
            if ($stmt2->execute()) {
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
    header("Location: list_request.php");
    exit();
}

// Handle reject action
if (isset($_GET['reject']) && isset($_GET['no'])) {
    $no = intval($_GET['no']);
    $getOrder = $mysqli->prepare("SELECT * FROM dariwarehouse WHERE no = ?");
    $getOrder->bind_param("i", $no);
    $getOrder->execute();
    $order = $getOrder->get_result()->fetch_assoc();

    if ($order) {
        $currentDate = date('Y-m-d H:i:s');
        $stmt = $mysqli->prepare("INSERT INTO pemesanan 
            (namabarang, kategori, jumlah, satuan, id_supplier, tanggal, status, cabang) 
            VALUES (?, ?, ?, ?, ?, ?, '2', ?)");
        $stmt->bind_param("sssissi",
            $order['nama'],
            $order['kategori'],
            $order['jumlah'],
            $order['satuan'],
            $order['supplier'],
            $currentDate,
            $order['cabang']
        );
        if ($stmt->execute()) {
            $stmt2 = $mysqli->prepare("DELETE FROM dariwarehouse WHERE no = ?");
            $stmt2->bind_param("i", $no);
            if ($stmt2->execute()) {
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
    header("Location: list_request.php");
    exit();
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
                    <li class="active">
                        <a href="list_request.php">
                            <i class="fa fa-list"></i> <span>List Request</span>
                        </a>
                    </li>
                    <li>
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
                    List Request
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
                <p>List Request From Warehouse</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Order Date</th>
                                <th>Name</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Supplier</th>
                                <th>Status</th>
                                <th>Branch</th>
                                <th>Category</th>
                                <th>Action</th>
                            </tr>
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
                                        <td>".htmlspecialchars($baris['nama'])."</td>
                                        <td>{$baris['jumlah']}</td>
                                        <td>".htmlspecialchars($baris['satuan'])."</td>
                                        <td>".htmlspecialchars($baris['supplier'])."</td>
                                        <td><button class='btn btn-warning'>Pending</button></td>
                                        <td>".htmlspecialchars($baris['cabang'])."</td>
                                        <td>".htmlspecialchars($baris['kategori'])."</td>
                                        <td>
                                            <a href='/adminwarehouse/list_request.php?accept=true&no={$baris['no']}' class='btn btn-primary'>Accept</a>
                                            <a href='/adminwarehouse/list_request.php?reject=true&no={$baris['no']}' onclick='return confirm(\"Are you sure to reject this?\")' class='btn btn-danger'>Reject</a>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='10' style='text-align:center;'>No Order Requests</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </aside>
    </div>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/AdminLTE/app.js"></script>
</body>
</html>
