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
// AMBIL DATA KELURAHAN
// =========================

$kelurahan_query = "
    SELECT

        k.*,

        kc.nama_kecamatan

    FROM kelurahan k

    JOIN kecamatan kc
    ON k.id_kecamatan = kc.id_kecamatan

    ORDER BY
        kc.nama_kecamatan,
        k.nama_kelurahan
";

$kelurahan_result = pg_query(
    $conn,
    $kelurahan_query
);


// =========================
// CEK QUERY
// =========================

if(!$kelurahan_result){

    die("
        <h3 style='color:red'>
            Gagal mengambil data kelurahan
        </h3>
    ");
}


// =========================
// PROSES TAMBAH SEKOLAH
// =========================

if($_SERVER['REQUEST_METHOD'] == 'POST') {


    // Ambil data form
    $nama_sekolah = pg_escape_string(
        $conn,
        $_POST['nama_sekolah']
    );

    $alamat = pg_escape_string(
        $conn,
        $_POST['alamat']
    );

    $id_kelurahan = (int)
        $_POST['id_kelurahan'];

    $latitude = pg_escape_string(
        $conn,
        $_POST['latitude']
    );

    $longitude = pg_escape_string(
        $conn,
        $_POST['longitude']
    );

    $jumlah_siswa = (int)
        $_POST['jumlah_siswa'];

    $akreditasi = pg_escape_string(
        $conn,
        $_POST['akreditasi']
    );


    // =========================
    // INSERT SEKOLAH
    // =========================

    $query = "
        INSERT INTO smpn
        (
            nama_sekolah,
            alamat,
            id_kelurahan,
            latitude,
            longitude,
            jumlah_siswa,
            status,
            akreditasi
        )

        VALUES
        (
            '$nama_sekolah',
            '$alamat',
            $id_kelurahan,
            '$latitude',
            '$longitude',
            '$jumlah_siswa',
            'Negeri',
            '$akreditasi'
        )

        RETURNING id_sekolah
    ";


    $result = pg_query(
        $conn,
        $query
    );


    // =========================
    // JIKA BERHASIL
    // =========================

    if($result) {


        // Ambil ID sekolah baru
        $inserted = pg_fetch_assoc($result);

        $id_sekolah = $inserted['id_sekolah'];


        // =========================
        // DATA FASILITAS
        // =========================

        $lab =
            (isset($_POST['laboratorium']) && $_POST['laboratorium'] == '1')
            ? 'true'
            : 'false';

        $perpustakaan =
            isset($_POST['perpustakaan'])
            ? 'true'
            : 'false';

        $lapangan =
            isset($_POST['lapangan_olahraga'])
            ? 'true'
            : 'false';

        $toilet =
            isset($_POST['toilet'])
            ? 'true'
            : 'false';


        // =========================
        // INSERT FASILITAS
        // =========================

        $fasilitas_query = "
            INSERT INTO fasilitas
            (
                id_sekolah,
                laboratorium,
                perpustakaan,
                lapangan_olahraga,
                toilet
            )

            VALUES
            (
                $id_sekolah,
                $lab,
                $perpustakaan,
                $lapangan,
                $toilet
            )
        ";

        pg_query(
            $conn,
            $fasilitas_query
        );


        // =========================
        // SESSION MESSAGE
        // =========================

        $_SESSION['message'] =
            '✅ Sekolah berhasil ditambahkan!';

        $_SESSION['message_type'] =
            'success';


        header("Location: sekolah.php");
        exit();

    } else {
        // Simpan pesan error ke session agar bisa ditampilkan di halaman ini
        $_SESSION['message'] = "❌ Gagal menambahkan data sekolah: " . pg_last_error($conn);
        $_SESSION['message_type'] = "error";
        // Tidak perlu exit, biarkan halaman dirender dengan pesan error
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Sekolah - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; }
        .container { max-width: 600px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; border-radius: 24px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card h2 { color: #1e5631; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 13px; color: #333; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 12px; font-family: 'Inter', sans-serif; transition: all 0.3s; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #1e5631; }
        .checkbox-group { display: flex; gap: 15px; flex-wrap: wrap; margin-top: 10px; }
        .checkbox-group label { display: flex; align-items: center; gap: 6px; font-weight: normal; cursor: pointer; }
        .btn-submit { background: linear-gradient(135deg, #1e5631, #2d6a4f); color: white; border: none; padding: 14px; border-radius: 12px; cursor: pointer; width: 100%; font-weight: 700; font-size: 16px; margin-top: 20px; transition: all 0.3s; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(30,86,49,0.3); }
        .btn-back { background: #95a5a6; color: white; border: none; padding: 12px; border-radius: 12px; cursor: pointer; width: 100%; font-weight: 600; margin-top: 10px; }
        .alert-error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2><i class="fas fa-plus-circle"></i> Tambah Sekolah Baru</h2>
            <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <i class="fas <?php echo $_SESSION['message_type'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                <?php echo $_SESSION['message']; unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            </div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Sekolah</label>
                    <input type="text" name="nama_sekolah" required placeholder="Contoh: SMP Negeri 10 Ternate">
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" rows="2" required placeholder="Jl. ..."></textarea>
                </div>
                <div class="form-group">
                    <label>Kelurahan</label>
                    <select name="id_kelurahan" required>
                        <option value="">Pilih Kelurahan</option>
                        <?php while($row = pg_fetch_assoc($kelurahan_result)): ?>
                        <option value="<?php echo htmlspecialchars($row['id_kelurahan']); ?>"><?php echo htmlspecialchars($row['nama_kelurahan']) . " - " . htmlspecialchars($row['nama_kecamatan']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="number" step="any" name="latitude" required placeholder="Contoh: 0.7954">
                </div>
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="number" step="any" name="longitude" required placeholder="Contoh: 127.3896">
                </div>
                <div class="form-group">
                    <label>Jumlah Siswa</label>
                    <input type="number" name="jumlah_siswa" required>
                </div>
                <div class="form-group">
                    <label>Akreditasi</label>
                    <select name="akreditasi">
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
                <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Simpan Sekolah</button>
                <button type="button" class="btn-back" onclick="window.location.href='sekolah.php'"><i class="fas fa-arrow-left"></i> Kembali</button>
            </form>
        </div>
    </div>
</body>
</html>