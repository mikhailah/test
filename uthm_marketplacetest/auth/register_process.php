<?php

include("../config/database.php");

/* Ambil data dari form */

$nama_penuh = $_POST['nama_penuh'];

$emel = trim($_POST['emel']);

$no_telefon = $_POST['no_telefon'];

$kata_laluan = $_POST['kata_laluan'];

$sahkan_kata_laluan = $_POST['sahkan_kata_laluan'];


/* Semak password sama atau tidak */

if($kata_laluan != $sahkan_kata_laluan)
{
    die("Kata laluan tidak sama");
}


/* Semak emel UTHM */

if(
    !str_ends_with($emel, "@student.uthm.edu.my")
    &&
    !str_ends_with($emel, "@uthm.edu.my")
)
{
    die("Sila gunakan emel rasmi UTHM");
}


/* Semak emel sudah wujud atau belum */

$semak = mysqli_query(
    $conn,
    "SELECT * FROM pengguna WHERE emel='$emel'"
);

if(mysqli_num_rows($semak) > 0)
{
    die("Emel telah didaftarkan");
}


/* Tentukan peranan automatik */

if(str_ends_with($emel, "@student.uthm.edu.my"))
{
    $peranan = "pelajar";
}
else
{
    $peranan = "staff";
}


/* Hash password */

$kata_hash = password_hash(
    $kata_laluan,
    PASSWORD_DEFAULT
);


/* Simpan ke database */

$sql = "

INSERT INTO pengguna
(
    nama_penuh,
    emel,
    no_telefon,
    kata_laluan,
    peranan
)

VALUES
(
    '$nama_penuh',
    '$emel',
    '$no_telefon',
    '$kata_hash',
    '$peranan'
)

";

if(mysqli_query($conn,$sql))
{
    header(
        "Location: login.php?register=success"
    );
    exit();
}
else
{
    echo "Pendaftaran Gagal: " . mysqli_error($conn);
}

?>