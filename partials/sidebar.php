<?php
// Ensure $pegawai and $username are available from the main script
// Example:
// $getpegawai = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$idpegawai'");
// $pegawai = mysqli_fetch_array($getpegawai);
?>
<nav class="sidebar">
    <div class="sidebar-header text-center py-4">
        <h4 class="text-white">Warehouse System</h4>
    </div>
    
    <div class="user-panel">
        <img src="/capp/warehouse/img/<?php echo htmlspecialchars($pegawai['foto'] ?? 'default.jpg'); ?>" class="img-circle" alt="User Image">
        <div class="mt-3">
            <p class="mb-1"><?php echo htmlspecialchars($pegawai['Nama'] ?? 'N/A'); ?></p>
            <small><?php echo htmlspecialchars($pegawai['Jabatan'] ?? 'N/A'); ?></small>
        </div>
    </div>
    
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index2.php') ? 'active' : ''; ?>" href="index2.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'stock.php') ? 'active' : ''; ?>" href="stock.php">
                <i class="fas fa-boxes"></i> Inventory
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'product.php') ? 'active' : ''; ?>" href="product.php">
                <i class="fas fa-list"></i> Products
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'new_request.php') ? 'active' : ''; ?>" href="new_request.php">
                <i class="fas fa-clipboard-list"></i> Requests
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'history_request.php') ? 'active' : ''; ?>" href="history_request.php">
                <i class="fas fa-history"></i> Request History
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'mailbox.php') ? 'active' : ''; ?>" href="mailbox.php">
                <i class="fas fa-envelope"></i> Mailbox
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'profil.php') ? 'active' : ''; ?>" href="profil.php">
                <i class="fas fa-user"></i> Profile
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../logout.php"> <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</nav>