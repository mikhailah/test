<?php
ob_start();
include("../config/database.php");

// Check if form is submitted
if(empty($_POST)) {
    die("Tiada data POST. Sila submit form.");
}

$emel = mysqli_real_escape_string($conn, $_POST['emel']);
$kata_laluan = $_POST['kata_laluan'];

// Query user
$sql = "SELECT * FROM pengguna WHERE emel = '$emel'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if(!$user) {
    header("Location: login.php?error=Emel tidak berdaftar");
    exit();
}

// Verify password
if(password_verify($kata_laluan, $user['kata_laluan'])) {
    // Set session
    session_start();
    $_SESSION['id_pengguna'] = $user['id_pengguna'];
    $_SESSION['nama_penuh'] = $user['nama_penuh'];
    $_SESSION['peranan'] = $user['peranan'];
    $_SESSION['emel'] = $user['emel'];
    
    // DEBUG - show what's happening (remove after testing)
    // echo "Login successful! Role: " . $user['peranan'] . "<br>";
    // echo "Redirecting to: ";
    
    // Redirect based on role
    if($user['peranan'] == 'pentadbir') {
        // echo "../admin/dashboard_admin.php";
        header("Location: ../admin/dashboard_admin.php");
        exit();
    } else {
        // echo "../dashboard/index.php";
        header("Location: ../dashboard/index.php");
        exit();
    }
} else {
    header("Location: login.php?error=Kata laluan salah");
    exit();
}
?>