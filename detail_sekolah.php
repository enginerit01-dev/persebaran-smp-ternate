<?php

session_start();

require_once 'config/database.php';
require_once 'config/auth.php';

if(!isLoggedIn()) {

    header("Location: login.php");
    exit();
}


// Ambil ID sekolah
$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


// Query data sekolah
$query = "
    SELECT
        s.*,

        k.nama_kelurahan,

        kc.nama_kecamatan

    FROM smpn s

    LEFT JOIN kelurahan k
    ON s.id_kelurahan = k.id_kelurahan

    LEFT JOIN kecamatan kc
    ON k.id_kecamatan = kc.id_kecamatan

    WHERE s.id_sekolah = $id
";

$result = pg_query($conn, $query);


// Cek query berhasil
if(!$result){

    die("
        <h3 style='color:red'>
            Query database gagal
        </h3>
    ");
}


// Ambil data sekolah
$sekolah = pg_fetch_assoc($result);


// Jika data tidak ditemukan
if(!$sekolah) {

    header("Location: dashboard.php");
    exit();
}
// Ambil fasilitas
$query_fas = "SELECT * FROM fasilitas WHERE id_sekolah = $id";
$result_fas = pg_query($conn, $query_fas);
$fasilitas = pg_fetch_assoc($result_fas);

if(!$fasilitas) {
    $fasilitas = ['laboratorium' => false, 'perpustakaan' => false, 'lapangan_olahraga' => false, 'toilet' => true];
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Sekolah - <?php echo htmlspecialchars($sekolah['nama_sekolah']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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

        /* Navbar */
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 12px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            background: linear-gradient(135deg, #1e5631, #2d6a4f);
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .logo-text h2 {
            font-size: 18px;
            color: #1e5631;
        }

        .logo-text p {
            font-size: 10px;
            color: #666;
        }

        .nav-menu {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-menu a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            font-size: 14px;
            padding: 8px 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .nav-menu a:hover {
            background: #1e5631;
            color: white;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-left: 15px;
            border-left: 1px solid #ddd;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            background: #1e5631;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        /* Container */
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Card */
        .detail-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        /* Header */
        .detail-header {
            background: linear-gradient(135deg, #1e5631, #2d6a4f);
            color: white;
            padding: 25px;
            text-align: center;
        }

        .detail-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .detail-header p {
            font-size: 13px;
            opacity: 0.9;
        }

        /* Body */
        .detail-body {
            padding: 25px;
        }

        /* Grid Info */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .info-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            border-left: 4px solid #1e5631;
        }

        .info-card .label {
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-card .value {
            font-size: 16px;
            font-weight: 700;
            color: #333;
            margin-top: 5px;
        }

        /* Fasilitas */
        .fasilitas-section {
            margin-bottom: 25px;
        }

        .fasilitas-section h3 {
            color: #1e5631;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .fasilitas-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .fasilitas-item {
            background: #e8f5e9;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #1e5631;
        }

        /* Map */
        .map-section {
            margin-bottom: 25px;
        }

        .map-section h3 {
            color: #1e5631;
            margin-bottom: 15px;
            font-size: 18px;
        }

        #map {
            height: 350px;
            width: 100%;
            border-radius: 12px;
            z-index: 1;
        }

        .coord-info {
            background: #f0f2f5;
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn-google-maps {
            background: #1e5631;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
        }

        /* Analysis */
        .analysis-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .analysis-section h3 {
            color: #1e5631;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .analysis-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .analysis-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
        }

        .btn-buffer {
            background: #3498db;
            color: white;
        }

        .btn-nn {
            background: #27ae60;
            color: white;
        }

        #analysis-result {
            margin-top: 15px;
            padding: 12px;
            background: white;
            border-radius: 8px;
            display: none;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }

        .action-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back {
            background: #95a5a6;
            color: white;
        }

        .btn-edit {
            background: #f39c12;
            color: white;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 10px;
            }
            .info-grid {
                grid-template-columns: 1fr;
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
            <a href="dashboard.php"><i class="fas fa-map"></i> Peta</a>
            <a href="data_sekolah.php"><i class="fas fa-school"></i> Data Sekolah</a>
            <a href="analisis.php"><i class="fas fa-chart-line"></i> Analisis</a>
            <a href="tentang.php"><i class="fas fa-info-circle"></i> Tentang</a>
            <a href="bantuan.php"><i class="fas fa-question-circle"></i> Bantuan</a>
            <div class="user-info">
                <div class="user-avatar"><i class="fas fa-user"></i></div>
                <span><?php echo $username; ?></span>
                <a href="logout.php" style="color:#dc2626;"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="detail-card">
            <div class="detail-header">
                <h1><i class="fas fa-school"></i> <?php echo htmlspecialchars($sekolah['nama_sekolah']); ?></h1>
                <p><?php echo $sekolah['nama_kecamatan']; ?> | Terakreditasi <?php echo $sekolah['akreditasi']; ?></p>
            </div>
            
            <div class="detail-body">
                <!-- Informasi Grid -->
                <div class="info-grid">
                    <div class="info-card">
                        <div class="label">NPSN</div>
                        <div class="value">6020<?php echo $sekolah['id_sekolah']; ?>90</div>
                    </div>
                    <div class="info-card">
                        <div class="label">Status</div>
                        <div class="value"><?php echo $sekolah['status']; ?></div>
                    </div>
                    <div class="info-card">
                        <div class="label">Akreditasi</div>
                        <div class="value">
                            <span style="background: <?php echo $sekolah['akreditasi'] == 'A' ? '#27ae60' : ($sekolah['akreditasi'] == 'B' ? '#f39c12' : '#e74c3c'); ?>; color:white; padding:2px 10px; border-radius:20px; font-size:12px;">
                                <?php echo $sekolah['akreditasi']; ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="label">Jumlah Siswa</div>
                        <div class="value"><?php echo number_format($sekolah['jumlah_siswa']); ?> Siswa</div>
                    </div>
                    <div class="info-card">
                        <div class="label">Alamat</div>
                        <div class="value"><?php echo htmlspecialchars($sekolah['alamat']); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="label">Kelurahan</div>
                        <div class="value"><?php echo $sekolah['nama_kelurahan']; ?></div>
                    </div>
                    <div class="info-card">
                        <div class="label">Kecamatan</div>
                        <div class="value"><?php echo $sekolah['nama_kecamatan']; ?></div>
                    </div>
                    <div class="info-card">
                        <div class="label">Kota / Provinsi</div>
                        <div class="value">Ternate, Maluku Utara</div>
                    </div>
                </div>

                <!-- Fasilitas -->
                <div class="fasilitas-section">
                    <h3><i class="fas fa-building"></i> Fasilitas</h3>
                    <div class="fasilitas-list">
                        <div class="fasilitas-item"><i class="fas fa-chalkboard"></i> Ruang Kelas</div>
                        <?php if($fasilitas['laboratorium']): ?>
                        <div class="fasilitas-item"><i class="fas fa-flask"></i> Laboratorium</div>
                        <?php endif; ?>
                        <?php if($fasilitas['perpustakaan']): ?>
                        <div class="fasilitas-item"><i class="fas fa-book"></i> Perpustakaan</div>
                        <?php endif; ?>
                        <div class="fasilitas-item"><i class="fas fa-chalkboard-teacher"></i> Ruang Guru</div>
                        <?php if($fasilitas['lapangan_olahraga']): ?>
                        <div class="fasilitas-item"><i class="fas fa-futbol"></i> Lapangan Olahraga</div>
                        <?php endif; ?>
                        <?php if($fasilitas['toilet']): ?>
                        <div class="fasilitas-item"><i class="fas fa-toilet"></i> Toilet</div>
                        <?php endif; ?>
                        <div class="fasilitas-item"><i class="fas fa-wifi"></i> Internet</div>
                        <div class="fasilitas-item"><i class="fas fa-plug"></i> Listrik</div>
                    </div>
                </div>

                <!-- Peta Lokasi -->
                <div class="map-section">
                    <h3><i class="fas fa-map-marker-alt"></i> Lokasi Sekolah</h3>
                    <div id="map"></div>
                    <div class="coord-info">
                        <span><i class="fas fa-location-dot"></i> Koordinat: <?php echo $sekolah['latitude']; ?>, <?php echo $sekolah['longitude']; ?></span>
                        <button class="btn-google-maps" onclick="window.open('https://www.google.com/maps?q=<?php echo $sekolah['latitude']; ?>,<?php echo $sekolah['longitude']; ?>', '_blank')">
                            <i class="fab fa-google"></i> Buka di Google Maps
                        </button>
                    </div>
                </div>

                <!-- Analisis -->
                <div class="analysis-section">
                    <h3><i class="fas fa-chart-line"></i> Analisis Spasial</h3>
                    <div class="analysis-buttons">
                        <button class="analysis-btn btn-buffer" onclick="showBuffer()">
                            <i class="fas fa-circle-notch"></i> Buffer 500m
                        </button>
                        <button class="analysis-btn btn-nn" onclick="showNearestNeighbor()">
                            <i class="fas fa-chart-line"></i> Nearest Neighbor
                        </button>
                    </div>
                    <div id="analysis-result"></div>
                </div>

                <!-- Tombol Aksi -->
                <div class="action-buttons">
                    <button class="action-btn btn-back" onclick="history.back()">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    <?php if($role == 'admin'): ?>
                    <button class="action-btn btn-edit" onclick="window.location.href='admin/edit_sekolah.php?id=<?php echo $sekolah['id_sekolah']; ?>'">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="action-btn btn-delete" onclick="deleteSchool(<?php echo $sekolah['id_sekolah']; ?>)">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS - Load dengan urutan yang benar -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Pastikan Leaflet sudah load sebelum membuat peta
        document.addEventListener('DOMContentLoaded', function() {
            // Cek apakah Leaflet tersedia
            if(typeof L === 'undefined') {
                console.error('Leaflet tidak terload!');
                document.getElementById('map').innerHTML = '<div style="background:#f8d7da; padding:20px; text-align:center; border-radius:12px;">❌ Error: Peta tidak dapat dimuat. Silakan refresh halaman.</div>';
                return;
            }
            
            // Koordinat sekolah
            var lat = <?php echo $sekolah['latitude']; ?>;
            var lng = <?php echo $sekolah['longitude']; ?>;
            
            // Inisialisasi peta
            var map = L.map('map').setView([lat, lng], 15);
            
            // Tile layer
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(map);
            
            // Custom Marker
            var schoolIcon = L.divIcon({
                html: '<div style="background: #e74c3c; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>',
                iconSize: [22, 22],
                popupAnchor: [0, -11],
                className: 'custom-marker'
            });
            
            // Tambah marker
            var marker = L.marker([lat, lng], {icon: schoolIcon})
                .addTo(map)
                .bindPopup('<b><?php echo addslashes($sekolah['nama_sekolah']); ?></b><br><?php echo addslashes($sekolah['alamat']); ?>')
                .openPopup();
            
            // Simpan map ke global variable untuk fungsi lain
            window.detailMap = map;
            window.schoolLat = lat;
            window.schoolLng = lng;
        });
        
        // Buffer Layer
        var bufferLayer = null;
        
        function createBuffer(lat, lng, radius) {
            if(!window.detailMap) return;
            if(bufferLayer) window.detailMap.removeLayer(bufferLayer);
            
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
            
            bufferLayer = L.polygon(points, {
                color: '#3498db',
                weight: 2,
                fillColor: '#3498db',
                fillOpacity: 0.15
            }).addTo(window.detailMap);
            
            return bufferLayer;
        }
        
        function showBuffer() {
            if(bufferLayer) {
                window.detailMap.removeLayer(bufferLayer);
                bufferLayer = null;
                $('#analysis-result').hide();
            } else {
                createBuffer(window.schoolLat, window.schoolLng, 0.5);
                $('#analysis-result').html(`
                    <div style="background:#e8f5e9; padding:12px; border-radius:8px;">
                        <strong><i class="fas fa-circle-notch"></i> Buffer 500m</strong><br>
                        Area biru menunjukkan jangkauan 500 meter dari sekolah.
                    </div>
                `).show();
            }
        }
        
        function showNearestNeighbor() {
            $.ajax({
                url: 'backend/get_nearest.php',
                method: 'GET',
                success: function(response) {
                    var data = JSON.parse(response);
                    var schoolName = '<?php echo addslashes($sekolah['nama_sekolah']); ?>';
                    var schoolData = data.find(d => d.school === schoolName);
                    
                    if(schoolData) {
                        $('#analysis-result').html(`
                            <div style="background:#e3f2fd; padding:12px; border-radius:8px;">
                                <strong><i class="fas fa-chart-line"></i> Nearest Neighbor</strong><br>
                                Sekolah terdekat: <strong>${schoolData.nearest}</strong><br>
                                Jarak: <strong>${schoolData.distance} km</strong>
                            </div>
                        `).show();
                    } else {
                        $('#analysis-result').html(`
                            <div style="background:#fff3e0; padding:12px; border-radius:8px;">
                                <i class="fas fa-info-circle"></i> Data nearest neighbor sedang diproses.
                            </div>
                        `).show();
                    }
                }
            });
        }
        
        <?php if($role == 'admin'): ?>
        function deleteSchool(id) {
            if(confirm('⚠️ Yakin ingin menghapus sekolah <?php echo addslashes($sekolah['nama_sekolah']); ?>?')) {
                $.ajax({
                    url: 'admin/hapus_sekolah.php',
                    method: 'GET',
                    data: { id: id },
                    success: function(response) {
                        if(response.trim() === 'success') {
                            alert('✅ Sekolah berhasil dihapus!');
                            window.location.href = 'dashboard.php';
                        } else {
                            alert('❌ Gagal menghapus!');
                        }
                    }
                });
            }
        }
        <?php endif; ?>
    </script>
</body>
</html>