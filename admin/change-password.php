<?php
session_start();
include("../connection.php");

// Ensure only admin can access
if (!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'a') {
    header("location: ../login.php");
    exit();
}

$msg = "";
$msg_color = "red"; // default color for error
$show_new_form = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION["user"];

    // Step 1: Verify old password
    if (isset($_POST["old_password"]) && !isset($_POST["new_password"])) {
        $old_password = $_POST["old_password"];

        // Query admin table
        $query = $database->query("SELECT * FROM admin WHERE aemail='$username'");

        if (!$query) {
            $msg = "Database error: " . $database->error;
            $msg_color = "red";
        } else {
            $user = $query->fetch_assoc();

            if ($user && password_verify($old_password, $user["apassword"])) {
                $show_new_form = true; // Old password correct
            } else {
                $msg = "Wrong password, try again.";
                $msg_color = "red";
            }
        }
    }

    // Step 2: Update new password
    if (isset($_POST["new_password"])) {
        $new_password = $_POST["new_password"];
        $confirm_password = $_POST["confirm_password"];

        if ($new_password !== $confirm_password) {
            $msg = "New passwords do not match.";
            $msg_color = "red";
            $show_new_form = true; // keep form open
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $database->query("UPDATE admin SET apassword='$hashed_password' WHERE aemail='$username'");

            if ($update) {
                $msg = "Password changed successfully.";
                $msg_color = "green"; // success message
                $show_new_form = false;
            } else {
                $msg = "Database error: " . $database->error;
                $msg_color = "red";
                $show_new_form = true;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/dark-mode.css">
    <style>
        body {font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f4f6f9; margin:0;}
        .container {display:flex;}
        .content {flex:1; padding:40px;}
        .card {background:#fff; border-radius:12px; padding:30px; max-width:500px; margin:auto; box-shadow:0 4px 10px rgba(0,0,0,0.1);}
        .card h2 {margin-bottom:20px; color:#007bff; font-size:24px; text-align:center;}
        label {font-weight:600; display:block; margin-bottom:8px; color:#333;}
        input[type="password"] {width:100%; padding:10px; border-radius:8px; border:1px solid #ccc; margin-bottom:20px;}
        .btn-primary {width:100%; padding:12px; background:#007bff; border:none; border-radius:8px; color:#fff; font-size:16px; cursor:pointer; transition:0.3s;}
        .btn-primary:hover {background:#0056b3;}
        .message {text-align:center; font-weight:bold; margin-bottom:15px;} /* remove fixed color */
        .back-link {display:block; text-align:center; margin-top:15px; text-decoration:none; color:#007bff; font-weight:500;}
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="card">
                <h2>Change Password</h2>

                <?php if ($msg): ?>
                    <p class="message" style="color: <?= $msg_color ?>;">
                        <?= $msg ?>
                    </p>
                <?php endif; ?>

                <?php if (!$show_new_form): ?>
                    <!-- Step 1: Ask old password -->
                    <form method="POST">
                        <label>Old Password</label>
                        <input type="password" name="old_password" required>
                        <button type="submit" class="btn-primary">Verify</button>
                    </form>
                <?php else: ?>
                    <!-- Step 2: If old password correct → show new password form -->
                    <form method="POST">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>

                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>

                        <button type="submit" class="btn-primary">Update Password</button>
                    </form>
                <?php endif; ?>

                <a href="index.php" class="back-link">⬅ Back to Dashboard</a>
            </div>
        </div>
    </div>
    <script src="../darkmode.js"></script>
</body>
</html>
