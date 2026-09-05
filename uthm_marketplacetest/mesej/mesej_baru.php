<?php

include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];
$id_penerima = $_GET['id'];

// Notification count
$sql_notifikasi = "SELECT COUNT(*) AS jumlah FROM mesej WHERE id_penerima = '$id_pengguna' AND dibaca = 'tidak'";
$result_notifikasi = mysqli_query($conn, $sql_notifikasi);
$data_notifikasi = mysqli_fetch_assoc($result_notifikasi);
$jumlah_notifikasi = $data_notifikasi['jumlah'];

// Trade notification count for sidebar
$sql_notif_trade = "SELECT COUNT(*) AS jumlah FROM notifikasi WHERE id_penerima = '$id_pengguna' AND dibaca = 'tidak'";
$result_notif_trade = mysqli_query($conn, $sql_notif_trade);
$data_notif_trade = mysqli_fetch_assoc($result_notif_trade);
$jumlah_notif_trade = $data_notif_trade['jumlah'];

// ============================================================
// ADUAN NOTIFICATION COUNT
// ============================================================
$sql_notif_aduan = "SELECT COUNT(*) AS jumlah FROM aduan_chat 
                    WHERE id_aduan IN (SELECT id_aduan FROM aduan WHERE id_pengguna = '$id_pengguna')
                    AND id_pengguna != '$id_pengguna' 
                    AND status = 'belum_dibaca'";
$result_notif_aduan = mysqli_query($conn, $sql_notif_aduan);
$data_notif_aduan = mysqli_fetch_assoc($result_notif_aduan);
$jumlah_notif_aduan = $data_notif_aduan['jumlah'];

$sql = "SELECT * FROM pengguna WHERE id_pengguna = '$id_penerima'";
$result = mysqli_query($conn, $sql);
$penerima = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Hantar Mesej | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .main-content {
            padding: 2rem;
            background: #f8f9fc;
            min-height: 100vh;
        }
        
        .message-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 0px;
            padding: 2rem;
            border: 1px solid #eef2f8;
        }
        
        .message-container h1 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .recipient-info {
            background: #f8f9fc;
            padding: 1rem;
            border-radius: 0px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .recipient-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        textarea {
            width: 100%;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 0px;
            font-size: 0.9rem;
            resize: vertical;
        }
        
        textarea:focus {
            outline: none;
            border-color: #6c3cff;
        }
        
        .btn-send {
            background: linear-gradient(135deg, #6c3cff, #4f7cff);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 0px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 1rem;
        }
    </style>
</head>
<body>

<div class="dashboard">
    
    <!-- SIDEBAR - TAMBAH ADUAN BADGE -->
    <div class="sidebar">
        <div class="logo">PlatformJualBeli</div>
        <ul class="menu">
            <li><a href="../dashboard/index.php">Utama</a></li>
            <li><a href="../barang/senarai_barang.php">Barang</a></li>
            <li><a href="../servis/senarai_servis.php">Servis</a></li>
            <li>
                <a href="../tukar/senarai_tawaran.php">
                    Tawaran Pertukaran
                    <?php if($jumlah_notif_trade > 0): ?>
                        <span class="badge" style="background:#ef4444;"><?= $jumlah_notif_trade; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="active">
                <a href="../mesej/chat_senarai.php">
                    Mesej
                    <?php if($jumlah_notifikasi > 0): ?>
                        <span class="badge" style="background:#ef4444;"><?= $jumlah_notifikasi; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li><a href="../profil/profil.php">Profil</a></li>
            <li>
                <a href="../aduan/aduan_saya.php">
                    Aduan
                    <?php if($jumlah_notif_aduan > 0): ?>
                        <span class="badge" style="background:#ef4444;"><?= $jumlah_notif_aduan; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li><a href="../auth/logout.php">Log Keluar</a></li>
        </ul>
    </div>

    <div class="main-content">
        
        <div class="message-container">
            <h1>Hantar Mesej</h1>
            
            <div class="recipient-info">
                <div class="recipient-avatar">
                    <?= substr($penerima['nama_penuh'], 0, 1); ?>
                </div>
                <div>
                    <strong><?= htmlspecialchars($penerima['nama_penuh']); ?></strong>
                </div>
            </div>
            
            <form action="hantar_mesej.php" method="POST">
                <input type="hidden" name="id_penerima" value="<?= $id_penerima; ?>">
                <textarea name="mesej" rows="6" placeholder="Taip mesej anda di sini..." required></textarea>
                <button type="submit" class="btn-send">Hantar Mesej</button>
            </form>
        </div>
        
    </div>
    
</div>

</body>
</html>