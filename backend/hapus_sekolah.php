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
// AMBIL ID
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
// HAPUS FASILITAS
// =========================

pg_query(
    $conn,
    "DELETE FROM fasilitas
     WHERE id_sekolah = $id"
);


// =========================
// HAPUS SEKOLAH
// =========================

$delete = pg_query(
    $conn,
    "DELETE FROM smpn
     WHERE id_sekolah = $id"
);


// =========================
// RESPONSE
// =========================

if($delete) {

    echo json_encode([
        'success' => true,
        'message' => 'Sekolah berhasil dihapus'
    ]);

} else {

    echo json_encode([
        'success' => false,
        'message' => 'Gagal menghapus sekolah'
    ]);
}

?>