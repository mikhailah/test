<?php
include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];
$id_aduan = mysqli_real_escape_string($conn, $_POST['id_aduan']);
$mesej = mysqli_real_escape_string($conn, $_POST['mesej']);

if(empty($mesej)) {
    header("Location: detail_aduan.php?id=$id_aduan");
    exit();
}

// Insert message
$sql = "INSERT INTO aduan_chat (id_aduan, id_pengguna, mesej, status)
        VALUES ('$id_aduan', '$id_pengguna', '$mesej', 'belum_dibaca')";

mysqli_query($conn, $sql);

header("Location: detail_aduan.php?id=$id_aduan");
exit();
?>