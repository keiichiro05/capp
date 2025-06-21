<?php
require_once('../konekdb.php');
$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];
?>
<header class="header">
    <a href="#" class="logo">Warehouse Manager</a>
    <div class="navbar-right">
        <ul class="nav navbar-nav">
            <!-- Notifications Menu -->
            <li class="dropdown notifications-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-bell-o"></i>
                    <?php
                    $lowStockCount = $dashboardData['lowStockCount'] ?? 0;
                    if ($lowStockCount > 0): ?>
                        <span class="label label-danger"><?php echo htmlspecialchars($lowStockCount); ?></span>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu">
                    <li class="header">You have <?php echo htmlspecialchars($lowStockCount); ?> stock alerts</li>
                    <li>
                        <ul class="menu">
                            <?php
                            if (!empty($lowStockItems)): ?>
                                <?php foreach ($lowStockItems as $item): ?>
                                    <li>
                                        <a href="stock.php">
                                            <i class="fa fa-exclamation-circle text-danger"></i> 
                                            <?php echo htmlspecialchars($item['nama']); ?> - 
                                            Stock: <?php echo htmlspecialchars($item['stok']); ?> (Reorder: <?php echo htmlspecialchars($item['reorder_level']); ?>)
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><a href="#"><i class="fa fa-check-circle text-success"></i> No stock alerts</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <li class="footer"><a href="stock.php">View all</a></li>
                </ul>
            </li>
            
            <!-- User Menu -->
            <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="glyphicon glyphicon-user"></i>
                    <span><?php echo htmlspecialchars($username); ?> <i class="caret"></i></span>
                </a>
                <ul class="dropdown-menu">
                    <li class="user-header bg-light-blue">
                        <img src="../img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image" />
                        <p>
                            <?php echo htmlspecialchars($pegawai['Nama'] . " - " . $pegawai['Jabatan']); ?>
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
</header>