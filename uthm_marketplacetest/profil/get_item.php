<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

include("../config/database.php");

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$id_pengguna = $_SESSION['id_pengguna'];

$response = ['success' => false, 'message' => ''];

if ($type === 'barang') {
    // Check if penerangan column exists in barang table
    $check_column = mysqli_query($conn, "SHOW COLUMNS FROM barang LIKE 'penerangan'");
    $has_penerangan = mysqli_num_rows($check_column) > 0;
    
    if ($has_penerangan) {
        $sql = "SELECT nama_barang AS nama, harga, penerangan FROM barang WHERE id_barang = $id AND id_pengguna = $id_pengguna";
    } else {
        $sql = "SELECT nama_barang AS nama, harga, '' as penerangan FROM barang WHERE id_barang = $id AND id_pengguna = $id_pengguna";
    }
    
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        $response = ['success' => true, 'nama' => $row['nama'], 'harga' => $row['harga'], 'penerangan' => $row['penerangan'] ?? ''];
    } else {
        $response['message'] = 'Barang not found';
    }
} 
elseif ($type === 'servis') {
    $sql = "SELECT nama_servis AS nama, harga, penerangan FROM servis WHERE id_servis = $id AND id_pengguna = $id_pengguna";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        $response = ['success' => true, 'nama' => $row['nama'], 'harga' => $row['harga'], 'penerangan' => $row['penerangan'] ?? ''];
    } else {
        $response['message'] = 'Servis not found';
    }
} else {
    $response['message'] = 'Invalid type';
}

header('Content-Type: application/json');
echo json_encode($response);
?>