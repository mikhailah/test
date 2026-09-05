<?php

include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];

// Get user profile data
$sql = "
    SELECT *
    FROM pengguna
    WHERE id_pengguna = '$id_pengguna'
";
$result = mysqli_query($conn, $sql);
$pengguna = mysqli_fetch_assoc($result);

// Get notification count for sidebar badge
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

// Get user's products (barang)
$sql_barang = "
    SELECT * FROM barang
    WHERE id_pengguna = '$id_pengguna'
    ORDER BY id_barang DESC
";
$result_barang = mysqli_query($conn, $sql_barang);
$total_barang = mysqli_num_rows($result_barang);

// Get user's services (servis)
$sql_servis = "
    SELECT * FROM servis
    WHERE id_pengguna = '$id_pengguna'
    ORDER BY id_servis DESC
";
$result_servis = mysqli_query($conn, $sql_servis);
$total_servis = mysqli_num_rows($result_servis);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil Saya | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        /* Profile page styles - maintain square corners like index, but avatar bulat */
        
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Profile header */
        .profile-header {
            background: white;
            border-radius: 0px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            border: 1px solid #eef2f8;
            text-align: center;
        }
        
        /* Avatar bulat (rounded circle) */
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #6c3cff;
            margin-bottom: 1rem;
        }
        
        .profile-header h2 {
            font-size: 1.5rem;
            color: #1a1a2e;
            margin-bottom: 0.5rem;
        }
        
        .profile-email {
            color: #6c3cff;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        /* Stats row */
        .stats-row {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #6c3cff;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: #666;
        }
        
        /* Action buttons - square corners */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }
        
        .btn-sell {
            background: linear-gradient(135deg, #6c3cff, #4f7cff);
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 0px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-sell:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108,60,255,0.3);
        }
        
        .btn-service {
            background: white;
            color: #6c3cff;
            border: 2px solid #6c3cff;
            padding: 0.7rem 1.5rem;
            border-radius: 0px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-service:hover {
            background: #6c3cff;
            color: white;
        }
        
        /* Edit profile form - square corners */
        .edit-profile-section {
            background: white;
            border-radius: 0px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid #eef2f8;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #eef2f8;
            color: #1a1a2e;
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
        
        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 0px;
            font-size: 0.9rem;
            transition: border 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #6c3cff;
        }
        
        .btn-update {
            background: linear-gradient(135deg, #6c3cff, #4f7cff);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 0px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        
        .btn-update:hover {
            opacity: 0.9;
        }
        
        /* Listings section - square corners */
        .listings-section {
            background: white;
            border-radius: 0px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid #eef2f8;
        }
        
        .listings-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #eef2f8;
        }
        
        .tab-btn {
            background: none;
            border: none;
            padding: 0.7rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            color: #666;
            transition: all 0.2s;
        }
        
        .tab-btn.active {
            color: #6c3cff;
            border-bottom: 2px solid #6c3cff;
            margin-bottom: -2px;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Listings grid - gambar full tanpa crop */
        .listings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.2rem;
        }
        
        .listing-card {
            background: #f8f9fc;
            border-radius: 0px;
            overflow: hidden;
            border: 1px solid #eef2f8;
            transition: transform 0.2s;
        }
        
        .listing-card:hover {
            transform: translateY(-3px);
        }
        
        /* Gambar barang/servis FULL tanpa crop - object-fit contain */
        .listing-card img {
            width: 100%;
            height: 160px;
            object-fit: contain;
            background-color: #f5f5f5;
            padding: 8px;
        }
        
        .listing-info {
            padding: 0.8rem;
        }
        
        .listing-info h4 {
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
            color: #1a1a2e;
        }
        
        .listing-price {
            color: #6c3cff;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .listing-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .listing-actions button {
            flex: 1;
            text-align: center;
            padding: 0.4rem;
            border-radius: 0px;
            font-size: 0.75rem;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }
        
        .btn-edit {
            background: #eef2ff;
            color: #6c3cff;
        }
        
        .btn-edit:hover {
            background: #6c3cff;
            color: white;
        }
        
        .btn-delete {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .btn-delete:hover {
            background: #dc2626;
            color: white;
        }
        
        .empty-listings {
            text-align: center;
            padding: 2rem;
            color: #888;
        }
        
        /* MODAL STYLES for Edit */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            border-radius: 0px;
            width: 90%;
            max-width: 500px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .modal-content h3 {
            margin-bottom: 1rem;
            color: #1a1a2e;
        }
        
        .modal-content .form-group {
            margin-bottom: 1rem;
        }
        
        .modal-content input, .modal-content textarea {
            width: 100%;
            padding: 0.7rem;
            border: 1px solid #ddd;
        }
        
        .modal-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }
        
        .modal-buttons button {
            padding: 0.5rem 1rem;
            cursor: pointer;
        }
        
        .btn-save {
            background: #6c3cff;
            color: white;
            border: none;
        }
        
        .btn-cancel {
            background: #e2e2e2;
            border: none;
        }
        
        /* Alert popup */
        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 0.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 0.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            .listings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

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
            <li class="active">
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
        
        <div class="profile-container">
            
            <!-- PROFILE HEADER -->
            <div class="profile-header">
                <?php if(!empty($pengguna['gambar_profil'])): ?>
                    <img src="gambar/<?= $pengguna['gambar_profil']; ?>" class="profile-avatar">
                <?php else: ?>
                    <img src="https://via.placeholder.com/120" class="profile-avatar">
                <?php endif; ?>
                
                <h2><?= htmlspecialchars($pengguna['nama_penuh']); ?></h2>
                <div class="profile-email"><?= htmlspecialchars($pengguna['email'] ?? ''); ?></div>
                
                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-number"><?= $total_barang; ?></div>
                        <div class="stat-label">Barang</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?= $total_servis; ?></div>
                        <div class="stat-label">Servis</div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="../barang/tambah_barang.php" class="btn-sell">+ Jual Barang</a>
                    <a href="../servis/tambah_servis.php" class="btn-service">+ Iklan Servis</a>
                </div>
            </div>
            
            <!-- EDIT PROFILE FORM -->
            <div class="edit-profile-section">
                <h3 class="section-title">Kemaskini Profil</h3>
                <form action="kemaskini_profil.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nama Penuh</label>
                        <input type="text" name="nama_penuh" value="<?= htmlspecialchars($pengguna['nama_penuh']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>No Telefon</label>
                        <input type="text" name="no_telefon" value="<?= htmlspecialchars($pengguna['no_telefon']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Gambar Profil</label>
                        <input type="file" name="gambar_profil" accept="image/*">
                    </div>
                    <button type="submit" class="btn-update">Kemaskini Profil</button>
                </form>
            </div>
            
            <!-- USER LISTINGS SECTION -->
            <div class="listings-section">
                <h3 class="section-title">Barang & Servis Saya</h3>
                
                <div class="listings-tabs">
                    <button class="tab-btn active" onclick="showTab('barang')">Barang Saya (<?= $total_barang; ?>)</button>
                    <button class="tab-btn" onclick="showTab('servis')">Servis Saya (<?= $total_servis; ?>)</button>
                </div>
                
                <!-- Barang Tab -->
                <div id="tab-barang" class="tab-content active">
                    <div class="listings-grid" id="barangGrid">
                        <?php if(mysqli_num_rows($result_barang) > 0): ?>
                            <?php while($barang = mysqli_fetch_assoc($result_barang)): ?>
                                <div class="listing-card" data-id="<?= $barang['id_barang']; ?>" data-type="barang">
                                    <?php if(!empty($barang['gambar_barang'])): ?>
                                        <img src="../barang/gambar/<?= htmlspecialchars($barang['gambar_barang']); ?>" alt="<?= htmlspecialchars($barang['nama_barang']); ?>">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/240x160?text=Barang" alt="Placeholder">
                                    <?php endif; ?>
                                    <div class="listing-info">
                                        <h4><?= htmlspecialchars($barang['nama_barang']); ?></h4>
                                        <div class="listing-price">RM<?= number_format($barang['harga'], 2); ?></div>
                                        <div class="listing-actions">
                                            <button class="btn-edit" onclick="editItem('barang', <?= $barang['id_barang']; ?>)">Edit</button>
                                            <button class="btn-delete" onclick="deleteItem('barang', <?= $barang['id_barang']; ?>)">Padam</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-listings">Tiada barang. Klik "Jual Barang" untuk mula menjual.</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Servis Tab -->
                <div id="tab-servis" class="tab-content">
                    <div class="listings-grid" id="servisGrid">
                        <?php if(mysqli_num_rows($result_servis) > 0): ?>
                            <?php while($servis = mysqli_fetch_assoc($result_servis)): ?>
                                <div class="listing-card" data-id="<?= $servis['id_servis']; ?>" data-type="servis">
                                    <?php if(!empty($servis['gambar_servis'])): ?>
                                        <img src="../servis/gambar/<?= htmlspecialchars($servis['gambar_servis']); ?>" alt="<?= htmlspecialchars($servis['nama_servis']); ?>">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/240x160?text=Servis" alt="Placeholder">
                                    <?php endif; ?>
                                    <div class="listing-info">
                                        <h4><?= htmlspecialchars($servis['nama_servis']); ?></h4>
                                        <div class="listing-price">RM<?= number_format($servis['harga'], 2); ?></div>
                                        <div class="listing-actions">
                                            <button class="btn-edit" onclick="editItem('servis', <?= $servis['id_servis']; ?>)">Edit</button>
                                            <button class="btn-delete" onclick="deleteItem('servis', <?= $servis['id_servis']; ?>)">Padam</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-listings">Tiada servis. Klik "Iklan Servis" untuk mula menawarkan servis.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Modal for Edit -->
<div id="editModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <h3 id="modalTitle">Edit Barang</h3>
        <form id="editForm">
            <input type="hidden" id="editId" name="id">
            <input type="hidden" id="editType" name="type">
            <div class="form-group">
                <label>Nama</label>
                <input type="text" id="editNama" name="nama" required>
            </div>
            <div class="form-group">
                <label>Harga (RM)</label>
                <input type="number" step="0.01" id="editHarga" name="harga" required>
            </div>
            <div class="form-group" id="editDescGroup" style="display: none;">
                <label>Penerangan</label>
                <textarea id="editPenerangan" name="penerangan" rows="3"></textarea>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function showTab(tab) {
        document.getElementById('tab-barang').classList.remove('active');
        document.getElementById('tab-servis').classList.remove('active');
        
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        if(tab === 'barang') {
            document.getElementById('tab-barang').classList.add('active');
            document.querySelectorAll('.tab-btn')[0].classList.add('active');
        } else {
            document.getElementById('tab-servis').classList.add('active');
            document.querySelectorAll('.tab-btn')[1].classList.add('active');
        }
    }
    
    // Edit item function - support penerangan for BOTH barang AND servis
    function editItem(type, id) {
        console.log('Editing:', type, id);
        
        fetch(`get_item.php?type=${type}&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('editId').value = id;
                    document.getElementById('editType').value = type;
                    document.getElementById('editNama').value = data.nama;
                    document.getElementById('editHarga').value = data.harga;
                    
                    // Show penerangan field for BOTH barang and servis
                    document.getElementById('modalTitle').innerText = (type === 'servis') ? 'Edit Servis' : 'Edit Barang';
                    document.getElementById('editDescGroup').style.display = 'block';
                    document.getElementById('editPenerangan').value = data.penerangan || '';
                    
                    document.getElementById('editModal').style.display = 'flex';
                } else {
                    alert('Gagal memuat data: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Ralat: ' + error);
            });
    }
    
    // Close modal
    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    
    // Handle edit form submission - support penerangan for BOTH
    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('editId').value;
        const type = document.getElementById('editType').value;
        const nama = document.getElementById('editNama').value;
        const harga = document.getElementById('editHarga').value;
        const penerangan = document.getElementById('editPenerangan').value;
        
        const formData = new FormData();
        formData.append('id', id);
        formData.append('type', type);
        formData.append('nama', nama);
        formData.append('harga', harga);
        formData.append('penerangan', penerangan);  // Send for BOTH
        
        fetch('update_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Gagal: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Update error:', error);
            alert('Ralat: ' + error);
        });
    });
    
    // Delete item with confirmation
    function deleteItem(type, id) {
        if(confirm('Anda pasti mahu padam ' + (type === 'barang' ? 'barang' : 'servis') + ' ini?')) {
            fetch(`delete_item.php?type=${type}&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert(data.message || 'Berjaya dipadam!');
                        location.reload();
                    } else {
                        alert('Gagal memadam: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    alert('Ralat: ' + error);
                });
        }
    }
</script>

</body>
</html>