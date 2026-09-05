<?php

include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];
$id_servis = $_GET['id'];

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

$sql = "
    SELECT servis.*, pengguna.nama_penuh
    FROM servis
    LEFT JOIN pengguna ON servis.id_pengguna = pengguna.id_pengguna
    WHERE servis.id_servis = '$id_servis'
";
$result = mysqli_query($conn, $sql);
$servis = mysqli_fetch_assoc($result);

// Check if user is the owner of this service
$is_owner = ($servis['id_pengguna'] == $id_pengguna);

?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($servis['nama_servis']); ?> | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .main-content {
            padding: 2rem;
            background: #f8f9fc;
            min-height: 100vh;
        }
        
        .detail-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 0px;
            padding: 2rem;
            border: 1px solid #eef2f8;
        }
        
        .service-image {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: contain;
            border-radius: 0px;
            margin-bottom: 1.5rem;
            background: #f5f5f5;
        }
        
        .service-title {
            font-size: 1.8rem;
            color: #1a1a2e;
            margin-bottom: 0.5rem;
        }
        
        .service-price {
            font-size: 1.8rem;
            font-weight: 700;
            color: #6c3cff;
            margin-bottom: 1rem;
        }
        
        .service-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eef2f8;
        }
        
        .service-description {
            line-height: 1.6;
            margin: 1.5rem 0;
        }
        
        .btn-chat {
            background: linear-gradient(135deg, #6c3cff, #4f7cff);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 0px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-chat:hover {
            opacity: 0.9;
        }
        
        .btn-back {
            background: #eef2f8;
            color: #333;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 0px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-profile {
            background: #10b981;
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 0px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-profile:hover {
            background: #059669;
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            .btn-chat, .btn-profile, .btn-back {
                width: 100%;
                text-align: center;
                margin-left: 0 !important;
            }
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
            <li class="active"><a href="../servis/senarai_servis.php">Servis</a></li>
            <li>
                <a href="../tukar/senarai_tawaran.php">
                    Tawaran Pertukaran
                    <?php if($jumlah_notif_trade > 0): ?>
                        <span class="badge" style="background:#ef4444;"><?= $jumlah_notif_trade; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
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
        
        <div class="detail-container">
            <?php if(!empty($servis['gambar_servis'])): ?>
                <img src="gambar/<?= htmlspecialchars($servis['gambar_servis']); ?>" class="service-image">
            <?php else: ?>
                <img src="https://via.placeholder.com/800x400?text=Tiada+Gambar" class="service-image">
            <?php endif; ?>
            
            <h1 class="service-title"><?= htmlspecialchars($servis['nama_servis']); ?></h1>
            <div class="service-price">RM<?= number_format($servis['harga'], 2); ?></div>
            
            <div class="service-meta">
                Penyedia Servis: <?= htmlspecialchars($servis['nama_penuh']); ?>
            </div>
            
            <div class="service-description">
                <?= nl2br(htmlspecialchars($servis['penerangan'])); ?>
            </div>
            
            <!-- Buttons -->
            <div style="display:flex; gap:0.8rem; flex-wrap:wrap; margin-top:1rem;">
                <?php if($is_owner): ?>
                    <!-- Jika pemilik servis - hanya nampak Kembali sahaja -->
                    <a href="senarai_servis.php" class="btn-back">
                        Kembali
                    </a>
                <?php else: ?>
                    <!-- Jika bukan pemilik -->
                    <a href="../mesej/chat.php?id=<?= $servis['id_pengguna']; ?>" class="btn-chat">
                        Hubungi Penyedia Servis
                    </a>
                    
                    <a href="profil_penjual.php?id=<?= $servis['id_pengguna']; ?>" class="btn-profile">
                        Lihat Profil Penyedia
                    </a>
                    
                    <a href="senarai_servis.php" class="btn-back">
                        Kembali
                    </a>
                <?php endif; ?>
            </div>
            
        </div>
        
    </div>
    
</div>

</body>
</html>