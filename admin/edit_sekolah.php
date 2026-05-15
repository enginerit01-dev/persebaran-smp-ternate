<?php

session_start();

require_once '../config/database.php';
require_once '../config/auth.php';


// =========================
// CEK LOGIN ADMIN
// =========================

if(
    !isset($_SESSION['user_id'])
    ||
    $_SESSION['role'] != 'admin'
){

    header("Location: ../login.php");
    exit();
}


// =========================
// AMBIL ID SEKOLAH
// =========================

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


// =========================
// DATA SEKOLAH
// =========================

$query_sekolah = "
    SELECT *
    FROM smpn
    WHERE id_sekolah = $id
";

$result_sekolah = pg_query(
    $conn,
    $query_sekolah
);

$sekolah = pg_fetch_assoc(
    $result_sekolah
);


// =========================
// DATA FASILITAS
// =========================

$query_fasilitas = "
    SELECT *
    FROM fasilitas
    WHERE id_sekolah = $id
";

$result_fasilitas = pg_query(
    $conn,
    $query_fasilitas
);

$fasilitas = pg_fetch_assoc(
    $result_fasilitas
);


// =========================
// DATA KELURAHAN
// =========================

$query_kelurahan = "
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

$kelurahan = pg_query(
    $conn,
    $query_kelurahan
);


// =========================
// JIKA DATA TIDAK ADA
// =========================

if(!$sekolah){

    header("Location: sekolah.php");
    exit();
}


// =========================
// UPDATE DATA
// =========================

if($_SERVER['REQUEST_METHOD'] == 'POST') {


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
    // UPDATE SMPN
    // =========================

    $update = "
        UPDATE smpn
        SET

            nama_sekolah = '$nama_sekolah',

            alamat = '$alamat',

            id_kelurahan = '$id_kelurahan',

            latitude = '$latitude',

            longitude = '$longitude',

            jumlah_siswa = '$jumlah_siswa',

            akreditasi = '$akreditasi'

        WHERE id_sekolah = $id
    ";


    $result_update = pg_query(
        $conn,
        $update
    );


    // =========================
    // UPDATE FASILITAS
    // =========================

    if($result_update) {

        $lab =
            isset($_POST['laboratorium'])
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


        pg_query($conn, "
            UPDATE fasilitas
            SET

                laboratorium = $lab,

                perpustakaan = $perpustakaan,

                lapangan_olahraga = $lapangan,

                toilet = $toilet

            WHERE id_sekolah = $id
        ");


        header("Location: sekolah.php");
        exit();
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Sekolah</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Poppins',sans-serif;background:#f0f2f5}
        .container{max-width:600px;margin:50px auto;padding:20px}
        .card{background:white;border-radius:20px;padding:30px;box-shadow:0 5px 20px rgba(0,0,0,0.1)}
        .card h2{color:#1e5631;margin-bottom:20px}
        .form-group{margin-bottom:15px}
        .form-group label{display:block;margin-bottom:5px;font-weight:500}
        .form-group input,.form-group select,.form-group textarea{width:100%;padding:10px;border:1px solid #ddd;border-radius:8px}
        .btn-submit{background:#1e5631;color:white;border:none;padding:12px;border-radius:8px;cursor:pointer;width:100%}
        .checkbox-group{display:flex;gap:15px;margin-top:10px;flex-wrap:wrap}
        .back-link{display:block;text-align:center;margin-top:15px;color:#1e5631}
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2><i class="fas fa-edit"></i> Edit Data Sekolah</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Sekolah</label>
                    <input type="text" name="nama_sekolah" value="<?php echo htmlspecialchars($sekolah['nama_sekolah']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" rows="2" required><?php echo htmlspecialchars($sekolah['alamat']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Kelurahan</label>
                    <select name="id_kelurahan" required>
                        <?php while($row = mysqli_fetch_assoc($kelurahan)): ?>
                        <option value="<?php echo $row['id_kelurahan']; ?>" <?php echo ($row['id_kelurahan'] == $sekolah['id_kelurahan']) ? 'selected' : ''; ?>>
                            <?php echo $row['nama_kelurahan'] . " - " . $row['nama_kecamatan']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="number" step="any" name="latitude" value="<?php echo $sekolah['latitude']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="number" step="any" name="longitude" value="<?php echo $sekolah['longitude']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Jumlah Siswa</label>
                    <input type="number" name="jumlah_siswa" value="<?php echo $sekolah['jumlah_siswa']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Akreditasi</label>
                    <select name="akreditasi">
                        <option value="A" <?php echo ($sekolah['akreditasi']=='A')?'selected':''; ?>>A</option>
                        <option value="B" <?php echo ($sekolah['akreditasi']=='B')?'selected':''; ?>>B</option>
                        <option value="C" <?php echo ($sekolah['akreditasi']=='C')?'selected':''; ?>>C</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fasilitas</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="laboratorium" <?php echo ($fasilitas && $fasilitas['laboratorium']) ? 'checked' : ''; ?>> Lab</label>
                        <label><input type="checkbox" name="perpustakaan" <?php echo ($fasilitas && $fasilitas['perpustakaan']) ? 'checked' : ''; ?>> Perpus</label>
                        <label><input type="checkbox" name="lapangan_olahraga" <?php echo ($fasilitas && $fasilitas['lapangan_olahraga']) ? 'checked' : ''; ?>> Lapangan</label>
                        <label><input type="checkbox" name="toilet" <?php echo ($fasilitas && $fasilitas['toilet']) ? 'checked' : ''; ?>> Toilet</label>
                    </div>
                </div>
                <button type="submit" class="btn-submit">Update Sekolah</button>
                <a href="sekolah.php" class="back-link">Kembali</a>
            </form>
        </div>
    </div>
</body>
</html>