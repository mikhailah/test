<?php

include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];
$nama_servis = mysqli_real_escape_string($conn, $_POST['nama_servis']);
$harga = mysqli_real_escape_string($conn, $_POST['harga']);
$penerangan = mysqli_real_escape_string($conn, $_POST['penerangan']);

$gambar = "";
if(isset($_FILES['gambar_servis']) && $_FILES['gambar_servis']['error'] == 0) {
    $gambar = time() . "_" . basename($_FILES['gambar_servis']['name']);
    move_uploaded_file($_FILES['gambar_servis']['tmp_name'], "gambar/" . $gambar);
}

$sql = "INSERT INTO servis (id_pengguna, nama_servis, harga, penerangan, gambar_servis)
        VALUES ('$id_pengguna', '$nama_servis', '$harga', '$penerangan', '$gambar')";

if(mysqli_query($conn, $sql)) {
    $id_servis = mysqli_insert_id($conn);
    header("Location: detail_servis.php?id=$id_servis");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}

?>