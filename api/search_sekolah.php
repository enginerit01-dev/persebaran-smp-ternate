<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$search = isset($_GET['q']) ? db_escape($_GET['q']) : '';

$query = "SELECT s.*, k.nama_kelurahan, kc.nama_kecamatan 
          FROM smpn s 
          JOIN kelurahan k ON s.id_kelurahan = k.id_kelurahan 
          JOIN kecamatan kc ON k.id_kecamatan = kc.id_kecamatan
          WHERE s.nama_sekolah LIKE '%$search%' 
          LIMIT 10";
$result = db_query($query);

$schools = [];
while($row = db_fetch_assoc($result)) {
    $schools[] = $row;
}

echo json_encode($schools);
?>
