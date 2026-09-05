<?php
include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];

// Mark all trade notifications as read when viewing this page
$sql_update = "UPDATE notifikasi SET dibaca = 'ya' WHERE id_penerima = '$id_pengguna'";
mysqli_query($conn, $sql_update);

// Tawaran diterima (orang lain minta barang saya)
$sql_diterima = "
    SELECT t.*, 
           b1.nama_barang AS barang_diminta,
           b2.nama_barang AS barang_ditawar,
           p.nama_penuh AS nama_pengirim
    FROM tawaran_tukar t
    JOIN barang b1 ON t.id_barang_diminta = b1.id_barang
    JOIN barang b2 ON t.id_barang_ditawar = b2.id_barang
    JOIN pengguna p ON t.id_pengirim = p.id_pengguna
    WHERE t.id_penerima = '$id_pengguna'
    ORDER BY t.tarikh_hantar DESC
";
$result_diterima = mysqli_query($conn, $sql_diterima);

// Tawaran dihantar (saya minta barang orang lain)
$sql_dihantar = "
    SELECT t.*, 
           b1.nama_barang AS barang_diminta,
           b2.nama_barang AS barang_ditawar,
           p.nama_penuh AS nama_penerima
    FROM tawaran_tukar t
    JOIN barang b1 ON t.id_barang_diminta = b1.id_barang
    JOIN barang b2 ON t.id_barang_ditawar = b2.id_barang
    JOIN pengguna p ON t.id_penerima = p.id_pengguna
    WHERE t.id_pengirim = '$id_pengguna'
    ORDER BY t.tarikh_hantar DESC
";
$result_dihantar = mysqli_query($conn, $sql_dihantar);

// Notification count for sidebar (message)
$sql_notifikasi = "SELECT COUNT(*) AS jumlah FROM mesej WHERE id_penerima = '$id_pengguna' AND dibaca = 'tidak'";
$result_notifikasi = mysqli_query($conn, $sql_notifikasi);
$data_notifikasi = mysqli_fetch_assoc($result_notifikasi);
$jumlah_notifikasi = $data_notifikasi['jumlah'];

// Trade notification count (now should be 0 after update)
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Senarai Tawaran | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .main-content { padding: 2rem; background: #f8f9fc; min-height: 100vh; }
        .trade-container { max-width: 900px; margin: 0 auto; }
        
        .trade-card {
            background: white;
            border: 1px solid #eef2f8;
            padding: 1.2rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .trade-info { flex: 1; }
        .trade-info h4 { font-size: 1rem; margin-bottom: 0.3rem; }
        .trade-info p { font-size: 0.85rem; color: #666; }
        
        .status-badge {
            padding: 0.3rem 0.8rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-diterima { background: #d1fae5; color: #065f46; }
        .status-ditolak { background: #fee2e2; color: #991b1b; }
        .status-batal { background: #e5e7eb; color: #374151; }
        
        .btn-view {
            background: #6c3cff;
            color: white;
            border: none;
            padding: 0.5rem 1.2rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-view:hover { opacity: 0.9; }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin: 2rem 0 1rem 0;
            border-bottom: 2px solid #eef2f8;
            padding-bottom: 0.5rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #888;
            background: white;
            border: 1px solid #eef2f8;
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
            <li class="active">
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
        <div class="trade-container">
            <h1 style="font-size:1.8rem;margin-bottom:1.5rem;">Senarai Tawaran</h1>
            
            <!-- Tawaran Diterima -->
            <h2 class="section-title">Tawaran Diterima (Orang Lain Minta Barang Anda)</h2>
            <?php if(mysqli_num_rows($result_diterima) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result_diterima)): ?>
                    <div class="trade-card">
                        <div class="trade-info">
                            <h4><?= htmlspecialchars($row['nama_pengirim']); ?> menawarkan pertukaran</h4>
                            <p>Anda berikan: <strong><?= htmlspecialchars($row['barang_diminta']); ?></strong></p>
                            <p>Anda terima: <strong><?= htmlspecialchars($row['barang_ditawar']); ?></strong>
                            <?php if($row['tambah_wang'] > 0): ?> + RM<?= number_format($row['tambah_wang'], 2); ?><?php endif; ?></p>
                        </div>
                        <div>
                            <span class="status-badge status-<?= $row['status']; ?>"><?= $row['status']; ?></span>
                            <a href="detail_tawaran.php?id=<?= $row['id_tawaran']; ?>" class="btn-view">Lihat</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">Tiada tawaran diterima.</div>
            <?php endif; ?>
            
            <!-- Tawaran Dihantar -->
            <h2 class="section-title">Tawaran Dihantar (Anda Minta Barang Orang Lain)</h2>
            <?php if(mysqli_num_rows($result_dihantar) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result_dihantar)): ?>
                    <div class="trade-card">
                        <div class="trade-info">
                            <h4>Anda menawarkan kepada <?= htmlspecialchars($row['nama_penerima']); ?></h4>
                            <p>Anda berikan: <strong><?= htmlspecialchars($row['barang_ditawar']); ?></strong>
                            <?php if($row['tambah_wang'] > 0): ?> + RM<?= number_format($row['tambah_wang'], 2); ?><?php endif; ?></p>
                            <p>Anda terima: <strong><?= htmlspecialchars($row['barang_diminta']); ?></strong></p>
                        </div>
                        <div>
                            <span class="status-badge status-<?= $row['status']; ?>"><?= $row['status']; ?></span>
                            <a href="detail_tawaran.php?id=<?= $row['id_tawaran']; ?>" class="btn-view">Lihat</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">Tiada tawaran dihantar.</div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

</body>
</html>