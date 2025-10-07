<?php
session_start();
if(!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'a'){
    header("location: ../login.php");
    exit();
}

include("../connection.php");

// Add new drug
if(isset($_POST['add'])){
    $name = $_POST['drug_name'];
    $type = $_POST['drug_type'];
    $manufacturer = $_POST['manufacturer'];
    $form = $_POST['dosage_form'];
    $strength = $_POST['strength'];
    $qty = $_POST['quantity'];
    $price = $_POST['price'];
    $expiry = $_POST['expiry_date'];

    $sql = "INSERT INTO drugs (drug_name, drug_type, manufacturer, dosage_form, strength, quantity, price, expiry_date) 
            VALUES ('$name','$type','$manufacturer','$form','$strength','$qty','$price','$expiry')";
    $database->query($sql);
}

// Delete drug
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $database->query("DELETE FROM drugs WHERE drug_id='$id'");
    header("location: drugs.php");
    exit();
}

// Fetch drugs
$result = $database->query("SELECT * FROM drugs ORDER BY drug_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/dark-mode.css">

    <title>Manage Drugs</title>
    <style>
        .sub-table th, .sub-table td {
            padding: 8px;
            text-align: center;
        }
        .form-container input {
            margin: 5px;
            padding: 6px;
        }
        .form-container button {
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
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
                                    <a href="../logout.php">
                                        <input type="button" value="Log out" class="logout-btn btn-primary-soft btn">
                                    </a>
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

                <!-- Active Drugs -->
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-drugs menu-active menu-icon-drug-active">
                        <a href="drugs.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Drugs</p></div></a>
                    </td>
                </tr>

                <tr class="menu-row">
                    <td class="menu-btn menu-icon-delete-request">
                        <a href="doctors-delete-request.php" class="non-style-link-menu"><div><p class="menu-text">Doctor Deletion Requests</p></div></a>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Content -->
        <div class="dash-body" style="margin-top: 15px; padding: 20px;">
            <h2>Manage Drugs</h2>

            <!-- Add Drug Form -->
            <div class="form-container">
                <form method="post">
                    <input type="text" name="drug_name" placeholder="Drug Name" required>
                    <input type="text" name="drug_type" placeholder="Drug Type">
                    <input type="text" name="manufacturer" placeholder="Manufacturer">
                    <input type="text" name="dosage_form" placeholder="Dosage Form (Tablet, Syrup)">
                    <input type="text" name="strength" placeholder="Strength (500mg)">
                    <input type="number" name="quantity" placeholder="Quantity">
                    <input type="number" step="0.01" name="price" placeholder="Price">
                    <input type="date" name="expiry_date">
                    <button type="submit" name="add" class="btn btn-primary">Add Drug</button>
                </form>
            </div>

            <!-- Drug List -->
            <h3 style="margin-top:20px;">Drugs List</h3>
            <table width="100%" border="1" class="sub-table scrolldown" style="border-collapse: collapse;">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Manufacturer</th>
                    <th>Form</th>
                    <th>Strength</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Expiry</th>
                    <th>Action</th>
                </tr>
                <?php if($result->num_rows > 0){ 
                    while($row = $result->fetch_assoc()){ ?>
                    <tr>
                        <td><?php echo $row['drug_id']; ?></td>
                        <td><?php echo $row['drug_name']; ?></td>
                        <td><?php echo $row['drug_type']; ?></td>
                        <td><?php echo $row['manufacturer']; ?></td>
                        <td><?php echo $row['dosage_form']; ?></td>
                        <td><?php echo $row['strength']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td><?php echo $row['price']; ?></td>
                        <td><?php echo $row['expiry_date']; ?></td>
                        <td>
                            <a href="drugs.php?delete=<?php echo $row['drug_id']; ?>" onclick="return confirm('Delete this drug?')">Delete</a>
                        </td>
                    </tr>
                <?php } 
                } else { ?>
                    <tr><td colspan="10">No drugs found</td></tr>
                <?php } ?>
            </table>
        </div>
    </div>
    <script src="../darkmode.js"></script>
</body>
</html>
