<?php
include("../includes/session.php");
include("../config/database.php");

$id_pengirim = $_SESSION['id_pengguna'];
$id_barang_diminta = mysqli_real_escape_string($conn, $_POST['id_barang_diminta']);
$id_barang_ditawar = mysqli_real_escape_string($conn, $_POST['id_barang_ditawar']);
$tambah_wang = mysqli_real_escape_string($conn, $_POST['tambah_wang']);
$mesej = mysqli_real_escape_string($conn, $_POST['mesej']);

// Validate: cannot trade own item
$sql_check = "SELECT id_pengguna FROM barang WHERE id_barang = '$id_barang_diminta'";
$result_check = mysqli_query($conn, $sql_check);
$row_check = mysqli_fetch_assoc($result_check);

if($row_check['id_pengguna'] == $id_pengirim) {
    die("Anda tidak boleh menukar barang sendiri.");
}

// Get penerima (owner of requested item)
$sql_owner = "SELECT id_pengguna FROM barang WHERE id_barang = '$id_barang_diminta'";
$result_owner = mysqli_query($conn, $sql_owner);
$owner = mysqli_fetch_assoc($result_owner);
$id_penerima = $owner['id_pengguna'];

// Insert tawaran
$sql = "INSERT INTO tawaran_tukar (id_pengirim, id_penerima, id_barang_diminta, id_barang_ditawar, tambah_wang, mesej, status)
        VALUES ('$id_pengirim', '$id_penerima', '$id_barang_diminta', '$id_barang_ditawar', '$tambah_wang', '$mesej', 'pending')";

if(mysqli_query($conn, $sql)) {
    $id_tawaran = mysqli_insert_id($conn);
    
    // Insert notification
    $sql_notif = "INSERT INTO notifikasi (id_penerima, id_pengirim, jenis, id_referensi, mesej)
                  VALUES ('$id_penerima', '$id_pengirim', 'tawaran_tukar', '$id_tawaran', 'Anda menerima tawaran pertukaran baru.')";
    mysqli_query($conn, $sql_notif);
    
    // Redirect to detail tawaran
    header("Location: detail_tawaran.php?id=$id_tawaran");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>