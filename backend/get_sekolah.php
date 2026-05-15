<?php

header('Content-Type: application/json');

error_reporting(E_ALL);

ini_set('display_errors', 1);

require_once '../config/database.php';


// =========================
// QUERY DATA SEKOLAH
// =========================

$query = "
    SELECT *
    FROM smpn
";

$result = pg_query($conn, $query);


// =========================
// CEK QUERY
// =========================

if(!$result) {

    echo json_encode([
        'error' => pg_last_error($conn)
    ]);

    exit();
}


// =========================
// AMBIL DATA
// =========================

$schools = [];

while($row = pg_fetch_assoc($result)) {

    $schools[] = [

        'id_sekolah' =>
        (int) $row['id_sekolah'],

        'nama_sekolah' =>
        $row['nama_sekolah'],

        'alamat' =>
        $row['alamat'],

        'latitude' =>
        (float) $row['latitude'],

        'longitude' =>
        (float) $row['longitude'],

        'jumlah_siswa' =>
        (int) $row['jumlah_siswa'],

        'status' =>
        $row['status'],

        'akreditasi' =>
        $row['akreditasi']
    ];
}


// =========================
// OUTPUT JSON
// =========================

echo json_encode($schools);

?>