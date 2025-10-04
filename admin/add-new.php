<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Doctor</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
    </style>
</head>
<body>
    <?php
    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }
    }else{
        header("location: ../login.php");
    }

    //import database
    include("../connection.php");

    if($_POST){
        $name = $database->real_escape_string($_POST['name']);
        $nic = $database->real_escape_string($_POST['nic']);
        $spec = intval($_POST['spec']);
        $email = $database->real_escape_string($_POST['email']);
        $tele = $database->real_escape_string($_POST['Tele']);
        $password = $_POST['password'];
        $cpassword = $_POST['cpassword'];

        // Get new fields
        $qualification = $database->real_escape_string($_POST['qualification']);
        $experience = $database->real_escape_string($_POST['experience']);
        $hospital = $database->real_escape_string($_POST['hospital']);
        $consultationFee = $database->real_escape_string($_POST['consultationFee']);
        $availability = $database->real_escape_string($_POST['availability']);
        $descripton = $database->real_escape_string($_POST['descripton']); // Note: descripton not description
        
        if ($password == $cpassword){
            $error='3';
            $result = $database->query("SELECT * FROM webuser WHERE email='$email'");
            
            if($result->num_rows == 1){
                $error='1';
            }else{
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                $sql1 = "INSERT INTO doctor(docemail, docname, docpassword, docnic, doctel, specialties, qualification, experience, hospital, consultationFee, availability, descripton) 
                       VALUES('$email', '$name', '$hashedPassword', '$nic', '$tele', $spec, '$qualification', '$experience', '$hospital', '$consultationFee', '$availability', '$descripton')";
                
                $sql2 = "INSERT INTO webuser VALUES('$email','d')";
                
                if($database->query($sql1) && $database->query($sql2)){
                    $error = '4';
                }else{
                    $error = '3';
                    // For debugging: echo "Error: " . $database->error;
                }
            }
        }else{
            $error='2';
        }
    }else{
        $error='3';
    }

    header("location: doctors.php?action=add&error=".$error);
    ?>
</body>
</html>