<?php
session_start();
if(!isset($_SESSION['patient_id'])) {
    // If not logged in, redirect to login
    header("Location: ../login.php?redirect=patient/book-appointment.php?docid=".$_GET['docid']);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ehospital";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

$patient_id = $_SESSION['patient_id'];
$docid = $_GET['docid'] ?? 0;

// Fetch doctor's schedules
$schedule_sql = "SELECT * FROM schedule WHERE docid='$docid' AND scheduledate >= CURDATE() ORDER BY scheduledate, scheduletime";
$schedules = $conn->query($schedule_sql);

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scheduleid = $_POST['scheduleid'];
    $apponum = $_POST['apponum'];

    // Fetch schedule date
    $schedule_check = $conn->query("SELECT scheduledate FROM schedule WHERE scheduleid='$scheduleid'");
    $schedule_row = $schedule_check->fetch_assoc();
    $appodate = $schedule_row['scheduledate'];

    // Insert into appointment table
    $stmt = $conn->prepare("INSERT INTO appointment (pid, apponum, scheduleid, appodate) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $patient_id, $apponum, $scheduleid, $appodate);
    
    if($stmt->execute()) {
        $success = "Appointment booked successfully!";
    } else {
        $error = "Failed to book appointment.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <style>
        body { font-family: Arial; background: #e0f7fa; padding: 50px; }
        .container { max-width: 500px; margin: auto; background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #0077cc; }
        select, input[type=number] { width: 100%; padding: 10px; margin-bottom: 20px; border-radius: 8px; border: 1px solid #ccc; }
        input[type=submit] { background: #00bfff; color: #fff; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; width: 100%; }
        input[type=submit]:hover { background: #0099cc; }
        .success { color: green; text-align: center; margin-bottom: 15px; }
        .error { color: red; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Book Appointment</h2>
        <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

        <form method="POST">
            <label for="scheduleid">Choose Schedule</label>
            <select name="scheduleid" id="scheduleid" required>
                <?php
                if($schedules->num_rows > 0){
                    while($row = $schedules->fetch_assoc()){
                        echo '<option value="'.$row['scheduleid'].'">';
                        echo $row['title'] . ' | ' . $row['scheduledate'] . ' | ' . $row['scheduletime'];
                        echo '</option>';
                    }
                } else {
                    echo '<option value="">No schedules available</option>';
                }
                ?>
            </select>

            <label for="apponum">Number of Appointments</label>
            <input type="number" name="apponum" id="apponum" min="1" max="10" value="1" required>

            <input type="submit" value="Book Appointment">
        </form>
    </div>
</body>
</html>
