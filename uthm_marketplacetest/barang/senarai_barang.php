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

// Get all categories for dropdown
$sql_kategori = "SELECT * FROM kategori_barang ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($conn, $sql_kategori);

// Get all products
$sql = "
    SELECT
        barang.*,
        kategori_barang.nama_kategori
    FROM barang
    LEFT JOIN kategori_barang
        ON barang.id_kategori = kategori_barang.id_kategori
    ORDER BY id_barang DESC
";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Senarai Barang | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        /* Navbar with category dropdown */
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
        
        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a1a2e;
            border-left: 5px solid #6c3cff;
            padding-left: 1rem;
            margin-bottom: 2rem;
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
            <li class="active">
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
        
        <!-- NAVBAR with Category Dropdown -->
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
            <a href="../barang/tambah_barang.php">
                <button type="button">Jual Barang</button>
            </a>
        </div>
        
        <!-- Page Header -->
        <div class="page-header">
            <h1>Senarai Barang</h1>
        </div>
        
        <!-- Product Grid -->
        <div class="product-grid">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if(!empty($row['gambar_barang'])): ?>
                                <img src="gambar/<?= htmlspecialchars($row['gambar_barang']); ?>" alt="<?= htmlspecialchars($row['nama_barang']); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/250x180?text=Tiada+Gambar" alt="Placeholder">
                            <?php endif; ?>
                        </div>
                        <div class="product-category">
                            <?= htmlspecialchars($row['nama_kategori'] ?? 'PRODUK'); ?>
                        </div>
                        <h3><?= htmlspecialchars($row['nama_barang']); ?></h3>
                        <p class="price">RM<?= number_format($row['harga'], 2); ?></p>
                        <a href="detail_barang.php?id=<?= $row['id_barang']; ?>">
                            <button>Lihat Detail</button>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    Tiada barang dalam senarai.
                </div>
            <?php endif; ?>
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
    
    // Allow Enter key to search
    document.getElementById('searchKeyword').addEventListener('keypress', function(e) {
        if(e.key === 'Enter') {
            doSearch();
        }
    });
</script>

</body>
</html>