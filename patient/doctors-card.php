<?php
// doctors-card.php for patient view
// Reuse existing connection
if (!isset($database)) {
    include("../connection.php");
}

// Fetch doctors with specialties
$sql = "SELECT d.docid, d.docname, d.docemail, d.doctel, s.sname, 
               d.qualification, d.experience, d.hospital, d.consultationFee, 
               d.availability, d.description
        FROM doctor d 
        LEFT JOIN specialties s ON d.specialties = s.id
        ORDER BY d.docname ASC";
$result = $database->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $docid = htmlspecialchars($row['docid']);
        $docname = htmlspecialchars($row['docname']);
        $specialty = htmlspecialchars($row['sname'] ?? "General");
        $qualification = htmlspecialchars($row['qualification'] ?? '');
        $hospital = htmlspecialchars($row['hospital'] ?? '');
        $consultationFee = htmlspecialchars($row['consultationFee'] ?? '');
        $experience = htmlspecialchars($row['experience'] ?? '');
        $availability = htmlspecialchars($row['availability'] ?? '');
        $description = htmlspecialchars($row['description'] ?? '');
        
        // Get doctor initials
        $nameParts = explode(' ', $docname);
        $initials = '';
        foreach($nameParts as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        if(strlen($initials) > 2) {
            $initials = substr($initials, 0, 2);
        }

        echo '<div class="doctor-card">';
        
        // Check if image exists
        $imagePath = "../img/doctors/".$docid.".jpg";
        if(file_exists($imagePath)) {
            echo '<img src="'.$imagePath.'" alt="'.$docname.'" style="width:120px;height:120px;border-radius:50%;object-fit:cover;margin-bottom:20px;">';
        } else {
            echo '<div style="width:120px;height:120px;border-radius:50%;background:linear-gradient(45deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:white;font-size:36px;font-weight:bold;margin:0 auto 20px;">'.$initials.'</div>';
        }
        
        echo '<h3>'.$docname.'</h3>';
        echo '<p style="color:#0077cc;font-weight:600;">'.$specialty.'</p>';
        
        if(!empty($qualification)) {
            echo '<p style="font-size:13px;margin:5px 0;"><strong>Qualification:</strong> '.$qualification.'</p>';
        }
        if(!empty($experience)) {
            echo '<p style="font-size:13px;margin:5px 0;"><strong>Experience:</strong> '.$experience.'</p>';
        }
        if(!empty($hospital)) {
            echo '<p style="font-size:13px;margin:5px 0;"><strong>Hospital:</strong> '.$hospital.'</p>';
        }
        if(!empty($consultationFee)) {
            echo '<p style="font-size:13px;margin:5px 0;"><strong>Fee:</strong> '.$consultationFee.'</p>';
        }
        if(!empty($availability)) {
            echo '<p style="font-size:13px;margin:5px 0;"><strong>Availability:</strong> '.$availability.'</p>';
        }
        
        // View details button
        echo '<a href="?action=view&id='.$docid.'" style="display:inline-block;margin-top:15px;padding:10px 20px;background:#00bfff;color:white;text-decoration:none;border-radius:8px;font-weight:600;">View Details</a>';
        echo '</div>';
        
        echo '<a href="book-appointment.php?docid='.$docid.'" class="non-style-link"><button class="btn-primary-soft btn" style="padding:10px 20px;font-size:14px;">Book Appointment</button></a>';
        echo '</div>';
    }
} else {
    echo '<div style="text-align:center;padding:40px;color:#666;">';
    echo '<p style="font-size:18px;">No doctors available right now.</p>';
    echo '</div>';
}
?>