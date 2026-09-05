<?php
include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];

// Check if user is admin
$sql_user = "SELECT peranan FROM pengguna WHERE id_pengguna = '$id_pengguna'";
$result_user = mysqli_query($conn, $sql_user);
$user = mysqli_fetch_assoc($result_user);

if($user['peranan'] != 'pentadbir') {
    die("Akses ditolak.");
}

$id_aduan = mysqli_real_escape_string($conn, $_POST['id_aduan']);
$status = mysqli_real_escape_string($conn, $_POST['status']);
$tindakan = mysqli_real_escape_string($conn, $_POST['tindakan']);

// Get aduan owner
$sql_aduan = "SELECT id_pengguna FROM aduan WHERE id_aduan = '$id_aduan'";
$result_aduan = mysqli_query($conn, $sql_aduan);
$row_aduan = mysqli_fetch_assoc($result_aduan);
$id_pengguna_aduan = $row_aduan['id_pengguna'];

// Update aduan
$sql = "UPDATE aduan SET 
        status = '$status',
        tindakan = '$tindakan',
        tarikh_tindakan = NOW()
        WHERE id_aduan = '$id_aduan'";

if(mysqli_query($conn, $sql)) {
    // Add notification to chat - THIS SENDS NOTIFICATION TO USER
    $msg = "Admin telah mengemaskini status aduan kepada: " . strtoupper(str_replace('_', ' ', $status));
    if(!empty($tindakan)) {
        $msg .= ". Tindakan: " . $tindakan;
    }
    
    // Insert system message into chat (sent by admin, but will trigger notification for user)
    $sql_chat = "INSERT INTO aduan_chat (id_aduan, id_pengguna, mesej, status)
                 VALUES ('$id_aduan', '$id_pengguna', 'Sistem: $msg', 'belum_dibaca')";
    mysqli_query($conn, $sql_chat);
    
    header("Location: detail_aduan_admin.php?id=$id_aduan&success=1");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>