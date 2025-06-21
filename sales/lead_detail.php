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

$lead_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$lead_sql = mysqli_query($mysqli, "SELECT * FROM leads WHERE lead_id = '$lead_id'");
$lead = mysqli_fetch_array($lead_sql);

if (!$lead) {
    echo "Lead not found.";
    exit();
}

if (isset($_GET['converted']) && $_GET['converted'] == 'success') {
    echo '<div class="alert alert-success">Lead has been successfully converted to opportunity.</div>';
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Lead Detail</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" />
    <link href="../css/AdminLTE.css" rel="stylesheet" />
</head>
<body class="skin-blue">
<!-- header, sidebar, nav ... -->

<div class="wrapper row-offcanvas row-offcanvas-left">
    <!-- sidebar ... -->

    <aside class="right-side">
        <section class="content-header"><h1>Lead Detail</h1></section>

        <section class="content">
            <div class="box box-primary">
                <table class="table table-bordered table-striped">
                    <tr><th>Lead Name</th><td><?php echo htmlspecialchars($lead['lead_name']); ?></td></tr>
                    <tr><th>Account</th>
                        <td>
                            <?php
                            $accsql = mysqli_query($mysqli, "SELECT account_name FROM account WHERE account_id = '".$lead['account_id']."'");
                            $acc = mysqli_fetch_array($accsql);
                            echo htmlspecialchars($acc['account_name']);
                            ?>
                        </td>
                    </tr>
                    <tr><th>Business Line</th><td><?php echo htmlspecialchars($lead['business_line']); ?></td></tr>
                    <tr><th>Source</th><td><?php echo htmlspecialchars($lead['source']); ?></td></tr>
                    <tr><th>Start Date</th><td><?php echo htmlspecialchars($lead['start_date']); ?></td></tr>
                    <tr><th>End Date</th><td><?php echo htmlspecialchars($lead['end_date']); ?></td></tr>
                    <tr><th>Status</th><td><?php echo htmlspecialchars($lead['status']); ?></td></tr>
                    <tr><th>Reason</th><td><?php echo nl2br(htmlspecialchars($lead['reason'])); ?></td></tr>
                </table>

                <?php if ($lead['status'] === 'Open'): ?>
    <a href="convert_lead.php?id=<?php echo $lead_id; ?>" class="btn btn-success" style="margin-top:20px;">
        <i class="fa fa-exchange"></i> Convert to Opportunity
    </a>
    <a href="leads.php" class="btn btn-default">Back to Leads</a>
<?php else: ?>
    <!-- Tombol Create Opportunity -->
    <a href="add_opportunity.php?lead_id=<?php echo $lead_id; ?>" class="btn btn-primary" style="margin-top:20px;">
        <i class="fa fa-plus"></i> Create Opportunity
    </a>
    <a href="leads.php" class="btn btn-default" style="margin-top:20px;">Back to Leads</a>
<?php endif; ?>

            </div>
        </section>
    </aside>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
</body>
</html>
