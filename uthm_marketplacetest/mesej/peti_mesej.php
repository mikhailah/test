<?php

include("../includes/session.php");
include("../config/database.php");

$id_pengguna =
$_SESSION['id_pengguna'];

$sql = "

SELECT
mesej.*,
pengguna.nama_penuh

FROM mesej

LEFT JOIN pengguna

ON mesej.id_penghantar =
pengguna.id_pengguna

WHERE id_penerima =
'$id_pengguna'

ORDER BY id_mesej DESC

";

$result =
mysqli_query(
$conn,
$sql
);

?>

<!DOCTYPE html>
<html>

<head>

<title>Peti Mesej</title>

<link rel="stylesheet"
href="../assets/css/dashboard.css">

</head>

<body>

<div class="main-content">

<h1>

Peti Mesej

</h1>

<br>

<?php

while(
$row =
mysqli_fetch_assoc(
$result
)
)
{

?>

<div
style="
background:white;
padding:20px;
margin-bottom:20px;
border-radius:15px;
">

<h3>

<?= $row['nama_penuh']; ?>

</h3>

<br>

<p>

<?= $row['mesej']; ?>

</p>

<br>

<small>

<?= $row['tarikh_hantar']; ?>

</small>

</div>

<?php

}

?>

</div>

</body>

</html>