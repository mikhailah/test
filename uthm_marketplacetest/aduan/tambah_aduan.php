<?php
include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];

// Get notification counts
$sql_notifikasi = "SELECT COUNT(*) AS jumlah FROM mesej WHERE id_penerima = '$id_pengguna' AND dibaca = 'tidak'";
$result_notifikasi = mysqli_query($conn, $sql_notifikasi);
$data_notifikasi = mysqli_fetch_assoc($result_notifikasi);
$jumlah_notifikasi = $data_notifikasi['jumlah'];

$sql_notif_trade = "SELECT COUNT(*) AS jumlah FROM notifikasi WHERE id_penerima = '$id_pengguna' AND dibaca = 'tidak'";
$result_notif_trade = mysqli_query($conn, $sql_notif_trade);
$data_notif_trade = mysqli_fetch_assoc($result_notif_trade);
$jumlah_notif_trade = $data_notif_trade['jumlah'];

// ADUAN NOTIFICATION COUNT
$sql_notif_aduan = "SELECT COUNT(*) AS jumlah FROM aduan_chat 
                    WHERE id_aduan IN (SELECT id_aduan FROM aduan WHERE id_pengguna = '$id_pengguna')
                    AND id_pengguna != '$id_pengguna' 
                    AND status = 'belum_dibaca'";
$result_notif_aduan = mysqli_query($conn, $sql_notif_aduan);
$data_notif_aduan = mysqli_fetch_assoc($result_notif_aduan);
$jumlah_notif_aduan = $data_notif_aduan['jumlah'];

// Get all users
$sql_semua_pengguna = "SELECT id_pengguna, nama_penuh, emel, gambar_profil FROM pengguna WHERE id_pengguna != '$id_pengguna' ORDER BY nama_penuh ASC";
$result_semua_pengguna = mysqli_query($conn, $sql_semua_pengguna);

// Get all barang with seller name
$sql_semua_barang = "
    SELECT b.*, p.nama_penuh AS nama_penjual 
    FROM barang b 
    LEFT JOIN pengguna p ON b.id_pengguna = p.id_pengguna 
    ORDER BY b.nama_barang ASC";
$result_semua_barang = mysqli_query($conn, $sql_semua_barang);

// Get all servis with seller name
$sql_semua_servis = "
    SELECT s.*, p.nama_penuh AS nama_penjual 
    FROM servis s 
    LEFT JOIN pengguna p ON s.id_pengguna = p.id_pengguna 
    ORDER BY s.nama_servis ASC";
$result_semua_servis = mysqli_query($conn, $sql_semua_servis);

// Get user's products for reference
$sql_barang = "SELECT id_barang, nama_barang, harga, gambar_barang FROM barang WHERE id_pengguna = '$id_pengguna' ORDER BY nama_barang ASC";
$result_barang = mysqli_query($conn, $sql_barang);

// Get user's services for reference
$sql_servis = "SELECT id_servis, nama_servis, harga, gambar_servis FROM servis WHERE id_pengguna = '$id_pengguna' ORDER BY nama_servis ASC";
$result_servis = mysqli_query($conn, $sql_servis);

// Store data in arrays for JavaScript
$all_users = [];
while($u = mysqli_fetch_assoc($result_semua_pengguna)) {
    $all_users[] = $u;
}
mysqli_data_seek($result_semua_pengguna, 0);

$all_barang = [];
while($b = mysqli_fetch_assoc($result_semua_barang)) {
    $all_barang[] = $b;
}
mysqli_data_seek($result_semua_barang, 0);

$all_servis = [];
while($s = mysqli_fetch_assoc($result_semua_servis)) {
    $all_servis[] = $s;
}
mysqli_data_seek($result_semua_servis, 0);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buat Aduan | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .main-content { padding: 2rem; background: #f8f9fc; min-height: 100vh; }
        .form-container { max-width: 700px; margin: 0 auto; background: white; padding: 2rem; border: 1px solid #eef2f8; }
        .form-container h1 { font-size: 1.8rem; margin-bottom: 1.5rem; border-left: 5px solid #6c3cff; padding-left: 1rem; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; font-weight: 600; color: #333; margin-bottom: 0.5rem; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 0.8rem 1rem; border: 1px solid #ddd; border-radius: 0px; font-size: 0.9rem;
            box-sizing: border-box;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: #6c3cff;
        }
        .btn-submit { background: linear-gradient(135deg, #6c3cff, #4f7cff); color: white; border: none; padding: 0.8rem 2rem; border-radius: 0px; font-weight: 600; cursor: pointer; width: 100%; font-size: 1rem; }
        .btn-submit:hover { opacity: 0.9; }
        .note { font-size: 0.85rem; color: #666; margin-top: 0.3rem; }
        .ref-group { display: none; }
        
        /* Searchable select styles */
        .searchable-select {
            position: relative;
            width: 100%;
        }
        
        .searchable-select input[type="text"] {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 0px;
            font-size: 0.9rem;
            cursor: text;
            box-sizing: border-box;
        }
        
        .searchable-select input[type="text"]:focus {
            outline: none;
            border-color: #6c3cff;
        }
        
        .searchable-select .options-list {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 250px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            z-index: 1000;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .searchable-select .options-list.active {
            display: block;
        }
        
        .searchable-select .options-list li {
            padding: 0.6rem 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #f5f5f5;
            transition: background 0.2s;
        }
        
        .searchable-select .options-list li:hover {
            background: #f0eaff;
        }
        
        .searchable-select .options-list li .avatar-sm {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }
        
        .searchable-select .options-list li .avatar-sm-placeholder {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #6c3cff;
            flex-shrink: 0;
            font-size: 0.9rem;
        }
        
        .searchable-select .options-list li .item-img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            background: #f5f5f5;
            border-radius: 4px;
            flex-shrink: 0;
        }
        
        .searchable-select .options-list li .info {
            flex: 1;
        }
        
        .searchable-select .options-list li .info .name {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .searchable-select .options-list li .info .sub {
            font-size: 0.75rem;
            color: #888;
        }
        
        .searchable-select .options-list li .info .price {
            font-size: 0.8rem;
            color: #6c3cff;
            font-weight: 600;
        }
        
        .searchable-select .options-list li .info .seller {
            font-size: 0.7rem;
            color: #666;
        }
        
        .selected-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.5rem 0.8rem;
            background: #f8f9fc;
            border: 1px solid #eef2f8;
            margin-top: 0.3rem;
        }
        
        .selected-item img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .selected-item .item-img {
            width: 50px;
            height: 50px;
            object-fit: contain;
            background: #f5f5f5;
            border-radius: 4px;
        }
        
        .selected-item .info { flex: 1; }
        .selected-item .info .name { font-weight: 600; font-size: 0.9rem; }
        .selected-item .info .sub { font-size: 0.8rem; color: #888; }
        .selected-item .info .price { color: #6c3cff; font-weight: 600; }
        .selected-item .info .seller { font-size: 0.75rem; color: #666; }
        .selected-item .remove-btn {
            background: #fee2e2;
            color: #dc2626;
            border: none;
            padding: 0.2rem 0.6rem;
            cursor: pointer;
            font-size: 0.8rem;
            border-radius: 4px;
        }
        .selected-item .remove-btn:hover {
            background: #fecaca;
        }
        
        .lampiran-wrapper { border: 1px solid #ddd; padding: 1rem; }
        .lampiran-item { display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem; }
        .lampiran-item input[type="file"] { flex: 1; padding: 0.5rem; border: 1px solid #ddd; }
        .lampiran-item .btn-remove { background: #dc2626; color: white; border: none; padding: 0.3rem 0.8rem; cursor: pointer; font-weight: 600; border-radius: 4px; }
        .lampiran-item .btn-remove:hover { background: #b91c1c; }
        .btn-add-lampiran { background: #f3f4f6; border: 1px solid #ddd; padding: 0.5rem 1rem; cursor: pointer; margin-top: 0.5rem; font-weight: 600; border-radius: 4px; }
        .btn-add-lampiran:hover { background: #e5e7eb; }
        
        @media (max-width: 768px) { .main-content { padding: 1rem; } .form-container { padding: 1.5rem; } }
        
        .badge {
            display: inline-block;
            background: #ef4444;
            color: white;
            font-size: 0.7rem;
            padding: 0.1rem 0.5rem;
            border-radius: 50%;
            margin-left: 5px;
        }
        
        /* Fix for click issues */
        .searchable-select .options-list li {
            pointer-events: auto !important;
            cursor: pointer !important;
        }
        
        .searchable-select .options-list li * {
            pointer-events: none !important;
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
            <li><a href="../tukar/senarai_tawaran.php">Tawaran Pertukaran</a></li>
            <li><a href="../mesej/chat_senarai.php">Mesej <?php if($jumlah_notifikasi > 0): ?><span class="badge"><?= $jumlah_notifikasi; ?></span><?php endif; ?></a></li>
            <li><a href="../profil/profil.php">Profil</a></li>
            <li class="active">
                <a href="aduan_saya.php">
                    Aduan
                    <?php if($jumlah_notif_aduan > 0): ?>
                        <span class="badge"><?= $jumlah_notif_aduan; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li><a href="../auth/logout.php">Log Keluar</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="form-container">
            <h1>Buat Aduan</h1>
            <p style="color:#888;margin-bottom:1.5rem;">Laporkan sebarang masalah atau isu yang anda hadapi.</p>

            <form action="proses_aduan.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Kategori Aduan</label>
                    <select name="kategori" id="kategori" onchange="showRefField()" required>
                        <option value="">Pilih Kategori</option>
                        <option value="barang">Masalah Barang</option>
                        <option value="servis">Masalah Servis</option>
                        <option value="pengguna">Masalah Pengguna</option>
                        <option value="pembayaran">Masalah Pembayaran</option>
                        <option value="lain-lain">Lain-lain</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Pilih Pengguna Terlibat <span style="color:red;">*</span></label>
                    <div class="searchable-select" id="userSelectContainer">
                        <input type="text" id="userSearch" placeholder="Cari pengguna..." autocomplete="off">
                        <input type="hidden" name="id_pengguna_terlibat" id="selectedUserId" required>
                        <ul class="options-list" id="userOptions"></ul>
                        <div class="selected-item" id="selectedUserDisplay" style="display:none;">
                            <img class="avatar-sm" id="selectedUserAvatar" src="" alt="Avatar">
                            <div class="info">
                                <div class="name" id="selectedUserName"></div>
                                <div class="sub" id="selectedUserEmail"></div>
                            </div>
                            <button type="button" class="remove-btn" onclick="clearUserSelection()">X</button>
                        </div>
                    </div>
                    <div class="note">Cari dan pilih pengguna yang terlibat dalam aduan ini.</div>
                </div>

                <div class="form-group">
                    <label>Pilih Barang Terlibat (Pilihan)</label>
                    <div class="searchable-select" id="barangSelectContainer">
                        <input type="text" id="barangSearch" placeholder="Cari barang..." autocomplete="off">
                        <input type="hidden" name="id_barang_terlibat" id="selectedBarangId">
                        <ul class="options-list" id="barangOptions"></ul>
                        <div class="selected-item" id="selectedBarangDisplay" style="display:none;">
                            <img class="item-img" id="selectedBarangImage" src="" alt="Barang">
                            <div class="info">
                                <div class="name" id="selectedBarangName"></div>
                                <div class="price" id="selectedBarangPrice"></div>
                                <div class="seller" id="selectedBarangSeller"></div>
                            </div>
                            <button type="button" class="remove-btn" onclick="clearBarangSelection()">X</button>
                        </div>
                    </div>
                    <div class="note">Cari dan pilih barang yang berkaitan (jika ada).</div>
                </div>

                <div class="form-group">
                    <label>Pilih Servis Terlibat (Pilihan)</label>
                    <div class="searchable-select" id="servisSelectContainer">
                        <input type="text" id="servisSearch" placeholder="Cari servis..." autocomplete="off">
                        <input type="hidden" name="id_servis_terlibat" id="selectedServisId">
                        <ul class="options-list" id="servisOptions"></ul>
                        <div class="selected-item" id="selectedServisDisplay" style="display:none;">
                            <img class="item-img" id="selectedServisImage" src="" alt="Servis">
                            <div class="info">
                                <div class="name" id="selectedServisName"></div>
                                <div class="price" id="selectedServisPrice"></div>
                                <div class="seller" id="selectedServisSeller"></div>
                            </div>
                            <button type="button" class="remove-btn" onclick="clearServisSelection()">X</button>
                        </div>
                    </div>
                    <div class="note">Cari dan pilih servis yang berkaitan (jika ada).</div>
                </div>

                <div class="form-group ref-group" id="refGroup">
                    <label>Rujukan Barang/Servis Anda (Pilihan)</label>
                    <select name="id_referensi">
                        <option value="">-- Tiada Rujukan --</option>
                        <optgroup label="Barang Anda">
                            <?php while($row = mysqli_fetch_assoc($result_barang)): ?>
                                <option value="barang_<?= $row['id_barang']; ?>"><?= htmlspecialchars($row['nama_barang']); ?></option>
                            <?php endwhile; ?>
                        </optgroup>
                        <optgroup label="Servis Anda">
                            <?php while($row = mysqli_fetch_assoc($result_servis)): ?>
                                <option value="servis_<?= $row['id_servis']; ?>"><?= htmlspecialchars($row['nama_servis']); ?></option>
                            <?php endwhile; ?>
                        </optgroup>
                    </select>
                    <div class="note">Pilih barang/servis anda yang berkaitan dengan aduan ini.</div>
                </div>

                <div class="form-group">
                    <label>Tajuk Aduan</label>
                    <input type="text" name="tajuk" placeholder="Contoh: Barang tidak sampai" required>
                </div>

                <div class="form-group">
                    <label>Penerangan</label>
                    <textarea name="penerangan" rows="6" placeholder="Terangkan masalah anda secara terperinci..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Lampiran (Gambar/Dokumen)</label>
                    <div class="lampiran-wrapper">
                        <div id="lampiranContainer">
                            <div class="lampiran-item">
                                <input type="file" name="lampiran[]" accept="image/*,.pdf,.doc,.docx">
                                <button type="button" class="btn-remove" onclick="removeLampiran(this)">X</button>
                            </div>
                        </div>
                        <button type="button" class="btn-add-lampiran" onclick="addLampiranField()">+ Tambah Lampiran</button>
                    </div>
                    <div class="note">Anda boleh muat naik lebih daripada satu lampiran.</div>
                </div>

                <button type="submit" class="btn-submit">Hantar Aduan</button>
            </form>
        </div>
    </div>
</div>

<script>
    // ============================================================
    // DATA FROM PHP
    // ============================================================
    const allUsers = <?= json_encode($all_users); ?>;
    const allBarang = <?= json_encode($all_barang); ?>;
    const allServis = <?= json_encode($all_servis); ?>;

    // ============================================================
    // USER SEARCH - Using Event Delegation
    // ============================================================
    const userSearch = document.getElementById('userSearch');
    const userOptions = document.getElementById('userOptions');
    const selectedUserId = document.getElementById('selectedUserId');
    const selectedUserDisplay = document.getElementById('selectedUserDisplay');

    function renderUserOptions(filter = '') {
        const filtered = allUsers.filter(u => 
            u.nama_penuh.toLowerCase().includes(filter.toLowerCase()) ||
            u.emel.toLowerCase().includes(filter.toLowerCase())
        );
        
        if(filtered.length === 0) {
            userOptions.innerHTML = '<li style="color:#999;cursor:default;padding:0.8rem 1rem;">Tiada pengguna ditemui</li>';
            userOptions.classList.add('active');
            return;
        }
        
        let html = '';
        filtered.forEach(function(u) {
            const avatarHtml = (u.gambar_profil && u.gambar_profil !== 'default.png' && u.gambar_profil !== '') 
                ? `<img class="avatar-sm" src="../profil/gambar/${u.gambar_profil}" alt="Avatar" onerror="this.style.display=\'none\'">`
                : `<div class="avatar-sm-placeholder">${u.nama_penuh.charAt(0).toUpperCase()}</div>`;
            html += `
                <li data-id="${u.id_pengguna}" data-type="user">
                    ${avatarHtml}
                    <div class="info">
                        <div class="name">${u.nama_penuh}</div>
                        <div class="sub">${u.emel}</div>
                    </div>
                </li>
            `;
        });
        userOptions.innerHTML = html;
        userOptions.classList.add('active');
    }

    function selectUser(id) {
        const user = allUsers.find(u => u.id_pengguna == id); // FIXED: Loose comparison (==)
        if(!user) return;
        
        selectedUserId.value = id;
        const avatarImg = document.getElementById('selectedUserAvatar');
        if (user.gambar_profil && user.gambar_profil !== 'default.png' && user.gambar_profil !== '') {
            avatarImg.src = `../profil/gambar/${user.gambar_profil}`;
            avatarImg.style.display = 'block';
        } else {
            avatarImg.style.display = 'none';
        }
        document.getElementById('selectedUserName').textContent = user.nama_penuh;
        document.getElementById('selectedUserEmail').textContent = user.emel;
        selectedUserDisplay.style.display = 'flex';
        userSearch.value = user.nama_penuh;
        userOptions.classList.remove('active');
    }

    function clearUserSelection() {
        selectedUserId.value = '';
        selectedUserDisplay.style.display = 'none';
        userSearch.value = '';
        userSearch.focus();
        userOptions.classList.remove('active');
    }

    // Event delegation for user options
    userOptions.addEventListener('click', function(e) {
        const li = e.target.closest('li');
        if (li && li.dataset.id) {
            const id = li.dataset.id; // FIXED: Removed parseInt
            selectUser(id);
        }
    });

    userSearch.addEventListener('input', function() {
        renderUserOptions(this.value);
    });

    userSearch.addEventListener('focus', function() {
        renderUserOptions(this.value);
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if(!e.target.closest('#userSelectContainer')) {
            userOptions.classList.remove('active');
        }
    });

    // ============================================================
    // BARANG SEARCH - Using Event Delegation
    // ============================================================
    const barangSearch = document.getElementById('barangSearch');
    const barangOptions = document.getElementById('barangOptions');
    const selectedBarangId = document.getElementById('selectedBarangId');
    const selectedBarangDisplay = document.getElementById('selectedBarangDisplay');

    function renderBarangOptions(filter = '') {
        const filtered = allBarang.filter(b => 
            b.nama_barang.toLowerCase().includes(filter.toLowerCase()) ||
            (b.nama_penjual && b.nama_penjual.toLowerCase().includes(filter.toLowerCase()))
        );
        
        if(filtered.length === 0) {
            barangOptions.innerHTML = '<li style="color:#999;cursor:default;padding:0.8rem 1rem;">Tiada barang ditemui</li>';
            barangOptions.classList.add('active');
            return;
        }
        
        let html = '';
        filtered.forEach(function(b) {
            const imgHtml = b.gambar_barang 
                ? `<img class="item-img" src="../barang/gambar/${b.gambar_barang}" alt="Barang" onerror="this.style.display=\'none\'">`
                : `<div class="item-img" style="display:flex;align-items:center;justify-content:center;font-size:1.5rem;background:#f5f5f5;">📦</div>`;
            html += `
                <li data-id="${b.id_barang}" data-type="barang">
                    ${imgHtml}
                    <div class="info">
                        <div class="name">${b.nama_barang}</div>
                        <div class="price">RM${parseFloat(b.harga).toFixed(2)}</div>
                        <div class="seller">Penjual: ${b.nama_penjual || 'Unknown'}</div>
                    </div>
                </li>
            `;
        });
        barangOptions.innerHTML = html;
        barangOptions.classList.add('active');
    }

    function selectBarang(id) {
        const barang = allBarang.find(b => b.id_barang == id); // FIXED: Loose comparison (==)
        if(!barang) return;
        
        selectedBarangId.value = id;
        const img = document.getElementById('selectedBarangImage');
        if (barang.gambar_barang) {
            img.src = `../barang/gambar/${barang.gambar_barang}`;
            img.style.display = 'block';
        } else {
            img.style.display = 'none';
        }
        document.getElementById('selectedBarangName').textContent = barang.nama_barang;
        document.getElementById('selectedBarangPrice').textContent = `RM${parseFloat(barang.harga).toFixed(2)}`;
        document.getElementById('selectedBarangSeller').textContent = `Penjual: ${barang.nama_penjual || 'Unknown'}`;
        selectedBarangDisplay.style.display = 'flex';
        barangSearch.value = barang.nama_barang;
        barangOptions.classList.remove('active');
    }

    function clearBarangSelection() {
        selectedBarangId.value = '';
        selectedBarangDisplay.style.display = 'none';
        barangSearch.value = '';
        barangSearch.focus();
        barangOptions.classList.remove('active');
    }

    // Event delegation for barang options
    barangOptions.addEventListener('click', function(e) {
        const li = e.target.closest('li');
        if (li && li.dataset.id) {
            const id = li.dataset.id; // FIXED: Removed parseInt
            selectBarang(id);
        }
    });

    barangSearch.addEventListener('input', function() {
        renderBarangOptions(this.value);
    });

    barangSearch.addEventListener('focus', function() {
        renderBarangOptions(this.value);
    });

    document.addEventListener('click', function(e) {
        if(!e.target.closest('#barangSelectContainer')) {
            barangOptions.classList.remove('active');
        }
    });

    // ============================================================
    // SERVIS SEARCH - Using Event Delegation
    // ============================================================
    const servisSearch = document.getElementById('servisSearch');
    const servisOptions = document.getElementById('servisOptions');
    const selectedServisId = document.getElementById('selectedServisId');
    const selectedServisDisplay = document.getElementById('selectedServisDisplay');

    function renderServisOptions(filter = '') {
        const filtered = allServis.filter(s => 
            s.nama_servis.toLowerCase().includes(filter.toLowerCase()) ||
            (s.nama_penjual && s.nama_penjual.toLowerCase().includes(filter.toLowerCase()))
        );
        
        if(filtered.length === 0) {
            servisOptions.innerHTML = '<li style="color:#999;cursor:default;padding:0.8rem 1rem;">Tiada servis ditemui</li>';
            servisOptions.classList.add('active');
            return;
        }
        
        let html = '';
        filtered.forEach(function(s) {
            const imgHtml = s.gambar_servis 
                ? `<img class="item-img" src="../servis/gambar/${s.gambar_servis}" alt="Servis" onerror="this.style.display=\'none\'">`
                : `<div class="item-img" style="display:flex;align-items:center;justify-content:center;font-size:1.5rem;background:#f5f5f5;">🛠</div>`;
            html += `
                <li data-id="${s.id_servis}" data-type="servis">
                    ${imgHtml}
                    <div class="info">
                        <div class="name">${s.nama_servis}</div>
                        <div class="price">RM${parseFloat(s.harga).toFixed(2)}</div>
                        <div class="seller">Penyedia: ${s.nama_penjual || 'Unknown'}</div>
                    </div>
                </li>
            `;
        });
        servisOptions.innerHTML = html;
        servisOptions.classList.add('active');
    }

    function selectServis(id) {
        const servis = allServis.find(s => s.id_servis == id); // FIXED: Loose comparison (==)
        if(!servis) return;
        
        selectedServisId.value = id;
        const img = document.getElementById('selectedServisImage');
        if (servis.gambar_servis) {
            img.src = `../servis/gambar/${servis.gambar_servis}`;
            img.style.display = 'block';
        } else {
            img.style.display = 'none';
        }
        document.getElementById('selectedServisName').textContent = servis.nama_servis;
        document.getElementById('selectedServisPrice').textContent = `RM${parseFloat(servis.harga).toFixed(2)}`;
        document.getElementById('selectedServisSeller').textContent = `Penyedia: ${servis.nama_penjual || 'Unknown'}`;
        selectedServisDisplay.style.display = 'flex';
        servisSearch.value = servis.nama_servis;
        servisOptions.classList.remove('active');
    }

    function clearServisSelection() {
        selectedServisId.value = '';
        selectedServisDisplay.style.display = 'none';
        servisSearch.value = '';
        servisSearch.focus();
        servisOptions.classList.remove('active');
    }

    // Event delegation for servis options
    servisOptions.addEventListener('click', function(e) {
        const li = e.target.closest('li');
        if (li && li.dataset.id) {
            const id = li.dataset.id; // FIXED: Removed parseInt
            selectServis(id);
        }
    });

    servisSearch.addEventListener('input', function() {
        renderServisOptions(this.value);
    });

    servisSearch.addEventListener('focus', function() {
        renderServisOptions(this.value);
    });

    document.addEventListener('click', function(e) {
        if(!e.target.closest('#servisSelectContainer')) {
            servisOptions.classList.remove('active');
        }
    });

    // ============================================================
    // SHOW REF FIELD
    // ============================================================
    function showRefField() {
        var kategori = document.getElementById('kategori').value;
        var refGroup = document.getElementById('refGroup');
        if(kategori === 'barang' || kategori === 'servis' || kategori === 'pengguna') {
            refGroup.style.display = 'block';
        } else {
            refGroup.style.display = 'none';
        }
    }

    // ============================================================
    // LAMPIRAN FUNCTIONS
    // ============================================================
    function addLampiranField() {
        var container = document.getElementById('lampiranContainer');
        var div = document.createElement('div');
        div.className = 'lampiran-item';
        div.innerHTML = `
            <input type="file" name="lampiran[]" accept="image/*,.pdf,.doc,.docx">
            <button type="button" class="btn-remove" onclick="removeLampiran(this)">X</button>
        `;
        container.appendChild(div);
    }

    function removeLampiran(btn) {
        var item = btn.parentElement;
        var container = document.getElementById('lampiranContainer');
        if(container.children.length > 1) {
            item.remove();
        } else {
            alert('Perlu ada sekurang-kurangnya satu lampiran.');
        }
    }

    // Make functions globally accessible for inline onclick
    window.clearUserSelection = clearUserSelection;
    window.clearBarangSelection = clearBarangSelection;
    window.clearServisSelection = clearServisSelection;
    window.addLampiranField = addLampiranField;
    window.removeLampiran = removeLampiran;
    window.showRefField = showRefField;

    // Initialize - render options when page loads if search has value
    document.addEventListener('DOMContentLoaded', function() {
        if (userSearch.value.length > 0) {
            renderUserOptions(userSearch.value);
        }
        if (barangSearch.value.length > 0) {
            renderBarangOptions(barangSearch.value);
        }
        if (servisSearch.value.length > 0) {
            renderServisOptions(servisSearch.value);
        }
    });
</script>

</body>
</html>