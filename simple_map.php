<?php

require_once 'config/database.php';


// =========================
// AMBIL DATA SEKOLAH
// =========================

$query = pg_query(
    $conn,
    "SELECT * FROM smpn ORDER BY nama_sekolah"
);


// =========================
// CEK QUERY
// =========================

if(!$query){

    die("
        <h3 style='color:red'>
            Gagal mengambil data sekolah
        </h3>
    ");
}


// =========================
// SIMPAN KE ARRAY
// =========================

$sekolah_list = [];

while($row = pg_fetch_assoc($query)) {

    $sekolah_list[] = $row;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Peta Sederhana - WebGIS</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map { height: 500px; width: 100%; }
        body { font-family: Arial, sans-serif; padding: 20px; }
        .info { margin-top: 20px; padding: 10px; background: #f0f2f5; border-radius: 10px; }
    </style>
</head>
<body>
    <h2>📍 Test Peta Sederhana</h2>
    <p>Jika marker muncul di peta, berarti database dan leaflet berfungsi normal.</p>
    
    <div id="map"></div>
    
    <div class="info">
        <h3>Data dari Database:</h3>
        <?php if(count($sekolah_list) > 0): ?>
            <p>✅ Ditemukan <strong><?php echo count($sekolah_list); ?></strong> data sekolah:</p>
            <ul>
                <?php foreach($sekolah_list as $s): ?>
                <li><?php echo $s['nama_sekolah']; ?> - (<?php echo $s['latitude']; ?>, <?php echo $s['longitude']; ?>)</li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="color:red">❌ TIDAK ADA DATA! Silakan jalankan cek_db.php terlebih dahulu.</p>
        <?php endif; ?>
    </div>

    <script>
        // Inisialisasi peta
        var map = L.map('map').setView([0.78, 127.38], 13);
        
        // Base map
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Data sekolah dari PHP
        var schools = <?php echo json_encode($sekolah_list); ?>;
        
        console.log("Jumlah sekolah:", schools.length);
        
        if(schools.length === 0) {
            document.write("<p style='color:red'>Tidak ada data!</p>");
        } else {
            // Loop dan tambahkan marker
            schools.forEach(function(school) {
                console.log("Menambahkan marker:", school.nama_sekolah, school.latitude, school.longitude);
                
                L.marker([school.latitude, school.longitude])
                    .addTo(map)
                    .bindPopup("<b>" + school.nama_sekolah + "</b><br>" + school.alamat);
            });
        }
    </script>
</body>
</html>