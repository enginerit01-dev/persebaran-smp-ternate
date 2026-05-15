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
    <title>Bantuan - WebGIS SMPN Ternate</title>
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
            max-width: 900px;
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
        }

        .card h2 {
            color: #1e5631;
            font-size: 24px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 4px solid #1e5631;
            padding-left: 18px;
        }

        /* ============ FAQ ACCORDION ============ */
        .faq-item {
            margin-bottom: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .faq-item:hover {
            border-color: #1e5631;
        }

        .faq-question {
            background: #f8f9fa;
            padding: 18px 20px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }

        .faq-question:hover {
            background: #e8f5e9;
        }

        .faq-question span {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            color: #333;
        }

        .faq-question i.fa-chevron-down {
            color: #1e5631;
            transition: transform 0.3s;
        }

        .faq-question.active i.fa-chevron-down {
            transform: rotate(180deg);
        }

        .faq-answer {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
        }

        .faq-answer.active {
            padding: 20px;
            max-height: 500px;
        }

        .faq-answer p {
            color: #555;
            line-height: 1.7;
            font-size: 14px;
        }

        .faq-answer ul, .faq-answer ol {
            margin-left: 20px;
            margin-top: 10px;
            color: #555;
        }

        .faq-answer li {
            margin: 8px 0;
        }

        /* ============ CONTACT BOX ============ */
        .contact-box {
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            margin-top: 20px;
        }

        .contact-box i {
            font-size: 40px;
            color: #1e5631;
            margin-bottom: 15px;
        }

        .contact-box h3 {
            color: #1e5631;
            margin-bottom: 10px;
        }

        .contact-box p {
            color: #555;
            margin-bottom: 15px;
        }

        .contact-email {
            background: white;
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-block;
            font-weight: 600;
            color: #1e5631;
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
            margin-top: 20px;
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
            .faq-question {
                padding: 15px;
            }
            .faq-question span {
                font-size: 13px;
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
            <a href="tentang.php">
                <i class="fas fa-info-circle"></i> Tentang
            </a>
            <a href="bantuan.php" class="active">
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
        <div class="card">
            <h2>
                <i class="fas fa-question-circle"></i>
                Pusat Bantuan & Panduan
            </h2>

            <!-- FAQ 1 -->
            <div class="faq-item">
                <div class="faq-question">
                    <span><i class="fas fa-map-marker-alt" style="color:#1e5631"></i> Bagaimana cara melihat peta persebaran sekolah?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Untuk melihat peta persebaran sekolah, Anda dapat:</p>
                    <ol>
                        <li>Klik menu <strong>"Peta"</strong> pada navbar bagian atas</li>
                        <li>Anda akan melihat peta dengan marker berwarna merah yang menunjukkan lokasi setiap SMPN</li>
                        <li>Gunakan tombol <strong>zoom (+/-)</strong> untuk memperbesar/memperkecil tampilan peta</li>
                        <li>Seret peta untuk menjelajahi area lainnya</li>
                    </ol>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="faq-item">
                <div class="faq-question">
                    <span><i class="fas fa-info-circle" style="color:#1e5631"></i> Bagaimana melihat detail informasi sekolah?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Ada dua cara untuk melihat detail informasi sekolah:</p>
                    <ul>
                        <li><strong>Melalui Peta:</strong> Klik pada marker (titik merah) di peta, lalu klik tombol "Lihat Detail" pada popup yang muncul.</li>
                        <li><strong>Melalui Data Sekolah:</strong> Buka menu "Data Sekolah", cari sekolah yang diinginkan, lalu klik tombol "Detail".</li>
                    </ul>
                    <p>Informasi yang ditampilkan meliputi: NPSN, alamat, kelurahan, kecamatan, jumlah siswa, akreditasi, dan fasilitas sekolah.</p>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="faq-item">
                <div class="faq-question">
                    <span><i class="fas fa-search" style="color:#1e5631"></i> Bagaimana cara mencari sekolah?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Fitur pencarian tersedia di:</p>
                    <ul>
                        <li><strong>Halaman Peta:</strong> Gunakan kotak pencarian di sidebar kanan, ketik nama sekolah yang ingin dicari.</li>
                        <li><strong>Halaman Data Sekolah:</strong> Gunakan kotak pencarian di atas tabel data sekolah.</li>
                    </ul>
                    <p>Setelah mengetik nama sekolah, sistem akan langsung menampilkan hasil yang sesuai.</p>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="faq-item">
                <div class="faq-question">
                    <span><i class="fas fa-circle-notch" style="color:#1e5631"></i> Apa itu analisis buffer?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p><strong>Analisis Buffer</strong> adalah fitur yang menampilkan area jangkauan layanan sekolah dalam radius 500 meter.</p>
                    <p>Cara menggunakannya:</p>
                    <ol>
                        <li>Buka halaman <strong>"Peta"</strong></li>
                        <li>Pada sidebar kiri, aktifkan toggle <strong>"Buffer 500m"</strong></li>
                        <li>Area berwarna biru akan muncul di sekitar setiap marker sekolah</li>
                        <li>Area tersebut menunjukkan wilayah yang dapat dijangkau dalam 500 meter dari sekolah</li>
                    </ol>
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="faq-item">
                <div class="faq-question">
                    <span><i class="fas fa-chart-line" style="color:#1e5631"></i> Apa itu Nearest Neighbor Analysis?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p><strong>Nearest Neighbor Analysis</strong> adalah metode analisis spasial untuk mengetahui pola persebaran sekolah.</p>
                    <p>Hasil analisis menunjukkan apakah persebaran sekolah bersifat:</p>
                    <ul>
                        <li><strong>Mengelompok (Clustered)</strong> - Sekolah terkonsentrasi di area tertentu</li>
                        <li><strong>Acak (Random)</strong> - Tidak ada pola yang jelas</li>
                        <li><strong>Merata (Uniform)</strong> - Sekolah tersebar merata</li>
                    </ul>
                    <p>Lihat hasil analisis lengkap di menu <strong>"Analisis"</strong>.</p>
                </div>
            </div>

            <!-- FAQ 6 -->
            <div class="faq-item">
                <div class="faq-question">
                    <span><i class="fas fa-filter" style="color:#1e5631"></i> Bagaimana cara filter berdasarkan wilayah?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Fitur filter wilayah tersedia di halaman <strong>"Peta"</strong> pada sidebar kiri:</p>
                    <ol>
                        <li>Pilih <strong>Kecamatan</strong> yang diinginkan (Ternate Tengah atau Ternate Selatan)</li>
                        <li>Setelah memilih kecamatan, pilihan <strong>Kelurahan</strong> akan otomatis muncul</li>
                        <li>Pilih kelurahan untuk menampilkan hanya sekolah di kelurahan tersebut</li>
                    </ol>
                    <p>Marker di peta akan otomatis terfilter sesuai pilihan Anda.</p>
                </div>
            </div>

            <!-- FAQ 7 -->
            <div class="faq-item">
                <div class="faq-question">
                    <span><i class="fas fa-user-shield" style="color:#1e5631"></i> Bagaimana cara admin mengelola data sekolah?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Jika Anda login sebagai <strong>Admin</strong>, Anda dapat mengelola data sekolah:</p>
                    <ul>
                        <li><strong>Tambah Sekolah:</strong> Klik tombol "Tambah Lokasi Sekolah" di sidebar kiri halaman Peta</li>
                        <li><strong>Edit Sekolah:</strong> Klik tombol "Edit" pada popup marker atau di halaman Data Sekolah</li>
                        <li><strong>Hapus Sekolah:</strong> Klik tombol "Hapus" pada popup marker atau di halaman Data Sekolah</li>
                    </ul>
                    <p>Data yang diubah akan langsung terupdate di peta dan database.</p>
                </div>
            </div>

            <!-- FAQ 8 -->
            <div class="faq-item">
                <div class="faq-question">
                    <span><i class="fas fa-database" style="color:#1e5631"></i> Data sekolah tidak muncul di peta?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Jika data sekolah tidak muncul di peta, coba lakukan langkah berikut:</p>
                    <ol>
                        <li>Pastikan Anda login sebagai <strong>Admin</strong> atau <strong>User</strong> yang terdaftar</li>
                        <li>Pastikan toggle <strong>"Sekolah"</strong> di sidebar kiri dalam posisi AKTIF (warna hijau)</li>
                        <li>Coba refresh halaman (F5)</li>
                        <li>Jika masih kosong, tambahkan data sekolah melalui menu Admin</li>
                    </ol>
                </div>
            </div>

            <!-- Contact Box -->
            <div class="contact-box">
                <i class="fas fa-envelope"></i>
                <h3>Butuh Bantuan Lebih Lanjut?</h3>
                <p>Jika Anda masih mengalami kendala atau memiliki pertanyaan, jangan ragu untuk menghubungi tim pengembang.</p>
                <div class="contact-email">
                    <i class="fas fa-envelope"></i> webgis.smpn@unkhair.ac.id
                </div>
            </div>

            <!-- Tombol Kembali -->
            <div style="text-align: center; margin-top: 20px;">
                <button class="btn-back" onclick="window.location.href='dashboard.php'">
                    <i class="fas fa-arrow-left"></i> Kembali ke Peta
                </button>
            </div>
        </div>
    </div>

    <script>
        // Accordion FAQ
        document.querySelectorAll('.faq-question').forEach(item => {
            item.addEventListener('click', () => {
                // Toggle active class on question
                item.classList.toggle('active');
                
                // Toggle active class on answer
                const answer = item.nextElementSibling;
                answer.classList.toggle('active');
            });
        });
    </script>
</body>
</html>