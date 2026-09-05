<!DOCTYPE html>
<html>
<head>
    <title>Daftar Akaun | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Additional clean styling - square corners like homepage */
        .feature-card {
            text-align: center;
            padding: 1rem;
        }
        
        .error-message {
            background: #ef4444;
            color: white;
            padding: 0.8rem 1rem;
            border-radius: 0px;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            text-align: center;
        }
        
        /* Make all corners SQUARE (0px) like homepage */
        .container {
            border-radius: 0px !important;
        }
        
        .left-panel {
            border-radius: 0px !important;
        }
        
        .right-panel {
            border-radius: 0px !important;
        }
        
        .form-card {
            border-radius: 0px !important;
        }
        
        .form-control {
            border-radius: 0px !important;
        }
        
        .btn {
            border-radius: 0px !important;
        }
        
        .feature-card {
            border-radius: 0px !important;
        }
    </style>
</head>
<body>

<div class="container">

    <!-- LEFT PANEL - SAME AS LOGIN PAGE -->
    <div class="left-panel">

        <div class="logo">
            PlatformJualBeli
        </div>

        <div class="tagline">
            Platform Pelajar UTHM
        </div>

        <h1>
            Beli, Jual,
            <span>Jimat!</span>
        </h1>

        <p>
            Platform khas untuk pelajar UTHM
            membeli, menjual dan mengiklankan
            servis dengan lebih selamat.
        </p>

        <div class="feature-section">

            <div class="feature-card">
                Jual Barang
            </div>

            <div class="feature-card">
                Tukar Barang
            </div>

            <div class="feature-card">
                Akaun UTHM
            </div>

        </div>

    </div>

    <!-- RIGHT PANEL - REGISTRATION FORM -->
    <div class="right-panel">

        <div class="form-card">

            <h2>Daftar Akaun</h2>

            <p>
                Lengkapkan maklumat di bawah
            </p>

            <?php if(isset($_GET['error'])): ?>
                <div class="error-message">
                    <?= htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="register_process.php" method="POST">

                <div class="form-group">
                    <label>Nama Penuh</label>
                    <input type="text" name="nama_penuh" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Emel UTHM</label>
                    <input type="email" name="emel" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>No Telefon</label>
                    <input type="text" name="no_telefon" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Kata Laluan</label>
                    <input type="password" name="kata_laluan" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Sahkan Kata Laluan</label>
                    <input type="password" name="sahkan_kata_laluan" class="form-control" required>
                </div>

                <button class="btn" type="submit">
                    Daftar Akaun
                </button>

            </form>

            <div class="link">
                Sudah ada akaun?
                <a href="login.php">Log Masuk</a>
            </div>

        </div>

    </div>

</div>

</body>
</html>