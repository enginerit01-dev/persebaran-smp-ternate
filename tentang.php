<?php
session_start();
require_once 'config/database.php';
require_once 'config/auth.php';

if(!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Tentang - WebGIS SMPN Ternate</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
        }

        /* ============ NAVBAR SAMA DENGAN DASHBOARD ============ */
        .navbar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 12px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            flex-wrap: wrap;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #1e5631, #2d6a4f);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(30,86,49,0.3);
        }

        .logo-icon i {
            font-size: 22px;
            color: white;
        }

        .logo-text h2 {
            font-size: 20px;
            font-weight: 700;
            color: #1e5631;
            letter-spacing: -0.5px;
        }

        .logo-text p {
            font-size: 11px;
            color: #666;
            margin-top: -2px;
        }

        .nav-menu {
            display: flex;
            gap: 8px;
            align-items: center;
            background: #f8f9fa;
            padding: 5px;
            border-radius: 50px;
            flex-wrap: wrap;
        }

        .nav-menu a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 40px;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .nav-menu a:hover {
            background: #1e5631;
            color: white;
            transform: translateY(-2px);
        }

        .nav-menu a.active {
            background: #1e5631;
            color: white;
            box-shadow: 0 2px 8px rgba(30,86,49,0.3);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            background: #f8f9fa;
            padding: 5px 15px 5px 10px;
            border-radius: 50px;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #1e5631, #2d6a4f);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .user-info span {
            font-weight: 500;
            color: #333;
        }

        .logout-btn {
            color: #dc2626;
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: #fee2e2;
        }

        /* ============ CONTAINER ============ */
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* ============ CARD ============ */
        .card {
            background: white;
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-3px);
        }

        .card h2 {
            color: #1e5631;
            font-size: 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 4px solid #1e5631;
            padding-left: 18px;
        }

        .card h3 {
            color: #1e5631;
            font-size: 18px;
            margin: 25px 0 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card p {
            color: #555;
            line-height: 1.7;
            margin-bottom: 15px;
            font-size: 15px;
        }

        /* ============ TEAM SECTION ============ */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .team-card {
            background: linear-gradient(135deg, #f8f9fa, #fff);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s;
            border: 1px solid #eee;
        }

        .team-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .team-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #1e5631, #2d6a4f);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .team-avatar i {
            font-size: 45px;
            color: white;
        }

        .team-card h4 {
            font-size: 18px;
            font-weight: 700;
            color: #1e5631;
            margin-bottom: 5px;
        }

        .team-card p {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
        }

        .team-card .nim {
            font-size: 12px;
            color: #999;
            font-family: monospace;
        }

        /* ============ FEATURE LIST ============ */
        .feature-list {
            list-style: none;
            margin-top: 15px;
        }

        .feature-list li {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .feature-list li i {
            width: 30px;
            color: #1e5631;
            font-size: 18px;
        }

        /* ============ TECH STACK ============ */
        .tech-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }

        .tech-badge {
            background: #e8f5e9;
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #1e5631;
        }

        .tech-badge i {
            font-size: 16px;
        }

        /* ============ BUTTON ============ */
        .btn-back {
            background: linear-gradient(135deg, #1e5631, #2d6a4f);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30,86,49,0.3);
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 10px;
            }
            .card {
                padding: 20px;
            }
            .team-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar SAMA DENGAN DASHBOARD -->
    <nav class="navbar">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <div class="logo-text">
                <h2>WebGIS SMPN</h2>
                <p>Ternate Tengah & Selatan</p>
            </div>
        </div>
        <div class="nav-menu">
            <a href="dashboard.php">
                <i class="fas fa-map"></i> Peta
            </a>
            <a href="data_sekolah.php">
                <i class="fas fa-school"></i> Data Sekolah
            </a>
            <a href="analisis.php">
                <i class="fas fa-chart-line"></i> Analisis
            </a>
            <a href="tentang.php" class="active">
                <i class="fas fa-info-circle"></i> Tentang
            </a>
            <a href="bantuan.php">
                <i class="fas fa-question-circle"></i> Bantuan
            </a>
        </div>
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <span><?php echo $username; ?></span>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Card Utama -->
        <div class="card">
            <h2>
                <i class="fas fa-info-circle"></i>
                Tentang WebGIS SMPN
            </h2>
            <p>
                <strong>WebGIS SMPN</strong> adalah Sistem Informasi Geografis (SIG) berbasis web yang dikembangkan untuk 
                memetakan persebaran Sekolah Menengah Pertama Negeri (SMPN) di <strong>Kecamatan Ternate Tengah dan Ternate Selatan</strong>, 
                Kota Ternate, Provinsi Maluku Utara.
            </p>
            <p>
                Sistem ini bertujuan untuk membantu masyarakat, pemerintah, dan pemangku kepentingan dalam memperoleh informasi 
                tentang lokasi dan distribusi SMPN secara digital, interaktif, dan akurat. Dengan adanya sistem ini, 
                diharapkan dapat memudahkan analisis persebaran sekolah dan mendukung perencanaan pembangunan pendidikan 
                di wilayah Kota Ternate.
            </p>
        </div>

        <!-- Fitur Sistem -->
        <div class="card">
            <h2>
                <i class="fas fa-cogs"></i>
                Fitur Sistem
            </h2>
            <ul class="feature-list">
                <li><i class="fas fa-map"></i> <strong>Peta Interaktif</strong> - Menampilkan peta digital dengan marker lokasi SMPN</li>
                <li><i class="fas fa-map-marker-alt"></i> <strong>Marker & Popup</strong> - Informasi detail sekolah saat marker diklik</li>
                <li><i class="fas fa-search"></i> <strong>Pencarian Sekolah</strong> - Mencari sekolah berdasarkan nama atau alamat</li>
                <li><i class="fas fa-circle-notch"></i> <strong>Analisis Buffer</strong> - Menampilkan jangkauan layanan sekolah dalam radius 500m</li>
                <li><i class="fas fa-chart-line"></i> <strong>Nearest Neighbor Analysis</strong> - Analisis pola persebaran sekolah</li>
                <li><i class="fas fa-filter"></i> <strong>Filter Wilayah</strong> - Menyaring marker berdasarkan kecamatan/kelurahan</li>
                <li><i class="fas fa-school"></i> <strong>Data Sekolah</strong> - Tabel lengkap data SMPN</li>
                <li><i class="fas fa-user-shield"></i> <strong>Manajemen Data</strong> - Admin dapat menambah, mengedit, dan menghapus data sekolah</li>
            </ul>
        </div>

        <!-- Teknologi yang Digunakan -->
        <div class="card">
            <h2>
                <i class="fas fa-code"></i>
                Teknologi yang Digunakan
            </h2>
            <div class="tech-grid">
                <span class="tech-badge"><i class="fab fa-php"></i> PHP</span>
                <span class="tech-badge"><i class="fas fa-database"></i> PostgreSQL / Supabase</span>
                <span class="tech-badge"><i class="fab fa-js"></i> JavaScript</span>
                <span class="tech-badge"><i class="fab fa-css3-alt"></i> CSS3</span>
                <span class="tech-badge"><i class="fab fa-html5"></i> HTML5</span>
                <span class="tech-badge"><i class="fas fa-map"></i> Leaflet.js</span>
                <span class="tech-badge"><i class="fab fa-bootstrap"></i> Bootstrap</span>
                <span class="tech-badge"><i class="fas fa-chart-line"></i> Chart.js</span>
                <span class="tech-badge"><i class="fab fa-jquery"></i> jQuery</span>
                <span class="tech-badge"><i class="fas fa-server"></i> XAMPP</span>
            </div>
        </div>

        <!-- Tim Pengembang -->
        <div class="card">
            <h2>
                <i class="fas fa-users"></i>
                Tim Pengembang
            </h2>
            <div class="team-grid">
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h4>Ridwan Daeng Hanafi</h4>
                    <p>Program Studi Informatika</p>
                    <p class="nim">NIM: 07352311113</p>
                </div>
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h4>Adeavicka Madani</h4>
                    <p>Program Studi Informatika</p>
                    <p class="nim">NIM: 073523111102</p>
                </div>
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h4>Nabila Tabaika</h4>
                    <p>Program Studi Informatika</p>
                    <p class="nim">NIM: 07352311111</p>
                </div>
            </div>
        </div>

        <!-- Informasi Kontak -->
        <div class="card">
            <h2>
                <i class="fas fa-envelope"></i>
                Kontak & Informasi
            </h2>
            <p>
                <i class="fas fa-university"></i> <strong>Program Studi Informatika</strong><br>
                Fakultas Teknik, Universitas Khairun Ternate
            </p>
            <p>
                <i class="fas fa-map-marker-alt"></i> Jl. Pertamina Kampus II Gambesi, Kota Ternate<br>
                <i class="fas fa-envelope"></i> Email: webgis.smpn@unkhair.ac.id
            </p>
            <div style="text-align: center; margin-top: 20px;">
                <button class="btn-back" onclick="window.location.href='dashboard.php'">
                    <i class="fas fa-arrow-left"></i> Kembali ke Peta
                </button>
            </div>
        </div>
    </div>
</body>
</html>
