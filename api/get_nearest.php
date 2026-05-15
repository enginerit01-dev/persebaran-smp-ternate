<?php

header('Content-Type: application/json');

require_once '../config/database.php';


// =========================
// FUNGSI HAVERSINE
// =========================

function haversineDistance(
    $lat1,
    $lon1,
    $lat2,
    $lon2
){

    $earthRadius = 6371;

    $dLat = deg2rad($lat2 - $lat1);

    $dLon = deg2rad($lon2 - $lon1);

    $a =
        sin($dLat / 2) * sin($dLat / 2)
        +
        cos(deg2rad($lat1))
        *
        cos(deg2rad($lat2))
        *
        sin($dLon / 2)
        *
        sin($dLon / 2);

    $c = 2 * atan2(
        sqrt($a),
        sqrt(1 - $a)
    );

    return $earthRadius * $c;
}


// =========================
// AMBIL DATA SEKOLAH
// =========================

$query = "
    SELECT
        id_sekolah,
        nama_sekolah,
        latitude,
        longitude
    FROM smpn
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
// SIMPAN DATA SEKOLAH
// =========================

$schools = [];

while($row = pg_fetch_assoc($result)) {

    $schools[] = [

        'id_sekolah' =>
        (int) $row['id_sekolah'],

        'nama_sekolah' =>
        $row['nama_sekolah'],

        'latitude' =>
        (float) $row['latitude'],

        'longitude' =>
        (float) $row['longitude']
    ];
}


// =========================
// HITUNG NEAREST
// =========================

$nearest = [];

foreach($schools as $school) {

    $min_distance = PHP_FLOAT_MAX;

    $nearest_school = null;

    foreach($schools as $other) {

        if(
            $school['id_sekolah']
            !=
            $other['id_sekolah']
        ){

            $distance = haversineDistance(

                $school['latitude'],
                $school['longitude'],

                $other['latitude'],
                $other['longitude']
            );

            if($distance < $min_distance){

                $min_distance = $distance;

                $nearest_school = $other;
            }
        }
    }


    // Simpan hasil
    if($nearest_school){

        $nearest[] = [

            'school' =>
            $school['nama_sekolah'],

            'nearest' =>
            $nearest_school['nama_sekolah'],

            'distance' =>
            round($min_distance, 3)
        ];
    }
}


// =========================
// OUTPUT JSON
// =========================

echo json_encode($nearest);

?>