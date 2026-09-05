<?php
include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];
$id_barang_diminta = $_GET['id'];

// Get product details
$sql = "SELECT * FROM barang WHERE id_barang = '$id_barang_diminta'";
$result = mysqli_query($conn, $sql);
$barang_diminta = mysqli_fetch_assoc($result);

// Get user's own products (to offer)
$sql_my_barang = "SELECT * FROM barang WHERE id_pengguna = '$id_pengguna' AND id_barang != '$id_barang_diminta' ORDER BY id_barang DESC";
$result_my_barang = mysqli_query($conn, $sql_my_barang);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tawaran Pertukaran</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .modal-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border: 1px solid #eef2f8;
            border-radius: 0px;
        }
        
        .modal-container h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 5px solid #6c3cff;
            padding-left: 1rem;
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
        
        .btn-cancel {
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
        
        .btn-cancel:hover {
            background: #ccc;
        }
        
        .note {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.3rem;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="modal-container">
    <h2>Tawarkan Pertukaran</h2>
    
    <?php if(!$barang_diminta): ?>
        <div class="alert-danger">Barang tidak ditemui.</div>
        <button class="btn-cancel" onclick="window.close()">Tutup</button>
        <?php exit; ?>
    <?php endif; ?>
    
    <!-- Product yang diminta -->
    <div class="product-preview">
        <?php if(!empty($barang_diminta['gambar_barang'])): ?>
            <img src="../barang/gambar/<?= htmlspecialchars($barang_diminta['gambar_barang']); ?>" alt="">
        <?php else: ?>
            <img src="https://via.placeholder.com/80" alt="">
        <?php endif; ?>
        <div class="info">
            <h4><?= htmlspecialchars($barang_diminta['nama_barang']); ?></h4>
            <div class="price">RM<?= number_format($barang_diminta['harga'], 2); ?></div>
        </div>
    </div>
    
    <form action="proses_tawar_tukar.php" method="POST" id="formTawar">
        <input type="hidden" name="id_barang_diminta" value="<?= $id_barang_diminta; ?>">
        
        <div class="form-group">
            <label>Pilih barang anda untuk ditawarkan</label>
            <select name="id_barang_ditawar" required>
                <option value="">-- Pilih Barang --</option>
                <?php while($row = mysqli_fetch_assoc($result_my_barang)): ?>
                    <option value="<?= $row['id_barang']; ?>">
                        <?= htmlspecialchars($row['nama_barang']); ?> - RM<?= number_format($row['harga'], 2); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <?php if(mysqli_num_rows($result_my_barang) == 0): ?>
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
        
        <button type="submit" class="btn-submit" <?= (mysqli_num_rows($result_my_barang) == 0) ? 'disabled' : ''; ?>>
            Hantar Tawaran Pertukaran
        </button>
        
        <button type="button" class="btn-cancel" onclick="window.close()">Batal</button>
    </form>
</div>

</body>
</html>