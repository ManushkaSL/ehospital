<?php
session_start();
include("connection.php"); // Your DB connection

// Check if token and role are present
if(!isset($_GET['token']) || !isset($_GET['role'])){
    die("Invalid request!");
}

$token = $_GET['token'];
$table = $_GET['role'];

// Check if token exists and is valid
$query = "SELECT * FROM $table WHERE reset_token='$token' AND token_expiry > NOW()";
$result = $database->query($query);

if($result->num_rows == 0){
    die("Invalid or expired token!");
}

// If form submitted
if($_POST){
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if($password !== $confirm_password){
        $error = "Passwords do not match!";
    } else {
        // Hash the new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Update password and remove token
        $update = "UPDATE $table SET password='$hashedPassword', reset_token=NULL, token_expiry=NULL WHERE reset_token='$token'";
        if($database->query
