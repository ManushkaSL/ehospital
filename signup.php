<?php
session_start();
include("connection.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$error = "";

// Function to send OTP
function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'aingaran742@gmail.com'; // Gmail
        $mail->Password   = ''; // <-- Put your 16-char App Password here
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('aingaran742@gmail.com', 'eHospital');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Your OTP for Signup - eHospital";
        $mail->Body    = "Your OTP is: <b>$otp</b>. Please enter this to continue signup.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// -------------------- Step 1: Check Email & Send OTP --------------------
if (isset($_POST['check_email'])) {
    $email = $_POST['email'];

    $stmt = $database->prepare("SELECT * FROM webuser WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $error = "<p style='color:red;text-align:center;'>Email already registered. Try login.</p>";
    } else {
        $otp = rand(100000, 999999);
        $_SESSION['pending_email'] = $email;
        $_SESSION['signup_otp'] = $otp;
        $_SESSION['otp_time'] = time();

        if (sendOTP($email, $otp)) {
            $error = "<p style='color:green;text-align:center;'>OTP sent to $email. Please check your inbox.</p>";
        } else {
            $error = "<p style='color:red;text-align:center;'>Error sending OTP. Try again later.</p>";
        }
    }
}

// -------------------- Step 2: Verify OTP --------------------
if (isset($_POST['verify_otp'])) {
    $enteredOtp = $_POST['otp'];
    if ($enteredOtp == $_SESSION['signup_otp']) {
        $_SESSION['signup_email'] = $_SESSION['pending_email'];
        unset($_SESSION['pending_email'], $_SESSION['signup_otp'], $_SESSION['otp_time']);
        $_SESSION['otp_verified'] = true;
        $error = "<p style='color:green;text-align:center;'>Email verified. Please fill in your account details.</p>";
    } else {
        $error = "<p style='color:red;text-align:center;'>Invalid OTP. Try again.</p>";
    }
}

// -------------------- Step 3: Resend OTP --------------------
if (isset($_POST['resend_otp']) && isset($_SESSION['pending_email'])) {
    $timePassed = time() - $_SESSION['otp_time'];
    if ($timePassed < 300) {
        $remaining = 300 - $timePassed;
        $error = "<p style='color:red;text-align:center;'>You can request a new OTP after $remaining seconds.</p>";
    } else {
        $otp = rand(100000, 999999);
        $_SESSION['signup_otp'] = $otp;
        $_SESSION['otp_time'] = time();
        $email = $_SESSION['pending_email'];

        if (sendOTP($email, $otp)) {
            $error = "<p style='color:green;text-align:center;'>New OTP sent to $email.</p>";
        } else {
            $error = "<p style='color:red;text-align:center;'>Failed to resend OTP. Try again later.</p>";
        }
    }
}

// -------------------- Step 4: Create Account --------------------
if (isset($_POST['create_account']) && isset($_SESSION['otp_verified'])) {
    $pname = $_POST['pname'];
    $paddress = $_POST['paddress'];
    $pnic = $_POST['pnic'];
    $pdob = $_POST['pdob'];
    $email = $_SESSION['signup_email']; // use verified email
    $tele = $_POST['tele'];
    $newpassword = $_POST['newpassword'];
    $cpassword = $_POST['cpassword'];

    if ($newpassword === $cpassword) {
        $result = $database->query("SELECT * FROM webuser WHERE email='$email'");
        if ($result->num_rows > 0) {
            $error = "<p style='color:red;text-align:center;'>Account already exists for this email.</p>";
        } else {
            $hashed_password = password_hash($newpassword, PASSWORD_DEFAULT);

            // Insert into patient
            $database->query("INSERT INTO patient(pemail, pname, ppassword, paddress, pnic, pdob, ptel) 
                              VALUES('$email','$pname','$hashed_password','$paddress','$pnic','$pdob','$tele')");

            // Insert into webuser
            $database->query("INSERT INTO webuser VALUES('$email','p')");

            // Set session and redirect
            $_SESSION["user"] = $email;
            $_SESSION["usertype"] = "p";
            $_SESSION["username"] = $pname;

            header('Location: patient/index.php');
            exit();
        }
    } else {
        $error = "<p style='color:red;text-align:center;'>Password confirmation does not match.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - eHospital</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fc;
            margin: 0;
            padding: 0;
        }
        .card {
            background: white;
            max-width: 500px;
            margin: 80px auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        input {
            width: 90%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
            transition: border 0.3s;
        }
        input:focus {
            border-color: #4e73df;
        }
        button {
            width: 95%;
            padding: 12px;
            margin-top: 10px;
            background: #4e73df;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #2e59d9;
        }
        p {
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Sign Up</h2>
    <?php echo $error; ?>

    <!-- Step 1: Enter Email -->
    <?php if(!isset($_SESSION['pending_email']) && !isset($_SESSION['otp_verified'])): ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Enter Email" required>
            <button type="submit" name="check_email">Send OTP</button>
        </form>

    <!-- Step 2: Verify OTP -->
    <?php elseif(isset($_SESSION['pending_email']) && !isset($_SESSION['otp_verified'])): ?>
        <form method="POST">
            <input type="text" name="otp" placeholder="Enter OTP" required>
            <button type="submit" name="verify_otp">Verify OTP</button>
        </form>
        <form method="POST">
            <button type="submit" name="resend_otp">Resend OTP</button>
        </form>

    <!-- Step 3: Create Account -->
    <?php elseif(isset($_SESSION['otp_verified'])): ?>
        <form method="POST">
            <input type="text" name="pname" placeholder="Full Name" required><br>
            <input type="text" name="paddress" placeholder="Address" required><br>
            <input type="text" name="pnic" placeholder="NIC Number" required><br>
            <input type="date" name="pdob" required><br>
            <input type="email" name="email" value="<?php echo $_SESSION['signup_email']; ?>" readonly><br>
            <input type="tel" name="tele" placeholder="Mobile Number" pattern="[0]{1}[0-9]{9}" required><br>
            <input type="password" name="newpassword" placeholder="New Password" required><br>
            <input type="password" name="cpassword" placeholder="Confirm Password" required><br>
            <button type="submit" name="create_account">Create Account</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
