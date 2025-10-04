<?php
// Include database connection
include("connection.php");

// Get search parameters if submitted
$searchName = isset($_POST['search']) ? $_POST['search'] : '';
$filterSpecialty = isset($_POST['specialty']) ? $_POST['specialty'] : '';

// Build SQL query with filters
$sql = "SELECT d.*, s.sname as specialty_name 
        FROM doctor d 
        LEFT JOIN specialties s ON d.specialties = s.id 
        WHERE 1=1";

if (!empty($searchName)) {
    $searchName = $database->real_escape_string($searchName);
    $sql .= " AND (d.docname LIKE '%$searchName%' OR d.docemail LIKE '%$searchName%')";
}

if (!empty($filterSpecialty)) {
    $filterSpecialty = $database->real_escape_string($filterSpecialty);
    $sql .= " AND s.sname = '$filterSpecialty'";
}

$sql .= " ORDER BY d.docname";

$result = $database->query($sql);

// Get all specialties for filter dropdown
$specialtiesQuery = "SELECT DISTINCT sname FROM specialties ORDER BY sname";
$specialtiesResult = $database->query($specialtiesQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Doctors - eHospital</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .overlay {
            background: rgba(26, 26, 26, 0.7);
            min-height: 100vh;
            padding: 20px 0;
        }

        .header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 15px 0;
            margin-bottom: 30px;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .edoc-logo {
            color: white;
            font-weight: bolder;
            font-size: 24px;
            text-decoration: none;
        }

        .edoc-logo-sub {
            color: rgba(255, 255, 255, 0.733);
            font-size: 12px;
            margin-left: 5px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
        }

        .nav-item {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-item:hover {
            color: #f0f0f0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .page-title {
            color: white;
            font-size: 36px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 18px;
            text-align: center;
            margin-bottom: 40px;
        }

        .search-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .search-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-input, .filter-select {
            padding: 12px 15px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            outline: none;
        }

        .search-input {
            flex: 1;
            min-width: 250px;
        }

        .filter-select {
            min-width: 180px;
        }

        .search-btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .doctors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .doctor-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 25px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .doctor-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .doctor-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: bold;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .doctor-info h3 {
            color: #333;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .doctor-info .specialty {
            color: #667eea;
            font-size: 16px;
            font-weight: 600;
        }

        .doctor-details {
            border-top: 2px solid #f0f0f0;
            padding-top: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding: 8px 0;
        }

        .detail-label {
            color: #666;
            font-weight: 600;
            font-size: 14px;
        }

        .detail-value {
            color: #333;
            font-size: 14px;
            text-align: right;
        }

        .book-btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .book-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .no-results {
            text-align: center;
            color: white;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 60px 20px;
            border-radius: 15px;
            margin: 40px 0;
        }

        .no-results h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 15px;
            }

            .search-form {
                flex-direction: column;
            }

            .search-input, .filter-select {
                width: 100%;
            }

            .doctors-grid {
                grid-template-columns: 1fr;
            }

            .doctor-header {
                flex-direction: column;
                text-align: center;
            }

            .doctor-avatar {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="overlay">
        <header class="header">
            <div class="nav-container">
                <div>
                    <a href="index.php" class="edoc-logo">ehospital.</a>
                    <span class="edoc-logo-sub">| THE ECHANNELING PROJECT</span>
                </div>
                <nav class="nav-links">
                    <a href="index.php" class="nav-item">HOME</a>
                    <!--<a href="doctors.php" class="nav-item">DOCTORS</a>-->
                    <a href="signup.php" class="nav-item">REGISTER</a>
                    <a href="login.php" class="nav-item">LOGIN</a>
                </nav>
            </div>
        </header>

        <div class="container">
            <h1 class="page-title fade-in">Our Expert Doctors</h1>
            <p class="page-subtitle fade-in">Find and connect with qualified healthcare professionals</p>

            <div class="search-section fade-in">
                <form method="POST" action="" class="search-form">
                    <input type="text" class="search-input" name="search" 
                           placeholder="Search doctors by name or email..." 
                           value="<?php echo htmlspecialchars($searchName); ?>">
                    
                    <select class="filter-select" name="specialty">
                        <option value="">All Specialties</option>
                        <?php
                        while($spec = $specialtiesResult->fetch_assoc()) {
                            $selected = ($spec['sname'] == $filterSpecialty) ? 'selected' : '';
                            echo '<option value="'.htmlspecialchars($spec['sname']).'" '.$selected.'>';
                            echo htmlspecialchars($spec['sname']);
                            echo '</option>';
                        }
                        ?>
                    </select>
                    
                    <button type="submit" class="search-btn">Search</button>
                </form>
            </div>

            <div class="doctors-grid">
                <?php
                if ($result->num_rows > 0) {
                    while($doctor = $result->fetch_assoc()) {
                        // Get initials for avatar
                        $nameParts = explode(' ', $doctor['docname']);
                        $initials = '';
                        foreach($nameParts as $part) {
                            $initials .= strtoupper(substr($part, 0, 1));
                        }
                        if(strlen($initials) > 2) {
                            $initials = substr($initials, 0, 2);
                        }
                        
                        echo '
                        <div class="doctor-card fade-in">
                            <div class="doctor-header">
                                <div class="doctor-avatar">'.$initials.'</div>
                                <div class="doctor-info">
                                    <h3>'.htmlspecialchars($doctor['docname']).'</h3>
                                    <div class="specialty">'.htmlspecialchars($doctor['specialty_name']).'</div>
                                </div>
                            </div>
                            <div class="doctor-details">
                                <div class="detail-row">
                                    <span class="detail-label">Qualification:</span>
                                    <span class="detail-value">'.htmlspecialchars($doctor['qualification']).'</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Experience:</span>
                                    <span class="detail-value">'.htmlspecialchars($doctor['experience']).'</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Hospital:</span>
                                    <span class="detail-value">'.htmlspecialchars($doctor['hospital']).'</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Consultation Fee:</span>
                                    <span class="detail-value">'.htmlspecialchars($doctor['consultationFee']).'</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Phone:</span>
                                    <span class="detail-value">'.htmlspecialchars($doctor['doctel']).'</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Email:</span>
                                    <span class="detail-value">'.htmlspecialchars($doctor['docemail']).'</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Availability:</span>
                                    <span class="detail-value">'.htmlspecialchars($doctor['availability']).'</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Description:</span>
                                    <span class="detail-value">#'.htmlspecialchars($doctor['descripton']).'</span>
                                </div>
                                <button class="book-btn" onclick="bookAppointment('.$doctor['docid'].')">
                                    Book Appointment
                                </button>
                            </div>
                        </div>';
                    }
                } else {
                    echo '
                    <div class="no-results" style="grid-column: 1 / -1;">
                        <h3>No doctors found</h3>
                        <p>Try adjusting your search criteria or check back later</p>
                    </div>';
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        function bookAppointment(doctorId) {
            alert('To book an appointment with this doctor, please login or register first.');
            window.location.href = 'login.php';
        }
    </script>
</body>
</html>
<?php
$database->close();
?>