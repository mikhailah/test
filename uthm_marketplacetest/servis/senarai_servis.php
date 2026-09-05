<?php

include("../includes/session.php");
include("../config/database.php");

// Get notification count for sidebar badge (consistent with index)
$id_pengguna = $_SESSION['id_pengguna'];
$sql_notifikasi = "
    SELECT COUNT(*) AS jumlah
    FROM mesej
    WHERE id_penerima = '$id_pengguna'
    AND dibaca = 'tidak'
";
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

// Get all services
$sql = "
    SELECT *
    FROM servis
    ORDER BY id_servis DESC
";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Senarai Servis | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a1a2e;
            border-left: 5px solid #6c3cff;
            padding-left: 1rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>

<!-- Full dashboard layout with sidebar (consistent with index) -->
<div class="dashboard">
    
    <!-- SIDEBAR - TAMBAH ADUAN BADGE -->
    <div class="sidebar">
        <div class="logo">
            PlatformJualBeli
        </div>
        <ul class="menu">
            <li>
                <a href="../dashboard/index.php">Utama</a>
            </li>
            <li>
                <a href="../barang/senarai_barang.php">Barang</a>
            </li>
            <li class="active">
                <a href="../servis/senarai_servis.php">Servis</a>
            </li>
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
            <li>
                <a href="../profil/profil.php">Profil</a>
            </li>
            <li>
                <a href="../aduan/aduan_saya.php">
                    Aduan
                    <?php if($jumlah_notif_aduan > 0): ?>
                        <span class="badge" style="background:#ef4444;"><?= $jumlah_notif_aduan; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="../auth/logout.php">Log Keluar</a>
            </li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        
        <!-- NAVBAR - SAME AS INDEX -->
        <div class="navbar">
            <form action="../barang/carian.php" method="GET">
                <input type="text" name="keyword" placeholder="Cari barang atau servis" required>
                <button type="submit">Cari</button>
            </form>
            <a href="../servis/tambah_servis.php">
                <button type="button">Iklan Servis</button>
            </a>
        </div>
        
        <!-- Page Header -->
        <div class="page-header">
            <h1>Senarai Servis</h1>
        </div>
        
        <!-- Product Grid -->
        <div class="product-grid">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if(!empty($row['gambar_servis'])): ?>
                                <img src="gambar/<?= htmlspecialchars($row['gambar_servis']); ?>" alt="<?= htmlspecialchars($row['nama_servis']); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/250x180?text=Tiada+Gambar" alt="Placeholder">
                            <?php endif; ?>
                        </div>
                        <div class="product-category">
                            SERVIS
                        </div>
                        <h3><?= htmlspecialchars($row['nama_servis']); ?></h3>
                        <p class="price">RM<?= number_format($row['harga'], 2); ?></p>
                        <a href="detail_servis.php?id=<?= $row['id_servis']; ?>">
                            <button>Lihat Detail</button>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    Tiada servis dalam senarai.
                </div>
            <?php endif; ?>
        </div>
        
    </div>
    
</div>

</body>
</html>