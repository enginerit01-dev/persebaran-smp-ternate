<?php
// debug.php - Cek semua fungsi
session_start();
require_once 'config/database.php';

echo "<h2>Debug WebGIS</h2>";

// 1. Cek session
echo "<h3>Session:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 2. Cek database
echo "<h3>Database:</h3>";
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM smpn");
$row = mysqli_fetch_assoc($result);
echo "Total sekolah: " . $row['total'] . "<br>";

// 3. Cek data sekolah
$result2 = mysqli_query($conn, "SELECT * FROM smpn");
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Nama</th><th>Lat</th><th>Lng</th></tr>";
while($row2 = mysqli_fetch_assoc($result2)) {
    echo "<tr>";
    echo "<td>{$row2['id_sekolah']}</td>";
    echo "<td>{$row2['nama_sekolah']}</td>";
    echo "<td>{$row2['latitude']}</td>";
    echo "<td>{$row2['longitude']}</td>";
    echo "</tr>";
}
echo "</table>";

// 4. Link
echo "<br><a href='dashboard.php'>Kembali ke Dashboard</a>";
?>