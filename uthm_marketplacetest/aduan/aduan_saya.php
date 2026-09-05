<?php
include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];

// Notification counts for sidebar
$sql_notifikasi = "SELECT COUNT(*) AS jumlah FROM mesej WHERE id_penerima = '$id_pengguna' AND dibaca = 'tidak'";
$result_notifikasi = mysqli_query($conn, $sql_notifikasi);
$data_notifikasi = mysqli_fetch_assoc($result_notifikasi);
$jumlah_notifikasi = $data_notifikasi['jumlah'];

$sql_notif_trade = "SELECT COUNT(*) AS jumlah FROM notifikasi WHERE id_penerima = '$id_pengguna' AND dibaca = 'tidak'";
$result_notif_trade = mysqli_query($conn, $sql_notif_trade);
$data_notif_trade = mysqli_fetch_assoc($result_notif_trade);
$jumlah_notif_trade = $data_notif_trade['jumlah'];

// COUNT UNREAD CHAT MESSAGES FOR ADUAN
$sql_notif_aduan = "SELECT COUNT(*) AS jumlah FROM aduan_chat 
                    WHERE id_aduan IN (SELECT id_aduan FROM aduan WHERE id_pengguna = '$id_pengguna')
                    AND id_pengguna != '$id_pengguna' 
                    AND status = 'belum_dibaca'";
$result_notif_aduan = mysqli_query($conn, $sql_notif_aduan);
$data_notif_aduan = mysqli_fetch_assoc($result_notif_aduan);
$jumlah_notif_aduan = $data_notif_aduan['jumlah'];

// Get user's aduan
$sql = "SELECT * FROM aduan WHERE id_pengguna = '$id_pengguna' ORDER BY tarikh_hantar DESC";
$result = mysqli_query($conn, $sql);
$total = mysqli_num_rows($result);

$status_badge = [
    'baru' => 'status-pending',
    'dalam_proses' => 'status-process',
    'selesai' => 'status-done',
    'ditolak' => 'status-rejected'
];

$status_label = [
    'baru' => 'Baru',
    'dalam_proses' => 'Dalam Proses',
    'selesai' => 'Selesai',
    'ditolak' => 'Ditolak'
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Aduan Saya | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .main-content { padding: 2rem; background: #f8f9fc; min-height: 100vh; }
        .container { max-width: 900px; margin: 0 auto; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .header h1 { font-size: 1.8rem; border-left: 5px solid #6c3cff; padding-left: 1rem; }
        
        .btn-primary { background: linear-gradient(135deg, #6c3cff, #4f7cff); color: white; border: none; padding: 0.7rem 1.5rem; border-radius: 0px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary:hover { opacity: 0.9; }
        
        .aduan-card { background: white; border: 1px solid #eef2f8; padding: 1.2rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .aduan-card:hover { background: #fafbfc; }
        .aduan-card.has-new { border-left: 4px solid #ef4444; }
        
        .aduan-info { flex: 1; }
        .aduan-info h3 { font-size: 1.1rem; margin-bottom: 0.3rem; color: #1a1a2e; }
        .aduan-info .meta { font-size: 0.85rem; color: #888; }
        .aduan-info .meta span { margin-right: 1rem; }
        .aduan-info .preview { margin-top: 0.3rem; font-size: 0.85rem; color: #666; }
        
        .status-badge { padding: 0.25rem 0.8rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; display: inline-block; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-process { background: #dbeafe; color: #1e40af; }
        .status-done { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        
        .btn-view { background: #6c3cff; color: white; border: none; padding: 0.4rem 1rem; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-view:hover { opacity: 0.9; }
        
        .notif-dot { display: inline-block; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; margin-left: 5px; animation: blink 1s infinite; }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
        
        .empty-state { text-align: center; padding: 3rem; color: #888; background: white; border: 1px solid #eef2f8; }
        
        .alert-success { background: #d1fae5; color: #065f46; padding: 0.8rem; margin-bottom: 1rem; text-align: center; }
        .alert-updated { background: #dbeafe; color: #1e40af; padding: 0.8rem; margin-bottom: 1rem; text-align: center; }
        
        @media (max-width: 768px) { .main-content { padding: 1rem; } }
    </style>
</head>
<body>

<div class="dashboard">
    <!-- SIDEBAR - TAMBAH ACTIVE PADA ADUAN -->
    <div class="sidebar">
        <div class="logo">PlatformJualBeli</div>
        <ul class="menu">
            <li><a href="../dashboard/index.php">Utama</a></li>
            <li><a href="../barang/senarai_barang.php">Barang</a></li>
            <li><a href="../servis/senarai_servis.php">Servis</a></li>
            <li><a href="../tukar/senarai_tawaran.php">Tawaran Pertukaran</a></li>
            <li><a href="../mesej/chat_senarai.php">Mesej <?php if($jumlah_notifikasi > 0): ?><span class="badge" style="background:#ef4444;"><?= $jumlah_notifikasi; ?></span><?php endif; ?></a></li>
            <li><a href="../profil/profil.php">Profil</a></li>
            <li class="active">
                <a href="aduan_saya.php">
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
        <div class="container">
            <div class="header">
                <h1>Aduan Saya</h1>
                <a href="tambah_aduan.php" class="btn-primary">Aduan Baru</a>
            </div>

            <?php if(isset($_GET['success'])): ?>
                <div class="alert-success">Aduan berjaya dihantar. Kami akan tindaklanjuti secepat mungkin.</div>
            <?php endif; ?>

            <?php if(isset($_GET['updated'])): ?>
                <div class="alert-updated">Status aduan telah dikemaskini.</div>
            <?php endif; ?>

            <?php if($total > 0): ?>
                <?php 
                mysqli_data_seek($result, 0);
                while($row = mysqli_fetch_assoc($result)): 
                    $sql_check_unread = "SELECT COUNT(*) AS unread FROM aduan_chat 
                                        WHERE id_aduan = '{$row['id_aduan']}' 
                                        AND id_pengguna != '$id_pengguna' 
                                        AND status = 'belum_dibaca'";
                    $result_check = mysqli_query($conn, $sql_check_unread);
                    $check = mysqli_fetch_assoc($result_check);
                    $has_unread = ($check['unread'] > 0);
                ?>
                    <div class="aduan-card <?= $has_unread ? 'has-new' : ''; ?>">
                        <div class="aduan-info">
                            <h3>
                                <?= htmlspecialchars($row['tajuk']); ?>
                                <?php if($has_unread): ?>
                                    <span class="notif-dot"></span>
                                <?php endif; ?>
                            </h3>
                            <div class="meta">
                                <span><?= date('d/m/Y H:i', strtotime($row['tarikh_hantar'])); ?></span>
                                <span><?= ucfirst($row['kategori']); ?></span>
                            </div>
                            <div class="preview">
                                <?= htmlspecialchars(substr($row['penerangan'], 0, 100)) . (strlen($row['penerangan']) > 100 ? '...' : ''); ?>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div class="status-badge <?= $status_badge[$row['status']] ?? 'status-pending'; ?>"><?= $status_label[$row['status']] ?? strtoupper($row['status']); ?></div>
                            <a href="detail_aduan.php?id=<?= $row['id_aduan']; ?>" class="btn-view" style="margin-top:0.5rem;">Lihat</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>Tiada aduan. Klik "Aduan Baru" untuk membuat aduan.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>