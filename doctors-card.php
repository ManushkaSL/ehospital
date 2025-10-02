<?php
// doctors-card.php

$servername = "localhost";
$username = "root";   // change if needed
$password = "";       // change if needed
$dbname = "ehospital";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Fetch doctors with specialties
$sql = "SELECT d.docid, d.docname, s.sname 
        FROM doctor d 
        LEFT JOIN specialties s ON d.specialties = s.id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $docid = $row['docid'];
        $docname = $row['docname'];
        $specialty = $row['sname'] ?? "General";

        echo '<div class="doctor-card">';
        // Doctor image should be stored in /images/doctors/ as {docid}.jpg
        echo '<img src="images/doctors/'.$docid.'.jpg" alt="'.$docname.'">';
        echo '<h3>'.$docname.'</h3>';
        echo '<p>'.$specialty.'</p>';
        // Link to new book-appointment.php, passing doctor id
        echo '<a href="book-appointment.php?docid='.$docid.'">Book Appointment</a>';
        echo '</div>';
    }
} else {
    echo "<p style='text-align:center;'>No doctors available right now.</p>";
}

$conn->close();
?>
