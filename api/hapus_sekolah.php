<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

if(!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : 0;

db_query("DELETE FROM fasilitas WHERE id_sekolah = $id");
if(db_query("DELETE FROM smpn WHERE id_sekolah = $id")) {
    echo json_encode(['success' => true, 'message' => 'Sekolah berhasil dihapus']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus: ' . db_error()]);
}
?>
