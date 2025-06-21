<?php
include "konekdb.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

$lead_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($lead_id <= 0) {
    echo "Invalid lead ID.";
    exit();
}

// Ambil data lead dulu
$lead_sql = mysqli_query($mysqli, "SELECT * FROM leads WHERE lead_id = '$lead_id'");
$lead = mysqli_fetch_array($lead_sql);

if (!$lead) {
    echo "Lead not found.";
    exit();
}

if ($lead['status'] !== 'Open') {
    echo "Lead already converted or closed.";
    exit();
}

// Update status leads jadi 'Converted'
$update_lead = mysqli_query($mysqli, "UPDATE leads SET status = 'Converted' WHERE lead_id = '$lead_id'");

// Insert ke tabel opportunity
// (Sesuaikan nama field dan data dengan tabel opportunity yang sudah kamu buat)
$insert_opportunity = mysqli_query($mysqli, "INSERT INTO opportunity
    (opp_id, opp_name, account_id, contact_id, business_line, source, start_date, close_date, status, chance_of, sales_phase, expected_value) VALUES 
    (
        '".$lead['lead_id']."',
        '".mysqli_real_escape_string($mysqli, $lead['lead_name'])."',
        '".$lead['account_id']."',
        '".$lead['contact_id']."',
        '".$lead['business_line']."',
        '".$lead['source']."',
        '".$lead['start_date']."',
        '".$lead['end_date']."',
        'in progress',             -- Sesuai CHECK constraint
        '20%',                     -- Contoh default chance_of
        'qualification',          -- Sesuai CHECK constraint
        0.00                       -- Default expected_value
    )");

if ($update_lead && $insert_opportunity) {
    // Redirect kembali ke halaman detail lead dengan pesan sukses
    header("Location: lead_detail.php?id=$lead_id&converted=success");
    exit();
} else {
    echo "Failed to convert lead.";
}
