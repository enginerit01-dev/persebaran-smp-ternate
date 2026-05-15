<?php
// fix_login.php - Jalankan file ini untuk memperbaiki login
require_once 'config/database.php';

echo "<h2>🔧 Fix Login WebGIS SMPN</h2>";

// Cek koneksi database
if(!$conn) {
    die("<span style='color:red'>❌ Koneksi database GAGAL!</span>");
}
echo "<p style='color:green'>✅ Koneksi database BERHASIL</p>";

// Cek tabel users
$check_table = db_query("SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'users'");
if(db_num_rows($check_table) == 0) {
    echo "<p style='color:red'>❌ Tabel users tidak ditemukan! Import database terlebih dahulu.</p>";
    exit();
}

// Lihat data user saat ini
$query = "SELECT id_user, username, email, role, password FROM users";
$result = db_query($query);

echo "<h3>📋 Data User Saat Ini:</h3>";
echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse'>";
echo "<tr style='background:#1e5631;color:white'><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Password Hash</th></tr>";

$users = [];
while($row = db_fetch_assoc($result)) {
    $users[] = $row;
    echo "<tr>";
    echo "<td>{$row['id_user']}</td>";
    echo "<td>{$row['username']}</td>";
    echo "<td>{$row['email']}</td>";
    echo "<td>{$row['role']}</td>";
    echo "<td style='font-size:11px'>" . substr($row['password'], 0, 30) . "...</td>";
    echo "</tr>";
}
echo "</table>";

// Reset password dengan hash yang benar
echo "<h3>🔑 Reset Password:</h3>";

$admin_password = 'admin123';
$user_password = 'user123';

$hash_admin = password_hash($admin_password, PASSWORD_DEFAULT);
$hash_user = password_hash($user_password, PASSWORD_DEFAULT);

// Hapus user lama
db_query("DELETE FROM users");

// Insert user baru
$insert_admin = "INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@webgis.com', '$hash_admin', 'admin')";
$insert_user = "INSERT INTO users (username, email, password, role) VALUES ('user', 'user@webgis.com', '$hash_user', 'user')";

if(db_query($insert_admin) && db_query($insert_user)) {
    echo "<p style='color:green'>✅ User berhasil direset!</p>";
} else {
    echo "<p style='color:red'>❌ Gagal reset: " . db_error() . "</p>";
}

// Verifikasi
echo "<h3>✅ Verifikasi Login:</h3>";

$test_admin = db_query("SELECT password FROM users WHERE username='admin'");
if($row = db_fetch_assoc($test_admin)) {
    if(password_verify($admin_password, $row['password'])) {
        echo "<p style='color:green'>✓ Password 'admin123' untuk username 'admin' VALID</p>";
    } else {
        echo "<p style='color:red'>✗ Password 'admin123' untuk username 'admin' TIDAK VALID</p>";
    }
}

$test_user = db_query("SELECT password FROM users WHERE username='user'");
if($row = db_fetch_assoc($test_user)) {
    if(password_verify($user_password, $row['password'])) {
        echo "<p style='color:green'>✓ Password 'user123' untuk username 'user' VALID</p>";
    } else {
        echo "<p style='color:red'>✗ Password 'user123' untuk username 'user' TIDAK VALID</p>";
    }
}

echo "<hr>";
echo "<h3>🔐 Silakan login dengan:</h3>";
echo "<ul>";
echo "<li><strong>Admin:</strong> username = 'admin' , password = 'admin123'</li>";
echo "<li><strong>User:</strong> username = 'user' , password = 'user123'</li>";
echo "</ul>";
echo "<a href='login.php' style='background:#1e5631;color:white;padding:10px 20px;text-decoration:none;border-radius:5px'>➡️ Klik untuk Login</a>";
?>
