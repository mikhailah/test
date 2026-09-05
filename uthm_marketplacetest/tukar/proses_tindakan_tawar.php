<?php
include("../includes/session.php");
include("../config/database.php");

$id_tawaran = $_GET['id'];
$action = $_GET['action'];
$id_pengguna = $_SESSION['id_pengguna'];

// Update status
$status = ($action == 'terima') ? 'diterima' : 'ditolak';
$sql = "UPDATE tawaran_tukar SET status = '$status', tarikh_tindakan = NOW() WHERE id_tawaran = '$id_tawaran' AND id_penerima = '$id_pengguna'";

if(mysqli_query($conn, $sql)) {
    // Get tawaran details for notification
    $sql_tawaran = "SELECT id_pengirim, id_penerima, id_barang_diminta, id_barang_ditawar FROM tawaran_tukar WHERE id_tawaran = '$id_tawaran'";
    $result_tawaran = mysqli_query($conn, $sql_tawaran);
    $tawaran = mysqli_fetch_assoc($result_tawaran);
    
    // Insert notification
    $jenis = ($action == 'terima') ? 'tawaran_diterima' : 'tawaran_ditolak';
    $mesej = ($action == 'terima') ? 'Tawaran pertukaran anda telah diterima!' : 'Tawaran pertukaran anda telah ditolak.';
    $sql_notif = "INSERT INTO notifikasi (id_penerima, id_pengirim, jenis, id_referensi, mesej)
                  VALUES ('{$tawaran['id_pengirim']}', '$id_pengguna', '$jenis', '$id_tawaran', '$mesej')";
    mysqli_query($conn, $sql_notif);
    
    // If accepted, update item ownership
    if($action == 'terima') {
        // Swap ownership: pengirim gets barang_diminta, penerima gets barang_ditawar
        $sql_update1 = "UPDATE barang SET id_pengguna = '{$tawaran['id_pengirim']}' WHERE id_barang = '{$tawaran['id_barang_diminta']}'";
        $sql_update2 = "UPDATE barang SET id_pengguna = '{$tawaran['id_penerima']}' WHERE id_barang = '{$tawaran['id_barang_ditawar']}'";
        mysqli_query($conn, $sql_update1);
        mysqli_query($conn, $sql_update2);
    }
    
    header("Location: detail_tawaran.php?id=$id_tawaran");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>