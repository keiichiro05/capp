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
    $deleteQuery = "DELETE FROM dariwarehouse WHERE no = '$delete_id'";
    if (mysqli_query($mysqli, $deleteQuery)) {
        $_SESSION['message'] = "<div class='alert alert-success'>Data deleted successfully!</div>";
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger'>Failed to delete data. Please try again.</div>";
    }
    header("Location: order.php");
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
                background-color: #002147; /* Base color for header and navbar */
            }

            .navbar-btn.sidebar-toggle {
                color: #fff; /* Color for toggle navigation icon */
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
                background: #002147; /* Apply base color to primary button */
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                cursor: pointer;
                transition: all 0.3s;
            }
            .btn-3d:hover {
                background: #003366; /* A slightly darker shade for hover effect */
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
                color: #002147; /* Use the base color for view button */
            }
            .btn-edit {
                color: #f39c12;
            }
            .btn-delete {
                color: #dd4b39;
            }
            .barcode-img {
                height: 40px;
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
                            <img src="img/<?php echo $pegawai['foto'];?>" class="img-circle" alt="User Image" />
                        </div>
                        <div class="pull-left info">
                            <p>Hello, <?php echo $username;?></p>
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
                                <i class="fa fa-exchange"></i> <span>Stock Transfer</span>
                            </a>
                        </li>
                        <li>
                            <a href="product.php">
                                <i class="fa fa-list-alt"></i> <span>Products</span>
                            </a>
                        </li>
                        <li class="active">
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
                                <i class="fa fa-comments"></i> <span>Mailbox</span>
                            </a>
                        </li>
                    </ul>
                </section>
            </aside>

            <aside class="right-side">
                <section class="content-header">
                    <h1>
                        Request Stock Management
                        <small>Form Request for New Stock </small>
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
                                    <h1>Create New Request</h1>
                                    <form method="post" id="orderForm">

                                        <div class="input-3d">
                                            <label>Quantity</label>
                                            <input type="number" placeholder="Enter Quantity..." name="jumlah" min="5" required>
                                        </div>
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
                                                    echo "<option value=\"{$row['Nama']}\">{$row['Nama']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-3d-container">
                                <div class="form-3d">
                                        <div class="input-3d">
                                            <label>Warehouse</label>
                                            <select name="warehouse2" required>
                                                <option value="">Select Warehouse</option>
                                                <?php
                                                $query = mysqli_query($mysqli, "SELECT nama FROM list_warehouse");
                                                while ($row = mysqli_fetch_assoc($query)) {
                                                    echo "<option value=\"{$row['nama']}\">{$row['nama']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="input-3d">
                                            <label>Product ID</label>
                                            <input type="number" placeholder="Enter Product ID..." name="code2" required>
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
                                                    echo "<option value=\"{$row['nama_kategori']}\">{$row['nama_kategori']}</option>";
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
                                                     echo "<option value=\"{$row['pic']}\">{$row['pic']}</option>";
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

                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_id'])) {
                        $code = mysqli_real_escape_string($mysqli, $_POST['code2']);
                        $nama = mysqli_real_escape_string($mysqli, $_POST['nama2']);
                        $kategori = mysqli_real_escape_string($mysqli, $_POST['kategori2']);
                        $jumlah = mysqli_real_escape_string($mysqli, $_POST['jumlah']);
                        $satuan = mysqli_real_escape_string($mysqli, $_POST['satuan']);
                        $reorder = mysqli_real_escape_string($mysqli, $_POST['reorder-level']);
                        $supplier = mysqli_real_escape_string($mysqli, $_POST['supplier']);
                        $warehouse = mysqli_real_escape_string($mysqli, $_POST['warehouse2']);
                        $pic = mysqli_real_escape_string($mysqli, $_POST['pic']);

                        $barcode = "https://barcode.tec-it.com/barcode.ashx?data=$code&code=Code128&dpi=96";
                        $status = "Order Sent";

                        $currentDateTime = date('Y-m-d H:i:s');
                        $insertQuery = "INSERT INTO dariwarehouse (code, nama, kategori, jumlah, satuan, reorder, supplier, cabang, status, date_created, pic)
                                     VALUES ('$code', '$nama', '$kategori', '$jumlah', '$satuan', '$reorder', '$supplier', '$warehouse', '$status', '$currentDateTime', '$pic')";
                        $result = mysqli_query($mysqli, $insertQuery);

                        if ($result) {
                            echo "<div class='alert alert-success'>Order added successfully!</div>";
                        } else {
                            echo "<div class='alert alert-danger'>Failed to add order. Please try again.</div>";
                        }
                    }
                    ?>
                </section>

                <section class="content animated">
                    <h2>Recent Orders</h2>
                    <div class="table-responsive">
                        <table class="table table-3d">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Product Code</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Supplier</th>
                                    <th>Date Created</th>
                                    <th>Barcode</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $orders = mysqli_query($mysqli, "SELECT * FROM dariwarehouse ORDER BY no DESC");
                                $counter = 1;
                                while ($order = mysqli_fetch_assoc($orders)) {
                                    echo "<tr>
                                            <td>".$counter++."</td>
                                            <td>{$order['code']}</td>
                                            <td>{$order['nama']}</td>
                                            <td>{$order['kategori']}</td>
                                            <td>{$order['jumlah']}</td>
                                            <td>{$order['satuan']}</td>
                                            <td>{$order['supplier']}</td>
                                            <td>".date('d M Y H:i', strtotime($order['date_created']))."</td>
                                            <td><img src='https://barcode.tec-it.com/barcode.ashx?data={$order['code']}&code=Code128&dpi=96' class='barcode-img' alt='Barcode'></td>
                                            <td>";
                                    if ($order['status'] === "0") {
                                        echo "<span class='status-badge status-pending'>Pending</span>";
                                    } elseif ($order['status'] === "1") {
                                        echo "<span class='status-badge status-accepted'>Accepted</span>";
                                    } elseif ($order['status'] === "2") {
                                        echo "<span class='status-badge status-rejected'>Rejected</span>";
                                    }
                                    echo "</td>
                                            <td>
                                                <div class='btn-group-actions'>
                                                    <button class='btn-action btn-view' title='View Details' onclick=\"viewDetails({$order['no']})\">
                                                        <i class='fa fa-eye'></i>
                                                    </button>
                                                    <button class='btn-action btn-edit' title='Edit' onclick=\"editOrder({$order['no']})\">
                                                        <i class='fa fa-edit'></i>
                                                    </button>
                                                    <form method='post' style='display:inline;'>
                                                        <input type='hidden' name='delete_id' value='{$order['no']}'>
                                                        <button type='submit' class='btn-action btn-delete' title='Delete' onclick=\"return confirm('Are you sure you want to delete this item?');\">
                                                            <i class='fa fa-trash'></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
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
                // Implement modal or redirect to detail page
                alert('Viewing details for order ID: ' + orderId);
                // window.location.href = 'order_detail.php?id=' + orderId;
            }

            // Edit order function
            function editOrder(orderId) {
                // Implement modal or redirect to edit page
                alert('Editing order ID: ' + orderId);
                // window.location.href = 'order_edit.php?id=' + orderId;
            }

            // Initialize tooltips
            $(document).ready(function() {
                $('[title]').tooltip();

                // Add hover effect to form elements
                $('.input-3d input, .input-3d select').hover(
                    function() {
                        $(this).css('transform', 'translateY(-2px)');
                    },
                    function() {
                        $(this).css('transform', 'translateY(0)');
                    }
                );

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