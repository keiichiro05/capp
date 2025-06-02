<?php
include('../konekdb.php');
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = $_POST['id'];
$type = $_POST['type'];
$qty = $_POST['qty'];
$notes = $_POST['notes'];
$user = $_SESSION['username'];

// First get current stock
$query = "SELECT Stok FROM barang WHERE id_barang = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$current = $result->fetch_assoc()['Stok'];

// Calculate new stock
switch ($type) {
    case 'add':
        $new_stock = $current + $qty;
        break;
    case 'remove':
        $new_stock = $current - $qty;
        break;
    case 'set':
        $new_stock = $qty;
        break;
    default:
        $new_stock = $current;
}

// Update stock
$query = "UPDATE barang SET Stok = ? WHERE id_barang = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $new_stock, $id);

if ($stmt->execute()) {
    // Log the adjustment
    $query = "INSERT INTO stock_adjustments (item_id, previous_qty, new_qty, adjustment_type, notes, adjusted_by) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("iiisss", $id, $current, $new_stock, $type, $notes, $user);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $mysqli->error]);
}
?>