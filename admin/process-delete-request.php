<?php
session_start();
include("../connection.php");

if (isset($_GET['action']) && isset($_GET['request_id'])) {
    $action = $_GET['action'];
    $request_id = intval($_GET['request_id']);

    // Find the request
    $result = $database->query("SELECT * FROM delete_requests WHERE id='$request_id'");
    if ($result && $result->num_rows > 0) {
        $req = $result->fetch_assoc();
        $doctor_id = $req['doctor_id'];

        if ($action == 'approve') {
            // Fetch doctor details
            $doc = $database->query("SELECT * FROM doctor WHERE docid='$doctor_id'")->fetch_assoc();
            $sp_result = $database->query("SELECT sname FROM specialties WHERE id='".$doc['specialties']."'");
            $doctor_type = ($sp_result && $sp_result->num_rows > 0) ? $sp_result->fetch_assoc()['sname'] : "Unknown";

            $today = date("Y-m-d");
            $reason = "Deleted by Administrator";

            // Move to ex_doctors
            $insert = "INSERT INTO ex_doctors 
                (doctor_id, full_name, email, doctor_type, phone, join_date, resign_date, reason_for_exit)
                VALUES 
                ('".$doc['docid']."','".$doc['docname']."','".$doc['docemail']."',
                '$doctor_type','".$doc['doctel']."',NULL,'$today','$reason')";
            $database->query($insert);

            // Delete from doctor & webuser
            $database->query("DELETE FROM doctor WHERE docid='$doctor_id'");
            $database->query("DELETE FROM webuser WHERE email='".$doc['docemail']."'");

            // Update request status
            $database->query("UPDATE delete_requests SET status='approved' WHERE id='$request_id'");

            echo "<p style='color:green; text-align:center; margin-top:50px;'>✅ Doctor account deleted successfully.</p>";
            echo "<p style='text-align:center;'><a href='doctors-delete-request.php'>Back to Requests</a></p>";

        } elseif ($action == 'reject') {
            $database->query("UPDATE delete_requests SET status='rejected' WHERE id='$request_id'");
            echo "<p style='color:orange; text-align:center; margin-top:50px;'>⚠️ Deletion request rejected.</p>";
            echo "<p style='text-align:center;'><a href='doctors-delete-request.php'>Back to Requests</a></p>";
        }
    } else {
        echo "<p style='color:red; text-align:center; margin-top:50px;'>❌ Request not found.</p>";
    }
} else {
    echo "<p style='color:red; text-align:center; margin-top:50px;'>❌ Invalid request.</p>";
}
?>
