<?php
// sidebar.php
// Ensure $pegawai and $username are available if this file is included after header.php
// If sidebar.php is included on its own, you might need to fetch this data here as well.
// For this scenario, assuming it's included after header.php, the variables will be in scope.
?>
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
            <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
            <li><a href="stock.php"><i class="fa fa-exchange"></i> <span>Stock Transfer</span></a></li>
            <li class="active"><a href="product.php"><i class="fa fa-list-alt"></i> <span>Products</span></a></li>
            <li class="treeview">
                <a href="order.php">
                    <i class="fa fa-th"></i> <span>Request</span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="history_order.php"><i class="fa fa-archive"></i>Request History</a></li>
                </ul>
            </li>
            <li><a href="mailbox.php"><i class="fa fa-comments"></i> <span>Mailbox</span></a></li>
        </ul>
    </section>
</aside>