<?php
//import database
include("../connection.php");

if($_POST){
    $result= $database->query("select * from webuser");
    $name=$_POST['name'];
    $nic=$_POST['nic'];
    $oldemail=$_POST["oldemail"];
    $spec=$_POST['spec'];
    $email=$_POST['email'];
    $tele=$_POST['Tele'];
    $password=$_POST['password'];
    $cpassword=$_POST['cpassword'];
    $id=$_POST['id00'];
    
    // Get new fields - make sure names match your form
    $qualification=$_POST['qualification'];
    $experience=$_POST['experience'];
    $hospital=$_POST['hospital'];
    $consultationFee=$_POST['consultationFee'];
    $availability=$_POST['availability'];
    $descripton=$_POST['descripton'];

    if ($password==$cpassword){
        $error='3';
        $result= $database->query("select doctor.docid from doctor inner join webuser on doctor.docemail=webuser.email where webuser.email='$email';");
        
        if($result->num_rows==1){
            $id2=$result->fetch_assoc()["docid"];
        }else{
            $id2=$id;
        }
        
        if($id2!=$id){
            $error='1';
        }else{
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // FIXED: Use consultationFee not consultation_fee
            $sql1="UPDATE doctor SET 
                   docemail='$email',
                   docname='$name',
                   docpassword='$hashedPassword',
                   docnic='$nic',
                   doctel='$tele',
                   specialties=$spec,
                   qualification='$qualification',
                   experience='$experience',
                   hospital='$hospital',
                   consultationFee='$consultationFee',
                   availability='$availability',
                   descripton='$descripton'
                   WHERE docid=$id;";
            $database->query($sql1);
            
            $sql2="update webuser set email='$email' where email='$oldemail';";
            $database->query($sql2);
            
            $error= '4';
        }
        
    }else{
        $error='2';
    }
}else{
    $error='3';
}

header("location: doctors.php?action=edit&error=".$error."&id=".$id);
?>