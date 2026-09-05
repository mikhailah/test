<?php
include("../includes/session.php");
include("../config/database.php");

$id_penghantar = $_SESSION['id_pengguna'];
$id_penerima = mysqli_real_escape_string($conn, $_POST['id_penerima']);
$mesej = mysqli_real_escape_string($conn, $_POST['mesej']);

// Function to clean filename
function cleanFileName($filename) {
    // Remove any characters that are not allowed
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    // Remove multiple underscores
    $filename = preg_replace('/_+/', '_', $filename);
    return $filename;
}

// Create lampiran folder if not exists
$target_dir = "lampiran/";
if(!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Handle file upload
$lampiran = "";
if(isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] == 0) {
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if(in_array($_FILES['lampiran']['type'], $allowed)) {
        // Clean the filename
        $clean_name = cleanFileName($_FILES['lampiran']['name']);
        $lampiran = time() . "_" . $clean_name;
        $target_file = $target_dir . $lampiran;
        if(!move_uploaded_file($_FILES['lampiran']['tmp_name'], $target_file)) {
            $lampiran = "";
        }
    }
}

if(empty($mesej) && empty($lampiran)) {
    echo json_encode(['success' => false, 'message' => 'Mesej atau lampiran kosong']);
    exit();
}

$sql = "INSERT INTO mesej (id_penghantar, id_penerima, mesej, lampiran, dibaca)
        VALUES ('$id_penghantar', '$id_penerima', '$mesej', '$lampiran', 'tidak')";

if(mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}
?>