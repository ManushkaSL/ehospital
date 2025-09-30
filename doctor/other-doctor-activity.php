<?php
session_start();
if(!isset($_SESSION["user"]) || $_SESSION['usertype']!='d'){
    header("location: ../login.php");
    exit;
}

include("../connection.php");

// safe current user email
$useremail = $database->real_escape_string($_SESSION["user"]);

// fetch current doctor row
$docrow = $database->query("SELECT * FROM doctor WHERE docemail='$useremail' LIMIT 1");
if(!$docrow || $docrow->num_rows==0){
    // invalid session user — redirect to login
    header("location: ../login.php");
    exit;
}
$docfetch = $docrow->fetch_assoc();
$doctorname = $docfetch["docname"];
// NOTE: your doctor table column is "specialites" in previous code — keep same spelling
$specialty_id = isset($docfetch["specialties"]) ? $database->real_escape_string($docfetch["specialties"]) : '';

// get specialty name if you have a specialties table
$specname = "Unknown";
if($specialty_id !== ''){
    $specrow = $database->query("SELECT sname FROM specialties WHERE id='$specialty_id' LIMIT 1");
    if($specrow && $specrow->num_rows>0){
        $specname = $specrow->fetch_assoc()["sname"];
    }
}

// Fetch treatments created by other doctors with the same specialty
$sql = "SELECT td.treatment_id,
               td.doctor_email,
               td.patient_email,
               td.diagnosis,
               td.treatment_plan,
               td.prescribed_medicines,
               td.treatment_date,
               td.followup_date,
               d.docname AS treating_docname,
               p.pname AS patient_name
        FROM treatment_details td
        INNER JOIN doctor d ON td.doctor_email = d.docemail
        LEFT JOIN patient p ON td.patient_email = p.pemail
        WHERE d.specialties = '$specialty_id'
          AND td.doctor_email <> '$useremail'
        ORDER BY td.treatment_date DESC, td.treatment_id DESC";

$treatments = $database->query($sql);

// helper to escape output
function h($s){
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Other Doctors Activity</title>
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        /* Blue theme styles */
        h2 {
            color: #1a73e8;
            font-weight: 600;
        }
        a.non-style-link {
            color: #1a73e8;
            font-weight: 500;
            text-decoration: none;
        }
        a.non-style-link:hover {
            text-decoration: underline;
        }
        .sub-table {
            border-collapse: collapse;
            background: #f9fbff;
            box-shadow: 0px 2px 6px rgba(0,0,0,0.1);
        }
        .sub-table th {
            background: #37afe2ff;
            color: #fff;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        .sub-table td {
            border-bottom: 1px solid #e0e0e0;
            padding: 10px;
            color: #333;
        }
        .sub-table tr:hover {
            background: #e8f0fe;
        }
        .no-data {
            text-align: center;
            color: #666;
        }
        /* Blue button style for back link */
        .blue-btn {
            display: inline-block;
            padding: 8px 14px;
            background: #37afe2ff;
            color: #fff !important;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s ease;
        }
        .blue-btn:hover {
            background: #1558b0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dash-body" style="margin-top: 15px">
            <p style="padding-left:20px;">
                <a class="blue-btn" href="index.php">&larr; Back to Dashboard</a>
            </p>

            <h2 style="padding-left:20px">Other <?php echo h($specname); ?> Doctors' Treatment Records</h2>
            
            <center>
            <div class="abc scroll" style="height:500px; width:92%; padding: 10px;">
                <table class="sub-table scrolldown" border="0" width="100%">
                    <thead>
                        <tr>
                            <th>Doctor</th>
                            <th>Patient</th>
                            <th>Patient Email</th>
                            <th>Diagnosis</th>
                            <th>Treatment Plan</th>
                            <th>Prescribed Medicines</th>
                            <th>Date</th>
                            <th>Follow-up</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!$treatments || $treatments->num_rows==0): ?>
                            <tr>
                                <td colspan="8" class="no-data">
                                    <br>
                                    <center>
                                        <img src="../img/notfound.svg" width="20%"><br><br>
                                        <p class="heading-main12" style="font-size:16px;color:#444">
                                            No treatment records by other <?php echo h($specname); ?> doctors were found.
                                        </p>
                                    </center>
                                    <br>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while($row = $treatments->fetch_assoc()): 
                                $treating_doc = $row['treating_docname'] ? $row['treating_docname'] : $row['doctor_email'];
                                $patient_name = $row['patient_name'] ? $row['patient_name'] : '-';
                                $patient_email = $row['patient_email'] ? $row['patient_email'] : '-';
                                $diagnosis = $row['diagnosis'] ? $row['diagnosis'] : '-';
                                $plan = $row['treatment_plan'] ? $row['treatment_plan'] : '-';
                                $meds = $row['prescribed_medicines'] ? $row['prescribed_medicines'] : '-';
                                $tdate = $row['treatment_date'] && $row['treatment_date'] != '0000-00-00' ? $row['treatment_date'] : '-';
                                $fdate = $row['followup_date'] && $row['followup_date'] != '0000-00-00' ? $row['followup_date'] : '-';
                            ?>
                            <tr>
                                <td><?php echo h($treating_doc); ?></td>
                                <td><?php echo h($patient_name); ?></td>
                                <td><?php echo h($patient_email); ?></td>
                                <td style="max-width:200px; word-wrap: break-word;"><?php echo h($diagnosis); ?></td>
                                <td style="max-width:200px; word-wrap: break-word;"><?php echo h($plan); ?></td>
                                <td style="max-width:160px; word-wrap: break-word;"><?php echo h($meds); ?></td>
                                <td><?php echo h($tdate); ?></td>
                                <td><?php echo h($fdate); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </center>
        </div>
    </div>
</body>
</html>
