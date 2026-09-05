<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "platform_jual_beli_uthm"
);

if(!$conn)
{
    die("Database gagal connect");
}

?>