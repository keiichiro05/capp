<?php
include('../konekdb.php');
session_start();

// Validate session and authorization
if(!isset($_SESSION['username']) || !isset($_SESSION['idpegawai'])) {
    header("location:../index.php?status=please login first");
    exit();
}

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Check user authorization
$stmt = $mysqli->prepare("SELECT count(username) as jmluser FROM authorization WHERE username = ? AND modul = 'Warehouse'");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user['jmluser'] == "0") {
    header("location:../index.php");
    exit();
}

// Fetch employee data
$stmt = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$stmt->bind_param("s", $idpegawai);
$stmt->execute();
$pegawai = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pegawai) {
    die("Employee data not found");
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_request'])) {
    // Validate and sanitize inputs
    $nama = isset($_POST['nama']) ? $mysqli->real_escape_string(trim($_POST['nama'])) : '';
    $kategori = isset($_POST['kategori']) ? $mysqli->real_escape_string(trim($_POST['kategori'])) : '';
    $jumlah = isset($_POST['jumlah']) ? intval($_POST['jumlah']) : 0;
    $satuan = isset($_POST['satuan']) ? $mysqli->real_escape_string(trim($_POST['satuan'])) : '';
    $supplier = isset($_POST['supplier']) ? $mysqli->real_escape_string(trim($_POST['supplier'])) : '';
    $cabang = isset($_POST['warehouse']) ? $mysqli->real_escape_string(trim($_POST['warehouse'])) : '';
    $pic = isset($_POST['pic']) ? $mysqli->real_escape_string(trim($_POST['pic'])) : '';
    $notes = isset($_POST['notes']) ? $mysqli->real_escape_string(trim($_POST['notes'])) : '';
    
    // Validate required fields
    if (empty($nama) || empty($kategori) || $jumlah <= 0 || empty($satuan) || 
        empty($supplier) || empty($cabang) || empty($pic)) {
        $_SESSION['error'] = "Please fill all required fields";
    } else {
        // Insert query with prepared statement
        $insert_query = "INSERT INTO dariwarehouse 
                        (nama, kategori, jumlah, satuan, supplier, cabang, pic, notes, status, date_created) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, '0', NOW())";
        $stmt = $mysqli->prepare($insert_query);
        $stmt->bind_param("ssisssss", $nama, $kategori, $jumlah, $satuan, $supplier, $cabang, $pic, $notes);
        
        if($stmt->execute()) {
            $stmt->close();
            $_SESSION['message'] = "Request submitted successfully";
            header("Location: new_request.php");
            exit();
        } else {
            $_SESSION['error'] = "Error submitting request: ".$mysqli->error;
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Request - Warehouse</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />
    <style>
        /* Main Container */
        .main-container {
            padding: 20px;
        }
        
        /* Form Styles */
        .form-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .form-title {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control {
            border-radius: 4px;
            border: 1px solid #ddd;
            box-shadow: none;
            height: 40px;
            padding: 8px 12px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }
        
        textarea.form-control {
            height: auto;
            min-height: 80px;
        }
        
        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            border: none;
            padding: 10px 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9, #3498db);
            transform: translateY(-2px);
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pending {
            background-color: #f39c12;
            color: white;
        }
        
        .status-accepted {
            background-color: #2ecc71;
            color: white;
        }
        
        .status-rejected {
            background-color: #e74c3c;
            color: white;
        }
        
        /* Action Buttons */
        .action-buttons {
            white-space: nowrap;
        }
        
        .action-buttons .btn {
            margin-right: 5px;
        }
        
        .action-buttons .btn:last-child {
            margin-right: 0;
        }
        
        /* Table Improvements */
        .table-container {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table thead th {
            background-color: #2c3e50;
            color: white;
            font-weight: 600;
            border-bottom: none;
        }
        
        .table tbody tr:hover {
            background-color: #f5f5f5;
        }
        
        /* Responsive Grid */
        @media (min-width: 768px) {
            .form-row {
                display: flex;
                flex-wrap: wrap;
                margin: 0 -10px;
            }
            
            .form-col {
                padding: 0 10px;
                flex: 1;
                min-width: 0;
            }
            
            .form-col-md-6 {
                flex: 0 0 50%;
                max-width: 50%;
            }
            
            .form-col-md-4 {
                flex: 0 0 33.333%;
                max-width: 33.333%;
            }
        }
        
        @media (max-width: 768px) {
            .form-col-md-6,
            .form-col-md-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }
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
                            <span><?= htmlspecialchars($username) ?><i class="caret"></i></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-header bg-light-blue">
                                <img src="img/<?= htmlspecialchars($pegawai['foto']) ?>" class="img-circle" alt="User Image" />
                                <p>
                                    <?= htmlspecialchars($pegawai['Nama']) . " - " . htmlspecialchars($pegawai['Jabatan']) . " " . htmlspecialchars($pegawai['Departemen']) ?>
                                    <small>Member since <?= htmlspecialchars($pegawai['Tanggal_Masuk']) ?></small>
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
                        <img src="img/<?= htmlspecialchars($pegawai['foto']) ?>" class="img-circle" alt="User Image" />
                    </div>
                    <div class="pull-left info">
                        <p>Hello, <?= htmlspecialchars($username) ?></p>
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
            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <?= htmlspecialchars($_SESSION['message']) ?>
                            </div>
                            <?php unset($_SESSION['message']); ?>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <?= htmlspecialchars($_SESSION['error']) ?>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-primary">
                            <div class="box-header">
                                <h3 class="box-title"><i class="fa fa-plus-circle"></i> Create New Request</h3>
                            </div>
                            <div class="box-body">
                                <form id="request-form" method="post" class="form-horizontal">
                                    <div class="form-row">
                                        <div class="form-col form-col-md-6">
                                            <div class="form-group">
                                                <label>Warehouse *</label>
                                                <select name="warehouse" class="form-control" required>
                                                    <option value="">-- Select Warehouse --</option>
                                                    <?php
                                                    $warehouses = $mysqli->query("SELECT nama FROM list_warehouse");
                                                    while ($wh = $warehouses->fetch_assoc()):
                                                    ?>
                                                        <option value="<?= htmlspecialchars($wh['nama']) ?>">
                                                            <?= htmlspecialchars($wh['nama']) ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-col form-col-md-6">
                                            <div class="form-group">
                                                <label>PIC *</label>
                                                <select name="pic" class="form-control" required>
                                                    <option value="">-- Select PIC --</option>
                                                    <?php
                                                    $pics = $mysqli->query("SELECT DISTINCT pic FROM list_warehouse WHERE pic IS NOT NULL AND pic <> ''");
                                                    while ($p = $pics->fetch_assoc()):
                                                    ?>
                                                        <option value="<?= htmlspecialchars($p['pic']) ?>">
                                                            <?= htmlspecialchars($p['pic']) ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-col form-col-md-6">
                                            <div class="form-group">
                                                <label>Product Name *</label>
                                                <input type="text" name="nama" class="form-control" placeholder="Product name" required>
                                            </div>
                                        </div>
                                        <div class="form-col form-col-md-6">
                                            <div class="form-group">
                                                <label>Category *</label>
                                                <select name="kategori" class="form-control" required>
                                                    <option value="">-- Select Category --</option>
                                                    <?php
                                                    $categories = $mysqli->query("SELECT nama_kategori FROM kategori");
                                                    while ($cat = $categories->fetch_assoc()):
                                                    ?>
                                                        <option value="<?= htmlspecialchars($cat['nama_kategori']) ?>">
                                                            <?= htmlspecialchars($cat['nama_kategori']) ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-col form-col-md-4">
                                            <div class="form-group">
                                                <label>Quantity *</label>
                                                <input type="number" name="jumlah" class="form-control" placeholder="Qty" min="1" required>
                                            </div>
                                        </div>
                                      <!-- In the form section where you have the Unit field, replace the input with a select -->
<div class="form-col form-col-md-4">
    <div class="form-group">
        <label>Unit *</label>
        <select name="satuan" class="form-control" required>
            <option value="">-- Select Unit --</option>
            <?php
            // Query to get units from database
            $units = $mysqli->query("SELECT nama_satuan FROM satuan");
            while ($unit = $units->fetch_assoc()):
            ?>
                <option value="<?= htmlspecialchars($unit['nama_satuan']) ?>">
                    <?= htmlspecialchars($unit['nama_satuan']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
</div>
                                        <div class="form-col form-col-md-4">
                                            <div class="form-group">
                                                <label>Supplier *</label>
                                                <select name="supplier" class="form-control" required>
                                                    <option value="">-- Select Supplier --</option>
                                                    <?php
                                                    $suppliers = $mysqli->query("SELECT Nama FROM supplier");
                                                    while ($sup = $suppliers->fetch_assoc()):
                                                    ?>
                                                        <option value="<?= htmlspecialchars($sup['Nama']) ?>">
                                                            <?= htmlspecialchars($sup['Nama']) ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                                    </div>
                                    
                                    <div class="text-right">
                                        <button type="submit" name="submit_request" class="btn btn-primary">
                                            <i class="fa fa-paper-plane"></i> Submit Request
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-header" style="background-color:rgb(255, 255, 255); color: white;">
                                <h3 class="box-title"><i class="fa fa-history"></i> Recent Requests</h3>
                            </div>
                            <div class="box-body table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Product Name</th>
                                            <th>Category</th>
                                            <th>Quantity</th>
                                            <th>Unit</th>
                                            <th>Supplier</th>
                                            <th>Warehouse</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $requests = $mysqli->query("SELECT * FROM dariwarehouse ORDER BY no DESC LIMIT 10");
                                        while ($req = $requests->fetch_assoc()):
                                            $status_class = '';
                                            $status_text = '';
                                            
                                            if ($req['status'] === "0") {
                                                $status_class = 'status-pending';
                                                $status_text = 'Pending';
                                            } elseif ($req['status'] === "1") {
                                                $status_class = 'status-accepted';
                                                $status_text = 'Accepted';
                                            } elseif ($req['status'] === "2") {
                                                $status_class = 'status-rejected';
                                                $status_text = 'Rejected';
                                            }
                                        ?>
                                            <tr>
                                                <td><?= $req['no'] ?></td>
                                                <td><?= htmlspecialchars($req['nama']) ?></td>
                                                <td><?= htmlspecialchars($req['kategori']) ?></td>
                                                <td><?= $req['jumlah'] ?></td>
                                                <td><?= htmlspecialchars($req['satuan']) ?></td>
                                                <td><?= htmlspecialchars($req['supplier']) ?></td>
                                                <td><?= htmlspecialchars($req['cabang']) ?></td>
                                                <td><?= date('d M Y H:i', strtotime($req['date_created'])) ?></td>
                                                <td><span class="status-badge <?= $status_class ?>"><?= $status_text ?></span></td>
                                                <td class="action-buttons">
                                                    <a href="request_detail.php?id=<?= $req['no'] ?>" class="btn btn-info btn-xs">
                                                        <i class="fa fa-eye"></i> Detail
                                                    </a>
                                                    <a href="request_edit.php?id=<?= $req['no'] ?>" class="btn btn-warning btn-xs">
                                                        <i class="fa fa-edit"></i> Edit
                                                    </a>
                                                    <form method="post" action="delete_request.php" style="display:inline;">
                                                        <input type="hidden" name="delete_id" value="<?= $req['no'] ?>">
                                                        <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this request?')">
                                                            <i class="fa fa-trash-o"></i> Delete
                                                        </button>
                                                    </form>
                                                </td>
                                                <td><?= htmlspecialchars($req['notes']) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <!-- JavaScript Files -->
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/AdminLTE/app.js"></script>
    <script>
    $(document).ready(function() {
        // Form validation
        $('#request-form').submit(function(e) {
            let valid = true;
            $(this).find('[required]').each(function() {
                if (!$(this).val()) {
                    $(this).css('border-color', 'red');
                    valid = false;
                } else {
                    $(this).css('border-color', '');
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Please fill all required fields!');
            }
        });
        
        // Reset form validation on change
        $('input, select').on('change', function() {
            if ($(this).val()) {
                $(this).css('border-color', '');
            }
        });
    });
    </script>
</body>
</html>