<?php
include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];
$id_aduan = $_GET['id'];

// Get aduan details
$sql = "SELECT * FROM aduan WHERE id_aduan = '$id_aduan'";
$result = mysqli_query($conn, $sql);
$aduan = mysqli_fetch_assoc($result);

if(!$aduan || ($aduan['id_pengguna'] != $id_pengguna)) {
    die("Akses ditolak.");
}

// Notification counts for sidebar
$sql_notifikasi = "SELECT COUNT(*) AS jumlah FROM mesej WHERE id_penerima = '$id_pengguna' AND dibaca = 'tidak'";
$result_notifikasi = mysqli_query($conn, $sql_notifikasi);
$data_notifikasi = mysqli_fetch_assoc($result_notifikasi);
$jumlah_notifikasi = $data_notifikasi['jumlah'];

$sql_notif_trade = "SELECT COUNT(*) AS jumlah FROM notifikasi WHERE id_penerima = '$id_pengguna' AND dibaca = 'tidak'";
$result_notif_trade = mysqli_query($conn, $sql_notif_trade);
$data_notif_trade = mysqli_fetch_assoc($result_notif_trade);
$jumlah_notif_trade = $data_notif_trade['jumlah'];

// Get chat messages
$sql_chat = "SELECT * FROM aduan_chat WHERE id_aduan = '$id_aduan' ORDER BY tarikh_hantar ASC";
$result_chat = mysqli_query($conn, $sql_chat);

// ============================================================
// MARK CHAT AS READ - PENTING UNTUK NOTIFICATION BADGE
// ============================================================
mysqli_query($conn, "UPDATE aduan_chat SET status='dibaca' WHERE id_aduan = '$id_aduan' AND id_pengguna != '$id_pengguna'");

// Get multiple lampiran
$sql_lampiran = "SELECT * FROM aduan_lampiran WHERE id_aduan = '$id_aduan' ORDER BY id_lampiran ASC";
$result_lampiran = mysqli_query($conn, $sql_lampiran);
$total_lampiran = mysqli_num_rows($result_lampiran);

// ============================================================
// GET USER TERLIBAT
// ============================================================
$sql_user_terlibat = "SELECT nama_penuh, emel, gambar_profil FROM pengguna WHERE id_pengguna = '{$aduan['id_pengguna_terlibat']}'";
$result_user_terlibat = mysqli_query($conn, $sql_user_terlibat);
$user_terlibat = mysqli_fetch_assoc($result_user_terlibat);

// ============================================================
// GET BARANG TERLIBAT (if any)
// ============================================================
$barang_terlibat = null;
if(!empty($aduan['id_barang_terlibat'])) {
    $sql_barang_terlibat = "SELECT nama_barang, harga, gambar_barang FROM barang WHERE id_barang = '{$aduan['id_barang_terlibat']}'";
    $result_barang_terlibat = mysqli_query($conn, $sql_barang_terlibat);
    $barang_terlibat = mysqli_fetch_assoc($result_barang_terlibat);
}

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

// Re-calculate unread count after marking as read
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
    <title>Detail Aduan | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .main-content { padding: 2rem; background: #f8f9fc; min-height: 100vh; }
        .container { max-width: 800px; margin: 0 auto; }
        
        .detail-card { background: white; border: 1px solid #eef2f8; padding: 2rem; }
        .detail-card h1 { font-size: 1.5rem; margin-bottom: 0.5rem; }
        
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem; }
        .meta { color: #888; font-size: 0.85rem; margin-bottom: 1rem; }
        .meta span { margin-right: 1rem; }
        
        .status-badge { padding: 0.25rem 0.8rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; display: inline-block; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-process { background: #dbeafe; color: #1e40af; }
        .status-done { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        
        .description { margin: 1rem 0; padding: 1rem; background: #f8f9fc; }
        
        /* INVOLVED PARTIES */
        .involved-section { margin: 1rem 0; padding: 1rem; background: #f8f9fc; border-left: 4px solid #6c3cff; }
        .involved-section .row { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
        .involved-section .avatar-small { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #6c3cff; }
        .involved-section .barang-img { width: 60px; height: 60px; object-fit: contain; background: white; border: 1px solid #ddd; border-radius: 4px; }
        .involved-section .label { font-weight: 600; color: #333; }
        .involved-section .value { color: #1a1a2e; }
        .involved-section .divider { color: #ddd; }
        
        /* LAMPIRAN - MULTIPLE */
        .lampiran-section { margin: 1rem 0; padding: 1rem; background: #f8f9fc; border: 1px dashed #ddd; }
        .lampiran-section strong { display: block; margin-bottom: 0.5rem; }
        .lampiran-item { display: inline-block; border: 1px solid #ddd; padding: 0.5rem; text-align: center; width: 150px; margin-right: 0.5rem; margin-bottom: 0.5rem; background: white; }
        .lampiran-item img { max-width: 100%; max-height: 100px; object-fit: cover; }
        .lampiran-item .file-name { font-size: 0.7rem; color: #888; margin-top: 0.3rem; word-break: break-all; }
        .lampiran-item .file-icon { font-size: 2rem; padding: 0.5rem; }
        .lampiran-item a { color: #6c3cff; text-decoration: none; font-size: 0.7rem; }
        .lampiran-item a:hover { text-decoration: underline; }
        
        .tindakan { background: #fef3c7; padding: 1rem; margin: 1rem 0; border-left: 4px solid #f59e0b; }
        .tindakan small { color: #888; }
        
        .chat-box { margin-top: 2rem; border-top: 1px solid #eef2f8; padding-top: 1.5rem; }
        .chat-box h3 { margin-bottom: 1rem; }
        .chat { margin-bottom: 1rem; }
        .chat .msg { padding: 0.5rem 1rem; max-width: 80%; display: inline-block; text-align: left; }
        .chat.user { text-align: right; }
        .chat.user .msg { background: #6c3cff; color: white; }
        .chat.admin .msg { background: #f1f3f5; color: #1a1a2e; }
        .chat .time { font-size: 0.7rem; color: #999; margin-top: 0.2rem; }
        .chat .sender { font-size: 0.7rem; color: #888; margin-bottom: 0.2rem; }
        
        .chat-input { display: flex; gap: 0.8rem; margin-top: 1rem; }
        .chat-input input { flex: 1; padding: 0.8rem 1rem; border: 1px solid #ddd; border-radius: 0px; }
        .chat-input input:focus { outline: none; border-color: #6c3cff; }
        .chat-input button { background: linear-gradient(135deg, #6c3cff, #4f7cff); color: white; border: none; padding: 0.8rem 1.5rem; font-weight: 600; cursor: pointer; }
        .chat-input button:hover { opacity: 0.9; }
        
        .btn-back { background: #eef2f8; color: #333; border: none; padding: 0.8rem 2rem; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-back:hover { background: #ddd; }
        
        @media (max-width: 768px) { .main-content { padding: 1rem; } .chat .msg { max-width: 100%; } .lampiran-item { width: 120px; } }
    </style>
</head>
<body>

<div class="dashboard">
    <!-- SIDEBAR -->
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
            <div style="margin-bottom:1rem;">
                <a href="aduan_saya.php" class="btn-back">← Kembali</a>
            </div>

            <div class="detail-card">
                <div class="header-top">
                    <div>
                        <h1><?= htmlspecialchars($aduan['tajuk']); ?></h1>
                        <div class="meta">
                            <span><?= date('d/m/Y H:i', strtotime($aduan['tarikh_hantar'])); ?></span>
                            <span><?= ucfirst($aduan['kategori']); ?></span>
                            <?php if($aduan['jenis_referensi']): ?>
                                <span><?= ucfirst($aduan['jenis_referensi']); ?> #<?= $aduan['id_referensi']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="status-badge <?= $status_badge[$aduan['status']] ?? 'status-pending'; ?>"><?= $status_label[$aduan['status']] ?? strtoupper($aduan['status']); ?></span>
                </div>

                <!-- INVOLVED PARTIES -->
                <div class="involved-section">
                    <strong>Pihak Terlibat</strong>
                    <div style="margin-top:0.5rem;">
                        <div class="row">
                            <span class="label">Pengadu:</span>
                            <span class="value"><?= htmlspecialchars($aduan['nama_penuh'] ?? 'Unknown'); ?></span>
                        </div>
                        <div class="row" style="margin-top:0.3rem;">
                            <span class="label">Pengguna Terlibat:</span>
                            <?php if($user_terlibat): ?>
                                <?php if(!empty($user_terlibat['gambar_profil']) && $user_terlibat['gambar_profil'] != 'default.png'): ?>
                                    <img class="avatar-small" src="../profil/gambar/<?= htmlspecialchars($user_terlibat['gambar_profil']); ?>" alt="Avatar">
                                <?php else: ?>
                                    <div class="avatar-small" style="background:#eef2ff;display:flex;align-items:center;justify-content:center;font-weight:bold;color:#6c3cff;font-size:1.2rem;">
                                        <?= substr($user_terlibat['nama_penuh'], 0, 1); ?>
                                    </div>
                                <?php endif; ?>
                                <span class="value"><?= htmlspecialchars($user_terlibat['nama_penuh']); ?></span>
                                <span style="color:#888;font-size:0.8rem;">(<?= htmlspecialchars($user_terlibat['emel']); ?>)</span>
                            <?php else: ?>
                                <span class="value" style="color:#999;">Tiada</span>
                            <?php endif; ?>
                        </div>
                        <?php if($barang_terlibat): ?>
                            <div class="row" style="margin-top:0.3rem;">
                                <span class="label">Barang Terlibat:</span>
                                <?php if(!empty($barang_terlibat['gambar_barang'])): ?>
                                    <img class="barang-img" src="../barang/gambar/<?= htmlspecialchars($barang_terlibat['gambar_barang']); ?>" alt="Barang">
                                <?php endif; ?>
                                <span class="value"><?= htmlspecialchars($barang_terlibat['nama_barang']); ?></span>
                                <span style="color:#6c3cff;font-weight:600;">RM<?= number_format($barang_terlibat['harga'], 2); ?></span>
                            </div>
                        <?php else: ?>
                            <div class="row" style="margin-top:0.3rem;">
                                <span class="label">Barang Terlibat:</span>
                                <span class="value" style="color:#999;">Tiada</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="description">
                    <strong>Penerangan:</strong><br>
                    <?= nl2br(htmlspecialchars($aduan['penerangan'])); ?>
                </div>

                <!-- LAMPIRAN - MULTIPLE -->
                <?php if($total_lampiran > 0): ?>
                    <div class="lampiran-section">
                        <strong>Lampiran (<?= $total_lampiran; ?> fail)</strong>
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:0.5rem;">
                            <?php while($lampiran = mysqli_fetch_assoc($result_lampiran)): 
                                $lampiran_path = "lampiran/" . $lampiran['nama_file'];
                                $file_exists = file_exists($lampiran_path);
                                $is_image = false;
                                if($file_exists) {
                                    $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
                                    $ext = strtolower(pathinfo($lampiran['nama_file'], PATHINFO_EXTENSION));
                                    $is_image = in_array($ext, $image_extensions);
                                }
                            ?>
                                <div class="lampiran-item">
                                    <?php if($file_exists && $is_image): ?>
                                        <a href="<?= $lampiran_path; ?>" target="_blank">
                                            <img src="<?= $lampiran_path; ?>" alt="<?= htmlspecialchars($lampiran['nama_file']); ?>">
                                        </a>
                                    <?php elseif($file_exists): ?>
                                        <div class="file-icon">📄</div>
                                        <a href="<?= $lampiran_path; ?>" target="_blank"><?= htmlspecialchars($lampiran['nama_file']); ?></a>
                                    <?php else: ?>
                                        <div style="font-size:0.7rem;color:#dc2626;padding:0.5rem;">Fail tidak ditemui</div>
                                    <?php endif; ?>
                                    <div class="file-name"><?= htmlspecialchars($lampiran['nama_file']); ?></div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(!empty($aduan['tindakan'])): ?>
                    <div class="tindakan">
                        <strong>Tindakan:</strong><br>
                        <?= nl2br(htmlspecialchars($aduan['tindakan'])); ?>
                        <br><small><?= date('d/m/Y H:i', strtotime($aduan['tarikh_tindakan'])); ?></small>
                    </div>
                <?php endif; ?>

                <!-- Chat Section -->
                <div class="chat-box">
                    <h3>Perbualan</h3>
                    
                    <?php if(mysqli_num_rows($result_chat) > 0): ?>
                        <?php while($chat = mysqli_fetch_assoc($result_chat)): ?>
                            <div class="chat <?= ($chat['id_pengguna'] == $id_pengguna) ? 'user' : 'admin'; ?>">
                                <div>
                                    <div class="sender"><?= ($chat['id_pengguna'] == $id_pengguna) ? 'Anda' : 'Admin'; ?></div>
                                    <div class="msg"><?= nl2br(htmlspecialchars($chat['mesej'])); ?></div>
                                    <div class="time"><?= date('d/m/Y H:i', strtotime($chat['tarikh_hantar'])); ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color:#888;">Tiada perbualan. Hantar mesej di bawah.</p>
                    <?php endif; ?>

                    <form class="chat-input" action="hantar_mesej_aduan.php" method="POST">
                        <input type="hidden" name="id_aduan" value="<?= $id_aduan; ?>">
                        <input type="text" name="mesej" placeholder="Taip mesej..." required autocomplete="off">
                        <button type="submit">Hantar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>