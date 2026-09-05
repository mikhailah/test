<?php

include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];
$id_penjual = $_GET['id'];

// Get penjual profile data
$sql = "SELECT * FROM pengguna WHERE id_pengguna = '$id_penjual'";
$result = mysqli_query($conn, $sql);
$penjual = mysqli_fetch_assoc($result);

if(!$penjual) {
    die("Pengguna tidak ditemui.");
}

// Get notification count for sidebar
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

// Get penjual's products
$sql_barang = "SELECT * FROM barang WHERE id_pengguna = '$id_penjual' ORDER BY id_barang DESC";
$result_barang = mysqli_query($conn, $sql_barang);
$total_barang = mysqli_num_rows($result_barang);

// Get penjual's services
$sql_servis = "SELECT * FROM servis WHERE id_pengguna = '$id_penjual' ORDER BY id_servis DESC";
$result_servis = mysqli_query($conn, $sql_servis);
$total_servis = mysqli_num_rows($result_servis);

// Path untuk gambar profil
$gambar_profil_path = "";
if(!empty($penjual['gambar_profil'])) {
    if(file_exists("../profil/gambar/".$penjual['gambar_profil'])) {
        $gambar_profil_path = "../profil/gambar/".$penjual['gambar_profil'];
    } elseif(file_exists("gambar/".$penjual['gambar_profil'])) {
        $gambar_profil_path = "gambar/".$penjual['gambar_profil'];
    } elseif(file_exists("../barang/gambar/".$penjual['gambar_profil'])) {
        $gambar_profil_path = "../barang/gambar/".$penjual['gambar_profil'];
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil <?= htmlspecialchars($penjual['nama_penuh']); ?> | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .main-content {
            padding: 2rem;
            background: #f8f9fc;
            min-height: 100vh;
        }
        
        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .profile-header {
            background: white;
            border-radius: 0px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid #eef2f8;
            display: flex;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #6c3cff;
        }
        
        .profile-info {
            flex: 1;
        }
        
        .profile-info h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .profile-info .email {
            color: #6c3cff;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }
        
        .profile-info .phone {
            color: #666;
            font-size: 0.9rem;
        }
        
        .profile-stats {
            display: flex;
            gap: 2rem;
            margin-top: 0.5rem;
        }
        
        .profile-stats .stat {
            text-align: center;
        }
        
        .profile-stats .stat .number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #6c3cff;
        }
        
        .profile-stats .stat .label {
            font-size: 0.8rem;
            color: #888;
        }
        
        .btn-chat-profile {
            background: linear-gradient(135deg, #6c3cff, #4f7cff);
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 0px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-chat-profile:hover {
            opacity: 0.9;
        }
        
        .btn-back-profile {
            background: #eef2f8;
            color: #333;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 0px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-back-profile:hover {
            background: #ddd;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin: 2rem 0 1rem 0;
            border-bottom: 2px solid #eef2f8;
            padding-bottom: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            .profile-stats {
                justify-content: center;
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
            <li><a href="../servis/senarai_servis.php">Servis</a></li>
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
        
        <div class="profile-container">
            
            <!-- Profile Header -->
            <div class="profile-header">
                <?php if(!empty($gambar_profil_path)): ?>
                    <img src="<?= $gambar_profil_path; ?>" class="profile-avatar">
                <?php else: ?>
                    <img src="https://via.placeholder.com/120?text=No+Image" class="profile-avatar">
                <?php endif; ?>
                
                <div class="profile-info">
                    <h1><?= htmlspecialchars($penjual['nama_penuh']); ?></h1>
                    <div class="email"><?= htmlspecialchars($penjual['email'] ?? ''); ?></div>
                    <div class="phone"><?= htmlspecialchars($penjual['no_telefon'] ?? 'Tiada'); ?></div>
                    
                    <div class="profile-stats">
                        <div class="stat">
                            <div class="number"><?= $total_barang; ?></div>
                            <div class="label">Barang</div>
                        </div>
                        <div class="stat">
                            <div class="number"><?= $total_servis; ?></div>
                            <div class="label">Servis</div>
                        </div>
                    </div>
                    
                    <div style="margin-top:1rem; display:flex; gap:0.8rem; flex-wrap:wrap;">
                        <a href="../mesej/mesej_baru.php?id=<?= $id_penjual; ?>" class="btn-chat-profile">Hubungi Penjual</a>
                        <a href="../barang/senarai_barang.php" class="btn-back-profile">Kembali</a>
                    </div>
                </div>
            </div>
            
            <!-- Barang Penjual -->
            <h2 class="section-title">Barang <?= htmlspecialchars($penjual['nama_penuh']); ?></h2>
            <div class="product-grid">
                <?php if(mysqli_num_rows($result_barang) > 0): ?>
                    <?php while($barang = mysqli_fetch_assoc($result_barang)): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if(!empty($barang['gambar_barang'])): ?>
                                    <img src="../barang/gambar/<?= htmlspecialchars($barang['gambar_barang']); ?>" alt="<?= htmlspecialchars($barang['nama_barang']); ?>">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/250x180?text=Barang" alt="Placeholder">
                                <?php endif; ?>
                            </div>
                            <div class="product-category">BARANG</div>
                            <h3><?= htmlspecialchars($barang['nama_barang']); ?></h3>
                            <p class="price">RM<?= number_format($barang['harga'], 2); ?></p>
                            <a href="../barang/detail_barang.php?id=<?= $barang['id_barang']; ?>">
                                <button>Lihat Detail</button>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">Tiada barang dijual oleh pengguna ini.</div>
                <?php endif; ?>
            </div>
            
            <!-- Servis Penjual -->
            <h2 class="section-title">Servis <?= htmlspecialchars($penjual['nama_penuh']); ?></h2>
            <div class="product-grid">
                <?php if(mysqli_num_rows($result_servis) > 0): ?>
                    <?php while($servis = mysqli_fetch_assoc($result_servis)): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if(!empty($servis['gambar_servis'])): ?>
                                    <img src="../servis/gambar/<?= htmlspecialchars($servis['gambar_servis']); ?>" alt="<?= htmlspecialchars($servis['nama_servis']); ?>">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/250x180?text=Servis" alt="Placeholder">
                                <?php endif; ?>
                            </div>
                            <div class="product-category">SERVIS</div>
                            <h3><?= htmlspecialchars($servis['nama_servis']); ?></h3>
                            <p class="price">RM<?= number_format($servis['harga'], 2); ?></p>
                            <a href="../servis/detail_servis.php?id=<?= $servis['id_servis']; ?>">
                                <button>Lihat Detail</button>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">Tiada servis ditawarkan oleh pengguna ini.</div>
                <?php endif; ?>
            </div>
            
        </div>
        
    </div>
    
</div>

</body>
</html>