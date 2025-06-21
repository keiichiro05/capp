<!DOCTYPE html>
<?php
include('../konekdb.php');
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['username']) || !isset($_SESSION['idpegawai'])) {
    header("location:../index.php");
    exit();
}

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

$cekuser = mysqli_query($mysqli, "SELECT count(username) as jmluser FROM authorization WHERE username = '$username' AND modul = 'Warehouse'");
if (!$cekuser) {
    die("Error checking user authorization: " . mysqli_error($mysqli));
}
$user = mysqli_fetch_array($cekuser);

$getpegawai = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$idpegawai'");
if (!$getpegawai) {
    die("Error fetching employee data: " . mysqli_error($mysqli));
}
$pegawai = mysqli_fetch_array($getpegawai);

if ($user['jmluser'] == "0") {
    header("location:../index.php");
    exit();
}

// Handle delete operation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = mysqli_real_escape_string($mysqli, $_POST['delete_id']);
    $deleteQuery = "DELETE FROM warehouse WHERE no = '$delete_id'";
    if (mysqli_query($mysqli, $deleteQuery)) {
        $_SESSION['message'] = "<div class='alert alert-success'>Data deleted successfully!</div>";
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger'>Failed to delete data. Please try again.</div>";
    }
    header("Location: new_request.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_id'])) {
    // Validate and sanitize inputs
    $code = isset($_POST['code2']) ? mysqli_real_escape_string($mysqli, $_POST['code2']) : '';
    $nama = isset($_POST['nama2']) ? mysqli_real_escape_string($mysqli, $_POST['nama2']) : '';
    $kategori = isset($_POST['kategori2']) ? mysqli_real_escape_string($mysqli, $_POST['kategori2']) : '';
    $Stok = isset($_POST['Stok']) ? intval($_POST['Stok']) : 0;
    $satuan = isset($_POST['satuan']) ? mysqli_real_escape_string($mysqli, $_POST['satuan']) : '';
    $reorder = isset($_POST['reorder-level']) ? intval($_POST['reorder-level']) : 0;
    $supplier = isset($_POST['supplier']) ? mysqli_real_escape_string($mysqli, $_POST['supplier']) : '';
    $warehouse = isset($_POST['warehouse2']) ? mysqli_real_escape_string($mysqli, $_POST['warehouse2']) : '';
    $pic = isset($_POST['pic']) ? mysqli_real_escape_string($mysqli, $_POST['pic']) : '';

    // Validate required fields
    if (empty($code) || empty($nama) || empty($kategori) || $Stok <= 0 || empty($satuan) || $reorder <= 0 || empty($supplier) || empty($warehouse) || empty($pic)) {
        $_SESSION['message'] = "<div class='alert alert-danger'>Please fill in all required fields correctly!</div>";
        header("Location: new_request.php");
        exit();
    }

    $barcode = "https://barcode.tec-it.com/barcode.ashx?data=$code&code=Code128&dpi=96";
    // Remove $status if the column does not exist in your table
    $currentDateTime = date('Y-m-d H:i:s');
    $insertQuery = "INSERT INTO warehouse (Code, Nama, Kategori, Stok, Satuan, reorder_level, Supplier, cabang, Tanggal, pic)
                 VALUES ('$code', '$nama', '$kategori', '$Stok', '$satuan', '$reorder', '$supplier', '$warehouse', '$currentDateTime', '$pic')";
    $result = mysqli_query($mysqli, $insertQuery);

    if ($result) {
        $_SESSION['message'] = "<div class='alert alert-success'>Order added successfully!</div>";
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger'>Failed to add order. Please try again.</div>";
    }
    header("Location: new_request.php");
    exit();
}
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
            /* Custom CSS for better sidebar and form layout */
            body.skin-blue .header,
            body.skin-blue .navbar,
            .navbar-static-top {
                background-color: #002147;
            }

            .navbar-btn.sidebar-toggle {
                color: #fff;
            }

            .sidebar {
                position: fixed;
                width: 220px;
                height: 100%;
                overflow-y: auto;
            }
            .left-side {
                width: 220px;
            }
            .right-side {
                margin-left: 220px;
            }
            .form-3d-container {
                margin-bottom: 20px;
            }
            .form-3d {
                padding: 20px;
                background: #fff;
                border-radius: 5px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            }
            .input-3d {
                margin-bottom: 15px;
            }
            .input-3d label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            .input-3d input, .input-3d select {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                transition: all 0.3s;
            }
            .btn-3d {
                background: #002147;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                cursor: pointer;
                transition: all 0.3s;
            }
            .btn-3d:hover {
                background: #003366;
                transform: translateY(-2px);
            }
            .table-3d {
                background: #fff;
                border-radius: 5px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            }
            .status-badge {
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: bold;
            }
            .status-pending {
                background: #f39c12;
                color: white;
            }
            .status-accepted {
                background: #00a65a;
                color: white;
            }
            .status-rejected {
                background: #dd4b39;
                color: white;
            }
            .btn-action {
                border: none;
                background: none;
                padding: 5px;
                margin: 0 2px;
                cursor: pointer;
            }
            .btn-view {
                color: #002147;
            }
            .btn-edit {
                color: #f39c12;
            }
            .btn-delete {
                color: #dd4b39;
            }
            .btn-download {
                color: #3c8dbc;
            }
            .barcode-img {
                height: 40px;
            }
            .loading {
                color: #999;
                font-style: italic;
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
                        <li>
                            <a href="movement.php">
                                <i class="fa fa-exchange"></i> <span>Movement</span>
                            </a>
                        <li>
                            <a href="product.php">
                                <i class="fa fa-list-alt"></i> <span>Products</span>
                            </a>
                        </li>
                        <li class="active">
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
                        Add New Product
                        <small>Manage your warehouse products</small>
                    
                    </h1>
                </section>

                <section class="content animated">
                    <?php
                    if (isset($_SESSION['message'])) {
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);
                    }
                    ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-3d-container">
                                <div class="form-3d">
                                    <h1>Create New Product</h1>
                                    <form method="post" id="orderForm">
                                        <div class="input-3d">
                                            <label>Warehouse</label>
                                            <select name="warehouse2" id="warehouseSelect" required>
                                                <option value="">Select Warehouse</option>
                                                <?php
                                                $query = mysqli_query($mysqli, "SELECT nama FROM list_warehouse");
                                                while ($row = mysqli_fetch_assoc($query)) {
                                                    echo "<option value=\"".htmlspecialchars($row['nama'])."\">".htmlspecialchars($row['nama'])."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                         <div class="input-3d">
                                            <label>Product ID</label>
                                            <input type="text" placeholder="Enter Product ID..." name="code2" required>
                                        </div>
                                        <div class="input-3d">
                                            <label>Product Name</label>
                                            <input type="text" placeholder="Enter Product Name..." name="nama2" required>
                                        </div>
                                        <div class="input-3d">
                                            <label>Category</label>
                                            <select name="kategori2" required>
                                                <option value="">Select Category</option>
                                                <?php
                                                $query = mysqli_query($mysqli, "SELECT nama_kategori FROM kategori");
                                                while ($row = mysqli_fetch_assoc($query)) {
                                                    echo "<option value=\"".htmlspecialchars($row['nama_kategori'])."\">".htmlspecialchars($row['nama_kategori'])."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="input-3d">
                                            <label>Stock</label>
                                            <input type="number" placeholder="Enter quantity..." name="Stok" min="1" required>
                                        </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-3d-container">
                                <div class="form-3d">
                                        <div class="input-3d">
                                            <label>Unit</label>
                                            <input type="text" placeholder="Enter Unit..." name="satuan" required>
                                        </div>
                                        <div class="input-3d">
                                            <label>Reorder Level</label>
                                            <input type="number" placeholder="Enter Reorder Level..." name="reorder-level" min="5" required>
                                        </div>
                                        <div class="input-3d">
                                            <label>Supplier</label>
                                            <select name="supplier" required>
                                                <option value="">Select Supplier</option>
                                                <?php
                                                $query = mysqli_query($mysqli, "SELECT Nama FROM supplier");
                                                while ($row = mysqli_fetch_assoc($query)) {
                                                    echo "<option value=\"".htmlspecialchars($row['Nama'])."\">".htmlspecialchars($row['Nama'])."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="input-3d">
                                            <label>PIC</label>
                                            <select name="pic" required>
                                                <option value="">Select PIC</option>
                                                <?php
                                                $query = mysqli_query($mysqli, "SELECT pic FROM list_warehouse");
                                                if (!$query) {
                                                    die("Error fetching PIC data: " . mysqli_error($mysqli));
                                                }
                                                while ($row = mysqli_fetch_assoc($query)) {
                                                    echo "<option value=\"".htmlspecialchars($row['pic'])."\">".htmlspecialchars($row['pic'])."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <br>
                                        <div class="text-center">
                                            <button type="submit" class="btn-3d">
                                                <i class="fa fa-paper-plane"></i> Submit Order
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
    </section>
    <!-- Removed duplicate <aside class="right-side"> and filter forms for clarity and to avoid HTML nesting errors -->
    <?php
    // Define missing filter variables to prevent undefined variable errors
    $cabang_filter = isset($_GET['cabang']) ? mysqli_real_escape_string($mysqli, $_GET['cabang']) : '';
    ?>
    <section class="content animated">
        <h2>All Stock</h2>
                    <div class="table-responsive">
                        <table class="table table-3d">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Product Code</th>
                                    <th>Product Name</th>
                                    <th>Stok</th>
                                    <th>Category</th>
                                    <th>Supplier</th>
                                    <th>Unit</th>
                                    <th>Minimum</th>
                                    <th>Warehouse</th>
                                    <th>Date Created</th>
                                    <th>Barcode</th>
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
                                
                                if (!$orders) {
                                    die("Error fetching orders: " . mysqli_error($mysqli));
                                }
                                
                                while ($order = mysqli_fetch_assoc($orders)) {
                                    echo "<tr>
                                            <td>".htmlspecialchars($order['no'])."</td>
                                            <td>".htmlspecialchars($order['Code'])."</td>
                                            <td>".htmlspecialchars($order['Nama'])."</td>
                                            <td>".htmlspecialchars($order['Stok'])."</td>
                                            <td>".htmlspecialchars($order['Kategori'])."</td>
                                            <td>".htmlspecialchars($order['supplier_name'])."</td>
                                            <td>".htmlspecialchars($order['Satuan'])."</td>
                                            <td>".htmlspecialchars($order['reorder_level'])."</td>
                                            <td>".htmlspecialchars($order['cabang'])."</td>
                                            <td>".htmlspecialchars(date('d M Y H:i', strtotime($order['Tanggal'])))."</td>
                                            <td><img src='https://barcode.tec-it.com/barcode.ashx?data=".urlencode($order['Code'])."&code=Code128&dpi=96' class='barcode-img' alt='Barcode'></td>
                                            <td class='action-buttons'>";
                                    
                                    // View button
                                    echo "<button class='btn-action btn-view' title='View Details' onclick='viewDetails(".$order['no'].")'><i class='fa fa-eye'></i></button>";
                                    
                                    // Edit button
                                    echo "<button class='btn-action btn-edit' title='Edit Order' onclick='editOrder(".$order['no'].")'><i class='fa fa-pencil'></i></button>";
                                    
                                    // Download button
                                    echo "<button class='btn-action btn-download' title='Download Barcode' onclick='downloadBarcode(\"".$order['Code']."\")'><i class='fa fa-download'></i></button>";
                                    
                                    // Delete button
                                    echo "<form method='post' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this order?\")'>
                                            <input type='hidden' name='delete_id' value='".$order['no']."'>
                                            <button type='submit' class='btn-action btn-delete' title='Delete Order'><i class='fa fa-trash'></i></button>
                                          </form>";
                                    
                                    echo "</td>
                                        </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </aside>
        </div>
        <script src="../js/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <script src="../js/AdminLTE/app.js" type="text/javascript"></script>
        <script>
            // View details function
            function viewDetails(orderId) {
                window.location.href = 'order_detail.php?id=' + orderId;
            }

            // Edit order function
            function editOrder(orderId) {
                window.location.href = 'order_edit.php?id=' + orderId;
            }

            // Download barcode function
            function downloadBarcode(productCode) {
                // Create a temporary link element
                var link = document.createElement('a');
                link.href = 'https://barcode.tec-it.com/barcode.ashx?data=' + encodeURIComponent(productCode) + '&code=Code128&dpi=96';
                link.download = 'barcode_' + productCode + '.png';
                
                // Append to the body, click it, and then remove it
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            $(document).ready(function() {
                // Initialize tooltips
                $('[title]').tooltip();

                // Warehouse selection change event
                $('#warehouseSelect').change(function() {
                    var warehouse = $(this).val();
                    var productSelect = $('#productName');
                    var productCode = $('#productCode');
                    
                    if (warehouse) {
                        // Clear and disable product dropdown while loading
                        productSelect.html('<option value="">Loading products...</option>').prop('disabled', true);
                        productCode.val('');
                        
                        // Fetch products for selected warehouse
                        $.ajax({
                            url: 'fetch_products.php',
                            type: 'POST',
                            data: { warehouse: warehouse },
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success' && response.data.length > 0) {
                                    var options = '<option value="">Select Product</option>';
                                    $.each(response.data, function(key, product) {
                                        options += '<option value="'+product.nama+'" data-code="'+product.code+'">'+product.nama+'</option>';
                                    });
                                    productSelect.html(options).prop('disabled', false);
                                } else {
                                    productSelect.html('<option value="">No products available</option>');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("AJAX Error:", status, error);
                                productSelect.html('<option value="">Error loading products</option>');
                            }
                        });
                    } else {
                        productSelect.html('<option value="">Select Product (choose warehouse first)</option>').prop('disabled', true);
                        productCode.val('');
                    }
                });
                
                // Product selection change event
                $('#productName').change(function() {
                    var selectedOption = $(this).find('option:selected');
                    var productCode = $('#productCode');
                    
                    if (selectedOption.val() && selectedOption.data('code')) {
                        productCode.val(selectedOption.data('code'));
                    } else {
                        productCode.val('');
                    }
                });

                // Form validation
                $('#orderForm').submit(function(e) {
                    var isValid = true;
                    $(this).find('input[required], select[required]').each(function() {
                        if ($(this).val() === '') {
                            isValid = false;
                            $(this).css('border-color', 'red');
                        } else {
                            $(this).css('border-color', '#ddd');
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fill in all required fields!');
                    }
                });
            });
        </script>
    </body>
</html>