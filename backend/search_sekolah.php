<?php

header('Content-Type: application/json');

require_once '../config/database.php';


// Ambil keyword pencarian
$search = isset($_GET['q'])
    ? pg_escape_string($conn, $_GET['q'])
    : '';


// Query pencarian sekolah
$query = "
    SELECT
        s.*,
        k.nama_kelurahan,
        kc.nama_kecamatan

    FROM smpn s

    JOIN kelurahan k
    ON s.id_kelurahan = k.id_kelurahan

    JOIN kecamatan kc
    ON k.id_kecamatan = kc.id_kecamatan

    WHERE s.nama_sekolah
    ILIKE '%$search%'

    ORDER BY s.nama_sekolah

    LIMIT 10
";

$result = pg_query($conn, $query);


// Cek query
if(!$result){

    echo json_encode([
        'success' => false,
        'message' => 'Query database gagal'
    ]);

    exit();
}


// Simpan data
$schools = [];

while($row = pg_fetch_assoc($result)) {

    $schools[] = $row;
}


// Output JSON
echo json_encode($schools);

?>