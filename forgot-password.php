<?php
session_start();
include("connection.php"); // Your DB connection

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$msg = "";

// Tables mapping
$tables = [
    'admin'   => ['role' => 'Admin',   'email_column' => 'aemail',   'pass_column' => 'apassword'],
    'doctor'  => ['role' => 'Doctor',  'email_column' => 'docemail', 'pass_column' => 'docpassword'],
    'patient' => ['role' => 'Patient', 'email_column' => 'pemail',   'pass_column' => 'ppassword']
];

// Step 1: Check Email and Send OTP
if (isset($_POST['check_email'])) {
    $email = $_POST['email'];
    $userFound = false;

    foreach ($tables as $table => $info) {
        $query = "SELECT * FROM $table WHERE {$info['email_column']}='$email'";
        $result = $database->query($query);

        if ($result && $result->num_rows > 0) {
            $userFound = true;
            $_SESSION['reset_email']   = $email;
            $_SESSION['reset_table']   = $table;
            $_SESSION['reset_column']  = $info['email_column'];
            $_SESSION['reset_passcol'] = $info['pass_column'];

            // Generate OTP
            $otp = rand(100000, 999999);
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['otp_time'] = time();

            // Send OTP email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'aingaran742@gmail.com';   // Gmail
                $mail->Password   = ''; // 🔑 Put 16-char App Password here
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('aingaran742@gmail.com', 'E-Hospital');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = "Password Reset OTP - {$info['role']}";
                $mail->Body    = "Hello {$info['role']},<br><br>
                                  Your OTP for password reset is: <b>$otp</b><br>
                                  This OTP will expire in 5 minutes.<br><br>
                                  If you did not request this, please ignore.";

                $mail->send();
                $msg = "OTP sent to your email!";
            } catch (Exception $e) {
                $msg = "Message could not be sent. Error: {$mail->ErrorInfo}";
            }
            break;
        }
    }

    if (!$userFound) {
        $msg = "Email not found in our records!";
    }
}

// Step 2: Verify OTP
if (isset($_POST['verify_otp'])) {
    $enteredOtp = $_POST['otp'];
    if (isset($_SESSION['reset_otp']) && $enteredOtp == $_SESSION['reset_otp']) {
        if (time() - $_SESSION['otp_time'] <= 300) { // 5 minutes expiry
            $_SESSION['otp_verified'] = true;
            $msg = "OTP verified. Please set your new password.";
        } else {
            $msg = "OTP expired! Please try again.";
            unset($_SESSION['reset_otp']);
        }
    } else {
        $msg = "Invalid OTP!";
    }
}

// Step 3: Change Password
if (isset($_POST['change_password']) && isset($_SESSION['otp_verified'])) {
    $newpassword = $_POST['newpassword'];
    $cpassword   = $_POST['cpassword'];

    if ($newpassword === $cpassword) {
        $hashed_password = password_hash($newpassword, PASSWORD_DEFAULT);

        $table   = $_SESSION['reset_table'];
        $email   = $_SESSION['reset_email'];
        $col     = $_SESSION['reset_column'];
        $passCol = $_SESSION['reset_passcol'];

        $update = "UPDATE $table SET $passCol='$hashed_password' WHERE $col='$email'";
        if ($database->query($update)) {
            $msg = "Password successfully changed!";
            session_destroy(); // clear session after reset
        } else {
            $msg = "Error updating password: " . $database->error;
        }
    } else {
        $msg = "Password confirmation does not match!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .card h2 {
            margin-bottom: 20px;
            color: #007bff;
            font-size: 24px;
            text-align: center;
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        input[type="email"], input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            color: #007bff;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2 class="forgot-password-heading">Forgot Password</h2>
            <?php if($msg): ?>
                <p class="message" style="color: <?= strpos($msg,'successfully')!==false || strpos($msg,'OTP sent')!==false || strpos($msg,'verified')!==false ? 'green':'red'; ?>;">
                    <?= $msg ?>
                </p>
            <?php endif; ?>

            <?php if(!isset($_SESSION['reset_email'])): ?>
                <!-- Step 1: Enter Email -->
                <form method="POST" action="">
                    <label>Enter your registered email:</label>
                    <input type="email" name="email" required>
                    <button type="submit" name="check_email" class="btn-primary">Send OTP</button>
                </form>

            <?php elseif(isset($_SESSION['reset_email']) && !isset($_SESSION['otp_verified'])): ?>
                <!-- Step 2: Verify OTP -->
                <form method="POST" action="">
                    <label>Enter the OTP sent to your email:</label>
                    <input type="text" name="otp" required>
                    <button type="submit" name="verify_otp" class="btn-primary">Verify OTP</button>
                </form>

            <?php elseif(isset($_SESSION['otp_verified'])): ?>
                <!-- Step 3: Change Password -->
                <form method="POST" action="">
                    <label>Enter New Password:</label>
                    <input type="password" name="newpassword" required>
                    <label>Confirm Password:</label>
                    <input type="password" name="cpassword" required>
                    <button type="submit" name="change_password" class="btn-primary">Change Password</button>
                </form>
            <?php endif; ?>

            <a href="login.php" class="back-link">⬅ Back to Login</a>
        </div>
    </div>
    <script src="darkmode.js"></script>
</body>
</html>
