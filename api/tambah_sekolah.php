<?php

session_start();

header('Content-Type: application/json');

require_once '../config/database.php';


// =========================
// CEK LOGIN ADMIN
// =========================

if(
    !isset($_SESSION['user_id'])
    ||
    $_SESSION['role'] != 'admin'
){

    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);

    exit();
}


// =========================
// CEK METHOD POST
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

    $id_kelurahan = (int) $_POST['id_kelurahan'];

    $latitude = pg_escape_string(
        $conn,
        $_POST['latitude']
    );

    $longitude = pg_escape_string(
        $conn,
        $_POST['longitude']
    );

    $jumlah_siswa = (int) $_POST['jumlah_siswa'];

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
            '$id_kelurahan',
            '$latitude',
            '$longitude',
            '$jumlah_siswa',
            'Negeri',
            '$akreditasi'
        )
        RETURNING id_sekolah
    ";


    $result = pg_query($conn, $query);


    // =========================
    // JIKA BERHASIL
    // =========================

    if($result) {

        $inserted = pg_fetch_assoc($result);

        $id_sekolah = $inserted['id_sekolah'];


        // Fasilitas
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


        // Insert fasilitas
        pg_query($conn, "
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
                '$id_sekolah',
                $lab,
                $perpustakaan,
                $lapangan,
                $toilet
            )
        ");


        echo json_encode([
            'success' => true,
            'message' => 'Sekolah berhasil ditambahkan'
        ]);

    } else {

        echo json_encode([
            'success' => false,
            'message' => 'Database error'
        ]);
    }

} else {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid method'
    ]);
}

?>