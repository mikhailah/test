<?php

include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];
$id_barang = $_GET['id'];

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

// Product details
$sql = "
    SELECT barang.*, pengguna.nama_penuh, kategori_barang.nama_kategori
    FROM barang
    LEFT JOIN pengguna ON barang.id_pengguna = pengguna.id_pengguna
    LEFT JOIN kategori_barang ON barang.id_kategori = kategori_barang.id_kategori
    WHERE id_barang = '$id_barang'
";
$result = mysqli_query($conn, $sql);
$barang = mysqli_fetch_assoc($result);

// Check if user is the owner of this product
$is_owner = ($barang['id_pengguna'] == $id_pengguna);

// Check if user has items to trade (only if not owner)
$has_items = false;
if(!$is_owner) {
    $sql_my_items = "SELECT COUNT(*) AS total FROM barang WHERE id_pengguna = '$id_pengguna' AND id_barang != '$id_barang'";
    $result_my_items = mysqli_query($conn, $sql_my_items);
    $my_items = mysqli_fetch_assoc($result_my_items);
    $has_items = ($my_items['total'] > 0);
}

// Get user's products for trade dropdown (only if not owner)
$result_barang_saya = null;
if(!$is_owner) {
    $sql_barang_saya = "SELECT * FROM barang WHERE id_pengguna = '$id_pengguna' AND id_barang != '$id_barang' ORDER BY id_barang DESC";
    $result_barang_saya = mysqli_query($conn, $sql_barang_saya);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($barang['nama_barang']); ?> | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .main-content {
            padding: 2rem;
            background: #f8f9fc;
            min-height: 100vh;
        }
        
        .detail-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 0px;
            padding: 2rem;
            border: 1px solid #eef2f8;
        }
        
        .product-image {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: contain;
            border-radius: 0px;
            margin-bottom: 1.5rem;
            background: #f5f5f5;
        }
        
        .product-title {
            font-size: 1.8rem;
            color: #1a1a2e;
            margin-bottom: 0.5rem;
        }
        
        .product-price {
            font-size: 1.8rem;
            font-weight: 700;
            color: #6c3cff;
            margin-bottom: 1rem;
        }
        
        .product-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eef2f8;
        }
        
        .product-description {
            line-height: 1.6;
            margin: 1.5rem 0;
        }
        
        /* Button styles */
        .btn-group {
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        
        .btn-chat {
            background: linear-gradient(135deg, #6c3cff, #4f7cff);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 0px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-chat:hover {
            opacity: 0.9;
        }
        
        .btn-back {
            background: #eef2f8;
            color: #333;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 0px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-back:hover {
            background: #ddd;
        }
        
        .btn-tukar {
            background: linear-gradient(135deg, #6c3cff, #4f7cff);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 0px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-tukar:hover {
            opacity: 0.9;
        }
        
        .btn-tukar:disabled {
            background: #d1d5db;
            color: #6b7280;
            cursor: not-allowed;
        }
        
        .btn-tukar:disabled:hover {
            background: #d1d5db;
            opacity: 1;
        }
        
        .btn-profile {
            background: #10b981;
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 0px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-profile:hover {
            background: #059669;
        }
        
        /* Modal Overlay for Trade */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            max-width: 600px;
            width: 95%;
            padding: 2rem;
            border: 1px solid #eef2f8;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-content h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 5px solid #6c3cff;
            padding-left: 1rem;
        }
        
        .modal-close {
            float: right;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #888;
        }
        
        .modal-close:hover {
            color: #333;
        }
        
        .product-preview {
            background: #f8f9fc;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .product-preview img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background: white;
        }
        
        .product-preview .info {
            flex: 1;
        }
        
        .product-preview .info h4 {
            font-size: 1rem;
            margin-bottom: 0.3rem;
        }
        
        .product-preview .info .price {
            color: #6c3cff;
            font-weight: 700;
        }
        
        .form-group {
            margin-bottom: 1.2rem;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .form-group select,
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 0px;
            font-size: 0.9rem;
        }
        
        .form-group select:focus,
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #6c3cff;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #6c3cff, #4f7cff);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 0px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
        }
        
        .btn-submit:hover {
            opacity: 0.9;
        }
        
        .btn-cancel-modal {
            background: #e2e2e2;
            color: #333;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 0px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 0.5rem;
        }
        
        .btn-cancel-modal:hover {
            background: #ccc;
        }
        
        .note {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.3rem;
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            .product-title {
                font-size: 1.4rem;
            }
            .btn-group {
                flex-direction: column;
            }
            .btn-group a, .btn-group button {
                width: 100%;
                text-align: center;
            }
            .modal-content {
                padding: 1.5rem;
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
            <li class="active"><a href="../barang/senarai_barang.php">Barang</a></li>
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
        
        <div class="detail-container">
            <?php if(!empty($barang['gambar_barang'])): ?>
                <img src="gambar/<?= htmlspecialchars($barang['gambar_barang']); ?>" class="product-image">
            <?php else: ?>
                <img src="https://via.placeholder.com/800x400?text=Tiada+Gambar" class="product-image">
            <?php endif; ?>
            
            <h1 class="product-title"><?= htmlspecialchars($barang['nama_barang']); ?></h1>
            <div class="product-price">RM<?= number_format($barang['harga'], 2); ?></div>
            
            <div class="product-meta">
                Kategori: <?= htmlspecialchars($barang['nama_kategori'] ?? 'Tiada Kategori'); ?><br>
                Penjual: <?= htmlspecialchars($barang['nama_penuh']); ?>
            </div>
            
            <div class="product-description">
                <?= nl2br(htmlspecialchars($barang['penerangan'])); ?>
            </div>
            
            <!-- Buttons -->
            <div class="btn-group">
                <?php if($is_owner): ?>
                    <!-- Jika pemilik barang - hanya nampak Kembali sahaja -->
                    <a href="senarai_barang.php" class="btn-back">
                        Kembali
                    </a>
                <?php else: ?>
                    <!-- Jika bukan pemilik -->
                    <a href="../mesej/mesej_baru.php?id=<?= $barang['id_pengguna']; ?>" class="btn-chat">
                        Hubungi Penjual
                    </a>
                    
                    <a href="profil_penjual.php?id=<?= $barang['id_pengguna']; ?>" class="btn-profile">
                        Lihat Profil Penjual
                    </a>
                    
                    <?php if($has_items): ?>
                        <button class="btn-tukar" onclick="openTradeModal(<?= $barang['id_barang']; ?>)">
                            Tukar Barang
                        </button>
                    <?php else: ?>
                        <button class="btn-tukar" disabled title="Anda tiada barang untuk ditukar. Sila tambah barang terlebih dahulu.">
                            Tukar Barang
                            <small style="display:block;font-size:0.6rem;">(Tiada barang untuk ditukar)</small>
                        </button>
                    <?php endif; ?>
                    
                    <a href="senarai_barang.php" class="btn-back">
                        Kembali
                    </a>
                <?php endif; ?>
            </div>
            
        </div>
        
    </div>
    
</div>

<!-- Modal Tukar Barang - hanya untuk bukan pemilik -->
<?php if(!$is_owner): ?>
<div class="modal-overlay" id="tradeModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeTradeModal()">×</button>
        <h2>Tawarkan Pertukaran</h2>
        
        <div class="product-preview">
            <?php if(!empty($barang['gambar_barang'])): ?>
                <img src="gambar/<?= htmlspecialchars($barang['gambar_barang']); ?>" alt="">
            <?php else: ?>
                <img src="https://via.placeholder.com/80" alt="">
            <?php endif; ?>
            <div class="info">
                <h4><?= htmlspecialchars($barang['nama_barang']); ?></h4>
                <div class="price">RM<?= number_format($barang['harga'], 2); ?></div>
            </div>
        </div>
        
        <form action="../tukar/proses_tawar_tukar.php" method="POST" id="formTawar">
            <input type="hidden" name="id_barang_diminta" value="<?= $id_barang; ?>">
            
            <div class="form-group">
                <label>Pilih barang anda untuk ditawarkan</label>
                <select name="id_barang_ditawar" required>
                    <option value="">-- Pilih Barang --</option>
                    <?php if($result_barang_saya): ?>
                        <?php while($row = mysqli_fetch_assoc($result_barang_saya)): ?>
                            <option value="<?= $row['id_barang']; ?>">
                                <?= htmlspecialchars($row['nama_barang']); ?> - RM<?= number_format($row['harga'], 2); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
                <?php if(!$has_items): ?>
                    <div class="note" style="color:#dc2626;">Anda tiada barang untuk ditawarkan. Sila tambah barang terlebih dahulu.</div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Tambah Wang (RM) - Jika nilai tidak sama</label>
                <input type="number" step="0.01" name="tambah_wang" value="0" min="0">
                <div class="note">Contoh: Jika barang anda RM350 dan barang diminta RM800, tambah RM450.</div>
            </div>
            
            <div class="form-group">
                <label>Nota kepada pemilik</label>
                <textarea name="mesej" rows="4" placeholder="Terangkan keadaan barang anda atau sebab pertukaran..."></textarea>
            </div>
            
            <button type="submit" class="btn-submit" <?= (!$has_items) ? 'disabled' : ''; ?>>
                Hantar Tawaran Pertukaran
            </button>
            
            <button type="button" class="btn-cancel-modal" onclick="closeTradeModal()">Batal</button>
        </form>
    </div>
</div>

<script>
    function openTradeModal(id_barang) {
        document.getElementById('tradeModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeTradeModal() {
        document.getElementById('tradeModal').classList.remove('active');
        document.body.style.overflow = '';
    }
    
    document.getElementById('tradeModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeTradeModal();
        }
    });
</script>
<?php endif; ?>

</body>
</html>