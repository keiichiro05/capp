<?php
session_start();
include('../konekdb.php');

// Check login
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'] ?? null;

// Check module authorization
$stmt = $mysqli->prepare("SELECT COUNT(username) as jmluser FROM authorization WHERE username = ? AND modul = 'Warehouse'");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($jmluser);
$stmt->fetch();
$stmt->close();

if ($jmluser == 0) {
    header("Location: ../index.php");
    exit();
}

// Get employee data
$stmt = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$stmt->bind_param("s", $idpegawai);
$stmt->execute();
$result = $stmt->get_result();
$pegawai = $result->fetch_assoc();
$stmt->close();

// Add category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nama_kategori'])) {
    $nama_kategori = trim($_POST['nama_kategori']);
    if ($nama_kategori !== '') {
        $stmt = $mysqli->prepare("SELECT id FROM kategori WHERE nama_kategori = ?");
        $stmt->bind_param("s", $nama_kategori);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $_SESSION['message'] = "<div class='alert alert-danger'>Category already exists!</div>";
        } else {
            $stmt->close();
            $stmt = $mysqli->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
            $stmt->bind_param("s", $nama_kategori);
            if ($stmt->execute()) {
                $_SESSION['message'] = "<div class='alert alert-success'>New category added!</div>";
            } else {
                $_SESSION['message'] = "<div class='alert alert-danger'>Failed to add category.</div>";
            }
        }
        $stmt->close();
    }
    header("Location: product.php?submenu=category");
    exit();
}

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $delete_id = mysqli_real_escape_string($mysqli, $_POST['delete_id']);
    mysqli_query($mysqli, "DELETE FROM warehouse WHERE Code = '$delete_id'");
    if (mysqli_affected_rows($mysqli) > 0) {
        $_SESSION['message'] = "<div class='alert alert-success'>Item deleted successfully!</div>";
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger'>Error deleting item.</div>";
    }
    header("Location: product.php?submenu=all-products");
    exit();
} elseif (isset($_POST['edit_id'])) {
    // Update item
    $edit_id = (int)$_POST['edit_id'];
    $stok = (int)($_POST['stok'] ?? 0);
    
    if ($stok > 0) {
        $stmt = $mysqli->prepare("UPDATE warehouse SET Stok=? WHERE Code=?");
        $stmt->bind_param("ii", $stok, $edit_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "<div class='alert alert-success'>Stock updated successfully!</div>";
        } else {
            $_SESSION['message'] = "<div class='alert alert-danger'>Error updating stock.</div>";
        }
        $stmt->close();
    }
    header("Location: product.php?submenu=all-products");
    exit();
} elseif (isset($_POST['nama'])) {
    // Add new item
    $nama = trim($_POST['nama']);
    $stok = (int)($_POST['stok'] ?? 0);
    $kategori = trim($_POST['kategori'] ?? '');
    
    if ($nama !== '' && $stok > 0 && $kategori !== '') {
        // Check if item exists
        $stmt = $mysqli->prepare("SELECT Code FROM warehouse WHERE Nama=?");
        $stmt->bind_param("s", $nama);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->close();
            $stmt = $mysqli->prepare("UPDATE warehouse SET Stok=?, Kategori=? WHERE Nama=?");
            $stmt->bind_param("iss", $stok, $kategori, $nama);
            if ($stmt->execute()) {
                $_SESSION['message'] = "<div class='alert alert-success'>Item updated successfully!</div>";
            } else {
                $_SESSION['message'] = "<div class='alert alert-danger'>Error updating item.</div>";
            }
        } else {
            $stmt->close();
            $stmt = $mysqli->prepare("INSERT INTO warehouse (Nama, Stok, Kategori) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $nama, $stok, $kategori);
            if ($stmt->execute()) {
                $_SESSION['message'] = "<div class='alert alert-success'>New item added successfully!</div>";
            } else {
                $_SESSION['message'] = "<div class='alert alert-danger'>Error adding item.</div>";
            }
        }
        $stmt->close();
    }
    header("Location: product.php?submenu=all-products");
    exit();
}

// Get all categories for dropdown
$kategori_list = [];
$res = $mysqli->query("SELECT nama_kategori FROM kategori ORDER BY nama_kategori ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $kategori_list[] = $row['nama_kategori'];
    }
}

// Determine active submenu
$active_submenu = $_GET['submenu'] ?? 'all-products';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Products - Warehouse</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />
    <style>
        .submenu-content { padding: 20px 0; }
        .nav-pills { margin-bottom: 20px; }
        .btn-action { padding: 5px 10px; font-size: 12px; }
        .btn-edit { background-color: #f0ad4e; border-color: #eea236; color: white; }
        .btn-delete { background-color: #d9534f; border-color: #d43f3a; color: white; }
        .input-group { width: auto; display: inline-flex; }
        .is-invalid { border-color: #dc3545 !important; }
        
        /* Treeview styling */
        .sidebar-menu > li > a {
            position: relative;
            display: block;
            padding: 12px 5px 12px 15px;
        }
        .sidebar-menu li > a > .fa-angle-left {
            width: auto;
            height: auto;
            padding: 0;
            margin-right: 10px;
            margin-top: 3px;
            transition: transform 0.3s ease;
        }
        .sidebar-menu li.active > a > .fa-angle-left {
            transform: rotate(-90deg);
        }
        .treeview-menu {
            display: none;
            list-style: none;
            padding: 0;
            margin: 0;
            padding-left: 15px;
        }
        .treeview-menu > li {
            margin: 0;
        }
        .treeview-menu > li > a {
            padding: 8px 5px 8px 25px;
            display: block;
            font-size: 14px;
        }
        .treeview-menu > li > a > .fa {
            width: 20px;
        }
        .treeview-menu > li > a > .fa-circle-o {
            font-size: 10px;
        }
        .treeview-menu > li.active > a {
            font-weight: bold;
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        
        /* Table styling */
        .table-3d {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .table-3d th {
            background-color: #3c8dbc;
            color: white;
            text-align: left;
            padding: 12px;
        }
        .table-3d td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .table-3d tr:hover {
            background-color: #f5f5f5;
        }
        .barcode-img {
            height: 40px;
            width: auto;
        }
        .action-buttons {
            white-space: nowrap;
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
                                <?php echo htmlspecialchars($pegawai['Nama']) . " - " . htmlspecialchars($pegawai['Jabatan']) . " " . htmlspecialchars($pegawai['Departemen']); ?>
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
                <li>
                    <a href="movement.php">
                        <i class="fa fa-exchange"></i> <span>Movement</span>
                    </a>
                </li>
                <li class="treeview active">
                    <a href="#">
                        <i class="fa fa-list-alt"></i> <span>Products</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu" style="<?php echo in_array($active_submenu, ['all-products','add-products','category','unit']) ? 'display: block;' : ''; ?>">
                        <li class="<?php echo $active_submenu == 'all-products' ? 'active' : ''; ?>">
                            <a href="product.php?submenu=all-products"><i class="fa fa-folder"></i> All Products</a>
                        </li>
                        <li class="<?php echo $active_submenu == 'add-products' ? 'active' : ''; ?>">
                            <a href="product.php?submenu=add-products"><i class="fa fa-plus-square"></i> Add Products</a>
                        </li>
                        <li class="<?php echo $active_submenu == 'category' ? 'active' : ''; ?>">
                            <a href="product.php?submenu=category"><i class="fa fa-caret-square-o-right "></i> Category</a>
                        </li>
                        <li class="<?php echo $active_submenu == 'unit' ? 'active' : ''; ?>">
                            <a href="product.php?submenu=unit"><i class="fa fa-caret-square-o-right "></i> Unit</a>
                        </li>
                    </ul>
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
            <h1>Products Management
            <small><?php 
                $submenu_titles = [
                    'all-products' => 'All Products List',
                    'add-products' => 'Add Products',
                    'category' => 'Product Categories',
                    'unit' => 'Measurement Units'
                ];
                echo $submenu_titles[$active_submenu] ?? 'Products Management';
            ?></small>
            </h1>
        </section>
        <section class="content">
            <?php
            if (isset($_SESSION['message'])) {
                echo $_SESSION['message'];
                unset($_SESSION['message']);
            }
            ?>
            
            <div class="submenu-content">
                <?php if ($active_submenu == 'category'): ?>
                    <!-- Category Content -->
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title">Add Category</h3>
                        </div>
                        <div class="box-body">
                            <form method="post" class="form-inline">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Category Name" name="nama_kategori" required style="width: 300px;">
                                </div>
                                <button type="submit" class="btn btn-primary">Add</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Category List</h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Category Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
                                        $hasil = $mysqli->query($sql);
                                        while ($baris = $hasil->fetch_assoc()) {
                                            echo "<tr>
                                                <td>" . htmlspecialchars($baris['id']) . "</td>
                                                <td>" . htmlspecialchars($baris['nama_kategori']) . "</td>
                                                <td>
                                                    <a href='edit_product.php?id=" . htmlspecialchars($baris['id']) . "' class='btn btn-warning btn-sm'>Edit</a>
                                                    <a href='hapus_product.php?id=" . htmlspecialchars($baris['id']) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this category?\")'>Delete</a>
                                                </td>
                                            </tr>";
                                        }   
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                
                <?php elseif ($active_submenu == 'all-products'): ?>
            <div class="table-responsive">
                <table class="table table-3d">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Barcode</th>
                            <th>Name</th>
                            <th>Stock</th>
                            <th>Category</th>
                            <th>Supplier</th>
                            <th>Unit</th>
                            <th>Minimum</th>
                            <th>Warehouse</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $orders = mysqli_query($mysqli, "
                            SELECT w.*, s.Nama as supplier_name 
                            FROM warehouse w
                            LEFT JOIN supplier s ON w.Supplier = s.Nama
                            ORDER BY w.no DESC
                        ");
                        
                        while ($order = mysqli_fetch_assoc($orders)) {
                            echo "<tr>
                                    <td>".htmlspecialchars($order['no'])."</td>
                                      <td class='barcode-cell'>
                                        <div class='barcode-container'>
                                            <img src='https://barcode.tec-it.com/barcode.ashx?data=".urlencode($order['Code'])."&code=Code128&dpi=96' 
                                                class='barcode-img' 
                                                alt='Barcode ".htmlspecialchars($order['Code'])."'>
                                        </div>
                                    </td>
                                    <td>".htmlspecialchars($order['Nama'])."</td>
                                    <td>".htmlspecialchars($order['Stok'])."</td>
                                    <td>".htmlspecialchars($order['Kategori'])."</td>
                                    <td>".htmlspecialchars($order['supplier_name'])."</td>
                                    <td>".htmlspecialchars($order['Satuan'])."</td>
                                    <td>".htmlspecialchars($order['reorder_level'])."</td>
                                    <td>".htmlspecialchars($order['cabang'])."</td>
                                    <td>".htmlspecialchars(date('d M Y H:i', strtotime($order['Tanggal'])))."</td>
                                  
                                    <td class='action-buttons'>";
                            
                            // View button
                            echo "<a href='details.php?code=".urlencode($order['Code'])."' class='btn-action btn-view' title='View Details'><i class='fa fa-eye'></i></a>";
                            // Delete button
                            echo "<form method='post' style='display:inline;'>
                                    <input type='hidden' name='delete_id' value='".$order['no']."'>
                                    <button type='submit' class='btn-action btn-delete' title='Delete Order'><i class='fa fa-trash-o'></i></button>
                                </form>";
                            
                            echo "</td>
                                </tr>";
                        }
                ?>
            </tbody>
        </table>
    </div>
                <?php elseif ($active_submenu == 'add-products'): ?>
                    <!-- Add Products Content -->
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title">Add New Product</h3>
                        </div>
                        <div class="box-body">
                            <form method="post" action="new_request.php" id="productForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Warehouse</label>
                                            <select name="warehouse2" class="form-control" required>
                                                <option value="">Select Warehouse</option>
                                                <?php
                                                $query = $mysqli->query("SELECT nama FROM list_warehouse");
                                                while ($row = $query->fetch_assoc()) {
                                                    echo "<option value=\"".htmlspecialchars($row['nama'])."\">".htmlspecialchars($row['nama'])."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Product ID</label>
                                            <input type="text" name="code2" class="form-control" placeholder="Enter Product ID" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="text" name="nama2" class="form-control" placeholder="Enter Name" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Category</label>
                                            <select name="kategori2" class="form-control" required>
                                                <option value="">Select Category</option>
                                                <?php
                                                $query = $mysqli->query("SELECT nama_kategori FROM kategori");
                                                while ($row = $query->fetch_assoc()) {
                                                    echo "<option value=\"".htmlspecialchars($row['nama_kategori'])."\">".htmlspecialchars($row['nama_kategori'])."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Stock</label>
                                            <input type="number" name="Stok" class="form-control" placeholder="Enter quantity" min="1" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Unit</label>
                                            <input type="text" name="satuan" class="form-control" placeholder="Enter Unit" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Reorder Level</label>
                                            <input type="number" name="reorder-level" class="form-control" placeholder="Enter Reorder Level" min="5" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Supplier</label>
                                            <select name="supplier" class="form-control" required>
                                                <option value="">Select Supplier</option>
                                                <?php
                                                $query = $mysqli->query("SELECT Nama FROM supplier");
                                                while ($row = $query->fetch_assoc()) {
                                                    echo "<option value=\"".htmlspecialchars($row['Nama'])."\">".htmlspecialchars($row['Nama'])."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>PIC</label>
                                    <select name="pic" class="form-control" required>
                                        <option value="">Select PIC</option>
                                        <?php
                                        $query = $mysqli->query("SELECT pic FROM list_warehouse");
                                        while ($row = $query->fetch_assoc()) {
                                            echo "<option value=\"".htmlspecialchars($row['pic'])."\">".htmlspecialchars($row['pic'])."</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-paper-plane"></i> Submit Product
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                
                <?php elseif ($active_submenu == 'unit'): ?>
                    <!-- Unit Content -->
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title">Measurement Units</h3>
                        </div>
                        <div class="box-body">
                            <form method="post" class="form-inline">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Unit Name" name="nama_unit" required style="width: 300px;">
                                </div>
                                <button type="submit" class="btn btn-primary">Add</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Units List</h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Unit Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM satuan ORDER BY nama_satuan ASC";
                                        $hasil = $mysqli->query($sql);
                                        while ($baris = $hasil->fetch_assoc()) {
                                            echo "<tr>
                                                <td>" . htmlspecialchars($baris['id']) . "</td>
                                                <td>" . htmlspecialchars($baris['nama_satuan']) . "</td>
                                                <td>
                                                    <a href='edit_product.php?id=" . htmlspecialchars($baris['id']) . "' class='btn btn-warning btn-sm'>Edit</a>
                                                    <form method='post' style='display:inline-block; margin-left:5px;'>
                                                        <input type='hidden' name='delete_id' value='" . htmlspecialchars($baris['id']) . "'>
                                                        <button type='submit' class='btn btn-danger btn-sm'>Delete</button>
                                                    </form>
                                                </td>
                                            </tr>";
                                        }   
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </aside>
</div>
<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js" type="text/javascript"></script>
<script src="../js/AdminLTE/app.js" type="text/javascript"></script>
<script>
$(document).ready(function() {
    // Initialize sidebar treeview
    $('.sidebar-menu').tree();
    
    // Highlight active menu based on URL parameter
    var urlParams = new URLSearchParams(window.location.search);
    var activeSubmenu = urlParams.get('submenu') || 'all-products';
    
    // Automatically expand the Products menu if on product.php
    if (window.location.pathname.includes('product.php')) {
        $('.sidebar-menu li.treeview').addClass('active');
        $('.treeview-menu').show();
    }
    
    // Highlight the active submenu item
    $('.treeview-menu li').removeClass('active');
    $('.treeview-menu li a').each(function() {
        if (this.href.includes(activeSubmenu)) {
            $(this).parent().addClass('active');
        }
    });
    
    // Confirm before deleting - using event delegation
    $(document).on('submit', 'form', function(e) {
        if ($(this).find('button[type=submit].btn-danger').length) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        }
    });
    
    // Form validation for product form
    $('#productForm').on('submit', function(e) {
        var isValid = true;
        var firstInvalid = null;
        
        $(this).find('input[required], select[required]').each(function() {
            if ($(this).val() === '') {
                isValid = false;
                $(this).addClass('is-invalid');
                if (!firstInvalid) {
                    firstInvalid = this;
                }
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields!');
            if (firstInvalid) {
                $(firstInvalid).focus();
            }
            return false;
        }
        return true;
    });
    
    // Handle view/edit/download buttons
    $(document).on('click', '.btn-view', function() {
        var no = $(this).closest('tr').find('td:first').text();
        window.location.href = 'view_product.php?no=' + no;
    });
    
    $(document).on('click', '.btn-edit', function() {
        var no = $(this).closest('tr').find('td:first').text();
        window.location.href = 'edit_product.php?no=' + no;
    });
    
   $(document).on('click', '.btn-download', function() {
    var code = $(this).data('code');
    var url = 'https://barcode.tec-it.com/barcode.ashx?data=' + encodeURIComponent(code) + '&code=Code128&dpi=150&download=true';
    window.open(url, '_blank');
});
});
</script>
</body>
</html>