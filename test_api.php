<?php
// test_api.php
$url = 'http://localhost/webgis_smpn/api/get_sekolah.php';

// Ambil data dari API
$response = file_get_contents($url);
$data = json_decode($response, true);

echo "<h2>Test API get_sekolah.php</h2>";
echo "<pre>";
print_r($data);
echo "</pre>";

if(empty($data)) {
    echo "<p style='color:red'>❌ API mengembalikan data KOSONG!</p>";
    echo "<p>Coba akses langsung: <a href='$url' target='_blank'>$url</a></p>";
} else {
    echo "<p style='color:green'>✅ API mengembalikan " . count($data) . " data</p>";
}

echo "<br><a href='simple_map.php'>Kembali ke Simple Map</a> | ";
echo "<a href='dashboard.php'>Ke Dashboard</a>";
?>