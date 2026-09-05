<?php

include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];
$nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
$id_kategori = mysqli_real_escape_string($conn, $_POST['id_kategori']);
$harga = mysqli_real_escape_string($conn, $_POST['harga']);
$penerangan = mysqli_real_escape_string($conn, $_POST['penerangan']);

$gambar = "";

if(isset($_FILES['gambar_barang']) && $_FILES['gambar_barang']['error'] == 0) {
    $gambar = time() . "_" . basename($_FILES['gambar_barang']['name']);
    move_uploaded_file($_FILES['gambar_barang']['tmp_name'], "gambar/" . $gambar);
}

$sql = "INSERT INTO barang (id_pengguna, id_kategori, nama_barang, penerangan, harga, gambar_barang)
        VALUES ('$id_pengguna', '$id_kategori', '$nama_barang', '$penerangan', '$harga', '$gambar')";

if(mysqli_query($conn, $sql)) {
    $id_barang = mysqli_insert_id($conn);
    header("Location: detail_barang.php?id=$id_barang");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}

?>