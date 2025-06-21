<?php 
include('../konekdb.php');
session_start();
$username = $_SESSION['username'] ?? null;
$idpegawai = $_SESSION['idpegawai'] ?? null;
$active_submenu = 'inbound'; // Set active submenu for inbound

if(!isset($_SESSION['username'])) {
    header("location:../index.php?status=please login first");
    exit();
}

// Handle stock updates for inbound
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = mysqli_real_escape_string($mysqli, $_POST['product_id']);
    $quantity = (int)$_POST['quantity'];
    $notes = mysqli_real_escape_string($mysqli, $_POST['notes'] ?? '');
    
    // Validate quantity
    if ($quantity <= 0) {
        $_SESSION['error'] = "Quantity must be greater than 0";
        header("Location: movement_inbound.php?product_id=$product_id");
        exit();
    }
    
    // Get current stock
    $current_query = mysqli_query($mysqli, "SELECT Stok, cabang FROM warehouse WHERE Code = '$product_id'");
    if (!$current_query || mysqli_num_rows($current_query) == 0) {
        $_SESSION['error'] = "Product not found";
        header("Location: movement_inbound.php");
        exit();
    }
    
    $current_data = mysqli_fetch_assoc($current_query);
    $current_stock = (int)$current_data['Stok'];
    $warehouse = $current_data['cabang'];
    
    $new_stock = $current_stock + $quantity;
    
    // Update warehouse stock
    if (!mysqli_query($mysqli, "UPDATE warehouse SET Stok = '$new_stock' WHERE Code = '$product_id'")) {
        $_SESSION['error'] = "Failed to update stock: " . mysqli_error($mysqli);
        header("Location: movement_inbound.php?product_id=$product_id");
        exit();
    }
    
    // Log the movement
    $movement_date = date('Y-m-d H:i:s');
    $insert_query = mysqli_query($mysqli, "INSERT INTO inventory_movement (product_code, movement_type, quantity, previous_stock, new_stock, movement_date, pic, warehouse, notes) 
                        VALUES ('$product_id', 'inbound', '$quantity', '$current_stock', '$new_stock', '$movement_date', '$username', '$warehouse', '$notes')");
    
    if (!$insert_query) {
        $_SESSION['error'] = "Failed to log movement: " . mysqli_error($mysqli);
        header("Location: movement_inbound.php?product_id=$product_id");
        exit();
    }
    
    $_SESSION['success'] = "Inbound movement recorded successfully!";
    header("Location: movement_inbound.php");
    exit();
}

$product_id = isset($_GET['product_id']) ? mysqli_real_escape_string($mysqli, $_GET['product_id']) : null;
$cabang_filter = isset($_GET['cabang']) ? mysqli_real_escape_string($mysqli, $_GET['cabang']) : '';
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Inbound Movements</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
        <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" /> 
        <style>
            .box-primary {
                border-top-color: #3c8dbc;
            }
            .label-success {
                background-color: #00a65a;
            }
            .table-responsive {
                overflow-x: auto;
            }
            .panel-heading {
                background-color: #3c8dbc !important;
                color: white !important;
            }
            .form-group {
                margin-bottom: 15px;
            }
            .btn-success {
                background-color: #00a65a;
                border-color: #008d4c;
            }
        </style>
    </head>
    <body class="skin-blue">
        <header class="header">
            <a href="index.php" class="logo">Admin Warehouse</a>
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
                            <a href="stock.php">
                                <i class="fa fa-folder"></i> <span>Stock</span>
                            </a>
                        </li>
                        <li class="treeview active">
                            <a href="#">
                                <i class="fa fa-exchange"></i> <span>Movement</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu" style="display: block;">
                                <li>
                                    <a href="movement.php?submenu=movement"><i class="fa fa-th"></i> All Movement</a>
                                </li>
                                <li>
                                    <a href="movement_history.php?submenu=movement-history"><i class="fa fa-undo"></i> Movement History</a>
                                </li>
                                <li class="active">
                                    <a href="movement_inbound.php?submenu=inbound"><i class="fa fa-sign-in"></i> Inbound</a>
                                </li>
                                <li>
                                    <a href="movement_outbound.php?submenu=outbound"><i class="fa fa-sign-out"></i> Outbound</a>
                                </li>
                            </ul>
                        </li>
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
                        Inbound Movements
                        <small>Add stock to inventory</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Inbound</li>
                    </ol>
                </section>

                <section class="content">
                    <div class="row">
                        <div class="col-md-12">
                            <?php if(isset($_SESSION['success'])): ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if(isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Inbound Movements</h3>
                                    <div class="box-tools pull-right">
                                        <a href="movement_history.php?type=inbound" class="btn btn-default btn-sm">
                                            <i class="fa fa-history"></i> View Inbound History
                                        </a>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <?php if ($product_id): ?>
                                        <?php
                                        $product_query = mysqli_query($mysqli, "SELECT * FROM warehouse WHERE Code = '$product_id'");
                                        if (!$product_query || mysqli_num_rows($product_query) == 0) {
                                            echo "<div class='alert alert-danger'>Product not found</div>";
                                        } else {
                                            $product = mysqli_fetch_assoc($product_query);
                                        ?>
                                        
                                        <div class="row">
                                            <div class="col-md-6 col-md-offset-3">
                                                <div class="panel panel-primary">
                                                    <div class="panel-heading">
                                                        <h3 class="panel-title">Add Inbound Movement</h3>
                                                    </div>
                                                    <div class="panel-body">
                                                        <form method="post">
                                                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
                                                            
                                                            <div class="form-group">
                                                                <label>Product Code</label>
                                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($product['Code']); ?>" readonly>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>Product Name</label>
                                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($product['Nama']); ?>" readonly>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>Current Stock</label>
                                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($product['Stok']); ?>" readonly>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>Quantity to Add *</label>
                                                                <input type="number" name="quantity" class="form-control" min="1" required>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>Notes</label>
                                                                <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes about this movement"></textarea>
                                                            </div>
                                                            
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="fa fa-plus"></i> Add Stock
                                                            </button>
                                                            <a href="movement_inbound.php" class="btn btn-default">Cancel</a>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php } ?>
                                    <?php else: ?>
                                        <!-- Filter Form -->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <form method="get" action="movement_inbound.php" class="form-inline">
                                                    <input type="hidden" name="submenu" value="inbound">
                                                    <div class="form-group">
                                                        <label for="cabang">Warehouse: </label>
                                                        <select name="cabang" class="form-control input-sm">
                                                            <option value="">All</option>
                                                            <?php
                                                            $warehouse_query = mysqli_query($mysqli, "SELECT nama FROM list_warehouse ORDER BY nama ASC");
                                                            while ($wh = mysqli_fetch_assoc($warehouse_query)): ?>
                                                                <option value="<?php echo htmlspecialchars($wh['nama']); ?>" <?php echo ($cabang_filter == $wh['nama'] ? 'selected' : ''); ?>>
                                                                    <?php echo htmlspecialchars($wh['nama']); ?>
                                                                </option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary btn-sm" style="margin-left:10px;">
                                                        <i class="fa fa-filter"></i> Filter
                                                    </button>
                                                    <a href="movement_inbound.php?submenu=inbound" class="btn btn-default btn-sm" style="margin-left:10px;">
                                                        <i class="fa fa-times"></i> Clear
                                                    </a>
                                                </form>
                                            </div>
                                        </div>
                                        
                                        <div class="table-responsive" style="margin-top:20px;">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Product ID</th>
                                                        <th>Item Name</th>
                                                        <th>Current Stock</th>
                                                        <th>Unit</th>
                                                        <th>Warehouse</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $sql = "SELECT * FROM warehouse WHERE 1=1";
                                                    if ($cabang_filter != '') {
                                                        $sql .= " AND cabang = '$cabang_filter'";
                                                    }
                                                    $sql .= " ORDER BY Nama ASC";
                                                    
                                                    $hasil = $mysqli->query($sql);
                                                    if ($hasil && $hasil->num_rows > 0) {
                                                        while ($baris = $hasil->fetch_assoc()) {
                                                            echo "<tr>
                                                                <td>" . htmlspecialchars($baris['Code']) . "</td>
                                                                <td>" . htmlspecialchars($baris['Nama']) . "</td>
                                                                <td>" . htmlspecialchars($baris['Stok']) . "</td>
                                                                <td>" . htmlspecialchars($baris['Satuan']) . "</td>
                                                                <td>" . htmlspecialchars($baris['cabang']) . "</td>
                                                                <td>
                                                                    <a href='movement_inbound.php?submenu=inbound&product_id=" . htmlspecialchars($baris['Code']) . "' class='btn btn-success btn-xs'>
                                                                        <i class='fa fa-plus'></i> Add Stock
                                                                    </a>
                                                                </td>
                                                            </tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='6' class='text-center'>No products found</td></tr>";
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>

        <!-- JavaScript Libraries -->
        <script src="../js/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <script src="../js/AdminLTE/app.js" type="text/javascript"></script>
    </body>
</html>