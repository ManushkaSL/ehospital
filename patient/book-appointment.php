<?php
session_start();
include("../connection.php");

$message = "";
$showForm = false;

// Get doctor ID from URL
$docid = isset($_GET['docid']) ? intval($_GET['docid']) : 0;

// Check if user is already logged in
if(isset($_SESSION["user"]) && $_SESSION['usertype']=='p'){
    $useremail = $_SESSION["user"];
    $userrow = $database->query("select * from patient where pemail='$useremail'");
    $userfetch = $userrow->fetch_assoc();
    $pid = $userfetch["pid"];
    $showForm = true;
}

// Step 1: Patient login
if(isset($_POST['login']) && !isset($_SESSION["user"])) {
    $email = $database->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM patient WHERE pemail='$email' LIMIT 1";
    $result = $database->query($query);

    if($result->num_rows == 1) {
        $patient = $result->fetch_assoc();
        if(password_verify($password, $patient['ppassword'])) {
            $_SESSION['user'] = $patient['pemail'];
            $_SESSION['usertype'] = 'p';
            $pid = $patient['pid'];
            $showForm = true;
        } else {
            $message = "Incorrect email or password!";
        }
    } else {
        $message = "Incorrect email or password!";
    }
}

// Step 2: Booking submission
if(isset($_POST['book']) && isset($_SESSION['user'])) {
    $useremail = $_SESSION["user"];
    $userrow = $database->query("select * from patient where pemail='$useremail'");
    $userfetch = $userrow->fetch_assoc();
    $pid = intval($userfetch["pid"]);
    
    $docid = intval($_POST['docid']);
    $appodate = $database->real_escape_string($_POST['appodate']);
    $appotime = $database->real_escape_string($_POST['appotime']);
    
    // Create a schedule title
    $scheduleTitle = "Appointment - " . date('M d, Y', strtotime($appodate));
    
    // Insert into schedule table
    $insertSchedule = "INSERT INTO schedule(docid, title, scheduledate, scheduletime, nop) 
                      VALUES ($docid, '$scheduleTitle', '$appodate', '$appotime', 50)";
    
    if($database->query($insertSchedule)) {
        $scheduleid = $database->insert_id;
        
        // Get appointment number
        $appoNumQuery = "SELECT COUNT(*) AS total FROM appointment WHERE scheduleid=$scheduleid";
        $appoNumResult = $database->query($appoNumQuery);
        $appoNumRow = $appoNumResult->fetch_assoc();
        $apponum = intval($appoNumRow['total']) + 1;
        
        // Insert appointment
        $sqlInsert = "INSERT INTO appointment(pid, apponum, scheduleid, appodate) 
                     VALUES ($pid, $apponum, $scheduleid, '$appodate')";
        
        if($database->query($sqlInsert)) {
            header("location: appointment.php?action=booking-added&id=$apponum");
            exit;
        } else {
            $message = "Error booking appointment: " . $database->error;
        }
    } else {
        $message = "Error creating schedule: " . $database->error;
    }
}

// Fetch doctor details including availability
$doctor = null;
$availableTimes = [];
$availableDays = [];

if($docid > 0) {
    $sqlDoc = "SELECT d.docid, d.docname, d.docemail, d.doctel, s.sname AS specialty,
               d.availability, d.consultationFee, d.hospital
               FROM doctor d
               LEFT JOIN specialties s ON d.specialties = s.id
               WHERE d.docid = $docid
               LIMIT 1";
    $resDoc = $database->query($sqlDoc);
    if($resDoc->num_rows == 1) {
        $doctor = $resDoc->fetch_assoc();
        
        // Parse availability (e.g., "Mon-Thu 7AM-4PM")
        if(!empty($doctor['availability'])) {
            $availability = $doctor['availability'];
            
            // Extract days and times
            if(preg_match('/([A-Za-z\-]+)\s+(\d+[AP]M)\-(\d+[AP]M)/', $availability, $matches)) {
                $daysStr = $matches[1];
                $startTime = $matches[2];
                $endTime = $matches[3];
                
                // Parse days (Mon-Thu or Mon,Wed,Fri)
                if(strpos($daysStr, '-') !== false) {
                    // Range format: Mon-Thu
                    $dayRange = explode('-', $daysStr);
                    $allDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                    $startIdx = array_search($dayRange[0], $allDays);
                    $endIdx = array_search($dayRange[1], $allDays);
                    if($startIdx !== false && $endIdx !== false) {
                        for($i = $startIdx; $i <= $endIdx; $i++) {
                            $availableDays[] = $allDays[$i];
                        }
                    }
                } else {
                    // Comma-separated: Mon,Wed,Fri
                    $availableDays = explode(',', str_replace(' ', '', $daysStr));
                }
                
                // Generate time slots between start and end time
                $start = strtotime(str_replace(['AM','PM'], [' AM',' PM'], $startTime));
                $end = strtotime(str_replace(['AM','PM'], [' AM',' PM'], $endTime));
                
                while($start < $end) {
                    $availableTimes[] = date('H:i:s', $start);
                    $start = strtotime('+1 hour', $start);
                }
            }
        }
    } else {
        $message = "Invalid doctor selected!";
    }
}

// If no times parsed, provide default times
if(empty($availableTimes)) {
    $availableTimes = ['09:00:00','10:00:00','11:00:00','12:00:00','13:00:00','14:00:00','15:00:00','16:00:00','17:00:00'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book Appointment</title>
<link rel="stylesheet" href="../css/animations.css">  
<link rel="stylesheet" href="../css/main.css">
<link rel="stylesheet" href="../css/admin.css">
<style>
body { background: #f0f4f8; font-family: Arial, sans-serif; }
.container-booking { max-width: 600px; margin: 50px auto; padding: 30px; background: white; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
h2 { text-align: center; color: #333; margin-bottom: 20px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 600; }
.form-group input, .form-group select { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; font-size: 14px; box-sizing: border-box; }
.btn-submit { width: 100%; padding: 12px; background: #00bfff; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; margin-top: 10px; }
.btn-submit:hover { background: #009acd; }
.btn-back { width: 100%; padding: 12px; background: #6c757d; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; margin-top: 10px; text-decoration: none; display: inline-block; text-align: center; }
.btn-back:hover { background: #5a6268; }
.message { padding: 12px; margin-bottom: 20px; border-radius: 8px; text-align: center; }
.message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.doctor-info { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 15px; margin-bottom: 25px; color: white; }
.doctor-info h3 { margin: 0 0 15px 0; color: white; font-size: 24px; }
.doctor-info p { margin: 8px 0; color: rgba(255,255,255,0.95); font-size: 15px; }
.info-badge { display: inline-block; background: rgba(255,255,255,0.2); padding: 5px 12px; border-radius: 20px; margin: 5px 5px 5px 0; font-size: 13px; }
.availability-note { background: #d1ecf1; border: 1px solid #0c5460; padding: 15px; border-radius: 8px; margin-bottom: 20px; color: #0c5460; }
.availability-note strong { display: block; margin-bottom: 5px; }
</style>
<script>
// JavaScript to validate date based on available days
<?php if(!empty($availableDays)): ?>
var availableDays = <?php echo json_encode($availableDays); ?>;
var dayMap = {
    'Sun': 0, 'Mon': 1, 'Tue': 2, 'Wed': 3, 
    'Thu': 4, 'Fri': 5, 'Sat': 6
};

function validateDate() {
    var dateInput = document.getElementById('appodate');
    var selectedDate = new Date(dateInput.value);
    var dayName = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][selectedDate.getDay()];
    
    if(availableDays.indexOf(dayName) === -1) {
        alert('Doctor is not available on ' + dayName + '. Available days: ' + availableDays.join(', '));
        dateInput.value = '';
        return false;
    }
    return true;
}
<?php endif; ?>
</script>
</head>
<body>
<div class="container-booking">

<h2>Book Appointment</h2>

<?php if($message != ""): ?>
<div class="message <?php echo (strpos($message, 'Error') !== false || strpos($message, 'Incorrect') !== false) ? 'error' : 'success'; ?>">
    <?php echo $message; ?>
</div>
<?php endif; ?>

<?php if(!$showForm && !isset($_SESSION["user"])): ?>
<!-- Login Form -->
<form method="post">
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required>
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
    </div>
    <button type="submit" name="login" class="btn-submit">Login to Book</button>
    <a href="doctors.php" class="btn-back">Back to Doctors</a>
</form>

<?php elseif($doctor && $showForm): ?>
<!-- Booking Form -->
<div class="doctor-info">
    <h3><?php echo htmlspecialchars($doctor['docname']); ?></h3>
    <p><strong>Specialization:</strong> <?php echo htmlspecialchars($doctor['specialty']); ?></p>
    <?php if(!empty($doctor['hospital'])): ?>
    <p><strong>Hospital:</strong> <?php echo htmlspecialchars($doctor['hospital']); ?></p>
    <?php endif; ?>
    <?php if(!empty($doctor['consultationFee'])): ?>
    <p><strong>Consultation Fee:</strong> <span class="info-badge"><?php echo htmlspecialchars($doctor['consultationFee']); ?></span></p>
    <?php endif; ?>
</div>

<?php if(!empty($doctor['availability'])): ?>
<div class="availability-note">
    <strong>Doctor's Availability:</strong>
    <div><?php echo htmlspecialchars($doctor['availability']); ?></div>
    <?php if(!empty($availableDays)): ?>
    <small>Available Days: <?php echo implode(', ', $availableDays); ?></small>
    <?php endif; ?>
</div>
<?php endif; ?>

<form method="post" onsubmit="return validateDate();">
    <input type="hidden" name="docid" value="<?php echo $doctor['docid']; ?>">
    
    <div class="form-group">
        <label>Choose Appointment Date</label>
        <input type="date" id="appodate" name="appodate" min="<?php echo date('Y-m-d'); ?>" 
               onchange="validateDate()" required>
        <?php if(!empty($availableDays)): ?>
        <small style="color: #666;">Available: <?php echo implode(', ', $availableDays); ?></small>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label>Preferred Time</label>
        <select name="appotime" required>
            <option value="">-- Select Time --</option>
            <?php foreach($availableTimes as $time): 
                $displayTime = date('h:i A', strtotime($time));
            ?>
            <option value="<?php echo $time; ?>"><?php echo $displayTime; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <button type="submit" name="book" class="btn-submit">Confirm Appointment</button>
    <a href="doctors.php" class="btn-back">Cancel</a>
</form>

<?php else: ?>
<div class="message error">Unable to load doctor information.</div>
<a href="doctors.php" class="btn-back">Back to Doctors</a>
<?php endif; ?>

</div>
</body>
</html>