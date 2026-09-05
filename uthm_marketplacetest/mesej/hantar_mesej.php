<?php

include("../includes/session.php");
include("../config/database.php");

$id_penghantar = $_SESSION['id_pengguna'];
$id_penerima = mysqli_real_escape_string($conn, $_POST['id_penerima']);
$mesej = mysqli_real_escape_string($conn, $_POST['mesej']);

if(empty($mesej)) {
    header("Location: mesej_baru.php?id=$id_penerima");
    exit();
}

$sql = "INSERT INTO mesej (id_penghantar, id_penerima, mesej, dibaca)
        VALUES ('$id_penghantar', '$id_penerima', '$mesej', 'tidak')";

if(mysqli_query($conn, $sql)) {
    header("Location: chat.php?id=$id_penerima");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}

?>