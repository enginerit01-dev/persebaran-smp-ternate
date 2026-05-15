<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// Cek login admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_sekolah = db_escape($_POST['nama_sekolah']);
    $alamat = db_escape($_POST['alamat']);
    $id_kelurahan = db_escape($_POST['id_kelurahan']);
    $latitude = db_escape($_POST['latitude']);
    $longitude = db_escape($_POST['longitude']);
    $jumlah_siswa = db_escape($_POST['jumlah_siswa']);
    $akreditasi = db_escape($_POST['akreditasi']);
    
    $query = "INSERT INTO smpn (nama_sekolah, alamat, id_kelurahan, latitude, longitude, jumlah_siswa, status, akreditasi) 
              VALUES ('$nama_sekolah', '$alamat', '$id_kelurahan', '$latitude', '$longitude', '$jumlah_siswa', 'Negeri', '$akreditasi')
              RETURNING id_sekolah";
    
    $insert_result = db_query($query);
    if($insert_result) {
        $inserted = db_fetch_assoc($insert_result);
        $id_sekolah = $inserted['id_sekolah'];
        
        $lab = isset($_POST['laboratorium']) ? 'true' : 'false';
        $perpustakaan = isset($_POST['perpustakaan']) ? 'true' : 'false';
        $lapangan = isset($_POST['lapangan_olahraga']) ? 'true' : 'false';
        $toilet = isset($_POST['toilet']) ? 'true' : 'false';
        
        db_query("INSERT INTO fasilitas (id_sekolah, laboratorium, perpustakaan, lapangan_olahraga, toilet) 
                            VALUES ('$id_sekolah', $lab, $perpustakaan, $lapangan, $toilet)");
        
        echo json_encode(['success' => true, 'message' => 'Sekolah berhasil ditambahkan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . db_error()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
}
?>
