<?php

include("../includes/session.php");
include("../config/database.php");

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

// ADUAN NOTIFICATION COUNT - UNREAD CHAT MESSAGES
$sql_notif_aduan = "SELECT COUNT(*) AS jumlah FROM aduan_chat 
                    WHERE id_aduan IN (SELECT id_aduan FROM aduan WHERE id_pengguna = '$id_pengguna')
                    AND id_pengguna != '$id_pengguna' 
                    AND status = 'belum_dibaca'";
$result_notif_aduan = mysqli_query($conn, $sql_notif_aduan);
$data_notif_aduan = mysqli_fetch_assoc($result_notif_aduan);
$jumlah_notif_aduan = $data_notif_aduan['jumlah'];

// Get all categories for dropdown
$sql_kategori = "SELECT * FROM kategori_barang ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($conn, $sql_kategori);

// Get latest products (barang)
$sql_barang = "
    SELECT
        barang.*,
        kategori_barang.nama_kategori
    FROM barang
    LEFT JOIN kategori_barang
        ON barang.id_kategori = kategori_barang.id_kategori
    ORDER BY id_barang DESC
    LIMIT 8
";

$result_barang = mysqli_query($conn, $sql_barang);

// Get latest services (servis)
$sql_servis = "
    SELECT *
    FROM servis
    ORDER BY id_servis DESC
    LIMIT 8
";

$result_servis = mysqli_query($conn, $sql_servis);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .search-wrapper {
            display: flex;
            flex: 1;
            gap: 10px;
            align-items: center;
        }
        
        .search-wrapper input {
            flex: 1;
            padding: 16px 20px;
            border: none;
            border-radius: 0px;
            background: white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            font-size: 15px;
        }
        
        .search-wrapper input:focus {
            outline: none;
        }
        
        .category-select {
            padding: 16px 20px;
            border: none;
            border-radius: 0px;
            background: white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            font-size: 15px;
            cursor: pointer;
            min-width: 150px;
        }
        
        .category-select:focus {
            outline: none;
        }
        
        .search-btn {
            padding: 16px 30px;
            border: none;
            border-radius: 0px;
            background: linear-gradient(90deg, #6c3cff, #4c7cff);
            color: white;
            font-weight: 600;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
            }
            .search-wrapper {
                width: 100%;
                flex-direction: column;
            }
            .search-wrapper input,
            .category-select,
            .search-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="dashboard">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="logo">
            PlatformJualBeli
        </div>
        <ul class="menu">
            <li class="active">
                Utama
            </li>
            <li>
                <a href="../barang/senarai_barang.php">Barang</a>
            </li>
            <li>
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

        <div class="navbar">
            <div class="search-wrapper">
                <input type="text" id="searchKeyword" placeholder="Cari barang atau servis">
                <select id="categoryFilter" class="category-select">
                    <option value="">Semua Kategori</option>
                    <?php while($kat = mysqli_fetch_assoc($result_kategori)): ?>
                        <option value="<?= $kat['id_kategori']; ?>"><?= htmlspecialchars($kat['nama_kategori']); ?></option>
                    <?php endwhile; ?>
                </select>
                <button class="search-btn" onclick="doSearch()">Cari</button>
            </div>
        </div>

        <div class="hero">
            <div class="hero-left">
                <h1>
                    Platform Jual Beli &
                    Tukar Barang Pelajar UTHM
                </h1>
                <p>
                    Cari barang, jual barang,
                    iklankan servis dan berhubung
                    dengan pelajar UTHM dengan mudah.
                </p>
            </div>
        </div>

        <div class="products">
            <h2>Produk Terbaru</h2>
            <div class="product-grid">
                <?php if(mysqli_num_rows($result_barang) > 0): ?>
                    <?php while($barang = mysqli_fetch_assoc($result_barang)): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if(!empty($barang['gambar_barang'])): ?>
                                <img src="../barang/gambar/<?= htmlspecialchars($barang['gambar_barang']); ?>" alt="<?= htmlspecialchars($barang['nama_barang']); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/250x180?text=Produk" alt="Placeholder">
                            <?php endif; ?>
                        </div>
                        <div class="product-category">
                            <?= htmlspecialchars($barang['nama_kategori'] ?? 'PRODUK'); ?>
                        </div>
                        <h3><?= htmlspecialchars($barang['nama_barang']); ?></h3>
                        <p class="price">RM<?= number_format($barang['harga'], 2); ?></p>
                        <a href="../barang/detail_barang.php?id=<?= $barang['id_barang']; ?>">
                            <button>Lihat Detail</button>
                        </a>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">Tiada produk. Klik "Iklan Baru" untuk menambah produk.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="products">
            <h2>Servis Terbaru</h2>
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
                        <div class="product-category">
                            SERVIS
                        </div>
                        <h3><?= htmlspecialchars($servis['nama_servis']); ?></h3>
                        <p class="price">RM<?= number_format($servis['harga'], 2); ?></p>
                        <a href="../servis/detail_servis.php?id=<?= $servis['id_servis']; ?>">
                            <button>Lihat Detail</button>
                        </a>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">Tiada servis. Klik "Iklan Baru" untuk menambah servis.</div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<script>
    function doSearch() {
        var keyword = document.getElementById('searchKeyword').value;
        var kategori = document.getElementById('categoryFilter').value;
        
        if(keyword.trim() === "") {
            alert("Sila masukkan kata kunci carian");
            return;
        }
        
        var url = "../barang/carian.php?keyword=" + encodeURIComponent(keyword);
        if(kategori) {
            url += "&kategori=" + encodeURIComponent(kategori);
        }
        
        window.location.href = url;
    }
    
    document.getElementById('searchKeyword').addEventListener('keypress', function(e) {
        if(e.key === 'Enter') {
            doSearch();
        }
    });
</script>

</body>
</html>