<?php

session_start();

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../config/auth.php';


// =========================
// CEK ADMIN
// =========================

if(!isAdmin()) {

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


    // =========================
    // AMBIL DATA FORM
    // =========================

    $id = (int) $_POST['id'];

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
    // UPDATE DATA SEKOLAH
    // =========================

    $query = "
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


    $result = pg_query($conn, $query);


    // =========================
    // JIKA BERHASIL
    // =========================

    if($result) {


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


        // =========================
        // UPDATE FASILITAS
        // =========================

        pg_query($conn, "
            UPDATE fasilitas
            SET

                laboratorium = $lab,

                perpustakaan = $perpustakaan,

                lapangan_olahraga = $lapangan,

                toilet = $toilet

            WHERE id_sekolah = $id
        ");


        echo json_encode([
            'success' => true,
            'message' => 'Sekolah berhasil diupdate'
        ]);

    } else {

        echo json_encode([
            'success' => false,
            'message' => 'Gagal update data sekolah'
        ]);
    }

} else {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

?>