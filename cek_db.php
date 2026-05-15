<?php
require_once 'config/database.php';

echo "<h2>=== CEK DATABASE ===</h2>";

// Cek koneksi
if($conn) {
    echo "✅ Koneksi database: BERHASIL<br>";
} else {
    echo "❌ Koneksi database: GAGAL<br>";
    exit();
}

// Cek tabel smpn
$query = "SELECT COUNT(*) as total FROM smpn";
$result = db_query($query);
$row = db_fetch_assoc($result);

echo "📊 Jumlah data di tabel smpn: <strong>" . $row['total'] . "</strong><br>";

if($row['total'] == 0) {
    echo "<p style='color:red'>⚠️ DATABASE KOSONG! Mengisi data contoh...</p>";
    
    // Insert kecamatan
    db_query("INSERT INTO kecamatan (id_kecamatan, nama_kecamatan) VALUES (1, 'Ternate Tengah'), (2, 'Ternate Selatan') ON CONFLICT (id_kecamatan) DO NOTHING");
    
    // Insert kelurahan
    db_query("INSERT INTO kelurahan (id_kelurahan, nama_kelurahan, id_kecamatan) VALUES 
        (1, 'Tanah Raja', 1), (2, 'Kampung Pisang', 1), (3, 'Takoma', 1),
        (4, 'Bastiong Talangame', 2), (5, 'Gambesi', 2)
        ON CONFLICT (id_kelurahan) DO NOTHING");
    
    // Insert smpn
    db_query("INSERT INTO smpn (id_sekolah, nama_sekolah, alamat, id_kelurahan, latitude, longitude, jumlah_siswa, status, akreditasi) VALUES 
        (1, 'SMP Negeri 1 Ternate', 'Jl. Pendidikan No.1, Ternate', 1, 0.7954, 127.3896, 850, 'Negeri', 'A'),
        (2, 'SMP Negeri 3 Ternate', 'Jl. Gambesi Raya', 5, 0.7573, 127.3681, 680, 'Negeri', 'B'),
        (3, 'SMP Negeri 4 Ternate', 'Jl. Bastiong No.45', 4, 0.7612, 127.3714, 710, 'Negeri', 'A'),
        (4, 'SMP Negeri 6 Ternate', 'Jl. Pahlawan Revolusi', 2, 0.7921, 127.3847, 620, 'Negeri', 'A'),
        (5, 'SMP Negeri 7 Ternate', 'Jl. Raya Takoma', 3, 0.7681, 127.3792, 540, 'Negeri', 'B')");
    
    // Insert fasilitas
    for($i=1; $i<=5; $i++) {
        db_query("INSERT INTO fasilitas (id_sekolah, laboratorium, perpustakaan, lapangan_olahraga, toilet) 
                            VALUES ($i, true, true, true, true)
                            ON CONFLICT (id_sekolah) DO NOTHING");
    }
    
    echo "✅ Data contoh telah ditambahkan!<br>";
}

// Tampilkan data
echo "<h3>Data Sekolah:</h3>";
$query2 = db_query("SELECT * FROM smpn");
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

echo "<br><a href='simple_map.php'>Klik disini untuk test peta sederhana</a>";
?>
