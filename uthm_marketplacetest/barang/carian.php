<?php

include("../includes/session.php");
include("../config/database.php");

$keyword = mysqli_real_escape_string($conn, $_GET['keyword']);
$kategori_aktif = isset($_GET['kategori']) ? $_GET['kategori'] : '';

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

// Get all categories for filter buttons
$sql_kategori = "SELECT * FROM kategori_barang ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($conn, $sql_kategori);
$kategori_list = mysqli_fetch_all($result_kategori, MYSQLI_ASSOC);

/* Search Products with Category Filter */
$filter_kategori = "";
if(!empty($kategori_aktif)) {
    $kategori_id = mysqli_real_escape_string($conn, $kategori_aktif);
    $filter_kategori = " AND barang.id_kategori = '$kategori_id'";
}

$sql_barang = "
    SELECT 
        barang.*,
        kategori_barang.nama_kategori
    FROM barang
    LEFT JOIN kategori_barang
        ON barang.id_kategori = kategori_barang.id_kategori
    WHERE (barang.nama_barang LIKE '%$keyword%'
    OR barang.penerangan LIKE '%$keyword%')
    $filter_kategori
    ORDER BY barang.id_barang DESC
";
$result_barang = mysqli_query($conn, $sql_barang);

/* Search Services */
$sql_servis = "
    SELECT *
    FROM servis
    WHERE nama_servis LIKE '%$keyword%'
    ORDER BY id_servis DESC
";
$result_servis = mysqli_query($conn, $sql_servis);

$total_barang = mysqli_num_rows($result_barang);
$total_servis = mysqli_num_rows($result_servis);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Carian | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .category-filter {
            background: white;
            border-radius: 0px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid #eef2f8;
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            align-items: center;
        }
        
        .category-filter span {
            font-weight: 600;
            color: #333;
            font-size: 0.85rem;
        }
        
        .category-btn {
            padding: 0.5rem 1.2rem;
            background: #f8f9fc;
            border: 1px solid #ddd;
            border-radius: 0px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: #333;
        }
        
        .category-btn:hover {
            background: #6c3cff;
            color: white;
            border-color: #6c3cff;
        }
        
        .category-btn.active {
            background: #6c3cff;
            color: white;
            border-color: #6c3cff;
        }
        
        .category-clear {
            padding: 0.5rem 1rem;
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 0px;
            font-size: 0.8rem;
            cursor: pointer;
            text-decoration: none;
            color: #dc2626;
        }
        
        .category-clear:hover {
            background: #dc2626;
            color: white;
        }
        
        .search-row {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-row form {
            flex: 1;
            display: flex;
            gap: 1rem;
        }
        
        @media (max-width: 768px) {
            .search-row {
                flex-direction: column;
            }
            .search-row form {
                width: 100%;
                flex-direction: column;
            }
            .category-filter {
                justify-content: center;
            }
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

        <div class="navbar">
            <div class="search-row">
                <form action="carian.php" method="GET">
                    <input type="text" name="keyword" placeholder="Cari barang atau servis" value="<?= htmlspecialchars($keyword); ?>" required>
                    <button type="submit">Cari</button>
                </form>
            </div>
        </div>

        <div class="category-filter">
            <span>Filter Kategori:</span>
            <a href="carian.php?keyword=<?= urlencode($keyword); ?>" class="category-btn <?= empty($kategori_aktif) ? 'active' : ''; ?>">Semua</a>
            <?php foreach($kategori_list as $kat): ?>
                <a href="carian.php?keyword=<?= urlencode($keyword); ?>&kategori=<?= $kat['id_kategori']; ?>" 
                   class="category-btn <?= ($kategori_aktif == $kat['id_kategori']) ? 'active' : ''; ?>">
                    <?= htmlspecialchars($kat['nama_kategori']); ?>
                </a>
            <?php endforeach; ?>
            <?php if(!empty($kategori_aktif)): ?>
                <a href="carian.php?keyword=<?= urlencode($keyword); ?>" class="category-clear">Clear Filter</a>
            <?php endif; ?>
        </div>

        <div class="search-header">
            <h1>Keputusan Carian: "<?= htmlspecialchars($keyword); ?>"</h1>
            <div class="search-stats">Ditemukan <?= ($total_barang + $total_servis); ?> item</div>
        </div>

        <div class="products">
            <h2>Produk Dijumpai</h2>
            <div class="product-grid">
                <?php if($total_barang > 0): ?>
                    <?php while($barang = mysqli_fetch_assoc($result_barang)): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if(!empty($barang['gambar_barang'])): ?>
                                <img src="../barang/gambar/<?= $barang['gambar_barang']; ?>" alt="<?= $barang['nama_barang']; ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/250x180?text=Produk" alt="Placeholder">
                            <?php endif; ?>
                        </div>
                        <div class="product-category"><?= $barang['nama_kategori'] ?? 'PRODUK'; ?></div>
                        <h3><?= $barang['nama_barang']; ?></h3>
                        <p class="price">RM<?= number_format($barang['harga'], 2); ?></p>
                        <a href="../barang/detail_barang.php?id=<?= $barang['id_barang']; ?>"><button>Lihat Detail</button></a>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">Tiada produk dijumpai untuk "<?= htmlspecialchars($keyword); ?>"</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="products">
            <h2>Servis Dijumpai</h2>
            <div class="product-grid">
                <?php if($total_servis > 0): ?>
                    <?php while($servis = mysqli_fetch_assoc($result_servis)): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if(!empty($servis['gambar_servis'])): ?>
                                <img src="../servis/gambar/<?= $servis['gambar_servis']; ?>" alt="<?= $servis['nama_servis']; ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/250x180?text=Servis" alt="Placeholder">
                            <?php endif; ?>
                        </div>
                        <div class="product-category">SERVIS</div>
                        <h3><?= $servis['nama_servis']; ?></h3>
                        <p class="price">RM<?= number_format($servis['harga'], 2); ?></p>
                        <a href="../servis/detail_servis.php?id=<?= $servis['id_servis']; ?>"><button>Lihat Detail</button></a>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">Tiada servis dijumpai untuk "<?= htmlspecialchars($keyword); ?>"</div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

</body>
</html>