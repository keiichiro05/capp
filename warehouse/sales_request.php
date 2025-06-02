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

// Check if user has access to Adminwarehouse module
$stmt = $mysqli->prepare("SELECT COUNT(username) as usercount FROM authorization WHERE username = ? AND modul = 'Warehouse'");
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
    <title>Admin Warehouse | E-pharm</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- bootstrap 3.0.2 -->
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- font Awesome -->
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons -->
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />
</head>
<?php

// Handle fulfill action
if(isset($_GET['fulfill']) && isset($_GET['id'])) {
    $request_id = intval($_GET['id']);
    
    // Get the request data first
    $getRequest = $mysqli->prepare("SELECT * FROM sales_requests WHERE id = ?");
    $getRequest->bind_param("i", $request_id);
    $getRequest->execute();
    $request = $getRequest->get_result()->fetch_assoc();
    
    if($request) {
        // Check if stock is available
        $checkStock = $mysqli->prepare("SELECT jumlah FROM inventory WHERE code = ?");
        $checkStock->bind_param("s", $request['item_code']);
        $checkStock->execute();
        $stockResult = $checkStock->get_result()->fetch_assoc();
        
        if($stockResult && $stockResult['jumlah'] >= $request['quantity']) {
            // Update inventory
            $newQuantity = $stockResult['jumlah'] - $request['quantity'];
            $updateStock = $mysqli->prepare("UPDATE inventory SET jumlah = ? WHERE code = ?");
            $updateStock->bind_param("is", $newQuantity, $request['item_code']);
            
            if($updateStock->execute()) {
                // Update request status to fulfilled
                $updateRequest = $mysqli->prepare("UPDATE sales_requests SET status = 'fulfilled', fulfilled_by = ?, fulfilled_at = NOW() WHERE id = ?");
                $updateRequest->bind_param("si", $username, $request_id);
                
                if($updateRequest->execute()) {
                    $_SESSION['message'] = '<div class="alert alert-success">Request has been fulfilled and inventory updated.</div>';
                } else {
                    $_SESSION['message'] = '<div class="alert alert-danger">Failed to update request status.</div>';
                }
            } else {
                $_SESSION['message'] = '<div class="alert alert-danger">Failed to update inventory.</div>';
            }
        } else {
            $_SESSION['message'] = '<div class="alert alert-danger">Insufficient stock to fulfill this request.</div>';
        }
    } else {
        $_SESSION['message'] = '<div class="alert alert-danger">Request not found.</div>';
    }
    
    header("Location: index.php");
    exit();
}

// Handle reject action
if(isset($_GET['reject']) && isset($_GET['id'])) {
    $request_id = intval($_GET['id']);
    
    // Update request status to rejected
    $updateRequest = $mysqli->prepare("UPDATE sales_requests SET status = 'rejected', rejected_by = ?, rejected_at = NOW() WHERE id = ?");
    $updateRequest->bind_param("si", $username, $request_id);
    
    if($updateRequest->execute()) {
        $_SESSION['message'] = '<div class="alert alert-success">Request has been rejected.</div>';
    } else {
        $_SESSION['message'] = '<div class="alert alert-danger">Failed to reject request.</div>';
    }
    
    header("Location: index.php");
    exit();
}
?>
<body class="skin-blue">
    <!-- header logo: style can be found in header.less -->
    <header class="header">
        <a href="index.html" class="logo">Admin Warehouse</a>
        <div class="navbar-right">
            <ul class="nav navbar-nav">
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="glyphicon glyphicon-user"></i>
                        <span><?php echo $username;?><i class="caret"></i></span>
                    </a>
                    <ul class=" dropdown-menu">
                        <li class="user-header bg-light-blue">
                            <img src="img/<?php echo $pegawai['foto'];?>" class="img-circle" alt="User Image" />
                            <p>
                                <?php echo $pegawai['Nama']." - ".$pegawai['Jabatan']." ".$pegawai['Departemen'];?>
                                <small>Member since <?php echo $pegawai['Tanggal_Masuk']; ?></small>
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
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="left-side sidebar-offcanvas">
            <section class="sidebar">
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="img/<?php echo $pegawai['foto'];?>" class="img-circle" alt="User Image" />
                    </div>
                    <div class="pull-left info">
                        <p>Hello, <?php echo $username;?></p>
                        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                    </div>
                </div>
                <ul class="sidebar-menu">
                    <li>
                        <a href="streamlit.php">
                            <i class="fa fa"></i> <span>Streamlit</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php">
                            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="index.php">
                            <i class="fa fa-list"></i> <span>Sales Requests</span>
                        </a>
                    </li>
                    <li>
                        <a href="request_history.php">
                            <i class="fa fa-th"></i> <span>Request History</span>
                        </a>
                    </li>
                    <li>
                        <a href="stock.php">
                            <i class="fa fa-list-alt"></i> <span>Inventory</span>
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

        <!-- Right side column. Contains the navbar and content of the page -->
        <aside class="right-side">
            <section class="content-header">
                <h1>
                    Sales Division Requests
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
                <p>List of item requests from Sales Division</p>
                <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Request Date</th>
                            <th>Sales Person</th>
                            <th>Customer</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Current Stock</th>
                            <th>Reason</th>
                            <th>Urgency</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get pending requests from sales division
                        $sql = "SELECT sr.*, i.nama as item_name, i.jumlah as current_stock 
                                FROM sales_requests sr
                                JOIN inventory i ON sr.item_code = i.code
                                WHERE sr.status = 'pending'";
                        $hasil = $mysqli->query($sql);

                        if ($hasil && $hasil->num_rows > 0) {
                            while ($request = $hasil->fetch_assoc()) {
                                // Determine status badge color
                                $status_badge = '';
                                switch($request['status']) {
                                    case 'pending': $status_badge = 'warning'; break;
                                    case 'fulfilled': $status_badge = 'success'; break;
                                    case 'rejected': $status_badge = 'danger'; break;
                                    default: $status_badge = 'info';
                                }
                                
                                // Determine urgency badge
                                $urgency_badge = '';
                                switch($request['urgency']) {
                                    case 'high': $urgency_badge = 'danger'; break;
                                    case 'medium': $urgency_badge = 'warning'; break;
                                    case 'low': $urgency_badge = 'info'; break;
                                    default: $urgency_badge = 'default';
                                }
                                
                                echo "<tr>
                                        <td>{$request['id']}</td>
                                        <td>{$request['request_date']}</td>
                                        <td>".htmlspecialchars($request['sales_person'])."</td>
                                        <td>".htmlspecialchars($request['customer_name'])."</td>
                                        <td>".htmlspecialchars($request['item_code'])."</td>
                                        <td>".htmlspecialchars($request['item_name'])."</td>
                                        <td>{$request['quantity']}</td>
                                        <td>{$request['current_stock']}</td>
                                        <td>".htmlspecialchars($request['reason'])."</td>
                                        <td><span class='badge bg-{$urgency_badge}'>".ucfirst($request['urgency'])."</span></td>
                                        <td><span class='badge bg-{$status_badge}'>".ucfirst($request['status'])."</span></td>
                                        <td>";
                                
                                // Only show actions for pending requests
                                if($request['status'] == 'pending') {
                                    echo "<a href='index.php?fulfill=true&id={$request['id']}' class='btn btn-success btn-sm'>Fulfill</a>
                                          <a href='index.php?reject=true&id={$request['id']}' onclick='return confirm(\"Are you sure you want to reject this request?\")' class='btn btn-danger btn-sm'>Reject</a>";
                                } else {
                                    echo "<span class='text-muted'>No actions</span>";
                                }
                                
                                echo "</td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='12' style='text-align:center;'>No pending requests from sales division</td></tr>";
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