<?php

include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];

// ============================================================
// KIRA NOTIFIKASI TERKINI DARI DATABASE
// ============================================================
$sql_notifikasi = "SELECT COUNT(*) AS jumlah FROM mesej WHERE id_penerima = '$id_pengguna' AND dibaca = 'tidak'";
$result_notifikasi = mysqli_query($conn, $sql_notifikasi);
$data_notifikasi = mysqli_fetch_assoc($result_notifikasi);
$jumlah_notifikasi = $data_notifikasi['jumlah'];

// Trade notification count
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

// Get all chat conversations with lampiran
$sql = "
    SELECT
        p.id_pengguna,
        p.nama_penuh,
        p.gambar_profil,
        (
            SELECT mesej
            FROM mesej
            WHERE (id_penghantar = p.id_pengguna AND id_penerima = '$id_pengguna')
               OR (id_penghantar = '$id_pengguna' AND id_penerima = p.id_pengguna)
            ORDER BY id_mesej DESC
            LIMIT 1
        ) AS mesej_terakhir,
        (
            SELECT lampiran
            FROM mesej
            WHERE (id_penghantar = p.id_pengguna AND id_penerima = '$id_pengguna')
               OR (id_penghantar = '$id_pengguna' AND id_penerima = p.id_pengguna)
            ORDER BY id_mesej DESC
            LIMIT 1
        ) AS lampiran_terakhir,
        (
            SELECT tarikh_hantar
            FROM mesej
            WHERE (id_penghantar = p.id_pengguna AND id_penerima = '$id_pengguna')
               OR (id_penghantar = '$id_pengguna' AND id_penerima = p.id_pengguna)
            ORDER BY id_mesej DESC
            LIMIT 1
        ) AS masa_terakhir,
        (
            SELECT COUNT(*)
            FROM mesej
            WHERE id_penghantar = p.id_pengguna
            AND id_penerima = '$id_pengguna'
            AND dibaca = 'tidak'
        ) AS belum_baca
    FROM pengguna p
    WHERE p.id_pengguna IN (
        SELECT id_penghantar FROM mesej WHERE id_penerima = '$id_pengguna'
        UNION
        SELECT id_penerima FROM mesej WHERE id_penghantar = '$id_pengguna'
    )
    ORDER BY masa_terakhir DESC
";

$result = mysqli_query($conn, $sql);
$total_conversations = mysqli_num_rows($result);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Mesej | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .main-content {
            padding: 2rem;
            background: #f8f9fc;
            min-height: 100vh;
        }
        
        .inbox-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 0px;
            overflow: hidden;
            border: 1px solid #eef2f8;
        }
        
        .inbox-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eef2f8;
            background: white;
        }
        
        .inbox-header h1 {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }
        
        .inbox-header p {
            color: #666;
            font-size: 0.85rem;
        }
        
        .chat-list-item {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s;
            cursor: pointer;
            text-decoration: none;
        }
        
        .chat-list-item:hover {
            background: #f8f9fc;
        }
        
        .chat-avatar {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
            background: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #6c3cff;
            overflow: hidden;
        }
        
        .chat-avatar img {
            width: 55px;
            height: 55px;
            object-fit: cover;
        }
        
        .chat-info {
            flex: 1;
        }
        
        .chat-name {
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .chat-message {
            font-size: 0.85rem;
            color: #888;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 300px;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .chat-message .attach-icon {
            font-size: 0.8rem;
        }
        
        .chat-time {
            font-size: 0.7rem;
            color: #aaa;
            text-align: right;
        }
        
        .unread-badge {
            background: #6c3cff;
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            min-width: 20px;
            text-align: center;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #888;
        }
    </style>
</head>
<body>

<div class="dashboard">
    
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
        
        <div class="inbox-container">
            <div class="inbox-header">
                <h1>Mesej</h1>
                <p><?= $total_conversations; ?> perbualan</p>
            </div>
            
            <?php if($total_conversations > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <a href="chat.php?id=<?= $row['id_pengguna']; ?>" class="chat-list-item">
                        <div class="chat-avatar">
                            <?php if(!empty($row['gambar_profil']) && file_exists("../profil/gambar/".$row['gambar_profil'])): ?>
                                <img src="../profil/gambar/<?= htmlspecialchars($row['gambar_profil']); ?>" alt="<?= htmlspecialchars($row['nama_penuh']); ?>">
                            <?php else: ?>
                                <?= substr($row['nama_penuh'], 0, 1); ?>
                            <?php endif; ?>
                        </div>
                        <div class="chat-info">
                            <div class="chat-name">
                                <?= htmlspecialchars($row['nama_penuh']); ?>
                                <?php if($row['belum_baca'] > 0): ?>
                                    <span class="unread-badge"><?= $row['belum_baca']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="chat-message">
                                <?php if(!empty($row['lampiran_terakhir'])): ?>
                                    <span class="attach-icon">📎</span>
                                <?php endif; ?>
                                <?= htmlspecialchars(substr($row['mesej_terakhir'] ?? '', 0, 50)); ?>
                            </div>
                        </div>
                        <div class="chat-time">
                            <?php 
                            if($row['masa_terakhir']) {
                                echo date('d/m/Y', strtotime($row['masa_terakhir']));
                            }
                            ?>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    Tiada perbualan lagi. Mula berchat dengan penjual atau pembeli.
                </div>
            <?php endif; ?>
        </div>
        
    </div>
    
</div>

</body>
</html>