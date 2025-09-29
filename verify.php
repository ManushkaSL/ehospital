<?php
session_start();
include("connection.php");  // Your DB connection

date_default_timezone_set('Asia/Kolkata');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; }
        .container {
            max-width: 600px;
            margin: 80px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 { color: #333; }
        p { margin: 15px 0; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
<?php
if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $database->real_escape_string($_GET['email']);
    $token = $database->real_escape_string($_GET['token']);

    // Check pending user
    $query = "SELECT * FROM pending_users WHERE email='$email' AND token='$token'";
    $result = $database->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check expiry (1 hour limit)
        $created_at = strtotime($user['created_at']);
        if (time() - $created_at > 3600) { // 3600 seconds = 1 hour
            echo "<h2>Verification Link Expired</h2>";
            echo "<p>Your link has expired. Please sign up again.</p>";
            // Delete expired record
            $database->query("DELETE FROM pending_users WHERE email='$email'");
        } else {
            // Move to patient + webuser
            $name     = $user['name'];
            $password = $user['password']; // already hashed
            $address  = $user['address'];
            $nic      = $user['nic'];
            $dob      = $user['dob'];
            $tele     = $user['tele'];

            // Insert into patient
            $database->query("INSERT INTO patient(pemail, pname, ppassword, paddress, pnic, pdob, ptel)
                              VALUES('$email','$name','$password','$address','$nic','$dob','$tele')");

            // Insert into webuser
            $database->query("INSERT INTO webuser(email, usertype) VALUES('$email','p')");

            // Delete from pending_users
            $database->query("DELETE FROM pending_users WHERE email='$email'");

            echo "<h2>Email Verified!</h2>";
            echo "<p>Your account has been created successfully.</p>";
            echo "<p><a href='login.php'>Click here to login</a></p>";
        }
    } else {
        echo "<h2>Invalid Verification Link</h2>";
        echo "<p>The link is invalid or already used.</p>";
    }
} else {
    echo "<h2>Invalid Request</h2>";
    echo "<p>No verification data found.</p>";
}
?>
</div>
</body>
</html>
