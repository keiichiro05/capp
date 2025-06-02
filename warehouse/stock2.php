<?php
include('../konekdb.php');
session_start();
$username = $_SESSION['username'] ?? null;
$idpegawai = $_SESSION['idpegawai'] ?? null;

if (!isset($_SESSION['username'])) {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Warehouse Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <style>
        /* Custom styles for better aesthetics and overrides */
        .content-header h1 {
            font-weight: 600;
        }
        .info-box {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            border-radius: .25rem;
        }
        .nav-tabs-custom .nav-item .nav-link {
            font-weight: 600;
            color: #444; /* Default tab link color */
        }
        .nav-tabs-custom .nav-item .nav-link.active {
            color: #ffffff; /* Active tab text color */
            background-color: #007bff; /* Primary blue for active tab */
            border-color: #007bff;
        }
        .card-header-primary {
            background-color: #007bff; /* Bootstrap primary blue */
            color: #fff;
        }
        .card-header-danger {
            background-color: #dc3545; /* Bootstrap danger red */
            color: #fff;
        }
        .table-responsive {
            margin-top: 15px;
        }
        .history-table th, .history-table td {
            vertical-align: middle;
        }
        .badge-completed {
            background-color: #28a745; /* Bootstrap success green */
            color: #fff;
            padding: .4em .6em;
            border-radius: .25rem;
        }
        .badge-pending {
            background-color: #ffc107; /* Bootstrap warning yellow */
            color: #212529;
            padding: .4em .6em;
            border-radius: .25rem;
        }
        .user-panel .image img {
            height: auto; /* Maintain aspect ratio */
            width: 2.1rem; /* Adjust size as needed */
        }
        /* Further adjust for responsiveness */
        @media (max-width: 767.98px) {
            .nav-tabs-custom .nav-tabs {
                flex-direction: column;
            }
            .nav-tabs-custom .nav-item {
                width: 100%;
                text-align: center;
            }
            .nav-tabs-custom .nav-item .nav-link {
                border-radius: .25rem; /* Rounded corners for stacked tabs */
                margin-bottom: 5px;
            }
            .nav-tabs-custom .nav-item .nav-link.active {
                border-top: none; /* Remove top border for active stacked tab */
                border-left: 1px solid #007bff; /* Add left border to indicate active */
            }
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="index.php" class="nav-link">Home</a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <img src="img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="user-image img-circle elevation-2" alt="User Image">
                        <span class="d-none d-md-inline"><?php echo htmlspecialchars($username); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <li class="user-header bg-primary">
                            <img src="img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="img-circle elevation-2" alt="User Image">
                            <p>
                                <?php echo htmlspecialchars($pegawai['Nama']) . " - " . htmlspecialchars($pegawai['Jabatan']); ?>
                                <small>Member since <?php echo date('M. Y', strtotime($pegawai['Tanggal_Masuk'])); ?></small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <a href="profil.php" class="btn btn-default btn-flat">Profile</a>
                            <a href="logout.php" class="btn btn-default btn-flat float-right">Sign out</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="index.php" class="brand-link">
                <img src="../img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">U-PSN</span>
            </a>

            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block"><?php echo htmlspecialchars($username); ?></a>
                    </div>
                </div>

                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="index.php" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item menu-open">
                            <a href="stock.php" class="nav-link active">
                                <i class="nav-icon fas fa-exchange-alt"></i>
                                <p>Stock Transfer</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="product.php" class="nav-link">
                                <i class="nav-icon fas fa-boxes"></i>
                                <p>Products</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="order.php" class="nav-link">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p>Requests</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="mailbox.php" class="nav-link">
                                <i class="nav-icon fas fa-envelope"></i>
                                <p>Mailbox</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Sign Out</p>
                            </a>
                        </li>
                    </ul>
                </nav>
                </div>
            </aside>

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Inventory Management</h1>
                        </div><div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item active">Inventory</li>
                            </ol>
                        </div></div></div></div>
            <section class="content">
                <div class="container-fluid">
                    <div class="card card-primary card-outline card-outline-tabs">
                        <div class="card-header p-0 border-bottom-0">
                            <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $active_tab == 'inbound' ? 'active' : ''; ?>" 
                                       href="?tab=inbound" role="tab" aria-controls="inbound" aria-selected="true">
                                        <i class="fas fa-download mr-1"></i> Inbound
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $active_tab == 'outbound' ? 'active' : ''; ?>" 
                                       href="?tab=outbound" role="tab" aria-controls="outbound" aria-selected="false">
                                        <i class="fas fa-upload mr-1"></i> Outbound
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="custom-tabs-four-tabContent">
                                <div class="tab-pane fade <?php echo $active_tab == 'inbound' ? 'show active' : ''; ?>" id="inbound" role="tabpanel" aria-labelledby="inbound-tab">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card card-primary">
                                                <div class="card-header card-header-primary">
                                                    <h3 class="card-title"><i class="fas fa-download mr-1"></i> Record Inbound</h3>
                                                </div>
                                                <form method="post" action="process_inbound.php">
                                                    <div class="card-body">
                                                        <div class="form-group">
                                                            <label for="itemNameInbound">Item Name</label>
                                                            <input type="text" class="form-control" id="itemNameInbound" name="Nama" placeholder="Enter item name" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="quantityInbound">Quantity</label>
                                                            <input type="number" class="form-control" id="quantityInbound" name="Stok" min="1" placeholder="Enter quantity" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="categoryInbound">Category</label>
                                                            <input type="text" class="form-control" id="categoryInbound" name="kategori" placeholder="Enter category" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="supplierInbound">Supplier</label>
                                                            <input type="text" class="form-control" id="supplierInbound" name="supplier" placeholder="Enter supplier name" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="poNumberInbound">Purchase Order Number</label>
                                                            <input type="text" class="form-control" id="poNumberInbound" name="po_number" placeholder="Enter PO number (optional)">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="notesInbound">Notes</label>
                                                            <textarea class="form-control" id="notesInbound" name="keterangan" rows="3" placeholder="Additional notes..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <button type="submit" name="inbound" class="btn btn-primary">
                                                            <i class="fas fa-save mr-1"></i> Record Inbound
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="card card-info">
                                                <div class="card-header">
                                                    <h3 class="card-title"><i class="fas fa-history mr-1"></i> Recent Inbound History</h3>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped history-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Date</th>
                                                                    <th>Item</th>
                                                                    <th>Qty</th>
                                                                    <th>Supplier</th>
                                                                    <th>Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                $sql = "SELECT i.*, p.Nama as penerima
                                                                        FROM inbound_log i
                                                                        JOIN pegawai p ON i.id_pegawai = p.id_pegawai
                                                                        ORDER BY i.tanggal DESC LIMIT 5";
                                                                $hasil = $mysqli->query($sql);
                                                                if ($hasil && $hasil->num_rows > 0) {
                                                                    while ($log = $hasil->fetch_assoc()): ?>
                                                                        <tr>
                                                                            <td><?php echo date('d/m/Y', strtotime($log['tanggal'])); ?></td>
                                                                            <td><?php echo htmlspecialchars($log['nama_barang']); ?></td>
                                                                            <td><?php echo $log['jumlah']; ?></td>
                                                                            <td><?php echo htmlspecialchars($log['supplier']); ?></td>
                                                                            <td><span class="badge badge-completed">Completed</span></td>
                                                                        </tr>
                                                                    <?php endwhile;
                                                                } else {
                                                                    echo '<tr><td colspan="5" class="text-center">No inbound history found.</td></tr>';
                                                                }
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="card-footer text-center">
                                                    <a href="inbound_history.php" class="btn btn-outline-info btn-sm">
                                                        <i class="fas fa-eye mr-1"></i> View Full History
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade <?php echo $active_tab == 'outbound' ? 'show active' : ''; ?>" id="outbound" role="tabpanel" aria-labelledby="outbound-tab">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card card-danger">
                                                <div class="card-header card-header-danger">
                                                    <h3 class="card-title"><i class="fas fa-upload mr-1"></i> Record Outbound</h3>
                                                </div>
                                                <form method="post" action="process_outbound.php">
                                                    <div class="card-body">
                                                        <div class="form-group">
                                                            <label for="selectItemOutbound">Select Item</label>
                                                            <select class="form-control" id="selectItemOutbound" name="id_barang" required>
                                                                <option value="">-- Select Item --</option>
                                                                <?php
                                                                $sql = "SELECT id_barang, Nama, Stok FROM warehouse WHERE Stok > 0 ORDER BY Nama";
                                                                $hasil = $mysqli->query($sql);
                                                                if ($hasil && $hasil->num_rows > 0) {
                                                                    while ($baris = $hasil->fetch_assoc()): ?>
                                                                        <option value="<?php echo htmlspecialchars($baris['id_barang']); ?>">
                                                                            <?php echo htmlspecialchars($baris['Nama']) . " (Stock: " . $baris['Stok'] . ")"; ?>
                                                                        </option>
                                                                    <?php endwhile;
                                                                } else {
                                                                    echo '<option value="" disabled>No items available for outbound.</option>';
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="quantityOutbound">Quantity</label>
                                                            <input type="number" class="form-control" id="quantityOutbound" name="jumlah" min="1" placeholder="Enter quantity" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="destinationOutbound">Destination</label>
                                                            <select class="form-control" id="destinationOutbound" name="tujuan" required>
                                                                <option value="">-- Select Destination --</option>
                                                                <option value="Production">Production</option>
                                                                <option value="Shipping">Shipping</option>
                                                                <option value="Maintenance">Maintenance</option>
                                                                <option value="Other">Other</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="notesOutbound">Purpose/Notes</label>
                                                            <textarea class="form-control" id="notesOutbound" name="keterangan" rows="3" placeholder="Purpose or additional notes..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <button type="submit" name="outbound" class="btn btn-danger">
                                                            <i class="fas fa-save mr-1"></i> Record Outbound
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="card card-info">
                                                <div class="card-header">
                                                    <h3 class="card-title"><i class="fas fa-history mr-1"></i> Recent Outbound History</h3>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped history-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Date</th>
                                                                    <th>Item</th>
                                                                    <th>Qty</th>
                                                                    <th>Destination</th>
                                                                    <th>Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                $sql = "SELECT o.*, p.Nama as pegawai_nama
                                                                        FROM outbound_log o
                                                                        JOIN pegawai p ON o.id_pegawai = p.id_pegawai
                                                                        ORDER BY o.tanggal DESC LIMIT 5";
                                                                $hasil = $mysqli->query($sql);
                                                                if ($hasil && $hasil->num_rows > 0) {
                                                                    while ($log = $hasil->fetch_assoc()): ?>
                                                                        <tr>
                                                                            <td><?php echo date('d/m/Y', strtotime($log['tanggal'])); ?></td>
                                                                            <td><?php echo htmlspecialchars($log['nama_barang']); ?></td>
                                                                            <td><?php echo $log['jumlah']; ?></td>
                                                                            <td><?php echo htmlspecialchars($log['tujuan']); ?></td>
                                                                            <td><span class="badge badge-completed">Completed</span></td>
                                                                        </tr>
                                                                    <?php endwhile;
                                                                } else {
                                                                    echo '<tr><td colspan="5" class="text-center">No outbound history found.</td></tr>';
                                                                }
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="card-footer text-center">
                                                    <a href="outbound_history.php" class="btn btn-outline-info btn-sm">
                                                        <i class="fas fa-eye mr-1"></i> View Full History
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div></section>
            </div>
        <aside class="control-sidebar control-sidebar-dark">
            </aside>
        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Version</b> 3.1.0
            </div>
            <strong>Copyright &copy; 2014-<?php echo date('Y'); ?> <a href="https://adminlte.io">AdminLTE.io</a>.</strong> All rights reserved.
        </footer>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/js/adminlte.min.js"></script>
    <script>
        $(function () {
            // This is to ensure the active tab remains active on page load via URL parameter
            var activeTab = "<?php echo $active_tab; ?>";
            $('.nav-tabs a[href="#' + activeTab + '"]').tab('show');
        });
    </script>
</body>
</html>