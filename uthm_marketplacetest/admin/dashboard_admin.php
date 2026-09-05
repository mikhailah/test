<?php
include("../includes/session.php");
include("../config/database.php");

// Check if user is admin ONLY (not staff)
$id_pengguna = $_SESSION['id_pengguna'];
$sql_user = "SELECT * FROM pengguna WHERE id_pengguna = '$id_pengguna'";
$result_user = mysqli_query($conn, $sql_user);
$user = mysqli_fetch_assoc($result_user);

// ONLY 'pentadbir' can access this page
if($user['peranan'] != 'pentadbir') {
    header("Location: ../dashboard/index.php");
    exit();
}

// Count aduan by status
$count_baru = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM aduan WHERE status='baru'"))['total'];
$count_proses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM aduan WHERE status='dalam_proses'"))['total'];
$count_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM aduan WHERE status='selesai'"))['total'];
$count_ditolak = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM aduan WHERE status='ditolak'"))['total'];
$total_aduan = $count_baru + $count_proses + $count_selesai + $count_ditolak;

// Count users
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pengguna"))['total'];

// Count products
$total_barang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM barang"))['total'];

// Count services
$total_servis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM servis"))['total'];

// Get recent aduan
$sql_recent = "SELECT a.*, p.nama_penuh FROM aduan a 
               LEFT JOIN pengguna p ON a.id_pengguna = p.id_pengguna 
               ORDER BY a.tarikh_hantar DESC LIMIT 5";
$result_recent = mysqli_query($conn, $sql_recent);

// Notification counts
$sql_notifikasi = "SELECT COUNT(*) AS jumlah FROM mesej WHERE id_penerima = '$id_pengguna' AND dibaca = 'tidak'";
$result_notifikasi = mysqli_query($conn, $sql_notifikasi);
$data_notifikasi = mysqli_fetch_assoc($result_notifikasi);
$jumlah_notifikasi = $data_notifikasi['jumlah'];

$sql_notif_trade = "SELECT COUNT(*) AS jumlah FROM notifikasi WHERE id_penerima = '$id_pengguna' AND dibaca = 'tidak'";
$result_notif_trade = mysqli_query($conn, $sql_notif_trade);
$data_notif_trade = mysqli_fetch_assoc($result_notif_trade);
$jumlah_notif_trade = $data_notif_trade['jumlah'];

// Count unread aduan for badge
$sql_notif_aduan = "SELECT COUNT(*) AS jumlah FROM aduan_chat WHERE id_pengguna != '$id_pengguna' AND status = 'belum_dibaca'";
$result_notif_aduan = mysqli_query($conn, $sql_notif_aduan);
$data_notif_aduan = mysqli_fetch_assoc($result_notif_aduan);
$jumlah_notif_aduan = $data_notif_aduan['jumlah'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .main-content { padding: 2rem; background: #f8f9fc; min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .welcome { margin-bottom: 2rem; }
        .welcome h1 { font-size: 1.8rem; border-left: 5px solid #6c3cff; padding-left: 1rem; }
        .welcome p { color: #888; margin-top: 0.5rem; }
        
        .stats-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border: 1px solid #eef2f8; text-align: center; }
        .stat-card .number { font-size: 2.5rem; font-weight: 700; color: #6c3cff; }
        .stat-card .label { font-size: 0.85rem; color: #888; margin-top: 0.3rem; }
        .stat-card .icon { font-size: 2rem; margin-bottom: 0.5rem; }
        
        .stat-card.red .number { color: #ef4444; }
        .stat-card.yellow .number { color: #f59e0b; }
        .stat-card.green .number { color: #10b981; }
        .stat-card.blue .number { color: #3b82f6; }
        
        .section-title { font-size: 1.3rem; font-weight: 600; margin: 2rem 0 1rem 0; border-bottom: 2px solid #eef2f8; padding-bottom: 0.5rem; }
        
        .recent-card { background: white; border: 1px solid #eef2f8; padding: 1rem; margin-bottom: 0.8rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .recent-card .info { flex: 1; }
        .recent-card .info .title { font-weight: 600; }
        .recent-card .info .meta { font-size: 0.8rem; color: #888; }
        
        .status-badge { padding: 0.2rem 0.6rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-process { background: #dbeafe; color: #1e40af; }
        .status-done { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        
        .btn-view { background: #6c3cff; color: white; border: none; padding: 0.3rem 1rem; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; font-size: 0.8rem; }
        .btn-view:hover { opacity: 0.9; }
        
        .quick-actions { display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem; }
        .quick-actions a { background: white; border: 1px solid #eef2f8; padding: 1rem 2rem; text-decoration: none; color: #1a1a2e; font-weight: 600; text-align: center; flex: 1; min-width: 150px; transition: 0.2s; }
        .quick-actions a:hover { background: #6c3cff; color: white; border-color: #6c3cff; }
        
        @media (max-width: 768px) { .main-content { padding: 1rem; } .stats-row { grid-template-columns: 1fr 1fr; } }
    </style>
</head>
<body>

<div class="dashboard">
    <div class="sidebar">
        <div class="logo">PlatformJualBeli</div>
        <ul class="menu">
            <li class="active"><a href="../admin/dashboard_admin.php">Utama</a></li>
            <li><a href="../barang/senarai_barang.php">Barang</a></li>
            <li><a href="../servis/senarai_servis.php">Servis</a></li>
            <li><a href="../tukar/senarai_tawaran.php">Tawaran Pertukaran</a></li>
            <li>
                <a href="../mesej/chat_senarai.php">
                    Mesej
                    <?php if($jumlah_notifikasi > 0): ?>
                        <span class="badge" style="background:#ef4444;"><?= $jumlah_notifikasi; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="admin_aduan.php">
                    Aduan
                    <?php if($jumlah_notif_aduan > 0): ?>
                        <span class="badge" style="background:#ef4444;"><?= $jumlah_notif_aduan; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li><a href="../profil/profil.php">Profil</a></li>
            <li><a href="../auth/logout.php">Log Keluar</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container">
            <div class="welcome">
                <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['nama_penuh']); ?></h1>
                <p>Anda login sebagai <strong><?= strtoupper($_SESSION['peranan']); ?></strong>. Ini adalah dashboard pentadbiran.</p>
            </div>

            <div class="stats-row">
                <div class="stat-card red">
                    <div class="icon">Aduan Baru</div>
                    <div class="number"><?= $count_baru; ?></div>
                    <div class="label">Aduan Baru</div>
                </div>
                <div class="stat-card yellow">
                    <div class="icon">Dalam Proses</div>
                    <div class="number"><?= $count_proses; ?></div>
                    <div class="label">Dalam Proses</div>
                </div>
                <div class="stat-card green">
                    <div class="icon">Selesai</div>
                    <div class="number"><?= $count_selesai; ?></div>
                    <div class="label">Selesai</div>
                </div>
                <div class="stat-card blue">
                    <div class="icon">Pengguna</div>
                    <div class="number"><?= $total_users; ?></div>
                    <div class="label">Pengguna</div>
                </div>
                <div class="stat-card">
                    <div class="icon">Barang</div>
                    <div class="number"><?= $total_barang; ?></div>
                    <div class="label">Barang</div>
                </div>
                <div class="stat-card">
                    <div class="icon">Servis</div>
                    <div class="number"><?= $total_servis; ?></div>
                    <div class="label">Servis</div>
                </div>
            </div>

            <div class="quick-actions">
                <a href="admin_aduan.php">Urus Aduan</a>
                <a href="../barang/senarai_barang.php">Urus Barang</a>
                <a href="../servis/senarai_servis.php">Urus Servis</a>
                <a href="../profil/profil.php">Urus Profil</a>
            </div>

            <h2 class="section-title">Aduan Terkini</h2>
            <?php if(mysqli_num_rows($result_recent) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result_recent)): ?>
                    <div class="recent-card">
                        <div class="info">
                            <div class="title"><?= htmlspecialchars($row['tajuk']); ?></div>
                            <div class="meta">
                                Oleh: <?= htmlspecialchars($row['nama_penuh'] ?? 'Unknown'); ?> • 
                                <?= date('d/m/Y H:i', strtotime($row['tarikh_hantar'])); ?>
                            </div>
                        </div>
                        <div style="display:flex;gap:0.8rem;align-items:center;">
                            <span class="status-badge status-<?= $row['status'] == 'dalam_proses' ? 'process' : ($row['status'] == 'selesai' ? 'done' : ($row['status'] == 'ditolak' ? 'rejected' : 'pending')); ?>">
                                <?= strtoupper(str_replace('_', ' ', $row['status'])); ?>
                            </span>
                            <a href="detail_aduan_admin.php?id=<?= $row['id_aduan']; ?>" class="btn-view">Lihat</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color:#888;">Tiada aduan terkini.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>