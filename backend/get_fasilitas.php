<?php

header('Content-Type: application/json');

require_once '../config/database.php';
/** @var resource $conn */


// =========================
// AMBIL ID SEKOLAH
// =========================

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


// Validasi ID
if($id <= 0){

    echo json_encode([
        'success' => false,
        'message' => 'ID tidak valid'
    ]);

    exit();
}


// =========================
// QUERY FASILITAS
// =========================

$query = "
    SELECT *
    FROM fasilitas
    WHERE id_sekolah = $id
";

$result = pg_query($conn, $query);


// =========================
// CEK QUERY
// =========================

if(!$result){

    echo json_encode([
        'success' => false,
        'message' => 'Query database gagal'
    ]);

    exit();
}


// =========================
// AMBIL DATA
// =========================

$fasilitas = pg_fetch_assoc($result);


// =========================
// DEFAULT JIKA TIDAK ADA
// =========================

if(!$fasilitas){

    $fasilitas = [

        'laboratorium' => false,

        'perpustakaan' => false,

        'lapangan_olahraga' => false,

        'toilet' => true
    ];
}

// Konversi nilai boolean dari PostgreSQL ('t'/'f') ke boolean PHP
if ($fasilitas) {
    $fasilitas['laboratorium'] = ($fasilitas['laboratorium'] === 't');
    $fasilitas['perpustakaan'] = ($fasilitas['perpustakaan'] === 't');
    $fasilitas['lapangan_olahraga'] = ($fasilitas['lapangan_olahraga'] === 't');
    $fasilitas['toilet'] = ($fasilitas['toilet'] === 't');
}

// =========================
// OUTPUT JSON
// =========================

echo json_encode($fasilitas);

?>