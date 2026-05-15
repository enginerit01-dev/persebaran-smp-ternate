<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
/** @var resource $conn */
require_once '../config/auth.php';


// =========================
// CEK ADMIN
// =========================

redirectIfNotAdmin();


// =========================
// QUERY DATA FASILITAS
// =========================

$query = "
    SELECT
        f.*,
        s.nama_sekolah

    FROM fasilitas f

    JOIN smpn s
    ON f.id_sekolah = s.id_sekolah

    ORDER BY s.nama_sekolah
";

$result = pg_query($conn, $query);


// =========================
// CEK QUERY
// =========================

if(!$result){

    die("
        <h3 style='color:red'>
            Gagal mengambil data fasilitas
        </h3>
    ");
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fasilitas - Admin</title>
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
        .sidebar a { display: block; padding: 12px 25px; color: white; text-decoration: none; }
        .content { flex: 1; padding: 30px; }
        .card { background: white; border-radius: 15px; padding: 20px; }
        .card h2 { color: #1e5631; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #1e5631; color: white; }
        .badge-yes { color: green; }
        .badge-no { color: red; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h3><i class="fas fa-map-marked-alt"></i> Admin Panel</h3>
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="sekolah.php"><i class="fas fa-school"></i> Data Sekolah</a>
            <a href="fasilitas.php" style="background:rgba(255,255,255,0.2)"><i class="fas fa-building"></i> Fasilitas</a>
            <a href="../dashboard.php"><i class="fas fa-arrow-left"></i> Kembali ke Peta</a>
        </div>
        <div class="content">
            <div class="card">
                <h2><i class="fas fa-building"></i> Data Fasilitas Sekolah</h2>
                <div class="table-container">
                    <table>
                        <thead><tr><th>ID</th><th>Sekolah</th><th>Laboratorium</th><th>Perpustakaan</th><th>Lapangan Olahraga</th><th>Toilet</th></tr></thead>
                        <tbody>
                            <?php while($row = pg_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['id_fasilitas']; ?></td>
                                <td><?php echo $row['nama_sekolah']; ?></td>
                                <td class="<?php echo $row['laboratorium'] === 't' ? 'badge-yes' : 'badge-no'; ?>"><?php echo $row['laboratorium'] === 't' ? '✓ Ya' : '✗ Tidak'; ?></td>
                                <td class="<?php echo $row['perpustakaan'] === 't' ? 'badge-yes' : 'badge-no'; ?>"><?php echo $row['perpustakaan'] === 't' ? '✓ Ya' : '✗ Tidak'; ?></td>
                                <td class="<?php echo $row['lapangan_olahraga'] === 't' ? 'badge-yes' : 'badge-no'; ?>"><?php echo $row['lapangan_olahraga'] === 't' ? '✓ Ya' : '✗ Tidak'; ?></td>
                                <td class="<?php echo $row['toilet'] === 't' ? 'badge-yes' : 'badge-no'; ?>"><?php echo $row['toilet'] === 't' ? '✓ Ya' : '✗ Tidak'; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>