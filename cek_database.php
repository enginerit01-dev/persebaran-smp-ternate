<?php
require_once 'config/database.php';

echo "<h2>Cek Database WebGIS</h2>";

// Cek tabel smpn
$query = "SELECT COUNT(*) as total FROM smpn";
$result = db_query($query);
$row = db_fetch_assoc($result);

echo "<p>Jumlah data di tabel smpn: <strong>" . $row['total'] . "</strong></p>";

if($row['total'] == 0) {
    echo "<p style='color:red'>⚠️ Database KOSONG! Silakan import data terlebih dahulu.</p>";
    
    // Insert data contoh jika kosong
    echo "<h3>Insert Data Contoh:</h3>";
    
    // Cek kecamatan
    $cek_kec = db_query("SELECT COUNT(*) as total FROM kecamatan");
    $kec = db_fetch_assoc($cek_kec);
    if($kec['total'] == 0) {
        db_query("INSERT INTO kecamatan (nama_kecamatan) VALUES ('Ternate Tengah'), ('Ternate Selatan')");
        echo "✅ Kecamatan inserted<br>";
    }
    
    // Cek kelurahan
    $cek_kel = db_query("SELECT COUNT(*) as total FROM kelurahan");
    $kel = db_fetch_assoc($cek_kel);
    if($kel['total'] == 0) {
        db_query("INSERT INTO kelurahan (nama_kelurahan, id_kecamatan) VALUES 
                            ('Tanah Raja', 1), ('Kampung Pisang', 1), ('Takoma', 1),
                            ('Bastiong Talangame', 2), ('Gambesi', 2)");
        echo "✅ Kelurahan inserted<br>";
    }
    
    // Insert data smpn
    $insert = db_query("INSERT INTO smpn (nama_sekolah, alamat, id_kelurahan, latitude, longitude, jumlah_siswa, akreditasi) VALUES 
        ('SMP Negeri 1 Ternate', 'Jl. Pendidikan No.1', 1, 0.7954, 127.3896, 850, 'A'),
        ('SMP Negeri 3 Ternate', 'Jl. Gambesi Raya', 5, 0.7573, 127.3681, 680, 'B'),
        ('SMP Negeri 4 Ternate', 'Jl. Bastiong No.45', 4, 0.7612, 127.3714, 710, 'A'),
        ('SMP Negeri 6 Ternate', 'Jl. Pahlawan Revolusi', 2, 0.7921, 127.3847, 620, 'A'),
        ('SMP Negeri 7 Ternate', 'Jl. Raya Takoma', 3, 0.7681, 127.3792, 540, 'B')");
    
    if($insert) {
        echo "✅ 5 data SMPN berhasil ditambahkan<br>";
        
        // Insert fasilitas
        for($i = 1; $i <= 5; $i++) {
            db_query("INSERT INTO fasilitas (id_sekolah, laboratorium, perpustakaan, lapangan_olahraga, toilet) 
                                VALUES ($i, 1, 1, 1, 1)");
        }
        echo "✅ Fasilitas inserted<br>";
    }
}

// Tampilkan data
$query2 = db_query("SELECT * FROM smpn");
echo "<h3>Data di Database:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nama Sekolah</th><th>Latitude</th><th>Longitude</th></tr>";
while($row2 = db_fetch_assoc($query2)) {
    echo "<tr>";
    echo "<td>{$row2['id_sekolah']}</td>";
    echo "<td>{$row2['nama_sekolah']}</td>";
    echo "<td>{$row2['latitude']}</td>";
    echo "<td>{$row2['longitude']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><a href='dashboard.php'>Kembali ke Peta</a>";
?>