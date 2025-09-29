<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/dark-mode.css">
    <link rel="stylesheet" href="css/login.css">
    <title>Login</title>
</head>
<body>
<?php
session_start();
$_SESSION["user"] = "";
$_SESSION["usertype"] = "";

date_default_timezone_set('Asia/Kolkata');
$_SESSION["date"] = date('Y-m-d');

include("connection.php");

$error = '<label for="promter" class="form-label">&nbsp;</label>';

if ($_POST) {
    $email = $_POST['useremail'];
    $password = $_POST['userpassword'];

    $result = $database->query("SELECT * FROM webuser WHERE email='$email'");
    if ($result->num_rows == 1) {
        $utype = $result->fetch_assoc()['usertype'];

        if ($utype == 'p') {
            $checker = $database->query("SELECT * FROM patient WHERE pemail='$email'");
            if ($checker->num_rows == 1) {
                $row = $checker->fetch_assoc();
                if (password_verify($password, $row['ppassword'])) {
                    $_SESSION['user'] = $email;
                    $_SESSION['usertype'] = 'p';
                    header('location: patient/index.php');
                    exit;
                }
            }
        } elseif ($utype == 'a') {
            $checker = $database->query("SELECT * FROM admin WHERE aemail='$email'");
            if ($checker->num_rows == 1) {
                $row = $checker->fetch_assoc();
                if (password_verify($password, $row['apassword'])) {
                    $_SESSION['user'] = $email;
                    $_SESSION['usertype'] = 'a';
                    header('location: admin/index.php');
                    exit;
                }
            }
        } elseif ($utype == 'd') {
            $checker = $database->query("SELECT * FROM doctor WHERE docemail='$email'");
            if ($checker->num_rows == 1) {
                $row = $checker->fetch_assoc();
                if (password_verify($password, $row['docpassword'])) {
                    $_SESSION['user'] = $email;
                    $_SESSION['usertype'] = 'd';
                    header('location: doctor/index.php');
                    exit;
                }
            }
        }

        // wrong password fallback
        $error = '<label for="promter" class="form-label" style="color:red;text-align:center;">Invalid email or password</label>';

    } else {
        $error = '<label for="promter" class="form-label" style="color:red;text-align:center;">No account found for this email.</label>';
    }
}
?>

<center>
<div class="container">
    <table border="0" style="margin: 0;padding: 0;width: 60%;">
        <tr>
            <td>
                <p class="header-text">Welcome Back!</p>
            </td>
        </tr>
        <div class="form-body">
        <tr>
            <td>
                <p class="sub-text">Login with your details to continue</p>
            </td>
        </tr>
        <tr>
            <form action="" method="POST" >
            <td class="label-td">
                <label for="useremail" class="form-label">Email: </label>
            </td>
        </tr>
        <tr>
            <td class="label-td">
                <input type="email" name="useremail" class="input-text" placeholder="Email Address" required>
            </td>
        </tr>
        <tr>
            <td class="label-td">
                <label for="userpassword" class="form-label">Password: </label>
            </td>
        </tr>
        <tr>
            <td class="label-td">
                <input type="Password" name="userpassword" class="input-text" placeholder="Password" required>
            </td>
        </tr>

        <!-- Display errors -->
        <tr>
            <td><br>
            <?php echo $error ?>
            </td>
        </tr>

        <tr>
            <td>
                <input type="submit" value="Login" class="login-btn btn-primary btn">
            </td>
        </tr>

        <!-- Forgot Password link -->
        <tr>
            <td>
                <br>
                <a href="forgot-password.php" class="hover-link1 non-style-link">Forgot Password?</a>
            </td>
        </tr>

        </div>
        <tr>
            <td>
                <br>
                <label for="" class="sub-text" style="font-weight: 280;">Don't have an account&#63; </label>
                <a href="signup.php" class="hover-link1 non-style-link">Sign Up</a>
                <br><br><br>
            </td>
        </tr>
        </form>
    </table>
</div>
</center>

<script src="darkmode.js"></script>

</body>
</html>
