<?php
// navbar.php
if (!isset($_SESSION)) {
    session_start();
}
?>
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
                        <span><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?><i class="caret"></i></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header bg-light-blue">
                            <img src="img/<?php echo htmlspecialchars($pegawai['foto'] ?? ''); ?>" class="img-circle" alt="User Image" />
                            <p>
                                <?php echo htmlspecialchars(($pegawai['Nama'] ?? '') . " - " . ($pegawai['Jabatan'] ?? '') . " " . ($pegawai['Departemen'] ?? '')); ?>
                                <small>Member since <?php echo htmlspecialchars($pegawai['Tanggal_Masuk'] ?? ''); ?></small>
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

<aside class="left-side sidebar-offcanvas">
    <section class="sidebar">
        <div class="user-panel">
            <div class="pull-left image">
                <img src="img/<?php echo htmlspecialchars($pegawai['foto'] ?? ''); ?>" class="img-circle" alt="User Image" />
            </div>
            <div class="pull-left info">
                <p>Hello, <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></p>
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
                <a href="new_request.php">
                    <i class="fa fa-plus-square"></i> <span>New Product</span>
                </a>
            <li>
                <a href="new_request.php">
                    <i class="fa fa-th"></i> <span>Request Product</span>
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