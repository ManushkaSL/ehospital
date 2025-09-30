<?php
session_start();
include("../connection.php");

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$doctor_email = $_SESSION['user'];

// Find doctor_id
$result = $database->query("SELECT docid FROM doctor WHERE docemail='$doctor_email'");
if ($result && $result->num_rows > 0) {
    $doc = $result->fetch_assoc();
    $docid = $doc['docid'];

    // Check if request already exists
    $check = $database->query("SELECT * FROM delete_requests WHERE doctor_id='$docid' AND status='pending'");
    if ($check->num_rows == 0) {
        $database->query("INSERT INTO delete_requests (doctor_id) VALUES ('$docid')");
        echo "<p style='color:green; text-align:center; margin-top:50px;'>✅ Your account deletion request has been sent to the administrator.</p>";
    } else {
        echo "<p style='color:orange; text-align:center; margin-top:50px;'>⚠️ You already have a pending deletion request.</p>";
    }
} else {
    echo "<p style='color:red; text-align:center; margin-top:50px;'>❌ Doctor not found.</p>";
}
?>
