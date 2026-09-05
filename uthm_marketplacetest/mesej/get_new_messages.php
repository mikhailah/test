<?php
include("../includes/session.php");
include("../config/database.php");

$id_sendiri = $_SESSION['id_pengguna'];
$id_lawan = $_GET['lawan'];
$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

// Get messages after last_id
$sql = "
    SELECT * FROM mesej
    WHERE ((id_penghantar='$id_sendiri' AND id_penerima='$id_lawan')
       OR (id_penghantar='$id_lawan' AND id_penerima='$id_sendiri'))
       AND id_mesej > '$last_id'
    ORDER BY tarikh_hantar ASC
";
$result = mysqli_query($conn, $sql);

$messages = [];
while($row = mysqli_fetch_assoc($result)) {
    $msg_date = date('Y-m-d', strtotime($row['tarikh_hantar']));
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    if ($msg_date == $today) {
        $display_date = 'Hari Ini';
    } elseif ($msg_date == $yesterday) {
        $display_date = 'Semalam';
    } else {
        $display_date = date('d/m/Y', strtotime($row['tarikh_hantar']));
    }
    
    // Check if message has attachment
    $has_attachment = !empty($row['lampiran']);
    $is_image = false;
    $file_path = "";
    if($has_attachment) {
        $file_path = "lampiran/" . $row['lampiran'];
        $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        $ext = strtolower(pathinfo($row['lampiran'], PATHINFO_EXTENSION));
        $is_image = in_array($ext, $image_extensions);
    }
    
    $messages[] = [
        'id' => $row['id_mesej'],
        'mesej' => nl2br(htmlspecialchars($row['mesej'])),
        'is_sent' => ($row['id_penghantar'] == $id_sendiri),
        'time' => date('H:i', strtotime($row['tarikh_hantar'])),
        'date' => $msg_date,
        'display_date' => $display_date,
        'has_attachment' => $has_attachment,
        'is_image' => $is_image,
        'file_name' => $row['lampiran'],
        'file_path' => $file_path
    ];
}

echo json_encode(['messages' => $messages]);
?>