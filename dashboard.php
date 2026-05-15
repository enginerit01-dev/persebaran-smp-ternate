<?php
session_start();

require_once 'config/database.php';
require_once 'config/auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];

try {

    // Ambil data sekolah
    $query_sekolah = "
        SELECT 
            s.*,
            COALESCE(k.nama_kelurahan, '-') AS nama_kelurahan,
            COALESCE(kc.nama_kecamatan, '-') AS nama_kecamatan
        FROM smpn s
        LEFT JOIN kelurahan k 
            ON s.id_kelurahan = k.id_kelurahan
        LEFT JOIN kecamatan kc 
            ON k.id_kecamatan = kc.id_kecamatan
        ORDER BY s.nama_sekolah
    ";

    $stmt_sekolah = $conn->prepare($query_sekolah);

    $stmt_sekolah->execute();

    $sekolah_list = $stmt_sekolah->fetchAll(PDO::FETCH_ASSOC);


    // Ambil data kelurahan untuk form
    $kelurahan_query = "
        SELECT 
            k.*,
            kc.nama_kecamatan
        FROM kelurahan k
        JOIN kecamatan kc 
            ON k.id_kecamatan = kc.id_kecamatan
        ORDER BY k.nama_kelurahan
    ";

    $stmt_kelurahan = $conn->prepare($kelurahan_query);

    $stmt_kelurahan->execute();

    $kelurahan_result = $stmt_kelurahan->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Database error: " . $e->getMessage());

}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>WebGIS SMPN - Pemetaan Sekolah Ternate</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
            overflow: hidden;
        }

        /* ============ NAVBAR MODERN ============ */
        .navbar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 12px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(0,0,0,0.05);
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

        /* ============ MAIN CONTAINER ============ */
        .main-container {
            display: flex;
            margin-top: 70px;
            height: calc(100vh - 70px);
        }

        /* ============ SIDEBAR KIRI ============ */
        .sidebar-left {
            width: 320px;
            background: white;
            box-shadow: 4px 0 20px rgba(0,0,0,0.05);
            padding: 20px;
            overflow-y: auto;
            z-index: 10;
        }

        .search-section {
            margin-bottom: 25px;
        }

        .search-box {
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .search-box input {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 50px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #1e5631;
            box-shadow: 0 0 0 3px rgba(30,86,49,0.1);
        }

        .section-title {
            font-weight: 700;
            font-size: 16px;
            color: #1e5631;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1e5631;
            display: inline-block;
        }

        .layer-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .layer-item:hover {
            background: #e8f5e9;
            transform: translateX(5px);
        }

        .layer-item label {
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toggle-switch {
            width: 44px;
            height: 24px;
            background: #ccc;
            border-radius: 50px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s;
        }

        .toggle-switch.active {
            background: #1e5631;
        }

        .toggle-switch::after {
            content: '';
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            position: absolute;
            top: 2px;
            left: 3px;
            transition: left 0.3s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        .toggle-switch.active::after {
            left: 21px;
        }

        .filter-group {
            margin-bottom: 20px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 13px;
            color: #555;
        }

        .filter-select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            background: white;
            cursor: pointer;
        }

        .btn-analysis {
            background: linear-gradient(135deg, #1e5631, #2d6a4f);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
            margin-top: 15px;
            transition: all 0.3s;
        }

        .btn-analysis:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30,86,49,0.3);
        }

        .btn-add {
            background: linear-gradient(135deg, #27ae60, #219a52);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39,174,96,0.3);
        }

        /* ============ MAP ============ */
        .map-container {
            flex: 1;
            position: relative;
        }

        #map {
            height: 100%;
            width: 100%;
        }

        /* ============ SIDEBAR KANAN ============ */
        .sidebar-right {
            width: 360px;
            background: white;
            box-shadow: -4px 0 20px rgba(0,0,0,0.05);
            padding: 20px;
            overflow-y: auto;
            z-index: 10;
        }

        .info-card {
            background: linear-gradient(135deg, #f8f9fa, #fff);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .info-header {
            background: linear-gradient(135deg, #1e5631, #2d6a4f);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .info-header h4 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .info-header p {
            font-size: 12px;
            opacity: 0.9;
        }

        .info-body {
            padding: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 13px;
        }

        .info-value {
            font-weight: 500;
            color: #333;
            font-size: 13px;
        }

        .facility-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 15px 0;
        }

        .facility-badge {
            background: #e8f5e9;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            color: #1e5631;
        }

        .btn-detail {
            background: #1e5631;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 12px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            margin-top: 15px;
            transition: all 0.3s;
        }

        .btn-detail:hover {
            background: #2d6a4f;
        }

        .admin-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .admin-btn {
            padding: 10px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            flex: 1;
            transition: all 0.3s;
        }

        .btn-edit {
            background: #f39c12;
            color: white;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .empty-info {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-info i {
            font-size: 50px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* ============ MODAL ============ */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            border-radius: 24px;
            padding: 30px;
            width: 500px;
            max-width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #1e5631;
        }

        .modal-header h3 {
            color: #1e5631;
            font-size: 20px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
            transition: all 0.3s;
        }

        .close-modal:hover {
            color: #e74c3c;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 13px;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #1e5631;
        }

        .checkbox-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: normal;
            cursor: pointer;
        }

        .btn-submit {
            background: linear-gradient(135deg, #1e5631, #2d6a4f);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            cursor: pointer;
            width: 100%;
            font-weight: 700;
            font-size: 16px;
            margin-top: 10px;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30,86,49,0.3);
        }

        .get-coord-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 8px;
        }

        .coord-info {
            background: #e8f5e9;
            padding: 10px;
            border-radius: 10px;
            font-size: 12px;
            margin-top: 8px;
            display: none;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #1e5631;
            border-radius: 10px;
        }

        @media (max-width: 900px) {
            .sidebar-left, .sidebar-right {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
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
            <a href="dashboard.php" class="active">
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

    <!-- Main Content -->
    <div class="main-container">
        <!-- Sidebar Kiri -->
        <div class="sidebar-left">
            <div class="search-section">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-school" placeholder="Cari nama sekolah...">
                </div>
            </div>

            <div class="section-title">Layer Peta</div>
            <div class="layer-item" id="toggle-smpn">
                <label>
                    <i class="fas fa-school" style="color:#e74c3c"></i> Sekolah
                </label>
                <div class="toggle-switch active" id="smpn-toggle"></div>
            </div>
            <div class="layer-item" id="toggle-buffer">
                <label>
                    <i class="fas fa-circle-notch" style="color:#3498db"></i> Buffer 500m
                </label>
                <div class="toggle-switch" id="buffer-toggle"></div>
            </div>

            <div class="section-title" style="margin-top: 25px;">Filter Wilayah</div>
            <div class="filter-group">
                <label>Kecamatan</label>
                <select class="filter-select" id="filter-kecamatan">
                    <option value="all">Semua Kecamatan</option>
                    <option value="Ternate Tengah">Ternate Tengah</option>
                    <option value="Ternate Selatan">Ternate Selatan</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Kelurahan</label>
                <select class="filter-select" id="filter-kelurahan">
                    <option value="all">Semua Kelurahan</option>
                </select>
            </div>

            <button class="btn-analysis" onclick="window.location.href='analisis.php'">
                <i class="fas fa-chart-line"></i> Analisis Nearest Neighbor
            </button>

            <?php if($role == 'admin'): ?>
            <button class="btn-add" onclick="openAddModal()">
                <i class="fas fa-plus-circle"></i> Tambah Lokasi Sekolah
            </button>
            <?php endif; ?>
        </div>

        <!-- Map Container -->
        <div class="map-container">
            <div id="map"></div>
        </div>

        <!-- Sidebar Kanan -->
        <div class="sidebar-right">
            <div id="school-info">
                <div class="info-card">
                    <div class="info-header">
                        <h4><i class="fas fa-info-circle"></i> Informasi Sekolah</h4>
                        <p>Klik pada marker untuk melihat detail</p>
                    </div>
                    <div class="empty-info">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>Belum ada sekolah dipilih</p>
                        <small>Klik marker pada peta</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Sekolah -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle"></i> Tambah Sekolah Baru</h3>
                <button class="close-modal" onclick="closeAddModal()">&times;</button>
            </div>
            <form id="addSchoolForm">
                <div class="form-group">
                    <label>Nama Sekolah</label>
                    <input type="text" name="nama_sekolah" id="nama_sekolah" required placeholder="Contoh: SMP Negeri 10 Ternate">
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" id="alamat" rows="2" required placeholder="Jl. ..."></textarea>
                </div>
                <div class="form-group">
                    <label>Kelurahan</label>
                    <select name="id_kelurahan" id="id_kelurahan" required>
                        <option value="">Pilih Kelurahan</option>
                        <?php while($row = mysqli_fetch_assoc($kelurahan_result)): ?>
                        <option value="<?php echo $row['id_kelurahan']; ?>"><?php echo $row['nama_kelurahan'] . " - " . $row['nama_kecamatan']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Koordinat Lokasi</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="number" step="any" name="latitude" id="latitude" placeholder="Latitude" style="flex:1">
                        <input type="number" step="any" name="longitude" id="longitude" placeholder="Longitude" style="flex:1">
                    </div>
                    <button type="button" class="get-coord-btn" id="pickCoordBtn">
                        <i class="fas fa-map-marker-alt"></i> Ambil dari Peta
                    </button>
                    <div id="coordInfo" class="coord-info"></div>
                </div>
                <div class="form-group">
                    <label>Jumlah Siswa</label>
                    <input type="number" name="jumlah_siswa" id="jumlah_siswa" required>
                </div>
                <div class="form-group">
                    <label>Akreditasi</label>
                    <select name="akreditasi" id="akreditasi">
                        <option value="A">A (Unggul)</option>
                        <option value="B">B (Baik)</option>
                        <option value="C">C (Cukup)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fasilitas</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="laboratorium" value="1"> Laboratorium</label>
                        <label><input type="checkbox" name="perpustakaan" value="1"> Perpustakaan</label>
                        <label><input type="checkbox" name="lapangan_olahraga" value="1"> Lapangan Olahraga</label>
                        <label><input type="checkbox" name="toilet" value="1"> Toilet</label>
                    </div>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Simpan Sekolah
                </button>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // ============ INISIALISASI PETA ============
        var map = L.map('map').setView([0.78, 127.38], 13);
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 19,
            minZoom: 3
        }).addTo(map);
        
        L.control.zoom({ position: 'bottomright' }).addTo(map);
        L.control.scale({ metric: true, imperial: false, position: 'bottomleft' }).addTo(map);
        
        // ============ DATA DARI PHP ============
        var schools = <?php echo json_encode($sekolah_list); ?>;
        var role = '<?php echo $role; ?>';
        var markers = {};
        var bufferLayers = [];
        var pickingCoord = false;
        var tempMarker = null;
        
        console.log("Jumlah sekolah:", schools.length);
        
        // Custom Marker Icon
        var schoolIcon = L.divIcon({
            html: `<div style="background: linear-gradient(135deg, #e74c3c, #c0392b); width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>`,
            iconSize: [22, 22],
            popupAnchor: [0, -11],
            className: 'custom-marker'
        });
        
        // ============ TAMPILKAN MARKER ============
        if(schools.length === 0) {
            alert("Belum ada data sekolah! Silakan tambah data melalui admin panel.");
        } else {
            for(var i = 0; i < schools.length; i++) {
                var school = schools[i];
                
                var popupContent = `
                    <div style="padding: 5px; min-width: 240px;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                            <div style="background: linear-gradient(135deg, #1e5631, #2d6a4f); width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-school" style="color: white; font-size: 18px;"></i>
                            </div>
                            <div>
                                <strong style="font-size: 16px; color: #1e5631;">${school.nama_sekolah}</strong>
                                <p style="font-size: 11px; color: #666; margin-top: 2px;">${school.nama_kecamatan}</p>
                            </div>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <div style="display: flex; gap: 8px; margin-bottom: 6px;">
                                <i class="fas fa-map-pin" style="color: #e74c3c; width: 18px; font-size: 12px;"></i>
                                <span style="font-size: 12px;">${school.alamat}</span>
                            </div>
                            <div style="display: flex; gap: 8px; margin-bottom: 6px;">
                                <i class="fas fa-users" style="color: #3498db; width: 18px; font-size: 12px;"></i>
                                <span style="font-size: 12px;">${school.jumlah_siswa} Siswa</span>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <i class="fas fa-star" style="color: #f39c12; width: 18px; font-size: 12px;"></i>
                                <span style="font-size: 12px;">Akreditasi ${school.akreditasi}</span>
                            </div>
                        </div>
                        <hr style="margin: 10px 0;">
                        <button onclick="showSchoolDetail(${school.id_sekolah})" style="background: linear-gradient(135deg, #1e5631, #2d6a4f); color: white; border: none; padding: 10px; border-radius: 10px; width: 100%; cursor: pointer; font-weight: 600;">
                            <i class="fas fa-info-circle"></i> Lihat Detail
                        </button>
                        ${role === 'admin' ? `
                        <div style="display: flex; gap: 8px; margin-top: 10px;">
                            <button onclick="editSchool(${school.id_sekolah})" style="background: #f39c12; color: white; border: none; padding: 8px; border-radius: 8px; flex: 1; cursor: pointer;">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button onclick="deleteSchool(${school.id_sekolah})" style="background: #e74c3c; color: white; border: none; padding: 8px; border-radius: 8px; flex: 1; cursor: pointer;">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                        ` : ''}
                    </div>
                `;
                
                var marker = L.marker([parseFloat(school.latitude), parseFloat(school.longitude)], {icon: schoolIcon})
                    .addTo(map)
                    .bindPopup(popupContent);
                
                marker.on('click', (function(s) {
                    return function() { showSchoolInfo(s); };
                })(school));
                
                markers[school.id_sekolah] = marker;
            }
            
            // Zoom ke sekolah pertama
            if(schools.length > 0) {
                map.setView([parseFloat(schools[0].latitude), parseFloat(schools[0].longitude)], 13);
            }
        }
        
        // ============ FUNGSI ============
        function showSchoolInfo(school) {
            $.ajax({
                url: 'api/get_fasilitas.php?id=' + school.id_sekolah,
                success: function(fasilitas) {
                    var fas = JSON.parse(fasilitas);
                    var html = `
                        <div class="info-card">
                            <div class="info-header">
                                <h4><i class="fas fa-school"></i> ${school.nama_sekolah}</h4>
                                <p>${school.nama_kecamatan}</p>
                            </div>
                            <div class="info-body">
                                <div class="info-row">
                                    <span class="info-label">NPSN</span>
                                    <span class="info-value">6020${school.id_sekolah}90</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Alamat</span>
                                    <span class="info-value">${school.alamat}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Kelurahan</span>
                                    <span class="info-value">${school.nama_kelurahan}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Kecamatan</span>
                                    <span class="info-value">${school.nama_kecamatan}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Jumlah Siswa</span>
                                    <span class="info-value">${school.jumlah_siswa} siswa</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Akreditasi</span>
                                    <span class="info-value">${school.akreditasi}</span>
                                </div>
                                <div class="facility-badges">
                                    <span class="facility-badge"><i class="fas fa-chalkboard"></i> Ruang Kelas</span>
                                    ${fas.laboratorium ? '<span class="facility-badge"><i class="fas fa-flask"></i> Laboratorium</span>' : ''}
                                    ${fas.perpustakaan ? '<span class="facility-badge"><i class="fas fa-book"></i> Perpustakaan</span>' : ''}
                                    ${fas.lapangan_olahraga ? '<span class="facility-badge"><i class="fas fa-futbol"></i> Lapangan</span>' : ''}
                                    ${fas.toilet ? '<span class="facility-badge"><i class="fas fa-toilet"></i> Toilet</span>' : ''}
                                </div>
                                <button class="btn-detail" onclick="window.location.href='detail_sekolah.php?id=${school.id_sekolah}'">
                                    <i class="fas fa-arrow-right"></i> Halaman Detail Lengkap
                                </button>
                                ${role === 'admin' ? `
                                <div class="admin-buttons">
                                    <button class="admin-btn btn-edit" onclick="editSchool(${school.id_sekolah})">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="admin-btn btn-delete" onclick="deleteSchool(${school.id_sekolah})">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                    $('#school-info').html(html);
                }
            });
        }
        
        function showSchoolDetail(id) {
            window.location.href = 'detail_sekolah.php?id=' + id;
        }
        
        <?php if($role == 'admin'): ?>
        function editSchool(id) {
            window.location.href = 'admin/edit_sekolah.php?id=' + id;
        }
        
        function deleteSchool(id) {
    if(confirm('⚠️ Yakin ingin menghapus sekolah ini? Data akan hilang permanen.')) {
        $.ajax({
            url: 'admin/hapus_sekolah.php',
            method: 'GET',
            data: { id: id },
            success: function(response) {
                if(response.trim() === 'success') {
                    alert('✅ Sekolah berhasil dihapus!');
                    location.reload();
                } else {
                    alert('❌ Gagal: ' + response);
                }
            },
            error: function() {
                // Jika error, coba redirect manual
                window.location.href = 'admin/hapus_sekolah.php?id=' + id;
            }
        });
    }
}
        
        // Modal functions
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
            document.getElementById('addSchoolForm').reset();
            document.getElementById('latitude').value = '';
            document.getElementById('longitude').value = '';
            document.getElementById('coordInfo').style.display = 'none';
        }
        
        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
            if(pickingCoord) disablePickMode();
        }
        
        function enablePickMode() {
            pickingCoord = true;
            var infoDiv = document.getElementById('coordInfo');
            infoDiv.style.display = 'block';
            infoDiv.innerHTML = '<i class="fas fa-info-circle"></i> 🔴 Klik pada peta untuk memilih lokasi sekolah';
            infoDiv.style.background = '#fff3cd';
            map.getContainer().style.cursor = 'crosshair';
            map.on('click', onMapPick);
        }
        
        function disablePickMode() {
            pickingCoord = false;
            document.getElementById('coordInfo').style.display = 'none';
            map.getContainer().style.cursor = '';
            map.off('click', onMapPick);
            if(tempMarker) {
                map.removeLayer(tempMarker);
                tempMarker = null;
            }
        }
        
        function onMapPick(e) {
            if(!pickingCoord) return;
            var lat = e.latlng.lat.toFixed(6);
            var lng = e.latlng.lng.toFixed(6);
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            document.getElementById('coordInfo').innerHTML = '<i class="fas fa-check-circle"></i> ✅ Koordinat: ' + lat + ', ' + lng;
            document.getElementById('coordInfo').style.background = '#d4edda';
            if(tempMarker) map.removeLayer(tempMarker);
            tempMarker = L.marker([lat, lng]).addTo(map);
            setTimeout(function() { disablePickMode(); }, 2000);
        }
        
        document.getElementById('pickCoordBtn').addEventListener('click', function() {
            if(pickingCoord) disablePickMode();
            else enablePickMode();
        });
        
        $('#addSchoolForm').on('submit', function(e) {
            e.preventDefault();
            var formData = {
                nama_sekolah: $('#nama_sekolah').val(),
                alamat: $('#alamat').val(),
                id_kelurahan: $('#id_kelurahan').val(),
                latitude: $('#latitude').val(),
                longitude: $('#longitude').val(),
                jumlah_siswa: $('#jumlah_siswa').val(),
                akreditasi: $('#akreditasi').val(),
                laboratorium: $('input[name="laboratorium"]').is(':checked') ? 1 : 0,
                perpustakaan: $('input[name="perpustakaan"]').is(':checked') ? 1 : 0,
                lapangan_olahraga: $('input[name="lapangan_olahraga"]').is(':checked') ? 1 : 0,
                toilet: $('input[name="toilet"]').is(':checked') ? 1 : 0
            };
            
            if(!formData.latitude || !formData.longitude) {
                alert('⚠️ Harap isi koordinat lokasi sekolah!');
                return;
            }
            
            $.ajax({
                url: 'api/tambah_sekolah.php',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        alert('✅ ' + response.message);
                        closeAddModal();
                        location.reload();
                    } else {
                        alert('❌ Gagal: ' + response.message);
                    }
                },
                error: function() {
                    alert('❌ Terjadi kesalahan pada server!');
                }
            });
        });
        
        window.onclick = function(event) {
            var modal = document.getElementById('addModal');
            if(event.target == modal) closeAddModal();
        }
        <?php endif; ?>
        
        // ============ BUFFER ============
        function createBuffer(lat, lng, radius = 0.5) {
            var points = [];
            var earthRadius = 6371;
            var rad = radius / earthRadius;
            var latRad = lat * Math.PI / 180;
            var lngRad = lng * Math.PI / 180;
            for(var i = 0; i <= 360; i++) {
                var bearing = i * Math.PI / 180;
                var lat2 = Math.asin(Math.sin(latRad) * Math.cos(rad) + Math.cos(latRad) * Math.sin(rad) * Math.cos(bearing));
                var lng2 = lngRad + Math.atan2(Math.sin(bearing) * Math.sin(rad) * Math.cos(latRad), Math.cos(rad) - Math.sin(latRad) * Math.sin(lat2));
                points.push([lat2 * 180 / Math.PI, lng2 * 180 / Math.PI]);
            }
            return L.polygon(points, {
                color: '#3498db',
                weight: 2,
                fillColor: '#3498db',
                fillOpacity: 0.15,
                smoothFactor: 1
            });
        }
        
        $('#toggle-buffer').click(function() {
            var active = $('#buffer-toggle').hasClass('active');
            if(active) {
                bufferLayers.forEach(layer => map.removeLayer(layer));
                bufferLayers = [];
                $('#buffer-toggle').removeClass('active');
            } else {
                schools.forEach(school => {
                    var buffer = createBuffer(parseFloat(school.latitude), parseFloat(school.longitude));
                    buffer.addTo(map);
                    bufferLayers.push(buffer);
                });
                $('#buffer-toggle').addClass('active');
            }
        });
        
        // ============ PENCARIAN ============
       var searchTimeout;
$('#search-school').on('keyup', function() {
    clearTimeout(searchTimeout);
    var query = $(this).val().toLowerCase().trim();
    
    // Jika input kosong, tampilkan semua marker
    if(query === '') {
        for(var id in markers) {
            map.addLayer(markers[id]);
        }
        $('#search-school').css('border-color', '#e0e0e0');
        return;
    }
    
    // Delay search untuk performance
    searchTimeout = setTimeout(function() {
        var foundSchools = [];
        
        // Cari sekolah yang cocok (lebih fleksibel)
        for(var i = 0; i < schools.length; i++) {
            var school = schools[i];
            var namaSekolah = school.nama_sekolah.toLowerCase();
            var alamat = school.alamat.toLowerCase();
            var kelurahan = school.nama_kelurahan.toLowerCase();
            var kecamatan = school.nama_kecamatan.toLowerCase();
            
            // Cari di nama, alamat, kelurahan, atau kecamatan
            if(namaSekolah.includes(query) || 
               alamat.includes(query) || 
               kelurahan.includes(query) || 
               kecamatan.includes(query)) {
                foundSchools.push(school);
            }
        }
        
        if(foundSchools.length > 0) {
            // Sembunyikan semua marker dulu
            for(var id in markers) {
                map.removeLayer(markers[id]);
            }
            
            // Tampilkan semua marker yang ditemukan
            for(var i = 0; i < foundSchools.length; i++) {
                var school = foundSchools[i];
                map.addLayer(markers[school.id_sekolah]);
            }
            
            // Jika hanya 1 hasil, zoom ke lokasi dan buka popup
            if(foundSchools.length === 1) {
                var school = foundSchools[0];
                map.setView([school.latitude, school.longitude], 16);
                markers[school.id_sekolah].openPopup();
                showSchoolInfo(school);
                $('#search-school').css('border-color', '#27ae60');
            } else {
                // Jika banyak hasil, zoom ke area tengah
                var bounds = [];
                for(var i = 0; i < foundSchools.length; i++) {
                    bounds.push([foundSchools[i].latitude, foundSchools[i].longitude]);
                }
                var group = new L.featureGroup();
                for(var i = 0; i < foundSchools.length; i++) {
                    L.marker([foundSchools[i].latitude, foundSchools[i].longitude]).addTo(group);
                }
                map.fitBounds(group.getBounds());
                map.setZoom(13);
                $('#search-school').css('border-color', '#27ae60');
            }
            
            // Tampilkan pesan jumlah hasil
            console.log("Ditemukan " + foundSchools.length + " sekolah");
        } else {
            // Tidak ditemukan
            $('#search-school').css('border-color', '#e74c3c');
            // Tampilkan semua marker lagi
            for(var id in markers) {
                map.addLayer(markers[id]);
            }
            alert('Sekolah "' + query + '" tidak ditemukan!');
        }
        
        // Reset border color setelah 2 detik
        setTimeout(function() {
            if($('#search-school').val().toLowerCase().trim() !== '') {
                $('#search-school').css('border-color', '#e0e0e0');
            }
        }, 2000);
    }, 300);
});
        
        // ============ FILTER ============
        $('#filter-kecamatan').change(function() {
            var kec = $(this).val();
            var kelSelect = $('#filter-kelurahan');
            kelSelect.html('<option value="all">Semua Kelurahan</option>');
            var kelSet = new Set();
            
            for(var id in markers) {
                var school = schools.find(s => s.id_sekolah == id);
                if(kec == 'all' || school.nama_kecamatan == kec) {
                    map.addLayer(markers[id]);
                    kelSet.add(school.nama_kelurahan);
                } else {
                    map.removeLayer(markers[id]);
                }
            }
            kelSet.forEach(kel => {
                kelSelect.append(`<option value="${kel}">${kel}</option>`);
            });
        });
        
        $('#filter-kelurahan').change(function() {
            var kel = $(this).val();
            for(var id in markers) {
                var school = schools.find(s => s.id_sekolah == id);
                if(kel == 'all' || school.nama_kelurahan == kel) {
                    map.addLayer(markers[id]);
                } else {
                    map.removeLayer(markers[id]);
                }
            }
        });
        
        // Toggle marker layer
        $('#toggle-smpn').click(function() {
            var active = $('#smpn-toggle').hasClass('active');
            if(active) {
                for(var id in markers) map.removeLayer(markers[id]);
                $('#smpn-toggle').removeClass('active');
            } else {
                for(var id in markers) map.addLayer(markers[id]);
                $('#smpn-toggle').addClass('active');
            }
        });
    </script>
</body>
</html>