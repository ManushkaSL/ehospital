<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    
    <title>ehospital</title>
    <style>
        /* Full background with glass overlay */
        .glass {
            background: rgba(0, 0, 0, 0.5); /* semi-transparent dark */
            min-height: 100vh;
            height: auto; 
            background-attachment: fixed;

            /* Glass effect */
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        table {
            animation: transitionIn-Y-bottom 0.5s;
        }

        /* Doctors Section */
        .doctor-section {
            padding: 50px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .doctor-section h2 {
            text-align: center;
            font-size: 36px;
            margin-bottom: 50px;
            color: #0077cc;
        }

        .doctor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .doctor-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.2);
        }

        .doctor-card img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
        }

        .doctor-card h3 {
            color: #0077cc;
            margin-bottom: 10px;
        }

        .doctor-card p {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }

        .doctor-card a {
            background: #00bfff; /* bright sky-blue */
            color: #fff;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(0,191,255,0.4);
        }

        .doctor-card a:hover {
            background: #0099cc;
            box-shadow: 0 6px 20px rgba(0,153,204,0.5);
        }

        /* View All Doctors Button */
        .view-all-doctors {
            text-align: center;
            margin-top: 40px;
        }

        .view-all-btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: #fff;
            padding: 15px 40px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 18px;
            display: inline-block;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .view-all-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            color: #004d80;
            font-size: 14px;
        }

    </style>
</head>
<body class="index-page">
    
    <div class="full-height">
        <center>
        <table border="0">
            <tr>
                <td width="80%">
                    <font class="edoc-logo">ehospital. </font>
                    <font class="edoc-logo-sub">| THE ECHANNELING PROJECT</font>
                </td>
                
                <td width="10%">
                    <a href="signup.php" class="non-style-link">
                        <p class="nav-item" style="padding-right: 10px;">REGISTER</p>
                    </a>
                </td>
            </tr>
            
            <tr>
                <td colspan="3">
                    <p class="heading-text">Avoid Hassles & Delays.</p>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <p class="sub-text2">How is health today, Sounds like not good!<br>
                    Don't worry. Find your doctor online Book as you wish with eDoc. <br>
                    We offer you a free doctor channeling service, Make your appointment now.</p>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                <center>
                    <div class="button-group">
                        <a href="doctors.php" class="view-doctors-btn btn" 
                           style="padding: 12px 30px; margin-right: 15px;
                                  background: rgba(255, 255, 255, 0.9);
                                  color: #0066cc;
                                  border: 2px solid #0066cc;
                                  border-radius: 8px;
                                  text-decoration: none;
                                  font-weight: 600;
                                  font-size: 16px;
                                  display: inline-block;
                                  transition: all 0.3s;">
                           VIEW DOCTORS
                        </a>
                        <a href="login.php" class="login-btn btn-primary btn" 
                           style="padding: 12px 30px;
                                  background: #0066cc;
                                  color: white;
                                  border: 2px solid #0066cc;
                                  border-radius: 8px;
                                  text-decoration: none;
                                  font-weight: 600;
                                  font-size: 16px;
                                  display: inline-block;
                                  transition: all 0.3s;">
                           LOGIN
                        </a>
                    </div>
                </center>
                </td>
            </tr>
        </table>
        <p class="sub-text2 footer-hashen">A Web Solution by Hashen and modifying by group4</p>
        </center>
    </div>

    <!-- Doctors Section -->
     <div class="full-height">
        <div class="doctor-section">
        <h2 style="text-align:center; margin-bottom:50px; color:#fff;">Our Doctors</h2>
            <div class="doctor-grid ">
                <?php include("doctors-card.php"); ?>
            </div>
            <div class="view-all-doctors">
                <a href="doctors.php" class="view-all-btn">View All Doctors</a>
            </div>
        </div>
     </div>
    
    <script src="darkmode.js"></script>
</body>
</html>