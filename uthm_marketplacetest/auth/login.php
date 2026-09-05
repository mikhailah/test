<?php ob_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Log Masuk | PlatformJualBeli</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Additional clean styling - square corners like homepage */
        .feature-card {
            text-align: center;
            padding: 1rem;
        }
        .feature-card br {
            display: none;
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
        
        .success-message {
            border-radius: 0px !important;
        }
        
        .link a {
            border-radius: 0px !important;
        }
        
        .admin-link {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: #888;
        }
        
        .admin-link a {
            color: #6c3cff;
            text-decoration: none;
        }
        
        .admin-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">

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
            menjual, membeli dan mencari servis.
        </p>

        <div class="feature-section">

            <div class="feature-card">
                Jual Barang
            </div>

            <div class="feature-card">
                Mesej Penjual
            </div>

            <div class="feature-card">
                Akaun UTHM
            </div>

        </div>

    </div>

    <div class="right-panel">

        <div class="form-card">

            <h2>Log Masuk</h2>

            <p>
                Masukkan maklumat akaun anda
            </p>

            <?php
            if(isset($_GET['register']))
            {
            ?>

            <div class="success-message">
                Pendaftaran berjaya.
                Sila log masuk.
            </div>

            <?php
            }
            ?>

            <?php
            if(isset($_GET['error']))
            {
            ?>

            <div class="error-message" style="background:#fee2e2;color:#991b1b;padding:0.8rem 1rem;margin-bottom:1.5rem;text-align:center;">
                <?= htmlspecialchars($_GET['error']); ?>
            </div>

            <?php
            }
            ?>

            <form action="login_process.php" method="POST">

                <div class="form-group">

                    <label>
                        Emel UTHM
                    </label>

                    <input type="email" name="emel" class="form-control" required>

                </div>

                <div class="form-group">

                    <label>
                        Kata Laluan
                    </label>

                    <input type="password" name="kata_laluan" class="form-control" required>

                </div>

                <button class="btn" type="submit">
                    Log Masuk
                </button>

            </form>

            <div class="link">
                Belum ada akaun?
                <a href="register.php">Daftar Akaun</a>
            </div>

            <!-- Admin/Staff Login Link -->
            <div class="admin-link">
                Admin / Staff? 
                <a href="../admin/dashboard_admin.php">Login ke Admin Dashboard</a>
                <span style="color:#ccc;font-size:0.75rem;">(Guna akaun admin/staff)</span>
            </div>

        </div>

    </div>

</div>

</body>
</html>