<?php
include("../config/database.php");
include("../includes/session.php");

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? 0;

$response = ['success' => false, 'message' => ''];

if($type === 'barang') {
    $sql = "DELETE FROM barang WHERE id_barang = '$id' AND id_pengguna = '{$_SESSION['id_pengguna']}'";
    if(mysqli_query($conn, $sql)) {
        $response['success'] = true;
    } else {
        $response['message'] = mysqli_error($conn);
    }
} 
elseif($type === 'servis') {
    $sql = "DELETE FROM servis WHERE id_servis = '$id' AND id_pengguna = '{$_SESSION['id_pengguna']}'";
    if(mysqli_query($conn, $sql)) {
        $response['success'] = true;
    } else {
        $response['message'] = mysqli_error($conn);
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>