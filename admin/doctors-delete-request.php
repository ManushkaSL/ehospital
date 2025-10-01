<?php
session_start();

if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
        header("location: ../login.php");
    }

}else{
    header("location: ../login.php");
}

// import database
include("../connection.php");

// Fetch all pending requests
$sql = "SELECT dr.id AS request_id, d.docid, d.docname, d.docemail, d.doctel, s.sname AS specialty, dr.requested_at
        FROM delete_requests dr
        JOIN doctor d ON dr.doctor_id = d.docid
        LEFT JOIN specialties s ON d.specialties = s.id
        WHERE dr.status='pending'
        ORDER BY dr.requested_at DESC";
$result = $database->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/dark-mode.css">
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Doctor Deletion Requests</title>
    <style>
        .dashbord-tables{
            animation: transitionIn-Y-over 0.5s;
        }
        .filter-container{
            animation: transitionIn-Y-bottom  0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }

        /* keep small table visuals consistent */
        .sub-table th, .sub-table td {
            padding: 10px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-size: 14px;
        }
        .table-headin {
            font-weight: 600;
            text-align: center;
        }
        /* fallback for action buttons if your admin.css doesn't style them */
        .btn-action {
            padding: 6px 10px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-delete { background-color: #e74c3c; color: #fff; }
        .btn-reject { background-color: #6c757d; color: #fff; }
    </style>
</head>
<body>
    <div class="container">
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
                                    <p class="profile-title">Administrator</p>
                                    <p class="profile-subtitle">admin@edoc.com</p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="../logout.php"><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="padding-top:10px;">
                                    <a href="change-password.php">
                                        <input type="button" value="Change Password" class="logout-btn btn-primary-soft btn">
                                    </a>
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
                    <td class="menu-btn menu-icon-doctor">
                        <a href="doctors.php" class="non-style-link-menu"><div><p class="menu-text">Doctors</p></div></a>
                    </td>
                </tr>

                <tr class="menu-row">
                    <td class="menu-btn menu-icon-schedule">
                        <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">Schedule</p></div></a>
                    </td>
                </tr>

                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">Appointment</p></div></a>
                    </td>
                </tr>

                <tr class="menu-row">
                    <td class="menu-btn menu-icon-patient">
                        <a href="patient.php" class="non-style-link-menu"><div><p class="menu-text">Patients</p></div></a>
                    </td>
                </tr>
                <!-- Drugs menu (kept same position as index.php) -->
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-drugs">
                        <a href="drugs.php" class="non-style-link-menu"><div><p class="menu-text">Drugs</p></div></a>
                    </td>
                </tr>

                <!-- Active: Doctor Deletion Requests (matches index.php style exactly) -->
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-delete-request menu-active">
                        <a href="doctors-delete-request.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Doctor Deletion Requests</p></div></a>
                    </td>
                </tr>

            </table>
        </div>

        <div class="dash-body" style="margin-top: 15px">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;" >
                <tr>
                    <td colspan="4">
                        <p style="padding:10px;padding-left:48px;padding-bottom:0;font-size:23px;font-weight:700;color:var(--primarycolor);">
                            Pending Doctor Account Deletion Requests
                        </p>
                        <p class="dark-mode-text" style="padding-bottom:19px;padding-left:50px;font-size:15px;font-weight:500;line-height:20px;">
                            Approve to remove the doctor's account from the system or reject to deny the request.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td colspan="4">
                        <center>
                            <div class="abc scroll" style="width:95%; margin:auto;">
                                <table class="sub-table scrolldown" width="100%" border="0" style="border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <th class="table-headin">Doctor ID</th>
                                            <th class="table-headin">Name</th>
                                            <th class="table-headin">Email</th>
                                            <th class="table-headin">Specialty</th>
                                            <th class="table-headin">Phone</th>
                                            <th class="table-headin">Requested At</th>
                                            <th class="table-headin">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if(!$result || $result->num_rows == 0){
                                            echo '<tr>
                                                    <td colspan="7" style="padding:40px;">
                                                        <center>
                                                            <img src="../img/notfound.svg" width="20%"><br><br>
                                                            <p class="heading-main12" style="font-size:18px;color:rgb(49,49,49)">No pending deletion requests found.</p>
                                                        </center>
                                                    </td>
                                                  </tr>';
                                        } else {
                                            while($row = $result->fetch_assoc()){
                                                $req_id = $row['request_id'];
                                                $docid = htmlspecialchars($row['docid']);
                                                $docname = htmlspecialchars($row['docname']);
                                                $docemail = htmlspecialchars($row['docemail']);
                                                $specialty = htmlspecialchars($row['specialty']);
                                                $doctel = htmlspecialchars($row['doctel']);
                                                $requested_at = htmlspecialchars($row['requested_at']);
                                                echo '<tr>
                                                        <td style="text-align:center;">'.$docid.'</td>
                                                        <td style="font-weight:600;">'.substr($docname,0,40).'</td>
                                                        <td>'.substr($docemail,0,40).'</td>
                                                        <td>'.substr($specialty,0,25).'</td>
                                                        <td>'.substr($doctel,0,20).'</td>
                                                        <td style="text-align:center;">'.substr($requested_at,0,16).'</td>
                                                        <td style="text-align:center;">
                                                            <a class="non-style-link" href="process-delete-request.php?action=approve&request_id='.$req_id.'">
                                                                <button class="btn-action btn-delete">Delete</button>
                                                            </a>
                                                            &nbsp;
                                                            <a class="non-style-link" href="process-delete-request.php?action=reject&request_id='.$req_id.'">
                                                                <button class="btn-action btn-reject">Reject</button>
                                                            </a>
                                                        </td>
                                                      </tr>';
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </center>
                    </td>
                </tr>

            </table>
        </div>
    </div>

<script src="../darkmode.js"></script>
</body>
</html>
