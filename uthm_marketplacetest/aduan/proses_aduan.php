<?php
include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];
$kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
$tajuk = mysqli_real_escape_string($conn, $_POST['tajuk']);
$penerangan = mysqli_real_escape_string($conn, $_POST['penerangan']);
$id_referensi = mysqli_real_escape_string($conn, $_POST['id_referensi']);
$id_pengguna_terlibat = mysqli_real_escape_string($conn, $_POST['id_pengguna_terlibat']);

// Handle barang terlibat (optional)
$id_barang_terlibat = isset($_POST['id_barang_terlibat']) && !empty($_POST['id_barang_terlibat']) 
                       ? mysqli_real_escape_string($conn, $_POST['id_barang_terlibat']) 
                       : 'NULL';

// Handle servis terlibat (optional)
$id_servis_terlibat = isset($_POST['id_servis_terlibat']) && !empty($_POST['id_servis_terlibat']) 
                       ? mysqli_real_escape_string($conn, $_POST['id_servis_terlibat']) 
                       : 'NULL';

// Handle reference
$jenis_referensi = null;
$id_ref = null;
if(!empty($id_referensi)) {
    $parts = explode('_', $id_referensi);
    $jenis_referensi = $parts[0];
    $id_ref = $parts[1];
}

// Insert aduan with new columns
$sql = "INSERT INTO aduan (id_pengguna, id_pengguna_terlibat, id_barang_terlibat, id_servis_terlibat, kategori, tajuk, penerangan, id_referensi, jenis_referensi, status)
        VALUES ('$id_pengguna', '$id_pengguna_terlibat', $id_barang_terlibat, $id_servis_terlibat, '$kategori', '$tajuk', '$penerangan', '$id_ref', '$jenis_referensi', 'baru')";

if(mysqli_query($conn, $sql)) {
    $id_aduan = mysqli_insert_id($conn);
    
    // Process multiple lampiran
    if(isset($_FILES['lampiran']) && !empty($_FILES['lampiran']['name'][0])) {
        $target_dir = "lampiran/";
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        if(!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $files = $_FILES['lampiran'];
        $file_count = count($files['name']);
        
        for($i = 0; $i < $file_count; $i++) {
            if($files['error'][$i] == 0 && in_array($files['type'][$i], $allowed)) {
                $file_name = time() . "_" . $i . "_" . basename($files['name'][$i]);
                $target_file = $target_dir . $file_name;
                
                if(move_uploaded_file($files['tmp_name'][$i], $target_file)) {
                    $sql_lampiran = "INSERT INTO aduan_lampiran (id_aduan, nama_file) VALUES ('$id_aduan', '$file_name')";
                    mysqli_query($conn, $sql_lampiran);
                }
            }
        }
    }
    
    header("Location: aduan_saya.php?success=1");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>