<?php
include 'konekdb.php'; // koneksi database

if (!isset($_GET['lead_id'])) {
    die("Lead ID not provided.");
}

$lead_id = intval($_GET['lead_id']);

// Ambil data lead berdasarkan lead_id
$sql = "SELECT lead_name FROM leads WHERE lead_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $lead_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Lead not found.");
}

$lead = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'convert') {
        // Redirect ke opportunity.php dengan lead_id
        header("Location: opportunity.php?lead_id=$lead_id");
        exit();
    } else {
        echo "Unknown action.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lead Action</title>
</head>
<body>
    <h2>Lead: <?php echo htmlspecialchars($lead['lead_name']); ?></h2>

    <form method="post" action="">
        <label>Action:</label>
        <select name="action">
            <option value="convert">Convert to Opportunity</option>
        </select><br><br>

        <button type="submit">Do Action</button>
    </form>
</body>
</html>
