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


// Ambil data sekolah
$query = "
    SELECT
        id_sekolah,
        nama_sekolah,
        latitude,
        longitude
    FROM smpn
";

$result = pg_query($conn, $query);


// Cek query
if(!$result){

    die("
        <h3 style='color:red'>
            Query database gagal
        </h3>
    ");
}


// Simpan data sekolah
$schools = [];

while($row = pg_fetch_assoc($result)) {

    $schools[] = $row;
}


// ========================
// CEK DATA
// ========================

$hasData = count($schools) > 0;


// ========================
// FUNGSI HAVERSINE
// ========================

function haversineDistance($lat1, $lon1, $lat2, $lon2) {

    $earthRadius = 6371;

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a =
        sin($dLat / 2) * sin($dLat / 2)
        +
        cos(deg2rad($lat1))
        *
        cos(deg2rad($lat2))
        *
        sin($dLon / 2)
        *
        sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c;
}


// ========================
// DEFAULT VALUE
// ========================

$mean_distance = 0;

$density = 0;

$expected_mean = 0;

$nnr = 0;

$pattern = "Tidak Ada Data";

$pattern_class = "random";

$pattern_desc =
"Belum ada data sekolah.
Silakan tambah data sekolah terlebih dahulu.";

$detail_distances = [];


// ========================
// PROSES NNA
// ========================

if($hasData) {

    $distances = [];

    $total_distance = 0;


    foreach($schools as $i => $school) {

        $min_distance = PHP_FLOAT_MAX;

        foreach($schools as $j => $other) {

            if($i != $j) {

                $distance = haversineDistance(
                    $school['latitude'],
                    $school['longitude'],
                    $other['latitude'],
                    $other['longitude']
                );

                if($distance < $min_distance) {

                    $min_distance = $distance;
                }
            }
        }

        if($min_distance != PHP_FLOAT_MAX) {

            $distances[] = $min_distance;

            $total_distance += $min_distance;
        }
    }


    // Mean Distance
    $mean_distance =
        count($distances) > 0
        ?
        $total_distance / count($distances)
        :
        0;


    // Density
    $area = 50;

    $density = count($schools) / $area;


    // Expected Mean
    if($density > 0) {

        $expected_mean =
            1 / (2 * sqrt($density));

        $nnr =
            $mean_distance > 0
            ?
            $mean_distance / $expected_mean
            :
            0;

    } else {

        $expected_mean = 0;

        $nnr = 0;
    }


    // ========================
    // POLA PERSEBARAN
    // ========================

    if($nnr < 0.7 && $nnr > 0) {

        $pattern = "Mengelompok (Clustered)";

        $pattern_class = "clustered";

        $pattern_desc =
        "Persebaran SMPN cenderung mengelompok
        pada wilayah tertentu.";

    }
    elseif($nnr > 1.3) {

        $pattern = "Merata (Uniform)";

        $pattern_class = "uniform";

        $pattern_desc =
        "Persebaran SMPN cenderung merata
        di wilayah penelitian.";

    }
    elseif($nnr > 0) {

        $pattern = "Acak (Random)";

        $pattern_class = "random";

        $pattern_desc =
        "Persebaran SMPN bersifat acak
        di wilayah penelitian.";
    }


    // ========================
    // DETAIL JARAK TERDEKAT
    // ========================

    foreach($schools as $i => $school) {

        $min_distance = PHP_FLOAT_MAX;

        $nearest_school = null;

        foreach($schools as $j => $other) {

            if($i != $j) {

                $distance = haversineDistance(
                    $school['latitude'],
                    $school['longitude'],
                    $other['latitude'],
                    $other['longitude']
                );

                if($distance < $min_distance) {

                    $min_distance = $distance;

                    $nearest_school = $other;
                }
            }
        }

        if($nearest_school) {

            $detail_distances[] = [

                'school' =>
                $school['nama_sekolah'],

                'nearest' =>
                $nearest_school['nama_sekolah'],

                'distance' =>
                $min_distance
            ];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Analisis Nearest Neighbor - WebGIS SMPN Ternate</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* ============ CARD ============ */
        .card {
            background: white;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-3px);
        }

        .card h2 {
            color: #1e5631;
            font-size: 22px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid #1e5631;
            padding-left: 15px;
        }

        /* ============ GRID ============ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: linear-gradient(135deg, #f8f9fa, #fff);
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            border: 1px solid #eee;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: #e8f5e9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .stat-icon i {
            font-size: 28px;
            color: #1e5631;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 800;
            color: #1e5631;
        }

        .stat-label {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }

        /* ============ PATTERN BADGE ============ */
        .pattern-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 18px;
            margin: 20px 0;
        }

        .pattern-clustered {
            background: #ff7675;
            color: white;
        }

        .pattern-uniform {
            background: #55efc4;
            color: #1e5631;
        }

        .pattern-random {
            background: #74b9ff;
            color: white;
        }

        /* ============ TABLE ============ */
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #1e5631;
            color: white;
            font-weight: 600;
        }

        tr:hover {
            background: #f8f9fa;
        }

        /* ============ CHART ============ */
        .chart-container {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 16px;
        }

        canvas {
            max-height: 400px;
            width: 100%;
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

        /* ============ INFO BOX ============ */
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px 20px;
            border-radius: 12px;
            margin: 20px 0;
        }

        .info-box p {
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }

        .alert-warning {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .alert-warning i {
            font-size: 48px;
            color: #ff9800;
            margin-bottom: 15px;
            display: block;
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 10px;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .stat-value {
                font-size: 24px;
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
            <a href="analisis.php" class="active">
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

    <div class="container">
        <div class="card">
            <h2>
                <i class="fas fa-chart-line"></i>
                Analisis Nearest Neighbor (NNR)
            </h2>
            <p style="color: #666; margin-bottom: 20px;">
                Nearest Neighbor Analysis digunakan untuk mengetahui pola persebaran SMPN di Kecamatan Ternate Tengah dan Ternate Selatan.
                Metode ini menghitung jarak rata-rata antar sekolah dan membandingkannya dengan jarak yang diharapkan dalam pola acak.
            </p>

            <?php if(!$hasData): ?>
                <!-- Alert jika tidak ada data -->
                <div class="alert-warning">
                    <i class="fas fa-database"></i>
                    <h3 style="margin-bottom: 10px;">Belum Ada Data Sekolah</h3>
                    <p>Silakan tambah data sekolah terlebih dahulu melalui menu <strong>Data Sekolah</strong> atau klik tombol <strong>"Tambah Lokasi Sekolah"</strong> di halaman peta.</p>
                    <button onclick="window.location.href='dashboard.php'" style="margin-top: 15px; background: #1e5631; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">
                        <i class="fas fa-arrow-left"></i> Kembali ke Peta
                    </button>
                </div>
            <?php else: ?>
                <!-- Statistik Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-school"></i>
                        </div>
                        <div class="stat-value"><?php echo count($schools); ?></div>
                        <div class="stat-label">Jumlah SMPN</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-map-area"></i>
                        </div>
                        <div class="stat-value">50</div>
                        <div class="stat-label">Luas Wilayah (km²)</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-simple"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($density, 4); ?></div>
                        <div class="stat-label">Kepadatan (sekolah/km²)</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-arrows-left-right"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($mean_distance, 3); ?></div>
                        <div class="stat-label">Rata-rata Jarak Observasi (km)</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($expected_mean, 3); ?></div>
                        <div class="stat-label">Jarak Harapan (km)</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-percent"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($nnr, 3); ?></div>
                        <div class="stat-label">Nilai NNR (J<sub>obs</sub>/J<sub>exp</sub>)</div>
                    </div>
                </div>

                <!-- Pola Persebaran -->
                <div style="text-align: center;">
                    <div class="pattern-badge pattern-<?php echo $pattern_class; ?>">
                        <i class="fas <?php echo $nnr < 0.7 ? 'fa-circle' : ($nnr > 1.3 ? 'fa-square' : 'fa-random'); ?>"></i>
                        Pola Persebaran: <?php echo $pattern; ?>
                    </div>
                    <div class="info-box">
                        <p>
                            <strong>Interpretasi:</strong> <?php echo $pattern_desc; ?>
                        </p>
                        <p style="margin-top: 10px;">
                            <small>
                                <strong>Rumus NNR:</strong> NNR = J<sub>obs</sub> / J<sub>exp</sub><br>
                                • NNR < 0,7 → Mengelompok (Clustered)<br>
                                • 0,7 ≤ NNR ≤ 1,3 → Acak (Random)<br>
                                • NNR > 1,3 → Merata (Uniform)
                            </small>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if($hasData): ?>
        <!-- Card Chart -->
        <div class="card">
            <h2>
                <i class="fas fa-chart-bar"></i>
                Visualisasi Perbandingan Jarak
            </h2>
            <div class="chart-container">
                <canvas id="nnrChart"></canvas>
            </div>
        </div>

        <!-- Card Detail Jarak Antar Sekolah -->
        <div class="card">
            <h2>
                <i class="fas fa-table-list"></i>
                Detail Jarak Terdekat Antar Sekolah
            </h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Sekolah</th>
                            <th>Sekolah Terdekat</th>
                            <th>Jarak (km)</th>
                        </thead>
                    <tbody>
                        <?php $no = 1; foreach($detail_distances as $detail): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><strong><?php echo htmlspecialchars($detail['school']); ?></strong></td>
                            <td><?php echo htmlspecialchars($detail['nearest']); ?></td>
                            <td><?php echo number_format($detail['distance'], 3); ?> km</td>
                        </table>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tombol Kembali -->
        <div style="text-align: center;">
            <button class="btn-back" onclick="window.location.href='dashboard.php'">
                <i class="fas fa-arrow-left"></i> Kembali ke Peta
            </button>
        </div>
        <?php endif; ?>
    </div>

    <?php if($hasData): ?>
    <script>
        // Chart.js - Perbandingan Jarak Observasi vs Jarak Harapan
        var ctx = document.getElementById('nnrChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jarak Observasi (J<sub>obs</sub>)', 'Jarak Harapan (J<sub>exp</sub>)'],
                datasets: [{
                    label: 'Jarak (km)',
                    data: [<?php echo $mean_distance; ?>, <?php echo $expected_mean; ?>],
                    backgroundColor: ['#1e5631', '#3498db'],
                    borderRadius: 10,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'Inter', size: 12 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw.toFixed(3) + ' km';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jarak (km)',
                            font: { family: 'Inter', weight: 'bold' }
                        },
                        grid: { color: '#e0e0e0' }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Jenis Jarak',
                            font: { family: 'Inter', weight: 'bold' }
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>