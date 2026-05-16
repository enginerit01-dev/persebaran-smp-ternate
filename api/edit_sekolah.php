<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

if(!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = db_escape($_POST['id']);
    $nama_sekolah = db_escape($_POST['nama_sekolah']);
    $alamat = db_escape($_POST['alamat']);
    $id_kelurahan = db_escape($_POST['id_kelurahan']);
    $latitude = db_escape($_POST['latitude']);
    $longitude = db_escape($_POST['longitude']);
    $jumlah_siswa = db_escape($_POST['jumlah_siswa']);
    $akreditasi = db_escape($_POST['akreditasi']);
    
    $query = "UPDATE smpn SET 
              nama_sekolah='$nama_sekolah', 
              alamat='$alamat', 
              id_kelurahan='$id_kelurahan', 
              latitude='$latitude', 
              longitude='$longitude', 
              jumlah_siswa='$jumlah_siswa', 
              akreditasi='$akreditasi' 
              WHERE id_sekolah=$id";
    
    if(db_query($query)) {
        $lab = isset($_POST['laboratorium']) ? 'true' : 'false';
        $perpustakaan = isset($_POST['perpustakaan']) ? 'true' : 'false';
        $lapangan = isset($_POST['lapangan_olahraga']) ? 'true' : 'false';
        $toilet = isset($_POST['toilet']) ? 'true' : 'false';
        db_query("UPDATE fasilitas SET 
                            laboratorium=$lab, 
                            perpustakaan=$perpustakaan, 
                            lapangan_olahraga=$lapangan, 
                            toilet=$toilet 
                            WHERE id_sekolah=$id");
        
        echo json_encode(['success' => true, 'message' => 'Sekolah berhasil diupdate']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal update: ' . db_error()]);
    }
}
?>
