<?php

include("../includes/session.php");
include("../config/database.php");

$id_pengguna =
$_SESSION['id_pengguna'];

$nama_penuh =
$_POST['nama_penuh'];

$no_telefon =
$_POST['no_telefon'];

$gambar = "";

if(
$_FILES['gambar_profil']['name']
!= ""
)
{

    $gambar =
    time() .
    "_" .
    $_FILES['gambar_profil']['name'];

    move_uploaded_file(

        $_FILES['gambar_profil']['tmp_name'],

        "gambar/" . $gambar

    );

    $sql = "

    UPDATE pengguna

    SET

    nama_penuh='$nama_penuh',

    no_telefon='$no_telefon',

    gambar_profil='$gambar'

    WHERE id_pengguna='$id_pengguna'

    ";

}
else
{

    $sql = "

    UPDATE pengguna

    SET

    nama_penuh='$nama_penuh',

    no_telefon='$no_telefon'

    WHERE id_pengguna='$id_pengguna'

    ";

}

mysqli_query(
$conn,
$sql
);

header(
"Location: profil.php"
);

exit();

?>