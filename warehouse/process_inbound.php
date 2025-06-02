<?php
include('konekdb.php'); // Include your database connection file
session_start();

// Check if the user is logged in and has the necessary permissions (optional, but good practice)
if (!isset($_SESSION['username']) || !isset($_SESSION['idpegawai'])) {
    header("location:../index.php?status=please login first");
    exit();
}

// Check if the form was submitted
if (isset($_POST['inbound'])) {
    // Sanitize and validate input
    $id_barang = filter_input(INPUT_POST, 'id_barang', FILTER_SANITIZE_NUMBER_INT);
    $jumlah = filter_input(INPUT_POST, 'jumlah', FILTER_SANITIZE_NUMBER_INT);
    $tujuan = filter_input(INPUT_POST, 'tujuan', FILTER_SANITIZE_STRING);
    $keterangan = filter_input(INPUT_POST, 'keterangan', FILTER_SANITIZE_STRING);
    $id_pegawai = $_SESSION['idpegawai']; // Get the logged-in employee ID

    // Basic validation
    if (empty($id_barang) || empty($jumlah) || empty($tujuan)) {
        header("location:stock.php?tab=inbound&status=error&message=All required fields must be filled.");
        exit();
    }

    if ($jumlah <= 0) {
        header("location:stock.php?tab=inbound&status=error&message=Quantity must be greater than zero.");
        exit();
    }

    // Start a transaction to ensure data integrity
    $mysqli->begin_transaction();

    try {
        // 1. Get current stock of the item
        $stmt_get_stock = $mysqli->prepare("SELECT Nama, Stok FROM warehouse WHERE id_barang = ? FOR UPDATE");
        $stmt_get_stock->bind_param("i", $id_barang);
        $stmt_get_stock->execute();
        $result_stock = $stmt_get_stock->get_result();
        $item = $result_stock->fetch_assoc();
        $stmt_get_stock->close();

        if (!$item) {
            throw new Exception("Selected item not found in warehouse.");
        }

        $current_stock = $item['Stok'];
        $item_name = $item['Nama'];

        // 2. Check if enough stock is available
        if ($current_stock < $jumlah) {
            throw new Exception("Insufficient stock for " . htmlspecialchars($item_name) . ". Available: " . $current_stock . ", Requested: " . $jumlah);
        }

        // 3. Update stock in the warehouse
        $new_stock = $current_stock - $jumlah;
        $stmt_update_stock = $mysqli->prepare("UPDATE warehouse SET Stok = ? WHERE id_barang = ?");
        $stmt_update_stock->bind_param("ii", $new_stock, $id_barang);
        if (!$stmt_update_stock->execute()) {
            throw new Exception("Error updating warehouse stock: " . $stmt_update_stock->error);
        }
        $stmt_update_stock->close();

        // 4. Record the inbound transaction in inbound_log
        $tanggal = date('Y-m-d H:i:s');
        $stmt_insert_log = $mysqli->prepare("INSERT INTO inbound_log (id_barang, nama_barang, jumlah, tanggal, id_pegawai, tujuan, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert_log->bind_param("isisiss", $id_barang, $item_name, $jumlah, $tanggal, $id_pegawai, $tujuan, $keterangan);
        if (!$stmt_insert_log->execute()) {
            throw new Exception("Error recording inbound log: " . $stmt_insert_log->error);
        }
        $stmt_insert_log->close();

        // Commit the transaction
        $mysqli->commit();
        header("location:stock.php?tab=inbound&status=success&message=inbound recorded successfully!");
        exit();

    } catch (Exception $e) {
        // Rollback the transaction on error
        $mysqli->rollback();
        header("location:stock.php?tab=inbound&status=error&message=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // If the form was not submitted directly, redirect to the stock page
    header("location:stock.php?status=invalid_access");
    exit();
}
?>