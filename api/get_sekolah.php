<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

// Query sederhana dulu untuk test
$query = "SELECT * FROM smpn";
$result = db_query($query);

if(!$result) {
    echo json_encode(['error' => db_error()]);
    exit();
}

$schools = [];
while($row = db_fetch_assoc($result)) {
    $schools[] = [
        'id_sekolah' => $row['id_sekolah'],
        'nama_sekolah' => $row['nama_sekolah'],
        'alamat' => $row['alamat'],
        'latitude' => (float)$row['latitude'],
        'longitude' => (float)$row['longitude'],
        'jumlah_siswa' => (int)$row['jumlah_siswa'],
        'status' => $row['status'],
        'akreditasi' => $row['akreditasi']
    ];
}

echo json_encode($schools);
?>
