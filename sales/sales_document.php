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

$opp_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$opp_sql = mysqli_query($mysqli, "SELECT * FROM opportunity WHERE opp_id = '$opp_id'");
$opp = mysqli_fetch_array($opp_sql);

if (!$opp) {
    echo "Opportunity not found.";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Sales Document</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" />
    <link href="../css/AdminLTE.css" rel="stylesheet" />
</head>
<body class="skin-blue">
<!-- header, sidebar, nav ... -->

<div class="wrapper row-offcanvas row-offcanvas-left">
    <!-- sidebar ... -->

    <aside class="right-side">
        <section class="content-header">
            <h1><?php echo htmlspecialchars($opp['opportunity_name']); ?></h1>
        </section>

        <section class="content">
            <div class="box box-primary">

                <!-- SALES ORDER -->
                <h4>Sales Order</h4>
                <?php
                $so_sql = mysqli_query($mysqli, "SELECT * FROM sales_order WHERE opportunity_id = '$opp_id'");
                $so = mysqli_fetch_array($so_sql);
                if ($so):
                ?>
                <table class="table table-bordered table-striped">
                    <tr><th>SO Number</th><td><?= htmlspecialchars($so['so_number']) ?></td></tr>
                    <tr><th>Date</th><td><?= htmlspecialchars($so['so_date']) ?></td></tr>
                    <tr><th>Status</th><td><?= htmlspecialchars($so['status']) ?></td></tr>
                </table>
                <?php else: ?>
                    <p><i>No sales order found.</i></p>
                <?php endif; ?>

                <!-- SALES ORDER ITEM -->
                <h4>Sales Order Item</h4>
                <?php
                $items_sql = mysqli_query($mysqli, "SELECT * FROM sales_order_item WHERE so_id = '".$so['so_id']."'");
                if (mysqli_num_rows($items_sql) > 0):
                ?>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Item Name</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        while ($item = mysqli_fetch_array($items_sql)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <td><?= htmlspecialchars($item['description']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= "Rp " . number_format($item['price'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p><i>No sales order items found.</i></p>
                <?php endif; ?>

                <!-- ATTACHMENTS -->
                <h4>Attachment</h4>
                <?php
                $att_sql = mysqli_query($mysqli, "SELECT * FROM attachments WHERE opportunity_id = '$opp_id'");
                if (mysqli_num_rows($att_sql) > 0): ?>
                    <ul>
                        <?php while ($att = mysqli_fetch_array($att_sql)): ?>
                            <li><a href="../uploads/<?= htmlspecialchars($att['file_name']) ?>" target="_blank"><?= htmlspecialchars($att['file_name']) ?></a></li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p><i>No attachments found.</i></p>
                <?php endif; ?>

                <a href="opportunity.php" class="btn btn-default" style="margin-top:20px;">Back to Opportunity List</a>

            </div>
        </section>
    </aside>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
</body>
</html>
