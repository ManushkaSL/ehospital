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
    header("location: ../login.php");
    exit;
}
$docfetch = $docrow->fetch_assoc();
$doctorname = $docfetch["docname"];
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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/dark-mode.css">
    <link rel="stylesheet" href="../css/admin.css">
    
    <style>
        h2 {
            color: #1a73e8;
            font-weight: 600;
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
    </style>
</head>
<body>
    <div class="container">
        <!-- ✅ Sidebar menu copied from index.php -->
        <div class="menu">
            <table class="menu-container" border="0">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px" >
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?php echo substr($doctorname,0,13) ?>..</p>
                                    <p class="profile-subtitle"><?php echo substr($useremail,0,22) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="../logout.php" ><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-dashbord">
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Dashboard</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Appointments</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-session">
                        <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">My Sessions</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-patient">
                        <a href="patient.php" class="non-style-link-menu"><div><p class="menu-text">My Patients</p></div></a>
                    </td>
                </tr>
                <!-- ✅ Active link here -->
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor menu-active menu-icon-dashbord-active">
                        <a href="other-doctor-activity.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Other Doctors Activity</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></div></a>
                    </td>
                </tr>
            </table>
        </div>

        <!-- ✅ Content -->
        <div class="dash-body" style="margin-top: 15px">
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
    <script src="../darkmode.js"></script>
</body>
</html>
