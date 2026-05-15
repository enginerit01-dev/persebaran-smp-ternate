<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Ambil data sekolah
$query = "SELECT s.*, k.nama_kelurahan, kc.nama_kecamatan 
          FROM smpn s 
          LEFT JOIN kelurahan k ON s.id_kelurahan = k.id_kelurahan 
          LEFT JOIN kecamatan kc ON k.id_kecamatan = kc.id_kecamatan
          ORDER BY s.nama_sekolah";
$result = db_query($query);

// Cek pesan session
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '';
unset($_SESSION['message']);
unset($_SESSION['message_type']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Data Sekolah</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { display: flex; align-items: center; gap: 10px; }
        .logo-icon { background: linear-gradient(135deg, #1e5631, #2d6a4f); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; }
        .nav-menu a { text-decoration: none; color: #333; margin-left: 25px; font-weight: 500; }
        .container { max-width: 1300px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .card h2 { color: #1e5631; margin-bottom: 20px; }
        .btn-add { background: #1e5631; color: white; border: none; padding: 12px 24px; border-radius: 10px; cursor: pointer; margin-bottom: 20px; font-weight: 600; }
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #1e5631; color: white; }
        .btn-edit { background: #f39c12; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; margin-right: 5px; }
        .btn-delete { background: #e74c3c; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; }
        .btn-map { background: #3498db; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; margin-right: 5px; }
        .alert { padding: 12px; border-radius: 10px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .badge { background: #e8f5e9; padding: 4px 10px; border-radius: 20px; font-size: 11px; }
        .back-link { display: inline-block; margin-top: 20px; color: #1e5631; text-decoration: none; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <div class="logo-icon"><i class="fas fa-map-marked-alt"></i></div>
            <h2>Admin Panel</h2>
        </div>
        <div class="nav-menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="sekolah.php" style="color:#1e5631; font-weight:bold;">Data Sekolah</a>
            <a href="../dashboard.php">Peta</a>
            <a href="../logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h2><i class="fas fa-school"></i> Manajemen Data Sekolah</h2>
            
            <?php if($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i> <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <button class="btn-add" onclick="window.location.href='tambah_sekolah.php'">
                <i class="fas fa-plus"></i> Tambah Sekolah Baru
            </button>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>ID</th><th>NPSN</th><th>Nama Sekolah</th><th>Alamat</th><th>Kelurahan</th><th>Kecamatan</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php while($row = db_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id_sekolah']; ?></td>
                            <td><span class="badge">6020<?php echo $row['id_sekolah']; ?>90</span></td>
                            <td><?php echo htmlspecialchars($row['nama_sekolah']); ?></td>
                            <td><?php echo substr(htmlspecialchars($row['alamat']), 0, 40); ?>...</td>
                            <td><?php echo $row['nama_kelurahan']; ?></td>
                            <td><?php echo $row['nama_kecamatan']; ?></td>
                            <td>
                                <button class="btn-map" onclick="window.location.href='../dashboard.php?cari=<?php echo $row['id_sekolah']; ?>'"><i class="fas fa-map-marker-alt"></i></button>
                                <button class="btn-edit" onclick="window.location.href='edit_sekolah.php?id=<?php echo $row['id_sekolah']; ?>'"><i class="fas fa-edit"></i></button>
                                <button class="btn-delete" onclick="if(confirm('Yakin hapus <?php echo addslashes($row['nama_sekolah']); ?>?')) window.location.href='hapus_sekolah.php?id=<?php echo $row['id_sekolah']; ?>'"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <a href="../dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Kembali ke Peta</a>
        </div>
    </div>
</body>
</html>