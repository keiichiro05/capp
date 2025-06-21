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

if ($user['jmluser'] == "0") {
    header("location:../index.php");
    exit();
}

// Get and validate request ID
$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($request_id <= 0) {
    die("Invalid request ID");
}

// Fetch request details with prepared statement
$query = "SELECT * FROM dariwarehouse WHERE no = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();
$stmt->close();

if(!$request) {
    die("Request not found");
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
if($_SERVER['REQUEST_METHOD'] == 'POST') {
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
        $error = "Please fill all required fields";
    } else {
        // Update query with prepared statement
        $update_query = "UPDATE dariwarehouse SET 
                        nama = ?, 
                        kategori = ?, 
                        jumlah = ?, 
                        satuan = ?, 
                        supplier = ?, 
                        cabang = ?, 
                        pic = ?, 
                        notes = ? 
                        WHERE no = ?";
        $stmt = $mysqli->prepare($update_query);
        $stmt->bind_param("ssisssssi", $nama, $kategori, $jumlah, $satuan, $supplier, $cabang, $pic, $notes, $request_id);
        
        if($stmt->execute()) {
            $stmt->close();
            $_SESSION['message'] = "Request updated successfully";
            header("Location: request_detail.php?id=".$request_id);
            exit();
        } else {
            $error = "Error updating request: ".$mysqli->error;
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Request #<?= htmlspecialchars($request['no']) ?></title>
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
            margin-left: 220px; /* Match sidebar width */
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
        
        .btn-default {
            background: #f5f5f5;
            border: 1px solid #ddd;
        }
        
        /* Alerts */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-danger {
            background-color: #f2dede;
            border-color: #ebccd1;
            color: #a94442;
        }
        
        .alert-success {
            background-color: #dff0d8;
            border-color: #d6e9c6;
            color: #3c763d;
        }
        
        /* Responsive Grid */
        @media (max-width: 768px) {
            .main-container {
                margin-left: 0;
                padding: 10px;
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
        
        <div class="right-side">
            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-primary">
                            <div class="box-header">
                                <h3 class="box-title">Edit Request #<?= htmlspecialchars($request['no']) ?></h3>
                            </div>
                            <div class="box-body">
                                <?php if(isset($error)): ?>
                                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                                <?php endif; ?>
                                
                                <?php if(isset($_SESSION['message'])): ?>
                                    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']) ?></div>
                                    <?php unset($_SESSION['message']); ?>
                                <?php endif; ?>
                                
                                <form method="post" class="form-horizontal">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Warehouse *</label>
                                        <div class="col-sm-10">
                                            <select name="warehouse" class="form-control" required>
                                                <option value="">-- Select Warehouse --</option>
                                                <?php
                                                $warehouses = $mysqli->query("SELECT nama FROM list_warehouse");
                                                while ($wh = $warehouses->fetch_assoc()):
                                                    $selected = ($wh['nama'] == $request['cabang']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= htmlspecialchars($wh['nama']) ?>" <?= $selected ?>>
                                                        <?= htmlspecialchars($wh['nama']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Product Name *</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="nama" class="form-control" 
                                                   value="<?= htmlspecialchars($request['nama']) ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Category *</label>
                                        <div class="col-sm-10">
                                            <select name="kategori" class="form-control" required>
                                                <option value="">-- Select Category --</option>
                                                <?php
                                                $categories = $mysqli->query("SELECT nama_kategori FROM kategori");
                                                while ($cat = $categories->fetch_assoc()):
                                                    $selected = ($cat['nama_kategori'] == $request['kategori']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= htmlspecialchars($cat['nama_kategori']) ?>" <?= $selected ?>>
                                                        <?= htmlspecialchars($cat['nama_kategori']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Quantity *</label>
                                        <div class="col-sm-10">
                                            <input type="number" name="jumlah" class="form-control" 
                                                   value="<?= htmlspecialchars($request['jumlah']) ?>" min="1" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Unit *</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="satuan" class="form-control" 
                                                   value="<?= htmlspecialchars($request['satuan']) ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Supplier *</label>
                                        <div class="col-sm-10">
                                            <select name="supplier" class="form-control" required>
                                                <option value="">-- Select Supplier --</option>
                                                <?php
                                                $suppliers = $mysqli->query("SELECT Nama FROM supplier");
                                                while ($sup = $suppliers->fetch_assoc()):
                                                    $selected = ($sup['Nama'] == $request['supplier']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= htmlspecialchars($sup['Nama']) ?>" <?= $selected ?>>
                                                        <?= htmlspecialchars($sup['Nama']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">PIC *</label>
                                        <div class="col-sm-10">
                                            <select name="pic" class="form-control" required>
                                                <option value="">-- Select PIC --</option>
                                                <?php
                                                $pics = $mysqli->query("SELECT DISTINCT pic FROM list_warehouse WHERE pic IS NOT NULL AND pic <> ''");
                                                while ($p = $pics->fetch_assoc()):
                                                    $selected = ($p['pic'] == $request['pic']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= htmlspecialchars($p['pic']) ?>" <?= $selected ?>>
                                                        <?= htmlspecialchars($p['pic']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Notes</label>
                                        <div class="col-sm-10">
                                            <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($request['notes']) ?></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <div class="col-sm-offset-2 col-sm-10">
                                            <a href="request_detail.php?id=<?= $request['no'] ?>" class="btn btn-default">Cancel</a>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    
    <!-- JavaScript Files -->
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../js/AdminLTE/app.js" type="text/javascript"></script>
</body>
</html>