<?php
include("../includes/session.php");
include("../config/database.php");

$id_pengguna = $_SESSION['id_pengguna'];

// Check if user is admin
$sql_user = "SELECT peranan FROM pengguna WHERE id_pengguna = '$id_pengguna'";
$result_user = mysqli_query($conn, $sql_user);
$user = mysqli_fetch_assoc($result_user);

if($user['peranan'] != 'pentadbir') {
    header("Location: ../dashboard/index.php");
    exit();
}

// Get notification counts
$sql_notifikasi = "SELECT COUNT(*) AS jumlah FROM mesej WHERE id_penerima = '$id_pengguna' AND dibaca = 'tidak'";
$result_notifikasi = mysqli_query($conn, $sql_notifikasi);
$data_notifikasi = mysqli_fetch_assoc($result_notifikasi);
$jumlah_notifikasi = $data_notifikasi['jumlah'];

// Count unread aduan messages for admin
$sql_notif_aduan = "SELECT COUNT(*) AS jumlah FROM aduan_chat 
                    WHERE id_pengguna != '$id_pengguna' 
                    AND status = 'belum_dibaca'";
$result_notif_aduan = mysqli_query($conn, $sql_notif_aduan);
$data_notif_aduan = mysqli_fetch_assoc($result_notif_aduan);
$jumlah_notif_aduan = $data_notif_aduan['jumlah'];

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Build query
$where = "1=1";
if($status_filter) $where .= " AND a.status = '$status_filter'";
if($kategori_filter) $where .= " AND a.kategori = '$kategori_filter'";
if($search) $where .= " AND (a.tajuk LIKE '%$search%' OR a.penerangan LIKE '%$search%' OR p.nama_penuh LIKE '%$search%')";

$sql = "
    SELECT a.*, p.nama_penuh, 
           (SELECT COUNT(*) FROM aduan_chat WHERE id_aduan = a.id_aduan AND status = 'belum_dibaca' AND id_pengguna != '$id_pengguna') AS mesej_baru
    FROM aduan a 
    LEFT JOIN pengguna p ON a.id_pengguna = p.id_pengguna 
    WHERE $where
    ORDER BY FIELD(a.status, 'baru', 'dalam_proses', 'selesai', 'ditolak'), a.tarikh_hantar DESC
";
$result = mysqli_query($conn, $sql);
$total_aduan = mysqli_num_rows($result);

// Count by status
$count_baru = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM aduan WHERE status='baru'"))['total'];
$count_proses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM aduan WHERE status='dalam_proses'"))['total'];
$count_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM aduan WHERE status='selesai'"))['total'];
$count_ditolak = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM aduan WHERE status='ditolak'"))['total'];

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

$kategori_list = ['barang', 'servis', 'pengguna', 'pembayaran', 'lain-lain'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Aduan | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .main-content { padding: 2rem; background: #f8f9fc; min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .header h1 { font-size: 1.8rem; border-left: 5px solid #6c3cff; padding-left: 1rem; }
        
        .stats-row { display: flex; gap: 1.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .stats-row .stat-box .number { font-size: 1.8rem; font-weight: 700; }
        .stats-row .stat-box .label { font-size: 0.8rem; color: #888; }
        .stats-row .stat-box.baru .number { color: #92400e; }
        .stats-row .stat-box.proses .number { color: #1e40af; }
        .stats-row .stat-box.selesai .number { color: #065f46; }
        .stats-row .stat-box.ditolak .number { color: #991b1b; }
        .stats-row .stat-box.total { background: #6c3cff; color: white; }
        .stats-row .stat-box.total .number { color: white; }
        .stats-row .stat-box.total .label { color: #e0d4ff; }
        
        .filter-section { background: white; padding: 1rem; margin-bottom: 1.5rem; border: 1px solid #eef2f8; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; }
        .filter-section select, .filter-section input { padding: 0.5rem 1rem; border: 1px solid #ddd; border-radius: 0px; }
        .filter-section button { background: #6c3cff; color: white; border: none; padding: 0.5rem 1.5rem; cursor: pointer; font-weight: 600; }
        .filter-section a { color: #6c3cff; text-decoration: none; }
        
        .table-responsive { overflow-x: auto; }
        table { width: 100%; background: white; border-collapse: collapse; border: 1px solid #eef2f8; }
        th, td { padding: 0.8rem 1rem; text-align: left; border-bottom: 1px solid #eef2f8; vertical-align: middle; }
        th { background: #f8f9fc; font-weight: 600; }
        .status-badge { padding: 0.2rem 0.6rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; display: inline-block; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-process { background: #dbeafe; color: #1e40af; }
        .status-done { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        
        .btn-action { background: #6c3cff; color: white; border: none; padding: 0.3rem 0.8rem; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; font-size: 0.8rem; }
        .btn-action:hover { opacity: 0.9; }
        .notif-dot { display: inline-block; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; margin-left: 5px; animation: blink 1s infinite; }
        .empty-state { text-align: center; padding: 3rem; color: #888; background: white; border: 1px solid #eef2f8; }
        
        @media (max-width: 768px) { .main-content { padding: 1rem; } .stats-row { flex-direction: column; } .filter-section { flex-direction: column; } }
    </style>
</head>
<body>

<div class="dashboard">
    <div class="sidebar">
        <div class="logo">PlatformJualBeli</div>
        <ul class="menu">
            <li><a href="../admin/dashboard_admin.php">Utama</a></li>
            <li><a href="../barang/senarai_barang.php">Barang</a></li>
            <li><a href="../servis/senarai_servis.php">Servis</a></li>
            <li><a href="../tukar/senarai_tawaran.php">Tawaran Pertukaran</a></li>
            <li><a href="../mesej/chat_senarai.php">Mesej <?php if($jumlah_notifikasi > 0): ?><span class="badge" style="background:#ef4444;"><?= $jumlah_notifikasi; ?></span><?php endif; ?></a></li>
            <li class="active">
                <a href="admin_aduan.php">
                    Aduan (Admin)
                    <?php if($jumlah_notif_aduan > 0): ?>
                        <span class="badge" style="background:#ef4444;"><?= $jumlah_notif_aduan; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li><a href="../profil/profil.php">Profil</a></li>
            <li><a href="../auth/logout.php">Log Keluar</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container">
            <div class="header">
                <h1>Pengurusan Aduan</h1>
                <a href="../aduan/aduan_saya.php" class="btn-action" style="padding:0.7rem 1.5rem;">Lihat Aduan Saya</a>
            </div>

            <div class="stats-row">
                <div class="stat-box baru"><div class="number"><?= $count_baru; ?></div><div class="label">Baru</div></div>
                <div class="stat-box proses"><div class="number"><?= $count_proses; ?></div><div class="label">Dalam Proses</div></div>
                <div class="stat-box selesai"><div class="number"><?= $count_selesai; ?></div><div class="label">Selesai</div></div>
                <div class="stat-box ditolak"><div class="number"><?= $count_ditolak; ?></div><div class="label">Ditolak</div></div>
                <div class="stat-box total"><div class="number"><?= $total_aduan; ?></div><div class="label">Jumlah</div></div>
            </div>

            <div class="filter-section">
                <form method="GET" style="display:flex;flex-wrap:wrap;gap:1rem;width:100%;align-items:center;">
                    <select name="status">
                        <option value="">Semua Status</option>
                        <option value="baru" <?= $status_filter=='baru'?'selected':''; ?>>Baru</option>
                        <option value="dalam_proses" <?= $status_filter=='dalam_proses'?'selected':''; ?>>Dalam Proses</option>
                        <option value="selesai" <?= $status_filter=='selesai'?'selected':''; ?>>Selesai</option>
                        <option value="ditolak" <?= $status_filter=='ditolak'?'selected':''; ?>>Ditolak</option>
                    </select>
                    <select name="kategori">
                        <option value="">Semua Kategori</option>
                        <?php foreach($kategori_list as $kat): ?>
                            <option value="<?= $kat; ?>" <?= $kategori_filter==$kat?'selected':''; ?>><?= ucfirst($kat); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="search" placeholder="Cari..." value="<?= htmlspecialchars($search); ?>">
                    <button type="submit">Filter</button>
                    <a href="admin_aduan.php">Reset</a>
                </form>
            </div>

            <div class="table-responsive">
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pengguna</th>
                                <th>Tajuk</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Tarikh</th>
                                <th>Chat</th>
                                <th>Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>#<?= $row['id_aduan']; ?></td>
                                    <td><?= htmlspecialchars($row['nama_penuh'] ?? 'Unknown'); ?></td>
                                    <td><a href="detail_aduan_admin.php?id=<?= $row['id_aduan']; ?>"><?= htmlspecialchars(substr($row['tajuk'], 0, 35)) . (strlen($row['tajuk']) > 35 ? '...' : ''); ?></a></td>
                                    <td><?= ucfirst($row['kategori']); ?></td>
                                    <td><span class="status-badge <?= $status_badge[$row['status']] ?? 'status-pending'; ?>"><?= $status_label[$row['status']] ?? $row['status']; ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($row['tarikh_hantar'])); ?></td>
                                    <td><a href="detail_aduan_admin.php?id=<?= $row['id_aduan']; ?>">💬<?php if($row['mesej_baru'] > 0): ?><span class="notif-dot"></span><?php endif; ?></a></td>
                                    <td><a href="detail_aduan_admin.php?id=<?= $row['id_aduan']; ?>" class="btn-action">Lihat</a></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">Tiada aduan ditemui.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>