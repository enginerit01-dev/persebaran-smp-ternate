<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

redirectIfNotAdmin();

$total_sekolah = db_fetch_assoc(db_query("SELECT COUNT(*) as total FROM smpn"))['total'];
$total_kelurahan = db_fetch_assoc(db_query("SELECT COUNT(*) as total FROM kelurahan"))['total'];
$total_kecamatan = db_fetch_assoc(db_query("SELECT COUNT(*) as total FROM kecamatan"))['total'];
$total_user = db_fetch_assoc(db_query("SELECT COUNT(*) as total FROM users"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - WebGIS SMPN</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; }
        .admin-container { display: flex; }
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1e5631 0%, #0d2818 100%);
            color: white;
            min-height: 100vh;
            padding: 20px 0;
        }
        .sidebar h3 { text-align: center; margin-bottom: 30px; }
        .sidebar a {
            display: block;
            padding: 12px 25px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.2); padding-left: 30px; }
        .content { flex: 1; padding: 30px; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .stat-card h3 { color: #666; font-size: 14px; margin-bottom: 10px; }
        .stat-card .number { font-size: 32px; font-weight: 700; color: #1e5631; }
        .welcome { background: white; padding: 20px; border-radius: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h3><i class="fas fa-map-marked-alt"></i> Admin Panel</h3>
            <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="sekolah.php"><i class="fas fa-school"></i> Data Sekolah</a>
            <a href="fasilitas.php"><i class="fas fa-building"></i> Fasilitas</a>
            <a href="../dashboard.php"><i class="fas fa-arrow-left"></i> Kembali ke Peta</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        <div class="content">
            <div class="welcome">
                <h2>Selamat Datang, Admin!</h2>
                <p>Kelola data sekolah, fasilitas, dan pengguna sistem WebGIS SMPN Ternate.</p>
            </div>
            <div class="stats">
                <div class="stat-card"><h3>SMP Negeri</h3><div class="number"><?php echo $total_sekolah; ?></div></div>
                <div class="stat-card"><h3>Kelurahan</h3><div class="number"><?php echo $total_kelurahan; ?></div></div>
                <div class="stat-card"><h3>Kecamatan</h3><div class="number"><?php echo $total_kecamatan; ?></div></div>
                <div class="stat-card"><h3>User</h3><div class="number"><?php echo $total_user; ?></div></div>
            </div>
        </div>
    </div>
</body>
</html>
