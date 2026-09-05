<?php
session_start();

if(!isset($_SESSION['id_pengguna'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>