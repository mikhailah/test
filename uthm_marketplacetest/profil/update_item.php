<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

include("../config/database.php");

$id = (int)($_POST['id'] ?? 0);
$type = $_POST['type'] ?? '';
$nama = mysqli_real_escape_string($conn, trim($_POST['nama'] ?? ''));
$harga = floatval($_POST['harga'] ?? 0);
$penerangan = mysqli_real_escape_string($conn, trim($_POST['penerangan'] ?? ''));
$id_pengguna = $_SESSION['id_pengguna'];

$response = ['success' => false, 'message' => ''];

if ($type === 'barang') {
    // Check if penerangan column exists in barang table
    $check_column = mysqli_query($conn, "SHOW COLUMNS FROM barang LIKE 'penerangan'");
    $has_penerangan = mysqli_num_rows($check_column) > 0;
    
    if ($has_penerangan) {
        $sql = "UPDATE barang SET nama_barang = '$nama', harga = $harga, penerangan = '$penerangan' WHERE id_barang = $id AND id_pengguna = $id_pengguna";
    } else {
        $sql = "UPDATE barang SET nama_barang = '$nama', harga = $harga WHERE id_barang = $id AND id_pengguna = $id_pengguna";
    }
    
    if (mysqli_query($conn, $sql)) {
        $response['success'] = true;
        $response['message'] = 'Barang berjaya dikemaskini';
    } else {
        $response['message'] = 'Ralat database: ' . mysqli_error($conn);
    }
} 
elseif ($type === 'servis') {
    $sql = "UPDATE servis SET nama_servis = '$nama', harga = $harga, penerangan = '$penerangan' WHERE id_servis = $id AND id_pengguna = $id_pengguna";
    if (mysqli_query($conn, $sql)) {
        $response['success'] = true;
        $response['message'] = 'Servis berjaya dikemaskini';
    } else {
        $response['message'] = 'Ralat database: ' . mysqli_error($conn);
    }
} else {
    $response['message'] = 'Jenis tidak sah';
}

header('Content-Type: application/json');
echo json_encode($response);
?>