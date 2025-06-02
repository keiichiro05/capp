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

// Set active tab
$active_tab = $_GET['tab'] ?? 'inbound';

// Get warehouse list
$warehouses = mysqli_query($mysqli, "SELECT * FROM list_warehouse ORDER BY nama");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Warehouse Management System</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />
    <link href="../css/select2.min.css" rel="stylesheet" type="text/css" />
    <style>
        /* Custom Tab Styles */
        .nav-tabs-custom {
            margin-bottom: 20px;
            background: #fff;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
            border-radius: 3px;
        }
        
        .nav-tabs-custom > .nav-tabs {
            margin: 0;
            border-bottom: 1px solid #f4f4f4;
            border-top-right-radius: 3px;
            border-top-left-radius: 3px;
        }
        
        .nav-tabs-custom > .nav-tabs > li {
            border-top: 3px solid transparent;
            margin-bottom: -1px;
        }
        
        .nav-tabs-custom > .nav-tabs > li > a {
            color: #444;
            border-radius: 0;
            padding: 12px 20px;
            font-weight: 600;
        }
        
        .nav-tabs-custom > .nav-tabs > li > a:hover {
            background-color: #f9f9f9;
        }
        
        .nav-tabs-custom > .nav-tabs > li.active {
            border-top-color: #3c8dbc;
        }
        
        .nav-tabs-custom > .nav-tabs > li.active > a {
            background-color: #fff;
            color: #444;
            border-left: 1px solid #f4f4f4;
            border-right: 1px solid #f4f4f4;
        }
        
        .nav-tabs-custom > .tab-content {
            background: #fff;
            padding: 15px;
            border-bottom-right-radius: 3px;
            border-bottom-left-radius: 3px;
        }
        
        /* Form Styles */
        .form-box {
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .form-box .header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: #fff;
            padding: 15px;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }
        
        .form-box .body {
            padding: 20px;
        }
        
        /* History Table Styles */
        .history-box {
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }
        
        .history-box .header {
            background: #f9f9f9;
            padding: 15px;
            border-bottom: 1px solid #f4f4f4;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }
        
        .history-box .body {
            padding: 0;
        }
        
        .history-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .history-table th {
            background: #f5f5f5;
            padding: 10px 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid #eee;
        }
        
        .history-table td {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .history-table tr:hover {
            background-color: #f9f9f9;
        }
        
        /* Status Badges */
        .badge-pending {
            background-color: #f39c12;
        }
        
        .badge-completed {
            background-color: #00a65a;
        }
        
        /* Form Improvements */
        .form-group {
            margin-bottom: 15px;
        }

        .form-control {
            border-radius: 3px;
            box-shadow: none;
            border: 1px solid #ddd;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52,152,219,0.2);
        }

        .select2-container .select2-selection--single {
            height: 34px;
            border: 1px solid #ddd;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 32px;
        }

        .help-block {
            font-size: 12px;
            color: #777;
            margin-top: 5px;
        }

        .btn-block {
            padding: 10px;
            font-size: 15px;
        }

        /* Required field indicator */
        label[required]:after {
            content: " *";
            color: #e74c3c;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .nav-tabs-custom > .nav-tabs > li {
                float: none;
                margin-bottom: 0;
            }
            
            .nav-tabs-custom > .nav-tabs > li > a {
                border-radius: 0;
            }
            
            .form-box, .history-box {
                margin-bottom: 15px;
            }
            
            .row {
                margin-left: -5px;
                margin-right: -5px;
            }
            
            .col-md-6, .col-md-12 {
                padding-left: 5px;
                padding-right: 5px;
            }
        }
        
        /* Loading spinner */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            -webkit-animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { -webkit-transform: rotate(360deg); }
        }
        
        @-webkit-keyframes spin {
            to { -webkit-transform: rotate(360deg); }
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
                    <li class="active">
                        <a href="stock.php">
                            <i class="fa fa-exchange"></i> <span>Stock Transfer</span>
                        </a>
                    </li>
                    <li>
                        <a href="product.php">
                            <i class="fa fa-list"></i> <span>Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="order.php">
                                <i class="fa fa-th"></i> <span>Request</span>
                            </a>
                        </li>
                        <li>
                            <a href="history_order.php">
                                <i class="fa fa-archive"></i> <span>Request History</span>
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
                    Inventory Management
                    <small>Track your warehouse movements</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Inventory</li>
                </ol>
            </section>

            <section class="content">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="<?php echo $active_tab == 'inbound' ? 'active' : ''; ?>">
                            <a href="?tab=inbound"><i class="fa fa-download"></i> Inbound</a>
                        </li>
                        <li class="<?php echo $active_tab == 'outbound' ? 'active' : ''; ?>">
                            <a href="?tab=outbound"><i class="fa fa-upload"></i> Outbound</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content">
                        <!-- Inbound Tab Content -->
                        <div class="tab-pane <?php echo $active_tab == 'inbound' ? 'active' : ''; ?>" id="inbound">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-box">
                                        <div class="header">
                                            <h3 class="box-title"><i class="fa fa-download"></i> Record Inbound</h3>
                                        </div>
                                        <div class="body">
                                            <form method="post" action="process_inbound.php">
                                                <div class="form-group">
                                                    <label required>Warehouse *</label>
                                                    <select class="form-control select2" name="warehouse" required>
                                                        <option value="">-- Select Warehouse --</option>
                                                        <?php 
                                                        mysqli_data_seek($warehouses, 0); // Reset pointer
                                                        while($wh = mysqli_fetch_assoc($warehouses)): ?>
                                                            <option value="<?php echo htmlspecialchars($wh['id']); ?>">
                                                                <?php echo htmlspecialchars($wh['nama']); ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label required>Item Name</label>
                                                    <input type="text" class="form-control" name="nama_barang" placeholder="Enter item name" required>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label required>Quantity</label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" name="jumlah" min="1" placeholder="0" required>
                                                                <span class="input-group-addon">units</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Unit Type</label>
                                                            <select class="form-control" name="unit_type">
                                                                <option value="Pieces">Pieces</option>
                                                                <option value="Boxes">Boxes</option>
                                                                <option value="Pallets">Pallets</option>
                                                                <option value="Kg">Kilograms</option>
                                                                <option value="Liters">Liters</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label required>Supplier</label>
                                                    <input type="text" class="form-control" name="supplier" placeholder="Supplier company name" required>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Purchase Order Number</label>
                                                            <input type="text" class="form-control" name="po_number" placeholder="PO-123456">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Expected Arrival</label>
                                                            <input type="date" class="form-control" name="expected_arrival">
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label>Notes</label>
                                                    <textarea class="form-control" name="keterangan" rows="3" placeholder="Any additional information..."></textarea>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <button type="submit" name="inbound" class="btn btn-primary btn-block">
                                                        <i class="fa fa-save"></i> Record Inbound
                                                    </button>
                                                </div>
                                                <small class="text-muted">* Required fields</small>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="history-box">
                                        <div class="header">
                                            <h3 class="box-title"><i class="fa fa-history"></i> Recent Inbound History</h3>
                                        </div>
                                        <div class="body">
                                            <div class="table-responsive">
                                                <table class="history-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Warehouse</th>
                                                            <th>Item</th>
                                                            <th>Qty</th>
                                                            <th>Supplier</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $sql = "SELECT i.*, p.Nama as penerima, w.nama as warehouse_name 
                                                                FROM inbound_log i 
                                                                JOIN pegawai p ON i.id_pegawai = p.id_pegawai 
                                                                JOIN list_warehouse w ON i.id = w.id
                                                                ORDER BY i.tanggal DESC LIMIT 5";
                                                        $hasil = $mysqli->query($sql);
                                                        while ($log = $hasil->fetch_assoc()): ?>
                                                            <tr>
                                                                <td><?php echo date('d/m/Y', strtotime($log['tanggal'])); ?></td>
                                                                <td><?php echo htmlspecialchars($log['warehouse_name']); ?></td>
                                                                <td><?php echo htmlspecialchars($log['nama_barang']); ?></td>
                                                                <td><?php echo $log['jumlah']; ?></td>
                                                                <td><?php echo htmlspecialchars($log['supplier']); ?></td>
                                                                <td>
                                                                    <span class="badge badge-completed">Completed</span>
                                                                </td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="box-footer text-center">
                                                <a href="inbound_history.php" class="btn btn-default btn-sm">
                                                    <i class="fa fa-eye"></i> View Full History
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Outbound Tab Content -->
                        <div class="tab-pane <?php echo $active_tab == 'outbound' ? 'active' : ''; ?>" id="outbound">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-box">
                                        <div class="header" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                                            <h3 class="box-title"><i class="fa fa-upload"></i> Record Outbound</h3>
                                        </div>
                                        <div class="body">
                                            <form method="post" action="process_outbound.php" id="outbound-form">
                                                <div class="form-group">
                                                    <label required>Source Warehouse *</label>
                                                    <select class="form-control select2" name="source_warehouse" id="source-warehouse" required>
                                                        <option value="">-- Select Warehouse --</option>
                                                        <?php 
                                                        mysqli_data_seek($warehouses, 0); // Reset pointer
                                                        while($wh = mysqli_fetch_assoc($warehouses)): ?>
                                                            <option value="<?php echo htmlspecialchars($wh['id']); ?>">
                                                                <?php echo htmlspecialchars($wh['nama']); ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label required>Select Item *</label>
                                                    <select class="form-control select2" name="id_barang" id="item-select" required style="width: 100%;" disabled>
                                                        <option value="">-- Select Warehouse First --</option>
                                                    </select>
                                                    <small class="text-muted help-block">Only items with available stock are shown</small>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label required>Quantity</label>
                                                            <input type="number" class="form-control" name="jumlah" min="1" id="outbound-qty" 
                                                                   placeholder="0" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Current Stock</label>
                                                            <input type="text" class="form-control" id="current-stock" readonly>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label required>Destination Warehouse *</label>
                                                    <select class="form-control select2" name="destination_warehouse" required>
                                                        <option value="">-- Select Warehouse --</option>
                                                        <?php 
                                                        mysqli_data_seek($warehouses, 0); // Reset pointer
                                                        while($wh = mysqli_fetch_assoc($warehouses)): ?>
                                                            <option value="<?php echo htmlspecialchars($wh['id']); ?>">
                                                                <?php echo htmlspecialchars($wh['nama']); ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label>Recipient/Department</label>
                                                    <input type="text" class="form-control" name="recipient" placeholder="Person or department receiving">
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label required>Purpose/Notes</label>
                                                    <textarea class="form-control" name="keterangan" rows="3" 
                                                              placeholder="Reason for outbound movement..." required></textarea>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <button type="submit" name="outbound" class="btn btn-danger btn-block" id="outbound-submit">
                                                        <i class="fa fa-save"></i> Record Outbound
                                                    </button>
                                                </div>
                                                <small class="text-muted">* Required fields</small>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="history-box">
                                        <div class="header">
                                            <h3 class="box-title"><i class="fa fa-history"></i> Recent Outbound History</h3>
                                        </div>
                                        <div class="body">
                                            <div class="table-responsive">
                                                <table class="history-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>From</th>
                                                            <th>To</th>
                                                            <th>Item</th>
                                                            <th>Qty</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $sql = "SELECT o.*, p.Nama as pegawai_nama, 
                                                               sw.nama as source_warehouse, dw.nama as dest_warehouse
                                                                FROM outbound_log o 
                                                                JOIN pegawai p ON o.id_pegawai = p.id_pegawai 
                                                                JOIN list_warehouse sw ON o.source_warehouse = sw.id
                                                                JOIN list_warehouse dw ON o.destination_warehouse = dw.id
                                                                ORDER BY o.tanggal DESC LIMIT 5";
                                                        $hasil = $mysqli->query($sql);
                                                        while ($log = $hasil->fetch_assoc()): ?>
                                                            <tr>
                                                                <td><?php echo date('d/m/Y', strtotime($log['tanggal'])); ?></td>
                                                                <td><?php echo htmlspecialchars($log['source_warehouse']); ?></td>
                                                                <td><?php echo htmlspecialchars($log['dest_warehouse']); ?></td>
                                                                <td><?php echo htmlspecialchars($log['nama_barang']); ?></td>
                                                                <td><?php echo $log['jumlah']; ?></td>
                                                                <td>
                                                                    <span class="badge badge-completed">Completed</span>
                                                                </td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="box-footer text-center">
                                                <a href="outbound_history.php" class="btn btn-default btn-sm">
                                                    <i class="fa fa-eye"></i> View Full History
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>
    
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../js/AdminLTE/app.js" type="text/javascript"></script>
    <script src="../js/select2.min.js" type="text/javascript"></script>
    <script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2();
        
        // Warehouse selection change handler for outbound
        $('#source-warehouse').change(function() {
            var warehouseId = $(this).val();
            var itemSelect = $('#item-select');
            
            if (warehouseId) {
                // Enable item select and load items
                itemSelect.prop('disabled', false).empty().append('<option value="">-- Loading Items --</option>');
                
                // Show loading state
                var submitBtn = $('#outbound-submit');
                var originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<span class="loading-spinner"></span> Loading items...');
                
                // AJAX request to get items for selected warehouse
                $.ajax({
                    url: 'get_warehouse_items.php',
                    type: 'GET',
                    data: { warehouse_id: warehouseId },
                    dataType: 'json',
                    success: function(data) {
                        itemSelect.empty().append('<option value="">-- Select Item --</option>');
                        $.each(data, function(index, item) {
                            itemSelect.append(
                                $('<option></option>')
                                    .val(item.id_barang)
                                    .text(item.Nama + ' (Stock: ' + item.Stok + ')')
                                    .data('stock', item.Stok)
                            );
                        });
                        submitBtn.prop('disabled', false).html(originalText);
                    },
                    error: function() {
                        itemSelect.empty().append('<option value="">-- Error loading items --</option>');
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            } else {
                // Disable item select if no warehouse selected
                itemSelect.prop('disabled', true).empty().append('<option value="">-- Select Warehouse First --</option>');
                $('#current-stock').val('');
                $('#outbound-qty').val('').removeAttr('max');
            }
        });
        
        // Item selection change handler for outbound
        $('#item-select').change(function() {
            var selected = $(this).find('option:selected');
            var stock = selected.data('stock');
            $('#current-stock').val(stock || '0');
            $('#outbound-qty').attr('max', stock || '1').val('');
        });
        
        // Form validation
        $('#outbound-form').submit(function(e) {
            var qty = parseInt($('#outbound-qty').val());
            var stock = parseInt($('#current-stock').val());
            var sourceWh = $('#source-warehouse').val();
            var destWh = $('select[name="destination_warehouse"]').val();
            
            if (sourceWh === destWh) {
                alert('Source and destination warehouses cannot be the same!');
                return false;
            }
            
            if (isNaN(qty)) {
                alert('Please enter a valid quantity!');
                return false;
            }
            
            if (qty > stock) {
                alert('Quantity cannot exceed available stock!');
                return false;
            }
            
            if (qty <= 0) {
                alert('Quantity must be greater than zero!');
                return false;
            }
            
            return true;
        });
        
        // Date picker for expected arrival
        $('input[name="expected_arrival"]').datepicker({
            format: 'yyyy-mm-dd',
            todayHighlight: true,
            autoclose: true
        });
    });
    </script>
</body>
</html>