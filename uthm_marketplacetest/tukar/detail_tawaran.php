<?php
include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];
$id_tawaran = $_GET['id'];

$sql = "
    SELECT t.*, 
           b1.nama_barang AS barang_diminta, b1.harga AS harga_diminta, b1.gambar_barang AS gambar_diminta,
           b2.nama_barang AS barang_ditawar, b2.harga AS harga_ditawar, b2.gambar_barang AS gambar_ditawar,
           p1.nama_penuh AS nama_pengirim,
           p2.nama_penuh AS nama_penerima
    FROM tawaran_tukar t
    JOIN barang b1 ON t.id_barang_diminta = b1.id_barang
    JOIN barang b2 ON t.id_barang_ditawar = b2.id_barang
    JOIN pengguna p1 ON t.id_pengirim = p1.id_pengguna
    JOIN pengguna p2 ON t.id_penerima = p2.id_pengguna
    WHERE t.id_tawaran = '$id_tawaran'
";
$result = mysqli_query($conn, $sql);
$tawaran = mysqli_fetch_assoc($result);

// Check if current user is involved
if(!$tawaran || ($tawaran['id_pengirim'] != $id_pengguna && $tawaran['id_penerima'] != $id_pengguna)) {
    die("Anda tidak mempunyai akses ke tawaran ini.");
}

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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Tawaran | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .main-content { padding: 2rem; background: #f8f9fc; min-height: 100vh; }
        .detail-container { max-width: 700px; margin: 0 auto; background: white; padding: 2rem; border: 1px solid #eef2f8; }
        
        .trade-header { text-align: center; margin-bottom: 2rem; }
        .trade-header h1 { font-size: 1.5rem; }
        .trade-id { color: #666; font-size: 0.85rem; }
        
        .trade-items { display: flex; align-items: center; justify-content: center; gap: 2rem; margin: 2rem 0; flex-wrap: wrap; }
        .trade-item { text-align: center; }
        .trade-item img { width: 120px; height: 120px; object-fit: contain; background: #f5f5f5; }
        .trade-item .label { font-size: 0.7rem; color: #888; text-transform: uppercase; margin-top: 0.5rem; }
        .trade-item .name { font-weight: 600; }
        .trade-item .price { color: #6c3cff; font-weight: 700; }
        .trade-arrow { font-size: 2rem; color: #6c3cff; }
        
        .trade-money { text-align: center; font-size: 1.1rem; margin: 1rem 0; }
        .trade-money strong { color: #6c3cff; }
        
        .trade-message { background: #f8f9fc; padding: 1rem; margin: 1rem 0; }
        
        .status-badge { display: inline-block; padding: 0.3rem 1rem; font-weight: 600; text-transform: uppercase; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-diterima { background: #d1fae5; color: #065f46; }
        .status-ditolak { background: #fee2e2; color: #991b1b; }
        
        .action-buttons { display: flex; gap: 1rem; margin-top: 2rem; justify-content: center; flex-wrap: wrap; }
        .btn-accept { background: #10b981; color: white; border: none; padding: 0.8rem 2rem; font-weight: 600; cursor: pointer; }
        .btn-accept:hover { background: #059669; }
        .btn-reject { background: #ef4444; color: white; border: none; padding: 0.8rem 2rem; font-weight: 600; cursor: pointer; }
        .btn-reject:hover { background: #dc2626; }
        .btn-chat { background: #6c3cff; color: white; border: none; padding: 0.8rem 2rem; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-chat:hover { opacity: 0.9; }
        .btn-back { background: #e2e2e2; color: #333; border: none; padding: 0.8rem 2rem; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-back:hover { background: #ccc; }
    </style>
</head>
<body>

<div class="dashboard">
    
    <!-- SIDEBAR - HANYA TAWARAN PERTUKARAN SAHAJA YANG ACTIVE -->
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
        <div class="detail-container">
            <div class="trade-header">
                <h1>Tawaran Pertukaran</h1>
                <div class="trade-id">#TRD<?= str_pad($tawaran['id_tawaran'], 5, '0', STR_PAD_LEFT); ?></div>
                <span class="status-badge status-<?= $tawaran['status']; ?>"><?= $tawaran['status']; ?></span>
            </div>
            
            <div class="trade-items">
                <div class="trade-item">
                    <?php if(!empty($tawaran['gambar_diminta'])): ?>
                        <img src="../barang/gambar/<?= htmlspecialchars($tawaran['gambar_diminta']); ?>" alt="">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/120" alt="">
                    <?php endif; ?>
                    <div class="label">Anda Berikan</div>
                    <div class="name"><?= htmlspecialchars($tawaran['barang_diminta']); ?></div>
                    <div class="price">RM<?= number_format($tawaran['harga_diminta'], 2); ?></div>
                </div>
                
                <div class="trade-arrow">↔</div>
                
                <div class="trade-item">
                    <?php if(!empty($tawaran['gambar_ditawar'])): ?>
                        <img src="../barang/gambar/<?= htmlspecialchars($tawaran['gambar_ditawar']); ?>" alt="">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/120" alt="">
                    <?php endif; ?>
                    <div class="label">Anda Terima</div>
                    <div class="name"><?= htmlspecialchars($tawaran['barang_ditawar']); ?></div>
                    <div class="price">RM<?= number_format($tawaran['harga_ditawar'], 2); ?></div>
                </div>
            </div>
            
            <?php if($tawaran['tambah_wang'] > 0): ?>
                <div class="trade-money">
                    Tambah Wang: <strong>RM<?= number_format($tawaran['tambah_wang'], 2); ?></strong>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($tawaran['mesej'])): ?>
                <div class="trade-message">
                    <strong>Nota:</strong><br>
                    <?= nl2br(htmlspecialchars($tawaran['mesej'])); ?>
                </div>
            <?php endif; ?>
            
            <div style="font-size:0.85rem;color:#666;text-align:center;margin-top:1rem;">
                Daripada: <?= htmlspecialchars($tawaran['nama_pengirim']); ?> → <?= htmlspecialchars($tawaran['nama_penerima']); ?>
            </div>
            
            <!-- Action Buttons -->
            <?php if($tawaran['id_penerima'] == $id_pengguna && $tawaran['status'] == 'pending'): ?>
                <div class="action-buttons">
                    <a href="proses_tindakan_tawar.php?id=<?= $tawaran['id_tawaran']; ?>&action=terima" class="btn-accept" onclick="return confirm('Terima tawaran ini?')">Terima</a>
                    <a href="proses_tindakan_tawar.php?id=<?= $tawaran['id_tawaran']; ?>&action=tolak" class="btn-reject" onclick="return confirm('Tolak tawaran ini?')">Tolak</a>
                    <a href="../mesej/chat.php?id=<?= $tawaran['id_pengirim']; ?>" class="btn-chat">Chat</a>
                </div>
            <?php else: ?>
                <div class="action-buttons">
                    <a href="../mesej/chat.php?id=<?= ($tawaran['id_pengirim'] == $id_pengguna) ? $tawaran['id_penerima'] : $tawaran['id_pengirim']; ?>" class="btn-chat">Chat</a>
                    <a href="senarai_tawaran.php" class="btn-back">Kembali</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>