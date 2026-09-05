<?php

include("../includes/session.php");
include("../config/database.php");

$id_sendiri = $_SESSION['id_pengguna'];
$id_lawan = $_GET['id'];

// ============================================================
// 1. TANDAKAN SEMUA MESEJ SEBAGAI DIBACA
// ============================================================
$sql_update = "UPDATE mesej SET dibaca='ya' WHERE id_penghantar='$id_lawan' AND id_penerima='$id_sendiri'";
mysqli_query($conn, $sql_update);

// Get user info
$sql_user = "SELECT * FROM pengguna WHERE id_pengguna = '$id_lawan'";
$result_user = mysqli_query($conn, $sql_user);
$user = mysqli_fetch_assoc($result_user);

// Get chat messages
$sql_chat = "
    SELECT * FROM mesej
    WHERE (id_penghantar='$id_sendiri' AND id_penerima='$id_lawan')
       OR (id_penghantar='$id_lawan' AND id_penerima='$id_sendiri')
    ORDER BY tarikh_hantar ASC
";
$result_chat = mysqli_query($conn, $sql_chat);

// Get last message id for AJAX
$last_id = 0;
if(mysqli_num_rows($result_chat) > 0) {
    $sql_last = "SELECT MAX(id_mesej) as last_id FROM mesej 
                 WHERE (id_penghantar='$id_sendiri' AND id_penerima='$id_lawan')
                    OR (id_penghantar='$id_lawan' AND id_penerima='$id_sendiri')";
    $result_last = mysqli_query($conn, $sql_last);
    $row_last = mysqli_fetch_assoc($result_last);
    $last_id = $row_last['last_id'];
}

// Reset result pointer
mysqli_data_seek($result_chat, 0);

// ============================================================
// 2. KIRA SEMULA NOTIFIKASI
// ============================================================
$sql_notifikasi = "SELECT COUNT(*) AS jumlah FROM mesej WHERE id_penerima = '$id_sendiri' AND dibaca = 'tidak'";
$result_notifikasi = mysqli_query($conn, $sql_notifikasi);
$data_notifikasi = mysqli_fetch_assoc($result_notifikasi);
$jumlah_notifikasi = $data_notifikasi['jumlah'];

// Trade notification count
$sql_notif_trade = "SELECT COUNT(*) AS jumlah FROM notifikasi WHERE id_penerima = '$id_sendiri' AND dibaca = 'tidak'";
$result_notif_trade = mysqli_query($conn, $sql_notif_trade);
$data_notif_trade = mysqli_fetch_assoc($result_notif_trade);
$jumlah_notif_trade = $data_notif_trade['jumlah'];

// ============================================================
// ADUAN NOTIFICATION COUNT
// ============================================================
$sql_notif_aduan = "SELECT COUNT(*) AS jumlah FROM aduan_chat 
                    WHERE id_aduan IN (SELECT id_aduan FROM aduan WHERE id_pengguna = '$id_sendiri')
                    AND id_pengguna != '$id_sendiri' 
                    AND status = 'belum_dibaca'";
$result_notif_aduan = mysqli_query($conn, $sql_notif_aduan);
$data_notif_aduan = mysqli_fetch_assoc($result_notif_aduan);
$jumlah_notif_aduan = $data_notif_aduan['jumlah'];

// Function to format date like WhatsApp
function formatDate($timestamp) {
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $msg_date = date('Y-m-d', strtotime($timestamp));
    
    if ($msg_date == $today) {
        return 'Hari Ini';
    } elseif ($msg_date == $yesterday) {
        return 'Semalam';
    } else {
        return date('d/m/Y', strtotime($timestamp));
    }
}

// Get last message date for tracking
$last_msg_date = '';
if(mysqli_num_rows($result_chat) > 0) {
    $sql_last_date = "SELECT tarikh_hantar FROM mesej 
                 WHERE (id_penghantar='$id_sendiri' AND id_penerima='$id_lawan')
                    OR (id_penghantar='$id_lawan' AND id_penerima='$id_sendiri')
                    ORDER BY id_mesej DESC LIMIT 1";
    $result_last_date = mysqli_query($conn, $sql_last_date);
    $row_last_date = mysqli_fetch_assoc($result_last_date);
    if($row_last_date) {
        $last_msg_date = date('Y-m-d', strtotime($row_last_date['tarikh_hantar']));
    }
}

// Reset result pointer again
mysqli_data_seek($result_chat, 0);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Chat dengan <?= htmlspecialchars($user['nama_penuh']); ?></title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .main-content { padding: 1.5rem; background: #f8f9fc; min-height: 100vh; }
        
        .chat-window {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: 1px solid #eef2f8;
            display: flex;
            flex-direction: column;
            height: 550px;
            max-height: 75vh;
        }
        
        .chat-header {
            padding: 0.8rem 1.2rem;
            border-bottom: 1px solid #eef2f8;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            background: white;
            flex-shrink: 0;
        }
        
        .chat-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #6c3cff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .chat-avatar img {
            width: 40px;
            height: 40px;
            object-fit: cover;
        }
        
        .chat-header-info h3 { margin: 0; font-size: 1rem; }
        
        .back-btn {
            background: none;
            border: none;
            font-size: 1.3rem;
            cursor: pointer;
            color: #6c3cff;
            padding: 0.2rem 0.5rem;
        }
        
        .chat-box {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 1.2rem;
            background: #f8f9fc;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        
        .date-separator {
            text-align: center;
            margin: 0.8rem 0;
        }
        
        .date-separator span {
            background: #e5e7eb;
            color: #6b7280;
            font-size: 0.7rem;
            padding: 0.2rem 0.8rem;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .message-wrapper {
            display: flex;
            flex-direction: column;
            max-width: 75%;
        }
        
        .message-wrapper.sent {
            align-self: flex-end;
            align-items: flex-end;
        }
        
        .message-wrapper.received {
            align-self: flex-start;
            align-items: flex-start;
        }
        
        .bubble {
            padding: 0.5rem 1rem;
            border-radius: 16px;
            word-wrap: break-word;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .bubble.sent {
            background: linear-gradient(135deg, #6c3cff, #4f7cff);
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .bubble.received {
            background: white;
            color: #1a1a2e;
            border: 1px solid #eef2f8;
            border-bottom-left-radius: 4px;
        }
        
        .msg-time {
            font-size: 0.6rem;
            color: #999;
            margin-top: 0.1rem;
            padding: 0 0.3rem;
        }
        
        .message-wrapper.sent .msg-time {
            text-align: right;
        }
        
        /* Attachment styles */
        .attachment {
            margin-top: 0.3rem;
        }
        
        .attachment img {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        
        .attachment .file-link {
            font-size: 0.8rem;
            color: #6c3cff;
            text-decoration: none;
            display: inline-block;
            padding: 0.2rem 0.5rem;
            background: #f0eaff;
            border-radius: 4px;
        }
        
        .chat-input-area {
            padding: 0.7rem 1.2rem;
            border-top: 1px solid #eef2f8;
            background: white;
            display: flex;
            gap: 0.6rem;
            flex-shrink: 0;
            flex-wrap: wrap;
        }
        
        .chat-input-area .input-wrapper {
            flex: 1;
            display: flex;
            gap: 0.6rem;
            align-items: center;
        }
        
        .chat-input-area input[type="text"] {
            flex: 1;
            padding: 0.6rem 1rem;
            border: 1px solid #ddd;
            border-radius: 30px;
            font-size: 0.9rem;
        }
        
        .chat-input-area input[type="text"]:focus {
            outline: none;
            border-color: #6c3cff;
        }
        
        .chat-input-area label.file-label {
            background: #eef2f8;
            padding: 0.6rem 0.8rem;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1.2rem;
            border: 1px solid #ddd;
        }
        
        .chat-input-area label.file-label:hover {
            background: #e2e8f0;
        }
        
        .chat-input-area input[type="file"] {
            display: none;
        }
        
        .chat-input-area button {
            background: linear-gradient(135deg, #6c3cff, #4f7cff);
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            flex-shrink: 0;
            font-size: 0.9rem;
        }
        
        .chat-input-area .file-name {
            font-size: 0.7rem;
            color: #888;
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .empty-chat {
            text-align: center;
            color: #aaa;
            padding: 2rem;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .main-content { padding: 0.5rem; }
            .chat-window { height: 90vh; max-height: none; }
            .message-wrapper { max-width: 90%; }
            .attachment img { max-width: 150px; max-height: 120px; }
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
        
        <div class="chat-window">
            <div class="chat-header">
                <a href="chat_senarai.php" style="text-decoration:none;">
                    <button class="back-btn">←</button>
                </a>
                <div class="chat-avatar">
                    <?php if(!empty($user['gambar_profil']) && file_exists("../profil/gambar/".$user['gambar_profil'])): ?>
                        <img src="../profil/gambar/<?= htmlspecialchars($user['gambar_profil']); ?>" alt="<?= htmlspecialchars($user['nama_penuh']); ?>">
                    <?php else: ?>
                        <?= substr($user['nama_penuh'], 0, 1); ?>
                    <?php endif; ?>
                </div>
                <div class="chat-header-info">
                    <h3><?= htmlspecialchars($user['nama_penuh']); ?></h3>
                </div>
            </div>
            
            <div class="chat-box" id="chatBox">
                <?php if(mysqli_num_rows($result_chat) > 0): ?>
                    <?php 
                    $prev_date = '';
                    while($row = mysqli_fetch_assoc($result_chat)): 
                        $msg_date = date('Y-m-d', strtotime($row['tarikh_hantar']));
                        $display_date = formatDate($row['tarikh_hantar']);
                        $has_attachment = !empty($row['lampiran']);
                        $is_image = false;
                        $file_path = "";
                        if($has_attachment) {
                            $file_path = "lampiran/" . $row['lampiran'];
                            $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
                            $ext = strtolower(pathinfo($row['lampiran'], PATHINFO_EXTENSION));
                            $is_image = in_array($ext, $image_extensions);
                        }
                    ?>
                        <?php if($prev_date != $msg_date): ?>
                            <div class="date-separator" data-date="<?= $msg_date; ?>">
                                <span><?= $display_date; ?></span>
                            </div>
                            <?php $prev_date = $msg_date; ?>
                        <?php endif; ?>
                        
                        <?php if($row['id_penghantar'] == $id_sendiri): ?>
                            <div class="message-wrapper sent" data-date="<?= $msg_date; ?>">
                                <div class="bubble sent">
                                    <?php if(!empty($row['mesej'])): ?>
                                        <?= nl2br(htmlspecialchars($row['mesej'])); ?>
                                    <?php endif; ?>
                                    <?php if($has_attachment && file_exists($file_path)): ?>
                                        <div class="attachment">
                                            <?php if($is_image): ?>
                                                <a href="<?= $file_path; ?>" target="_blank">
                                                    <img src="<?= $file_path; ?>" alt="Lampiran">
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= $file_path; ?>" target="_blank" class="file-link">📄 <?= htmlspecialchars($row['lampiran']); ?></a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="msg-time"><?= date('H:i', strtotime($row['tarikh_hantar'])); ?></div>
                            </div>
                        <?php else: ?>
                            <div class="message-wrapper received" data-date="<?= $msg_date; ?>">
                                <div class="bubble received">
                                    <?php if(!empty($row['mesej'])): ?>
                                        <?= nl2br(htmlspecialchars($row['mesej'])); ?>
                                    <?php endif; ?>
                                    <?php if($has_attachment && file_exists($file_path)): ?>
                                        <div class="attachment">
                                            <?php if($is_image): ?>
                                                <a href="<?= $file_path; ?>" target="_blank">
                                                    <img src="<?= $file_path; ?>" alt="Lampiran">
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= $file_path; ?>" target="_blank" class="file-link">📄 <?= htmlspecialchars($row['lampiran']); ?></a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="msg-time"><?= date('H:i', strtotime($row['tarikh_hantar'])); ?></div>
                            </div>
                        <?php endif; ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-chat">Tiada mesej. Mulakan perbualan!</div>
                <?php endif; ?>
            </div>
            
            <form class="chat-input-area" action="hantar_chat.php" method="POST" id="chatForm" enctype="multipart/form-data">
                <input type="hidden" name="id_penerima" value="<?= $id_lawan; ?>">
                <div class="input-wrapper">
                    <input type="text" name="mesej" id="mesejInput" placeholder="Taip mesej..." autocomplete="off">
                    <label class="file-label" title="Lampirkan fail">
                        📎
                        <input type="file" name="lampiran" id="fileInput" accept="image/*,.pdf,.doc,.docx">
                    </label>
                    <span class="file-name" id="fileName"></span>
                </div>
                <button type="submit" id="sendBtn">Hantar</button>
            </form>
        </div>
        
    </div>
    
</div>

<script>
    const chatBox = document.getElementById('chatBox');
    const mesejInput = document.getElementById('mesejInput');
    const sendBtn = document.getElementById('sendBtn');
    const fileInput = document.getElementById('fileInput');
    const fileName = document.getElementById('fileName');
    const chatForm = document.getElementById('chatForm');
    let lastId = <?= $last_id; ?>;
    let lastDate = '<?= $last_msg_date; ?>';
    
    // Show file name when selected
    fileInput.addEventListener('change', function() {
        if(this.files.length > 0) {
            fileName.textContent = this.files[0].name;
        } else {
            fileName.textContent = '';
        }
    });
    
    function scrollToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
    scrollToBottom();
    
    function getNewMessages() {
        fetch('get_new_messages.php?lawan=<?= $id_lawan; ?>&last_id=' + lastId)
            .then(response => response.json())
            .then(data => {
                if(data.messages && data.messages.length > 0) {
                    const emptyDiv = chatBox.querySelector('.empty-chat');
                    if(emptyDiv) emptyDiv.remove();
                    
                    data.messages.forEach(function(msg) {
                        const msgDate = msg.date || new Date().toISOString().split('T')[0];
                        const displayDate = msg.display_date || 'Hari Ini';
                        
                        if (lastDate !== msgDate) {
                            const separator = document.createElement('div');
                            separator.className = 'date-separator';
                            separator.innerHTML = '<span>' + displayDate + '</span>';
                            chatBox.appendChild(separator);
                            lastDate = msgDate;
                        }
                        
                        let attachmentHtml = '';
                        if(msg.has_attachment) {
                            if(msg.is_image) {
                                attachmentHtml = '<div class="attachment"><a href="' + msg.file_path + '" target="_blank"><img src="' + msg.file_path + '" alt="Lampiran"></a></div>';
                            } else {
                                attachmentHtml = '<div class="attachment"><a href="' + msg.file_path + '" target="_blank" class="file-link">📄 ' + msg.file_name + '</a></div>';
                            }
                        }
                        
                        const wrapper = document.createElement('div');
                        wrapper.className = 'message-wrapper ' + (msg.is_sent ? 'sent' : 'received');
                        wrapper.innerHTML = '<div class="bubble ' + (msg.is_sent ? 'sent' : 'received') + '">' + msg.mesej + attachmentHtml + '</div><div class="msg-time">' + msg.time + '</div>';
                        chatBox.appendChild(wrapper);
                        lastId = msg.id;
                    });
                    scrollToBottom();
                }
            })
            .catch(error => console.log('Error fetching messages:', error));
    }
    
    setInterval(getNewMessages, 2000);
    
    // Handle form submit
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const mesej = mesejInput.value.trim();
        const file = fileInput.files[0];
        
        if(!mesej && !file) {
            alert('Sila taip mesej atau lampirkan fail.');
            return;
        }
        
        sendBtn.disabled = true;
        sendBtn.textContent = 'Menghantar...';
        
        const formData = new FormData(this);
        
        fetch('hantar_chat_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                mesejInput.value = '';
                fileInput.value = '';
                fileName.textContent = '';
            } else {
                alert('Gagal menghantar mesej: ' + (data.message || 'Sila cuba lagi'));
                console.log('Error details:', data);
            }
            sendBtn.disabled = false;
            sendBtn.textContent = 'Hantar';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ralat menghantar mesej');
            sendBtn.disabled = false;
            sendBtn.textContent = 'Hantar';
        });
    });
    
    mesejInput.addEventListener('keypress', function(e) {
        if(e.key === 'Enter') {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit'));
        }
    });
</script>

</body>
</html>