<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$query = "SELECT * FROM fasilitas WHERE id_sekolah = $id";
$result = db_query($query);
$fasilitas = db_fetch_assoc($result);

if(!$fasilitas) {
    $fasilitas = [
        'laboratorium' => 0,
        'perpustakaan' => 0,
        'lapangan_olahraga' => 0,
        'toilet' => 1
    ];
} else {
    $fasilitas['laboratorium'] = (int) (bool) $fasilitas['laboratorium'];
    $fasilitas['perpustakaan'] = (int) (bool) $fasilitas['perpustakaan'];
    $fasilitas['lapangan_olahraga'] = (int) (bool) $fasilitas['lapangan_olahraga'];
    $fasilitas['toilet'] = (int) (bool) $fasilitas['toilet'];
}

echo json_encode($fasilitas);
?>
