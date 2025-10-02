<?php
session_start();
include("connection.php");

$message = "";
$showForm = false;

// Get doctor ID from URL (doctor card)
$docid = isset($_GET['docid']) ? intval($_GET['docid']) : 0;

// Step 1: Patient login
if(isset($_POST['login'])) {
    $email = $database->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM patient WHERE pemail='$email' LIMIT 1";
    $result = $database->query($query);

    if($result->num_rows == 1) {
        $patient = $result->fetch_assoc();
        if(password_verify($password, $patient['ppassword'])) {
            $_SESSION['pid'] = $patient['pid'];
            $_SESSION['pname'] = $patient['pname'];
            $_SESSION['pemail'] = $patient['pemail'];
            $showForm = true; // show booking form
        } else {
            $message = "Incorrect email or password!";
        }
    } else {
        $message = "Incorrect email or password!";
    }
}

// Step 2: Booking submission
if(isset($_POST['book']) && isset($_SESSION['pid'])) {
    $pid = intval($_SESSION['pid']);
    $docid = intval($_POST['docid']); // hidden field
    $appodate = $database->real_escape_string($_POST['appodate']);
    $apptime = $database->real_escape_string($_POST['apptime']); // optional, for reference

    // Auto increment scheduleid
    $resMaxSchedule = $database->query("SELECT MAX(scheduleid) AS maxid FROM booking");
    $rowMax = $resMaxSchedule->fetch_assoc();
    $scheduleid = $rowMax['maxid'] ? intval($rowMax['maxid']) + 1 : 1;

    // Appointment number = total bookings for this doctor + 1
    $resAppt = $database->query("SELECT COUNT(*) AS total FROM booking WHERE docid=$docid");
    $rowAppt = $resAppt->fetch_assoc();
    $apponum = intval($rowAppt['total']) + 1;

    // Insert booking
    $sqlInsert = "INSERT INTO booking(pid, docid, scheduleid, apponum, appodate)
                  VALUES ($pid, $docid, $scheduleid, $apponum, '$appodate')";
    if($database->query($sqlInsert)) {
        $message = "Booking Successful! Your appointment number is $apponum on $appodate.";
        $showForm = false;
    } else {
        $message = "Error booking appointment: " . $database->error;
        $showForm = true;
    }
}

// Fetch doctor details for form including specialty
$doctor = null;
if($docid > 0) {
    $sqlDoc = "
        SELECT d.docid, d.docname, d.docemail, d.doctel, s.sname AS specialty
        FROM doctor d
        LEFT JOIN specialties s ON d.specialties = s.id
        WHERE d.docid = $docid
        LIMIT 1
    ";
    $resDoc = $database->query($sqlDoc);
    if($resDoc->num_rows == 1) {
        $doctor = $resDoc->fetch_assoc();
    } else {
        $message = "Invalid doctor selected!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Appointment</title>
<link rel="stylesheet" href="../css/main.css">
<style>
body { background: #f0f4f8; font-family: Arial, sans-serif; }
.container { max-width: 600px; margin: 50px auto; padding: 20px; background: rgba(255,255,255,0.1); backdrop-filter: blur(12px); border-radius: 15px; border: 1px solid rgba(255,255,255,0.2); }
h2 { text-align: center; color: #333; }
input[type="text"], input[type="password"], input[type="date"], input[type="time"], input[type="submit"] { width: 100%; padding: 10px; margin: 10px 0; border-radius: 8px; border: 1px solid #ccc; }
input[type="submit"] { background: #00bfff; color: #fff; border: none; cursor: pointer; }
input[type="submit"]:hover { background: #009acd; }
.message { text-align: center; color: green; margin-bottom: 15px; }
</style>
</head>
<body>
<div class="container">

<h2>Book Appointment</h2>

<?php if($message != ""): ?>
<p class="message"><?php echo $message; ?></p>
<?php endif; ?>

<?php if(!$showForm): ?>
<!-- Login Form -->
<form method="post">
    <label>Email</label>
    <input type="text" name="email" required>
    <label>Password</label>
    <input type="password" name="password" required>
    <input type="submit" name="login" value="Login to Book">
</form>
<?php elseif($doctor): ?>
<!-- Booking Form -->
<form method="post">
    <p><strong>Doctor Name:</strong> <?php echo $doctor['docname']; ?></p>
    <p><strong>Specialization:</strong> <?php echo $doctor['specialty']; ?></p>
    <input type="hidden" name="docid" value="<?php echo $doctor['docid']; ?>">
    <label>Choose Appointment Date</label>
    <input type="date" name="appodate" required>
    <label>Choose Appointment Time (for reference)</label>
    <input type="time" name="apptime" required>
    <input type="submit" name="book" value="Confirm Appointment">
</form>
<?php endif; ?>

</div>
</body>
</html>
