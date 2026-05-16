<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

function haversineDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c;
}

$query = "SELECT id_sekolah, nama_sekolah, latitude, longitude FROM smpn";
$result = db_query($query);
$schools = [];
while($row = db_fetch_assoc($result)) {
    $schools[] = $row;
}

$nearest = [];
foreach($schools as $school) {
    $min_distance = PHP_FLOAT_MAX;
    $nearest_school = null;
    foreach($schools as $other) {
        if($school['id_sekolah'] != $other['id_sekolah']) {
            $distance = haversineDistance($school['latitude'], $school['longitude'], $other['latitude'], $other['longitude']);
            if($distance < $min_distance) {
                $min_distance = $distance;
                $nearest_school = $other;
            }
        }
    }
    if($nearest_school) {
        $nearest[] = [
            'school' => $school['nama_sekolah'],
            'nearest' => $nearest_school['nama_sekolah'],
            'distance' => round($min_distance, 3)
        ];
    }
}

echo json_encode($nearest);
?>
