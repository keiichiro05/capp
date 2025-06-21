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

// Ambil data lead berdasarkan ID
$lead_sql = mysqli_query($mysqli, "SELECT * FROM leads WHERE lead_id = '$lead_id'");
$lead = mysqli_fetch_array($lead_sql);

if (!$lead) {
    echo "Lead not found.";
    exit();
}

// Dropdown data
$accounts = mysqli_query($mysqli, "SELECT account_id, account_name FROM account ORDER BY created_at DESC");
$business_lines = ['CHO', 'QHO', 'SHO'];
$sources = ['Cold Call', 'IoT', 'Email', 'Online External Media', 'Sales Visit', 'Telemarketing', 'Web E-Commerce', 'Web Inquiry'];

$status = $lead['status'];
$reason = isset($lead['reason']) ? $lead['reason'] : '';

// Hak edit:
// Kalau status Open, semua field selain reason editable (reason editable tapi boleh kosong)
// Kalau status Converted atau Closed, hanya status dan reason yang bisa diedit, sisanya disabled

$can_edit_fields = ($status === 'Open');
$reason_required = ($status === 'Converted' || $status === 'Closed');

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status_new = $_POST['status'];
    $reason_new = trim($_POST['reason']);

    // Validasi reason jika status Converted atau Closed
    if (($status_new === 'Converted' || $status_new === 'Closed') && empty($reason_new)) {
        $error = "Reason wajib diisi jika status Converted atau Closed.";
    }

    if (empty($error)) {
        if ($can_edit_fields) {
            // status Open, bisa update semua field + reason
            $lead_name = $_POST['lead_name'];
            $account_id = $_POST['account_id'];
            $business_line = $_POST['business_line'];
            $source = $_POST['source'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];

            $update_sql = "UPDATE leads SET lead_name=?, account_id=?, business_line=?, source=?, start_date=?, end_date=?, status=?, reason=? WHERE lead_id=?";
            $stmt = mysqli_prepare($mysqli, $update_sql);
            mysqli_stmt_bind_param($stmt, "sissssssi", $lead_name, $account_id, $business_line, $source, $start_date, $end_date, $status_new, $reason_new, $lead_id);
        } else {
            // status Converted atau Closed, hanya update status & reason
            $update_sql = "UPDATE leads SET status=?, reason=? WHERE lead_id=?";
            $stmt = mysqli_prepare($mysqli, $update_sql);
            mysqli_stmt_bind_param($stmt, "ssi", $status_new, $reason_new, $lead_id);
        }

        if (mysqli_stmt_execute($stmt)) {
            header("Location: leads.php?message=updated");
            exit();
        } else {
            $error = "Error updating lead: " . mysqli_error($mysqli);
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Edit Lead</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" />
    <link href="../css/AdminLTE.css" rel="stylesheet" />
</head>
<body class="skin-blue">
<header class="header">
    <a href="index.php" class="logo">E-pharm</a>
    <nav class="navbar navbar-static-top">
        <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
        </a>
        <div class="navbar-right">
            <ul class="nav navbar-nav">
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="glyphicon glyphicon-user"></i>
                        <span><?php echo htmlspecialchars($hasiluser['Nama']); ?> <i class="caret"></i></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header bg-light-blue">
                            <img src="<?php echo htmlspecialchars($hasiluser['foto']); ?>" class="img-circle" alt="User Image" />
                            <p><?php echo $hasiluser['Nama'] . " - " . $hasiluser['Jabatan']; ?></p>
                        </li>
                        <li class="user-footer">
                            <div class="pull-left"><a href="profil.php" class="btn btn-default btn-flat">Profile</a></div>
                            <div class="pull-right"><a href="prosesLogout.php" class="btn btn-default btn-flat">Sign out</a></div>
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
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="account.php"><i class="fa fa-users"></i> Account</a></li>
                <li><a href="contact.php"><i class="fa fa-file-text"></i> Contact</a></li>
                <li class="active"><a href="leads.php"><i class="fa fa-user-plus"></i> Leads</a></li>
            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <section class="content-header"><h1>Edit Lead</h1></section>

        <section class="content">
            <div class="box box-primary">
                <form method="post" id="formLead">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <div class="box-body">

                        <div class="form-group">
                            <label>Lead Name</label>
                            <input type="text" name="lead_name" class="form-control" 
                                value="<?php echo htmlspecialchars($lead['lead_name']); ?>" 
                                <?php echo $can_edit_fields ? '' : 'disabled'; ?>>
                        </div>

                        <div class="form-group">
                            <label>Account</label>
                            <select name="account_id" class="form-control" <?php echo $can_edit_fields ? '' : 'disabled'; ?>>
                                <?php
                                // reset pointer before loop
                                mysqli_data_seek($accounts, 0);
                                while($account = mysqli_fetch_assoc($accounts)):
                                ?>
                                    <option value="<?php echo $account['account_id']; ?>" <?php if($account['account_id'] == $lead['account_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($account['account_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Business Line</label>
                            <select name="business_line" class="form-control" <?php echo $can_edit_fields ? '' : 'disabled'; ?>>
                                <?php foreach($business_lines as $bl): ?>
                                    <option value="<?php echo $bl; ?>" <?php if($lead['business_line'] == $bl) echo 'selected'; ?>><?php echo $bl; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Source</label>
                            <select name="source" class="form-control" <?php echo $can_edit_fields ? '' : 'disabled'; ?>>
                                <?php foreach($sources as $src): ?>
                                    <option value="<?php echo $src; ?>" <?php if($lead['source'] == $src) echo 'selected'; ?>><?php echo $src; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" 
                                value="<?php echo $lead['start_date']; ?>" 
                                <?php echo $can_edit_fields ? '' : 'disabled'; ?>>
                        </div>

                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" 
                                value="<?php echo $lead['end_date']; ?>" 
                                <?php echo $can_edit_fields ? '' : 'disabled'; ?>>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" id="statusSelect" required>
                                <option value="Open" <?php if ($status == 'Open') echo "selected"; ?>>Open</option>
                                <option value="Converted" <?php if ($status == 'Converted') echo "selected"; ?>>Converted</option>
                                <option value="Closed" <?php if ($status == 'Closed') echo "selected"; ?>>Closed</option>
                            </select>
                        </div>

                        <div class="form-group" id="reasonGroup">
                            <label>Reason <?php echo ($reason_required) ? '(required)' : '(optional)'; ?></label>
                            <textarea name="reason" class="form-control" id="reasonField" <?php echo ($reason_required) ? 'required' : ''; ?>><?php echo htmlspecialchars($reason); ?></textarea>
                        </div>

                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Update Lead</button>
                        <a href="leads.php" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </aside>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    function updateReasonRequirement() {
        var status = $('#statusSelect').val();
        var reasonLabel = $('#reasonGroup label');
        var reasonField = $('#reasonField');

        if (status === 'Converted' || status === 'Closed') {
            reasonLabel.text('Reason (required)');
            reasonField.prop('required', true);
        } else if (status === 'Open') {
            reasonLabel.text('Reason (optional)');
            reasonField.prop('required', false);
        }
    }

    $('#statusSelect').change(function() {
        var status = $(this).val();

        // Atur editable fields
        if (status === 'Open') {
            // Enable editable fields
            $('input[name="lead_name"]').prop('disabled', false);
            $('select[name="account_id"]').prop('disabled', false);
            $('select[name="business_line"]').prop('disabled', false);
            $('select[name="source"]').prop('disabled', false);
            $('input[name="start_date"]').prop('disabled', false);
            $('input[name="end_date"]').prop('disabled', false);
        } else {
            // Disable fields except status & reason
            $('input[name="lead_name"]').prop('disabled', true);
            $('select[name="account_id"]').prop('disabled', true);
            $('select[name="business_line"]').prop('disabled', true);
            $('select[name="source"]').prop('disabled', true);
            $('input[name="start_date"]').prop('disabled', true);
            $('input[name="end_date"]').prop('disabled', true);
        }

        updateReasonRequirement();
    });

    // Jalankan saat load halaman
    $('#statusSelect').trigger('change');
});
</script>
</body>
</html>
