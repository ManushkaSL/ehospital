<?php
session_start();
include("../connection.php");

// Fetch all pending requests
$sql = "SELECT dr.id AS request_id, d.docid, d.docname, d.docemail, d.doctel, s.sname AS specialty, dr.requested_at
        FROM delete_requests dr
        JOIN doctor d ON dr.doctor_id = d.docid
        LEFT JOIN specialties s ON d.specialties = s.id
        WHERE dr.status='pending'";
$result = $database->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Deletion Requests</title>
    <link rel="stylesheet" href="../css/main.css">
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 90%; margin: 20px auto; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background-color: #3498db; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        a.button {
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }
        .approve { background-color: #e74c3c; color: white; }
        .reject { background-color: #2ecc71; color: white; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Pending Doctor Account Deletion Requests</h2>
    <table>
        <tr>
            <th>Doctor ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Specialty</th>
            <th>Phone</th>
            <th>Requested At</th>
            <th>Action</th>
        </tr>
        <?php while($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['docid']; ?></td>
            <td><?php echo $row['docname']; ?></td>
            <td><?php echo $row['docemail']; ?></td>
            <td><?php echo $row['specialty']; ?></td>
            <td><?php echo $row['doctel']; ?></td>
            <td><?php echo $row['requested_at']; ?></td>
            <td>
                <a class="button approve" href="process-delete-request.php?action=approve&request_id=<?php echo $row['request_id']; ?>">Delete</a>
                <a class="button reject" href="process-delete-request.php?action=reject&request_id=<?php echo $row['request_id']; ?>">Reject</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
