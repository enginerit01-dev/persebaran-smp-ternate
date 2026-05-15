<?php
// create_user.php
require_once 'config/database.php';

// Password yang ingin digunakan
$admin_user = 'admin';
$admin_pass = 'admin123';
$admin_email = 'admin@webgis.com';

$normal_user = 'user';
$normal_pass = 'user123';
$normal_email = 'user@webgis.com';

// Hash password
$hash_admin = password_hash($admin_pass, PASSWORD_DEFAULT);
$hash_user = password_hash($normal_pass, PASSWORD_DEFAULT);

// Hapus semua user lama
db_query("TRUNCATE TABLE users");

// Buat user baru
$sql_admin = "INSERT INTO users (username, email, password, role) VALUES ('$admin_user', '$admin_email', '$hash_admin', 'admin')";
$sql_user = "INSERT INTO users (username, email, password, role) VALUES ('$normal_user', '$normal_email', '$hash_user', 'user')";

if(db_query($sql_admin)) {
    echo "✅ User ADMIN berhasil dibuat<br>";
} else {
    echo "❌ Gagal membuat admin: " . db_error() . "<br>";
}

if(db_query($sql_user)) {
    echo "✅ User biasa berhasil dibuat<br>";
} else {
    echo "❌ Gagal membuat user: " . db_error() . "<br>";
}

echo "<hr>";
echo "<h3>🔐 Login Info:</h3>";
echo "Admin: username = <strong>admin</strong> , password = <strong>admin123</strong><br>";
echo "User: username = <strong>user</strong> , password = <strong>user123</strong><br>";
echo "<br><a href='login.php'>Klik untuk Login</a>";
?>