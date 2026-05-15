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
$query = "SELECT s.*, COALESCE(k.nama_kelurahan, '-') as nama_kelurahan, COALESCE(kc.nama_kecamatan, '-') as nama_kecamatan 
          FROM smpn s 
          LEFT JOIN kelurahan k ON s.id_kelurahan = k.id_kelurahan 
          LEFT JOIN kecamatan kc ON k.id_kecamatan = kc.id_kecamatan
          ORDER BY s.nama_sekolah";
$result = db_query($query);
$sekolah_list = [];
while($row = db_fetch_assoc($result)) {
    $sekolah_list[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Data Sekolah - WebGIS SMPN Ternate</title>
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

        /* Navbar - SAMA DENGAN DASHBOARD */
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

        /* Container */
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Card */
        .card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .card-header h2 {
            color: #1e5631;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Search */
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
            width: 300px;
            padding: 12px 15px 12px 40px;
            border: 2px solid #e0e0e0;
            border-radius: 50px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #1e5631;
        }

        /* Button */
        .btn-add {
            background: linear-gradient(135deg, #27ae60, #219a52);
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
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39,174,96,0.3);
        }

        /* Stats */
        .stats {
            background: #e8f5e9;
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #1e5631;
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .badge {
            background: #e8f5e9;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            color: #1e5631;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-map, .btn-edit, .btn-delete {
            padding: 6px 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 12px;
        }

        .btn-map {
            background: #3498db;
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

        .btn-map:hover, .btn-edit:hover, .btn-delete:hover {
            transform: translateY(-2px);
        }

        /* Back link */
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #1e5631;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Modal Konfirmasi Hapus */
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
            width: 400px;
            text-align: center;
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

        .modal-icon {
            font-size: 60px;
            color: #e74c3c;
            margin-bottom: 20px;
        }

        .modal h3 {
            margin-bottom: 10px;
            color: #333;
        }

        .modal p {
            color: #666;
            margin-bottom: 25px;
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn-confirm {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-cancel {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
        }

        /* Alert */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .search-box input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar - SAMA DENGAN DASHBOARD -->
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
            <a href="data_sekolah.php" class="active">
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

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>
                    <i class="fas fa-school" style="color:#1e5631"></i>
                    Data Sekolah Menengah Pertama Negeri
                </h2>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari nama sekolah...">
                </div>
            </div>

            <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <i class="fas <?php echo $_SESSION['message_type'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                <?php echo $_SESSION['message']; unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            </div>
            <?php endif; ?>

            <div class="stats">
                <i class="fas fa-database"></i> Total Data: <strong><?php echo count($sekolah_list); ?></strong> Sekolah
            </div>

            <?php if($role == 'admin'): ?>
            <button class="btn-add" onclick="window.location.href='admin/tambah_sekolah.php'">
                <i class="fas fa-plus-circle"></i> Tambah Sekolah Baru
            </button>
            <?php endif; ?>

            <div class="table-container">
                <table id="schoolTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NPSN</th>
                            <th>Nama Sekolah</th>
                            <th>Alamat</th>
                            <th>Kelurahan</th>
                            <th>Kecamatan</th>
                            <th>Koordinat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach($sekolah_list as $row): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><span class="badge">6020<?php echo $row['id_sekolah']; ?>90</span></td>
                            <td><strong><?php echo htmlspecialchars($row['nama_sekolah']); ?></strong></td>
                            <td><?php echo substr(htmlspecialchars($row['alamat']), 0, 50); ?>...</td>
                            <td><?php echo $row['nama_kelurahan']; ?></td>
                            <td><?php echo $row['nama_kecamatan']; ?></td>
                            <td><?php echo $row['latitude']; ?>, <?php echo $row['longitude']; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-map" onclick="window.location.href='dashboard.php?cari=<?php echo $row['id_sekolah']; ?>'" title="Lihat di Peta">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </button>
                                    <?php if($role == 'admin'): ?>
                                    <button class="btn-edit" onclick="window.location.href='admin/edit_sekolah.php?id=<?php echo $row['id_sekolah']; ?>'" title="Edit Data">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-delete" onclick="showDeleteModal(<?php echo $row['id_sekolah']; ?>, '<?php echo addslashes($row['nama_sekolah']); ?>')" title="Hapus Data">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <a href="dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Kembali ke Peta
            </a>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Hapus Data Sekolah?</h3>
            <p id="deleteMessage">Apakah Anda yakin ingin menghapus data sekolah ini?</p>
            <p style="font-size: 12px; color: #999; margin-top: 10px;">⚠️ Data yang dihapus tidak dapat dikembalikan!</p>
            <div class="modal-buttons">
                <button class="btn-cancel" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button class="btn-confirm" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Pencarian
        $('#searchInput').on('keyup', function() {
            var searchText = $(this).val().toLowerCase();
            $('#schoolTable tbody tr').each(function() {
                var schoolName = $(this).find('td:nth-child(3)').text().toLowerCase();
                $(this).toggle(schoolName.includes(searchText));
            });
        });

        // Modal Hapus
        let deleteId = null;
        
        function showDeleteModal(id, namaSekolah) {
            deleteId = id;
            document.getElementById('deleteMessage').innerHTML = `Apakah Anda yakin ingin menghapus <strong>${namaSekolah}</strong>?`;
            document.getElementById('deleteModal').style.display = 'flex';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            deleteId = null;
        }
        
        document.getElementById('confirmDeleteBtn').onclick = function() {
            if(deleteId) {
                window.location.href = 'admin/hapus_sekolah.php?id=' + deleteId;
            }
        };
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            var modal = document.getElementById('deleteModal');
            if(event.target == modal) {
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>