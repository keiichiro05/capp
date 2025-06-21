<?php
include "konekdb.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

$iduser = $_SESSION['idpegawai'];
$usersql = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$iduser'");
$hasiluser = mysqli_fetch_array($usersql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $no = $_POST['no'];
    $nama = $_POST['nama'];
    $quantity = $_POST['quantity'];
    $reason = $_POST['reason'];

    $insert_sql = "INSERT INTO sales_req (no, nama, quantity, reason) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($mysqli, $insert_sql);
    mysqli_stmt_bind_param($stmt, "isis", $no, $nama, $quantity, $reason);

    if (mysqli_stmt_execute($stmt)) {
        // Redirect dengan parameter message=success supaya alert muncul
        header("Location: kirim_request.php?message=success");
        exit();
    } else {
        $error = "Error: " . mysqli_error($mysqli);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Request Product</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <!-- Bootstrap -->
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />
</head>
<body class="skin-blue">
<header class="header">
    <a href="index.php" class="logo">U-PSN</a>
    <nav class="navbar navbar-static-top" role="navigation">
        <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
        </a>
        <div class="navbar-right">
            <ul class="nav navbar-nav">
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="glyphicon glyphicon-user"></i>
                        <span><?php echo $hasiluser['Nama']; ?> <i class="caret"></i></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header bg-light-blue">
                            <img src="<?php echo $hasiluser['foto']; ?>" class="img-circle" alt="User Image" />
                            <p>
                                <?php echo $hasiluser['Nama'] . " - " . $hasiluser['Jabatan']; ?>
                                <small>Member since <?php echo $hasiluser['Tanggal_Masuk']; ?></small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="profil.php" class="btn btn-default btn-flat">Profile</a>
                            </div>
                            <div class="pull-right">
                                <a href="prosesLogout.php" class="btn btn-default btn-flat">Sign out</a>
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
                    <img src="<?php echo $hasiluser['foto']; ?>" class="img-circle" alt="User Image" />
                </div>
                <div class="pull-left info">
                    <p>Hello, <?php echo $hasiluser['Nama']; ?></p>
                    <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                </div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                <li><a href="account.php"><i class="fa fa-users"></i> <span>Account</span></a></li>
                <li><a href="contact.php"><i class="fa fa-envelope"></i> <span>Contact</span></a></li>
                <li><a href="products.php"><i class="fa fa-archive"></i> <span>Product</span></a></li>
                 <li class="active"><a href="product_request.php"><i class="fa fa-plus-square"></i> <span>Product Request</span></a></li>
                <li><a href="leads.php"><i class="fa fa-lightbulb-o"></i> <span>Leads</span></a></li>
                <li><a href="opportunity.php"><i class="fa fa-rocket"></i> <span>Opportunity</span></a></li>
                <li><a href="sales_new_request.php"><i class="fa fa-truck"></i> <span>Sales Order</span></a></li>
            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <section class="content-header">
            <h1>Product Request <small>Request product to sales_req</small></h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-truck"></i> sales_req</a></li>
                <li class="active">Product Request</li>
            </ol>
        </section>

        <section class="content">
            <div class="box box-primary">
                <form action="" method="post" role="form">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <div class="box-body">
                        <div class="form-group">
                            <label for="no">ID Product</label>
                            <input type="number" name="no" class="form-control" id="no" required>
                        </div>

                        <div class="form-group">
                            <label for="nama">Name Product</label>
                            <input type="text" name="nama" class="form-control" id="nama" required>
                        </div>

                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input type="number" name="quantity" class="form-control" id="quantity" required>
                        </div>

                        <div class="form-group">
                            <label for="reason">Reason</label>
                            <textarea name="reason" class="form-control" id="reason" required></textarea>
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Send Request</button>
                        <a href="index.php" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </aside>
</div>

<!-- jQuery dan Bootstrap -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
<script src="../js/bootstrap.min.js" type="text/javascript"></script>

<?php if (isset($_GET['message']) && $_GET['message'] == 'success'): ?>
<script>
    alert("Request berhasil dikirim!");
    // Redirect supaya alert tidak muncul lagi jika refresh
    window.location.href = 'kirim_request.php';
</script>
<?php endif; ?>

</body>
</html>
