<?php

// fix_login.php
require_once 'config/database.php';

echo "<h2>🔧 Fix Login WebGIS SMPN</h2>";

// Cek koneksi
if(!$conn) {

    die("<span style='color:red'>❌ Koneksi database GAGAL!</span>");
}

echo "<p style='color:green'>✅ Koneksi database BERHASIL</p>";


// Cek tabel users
$check_table = pg_query($conn, "
    SELECT table_name
    FROM information_schema.tables
    WHERE table_name = 'users'
");

if(pg_num_rows($check_table) == 0) {

    echo "<p style='color:red'>
            ❌ Tabel users tidak ditemukan!
          </p>";

    exit();
}


// Ambil data user
$query = "
    SELECT
        id_user,
        username,
        email,
        role,
        password
    FROM users
";

$result = pg_query($conn, $query);

echo "<h3>📋 Data User Saat Ini:</h3>";

echo "
<table border='1'
cellpadding='8'
cellspacing='0'
style='border-collapse:collapse'>
";

echo "
<tr style='background:#1e5631;color:white'>
    <th>ID</th>
    <th>Username</th>
    <th>Email</th>
    <th>Role</th>
    <th>Password Hash</th>
</tr>
";

$users = [];

while($row = pg_fetch_assoc($result)) {

    $users[] = $row;

    echo "<tr>";

    echo "<td>{$row['id_user']}</td>";
    echo "<td>{$row['username']}</td>";
    echo "<td>{$row['email']}</td>";
    echo "<td>{$row['role']}</td>";

    echo "<td style='font-size:11px'>"
        . substr($row['password'],0,30)
        . "...</td>";

    echo "</tr>";
}

echo "</table>";


// Reset password
echo "<h3>🔑 Reset Password:</h3>";

$admin_password = 'admin123';
$user_password = 'user123';

$hash_admin = password_hash($admin_password, PASSWORD_DEFAULT);
$hash_user = password_hash($user_password, PASSWORD_DEFAULT);


// Hapus user lama
pg_query($conn, "DELETE FROM users");


// Insert admin
$insert_admin = "
    INSERT INTO users
    (
        username,
        email,
        password,
        role
    )
    VALUES
    (
        'admin',
        'admin@webgis.com',
        '$hash_admin',
        'admin'
    )
";


// Insert user
$insert_user = "
    INSERT INTO users
    (
        username,
        email,
        password,
        role
    )
    VALUES
    (
        'user',
        'user@webgis.com',
        '$hash_user',
        'user'
    )
";

if(
    pg_query($conn, $insert_admin)
    &&
    pg_query($conn, $insert_user)
) {

    echo "<p style='color:green'>
            ✅ User berhasil direset!
          </p>";

} else {

    echo "<p style='color:red'>
            ❌ Gagal reset user
          </p>";
}


// Verifikasi admin
echo "<h3>✅ Verifikasi Login:</h3>";

$test_admin = pg_query($conn,
"
SELECT password
FROM users
WHERE username='admin'
");

if($row = pg_fetch_assoc($test_admin)) {

    if(password_verify($admin_password, $row['password'])) {

        echo "
        <p style='color:green'>
        ✓ Password 'admin123' untuk username 'admin' VALID
        </p>
        ";

    } else {

        echo "
        <p style='color:red'>
        ✗ Password admin TIDAK VALID
        </p>
        ";
    }
}


// Verifikasi user
$test_user = pg_query($conn,
"
SELECT password
FROM users
WHERE username='user'
");

if($row = pg_fetch_assoc($test_user)) {

    if(password_verify($user_password, $row['password'])) {

        echo "
        <p style='color:green'>
        ✓ Password 'user123' untuk username 'user' VALID
        </p>
        ";

    } else {

        echo "
        <p style='color:red'>
        ✗ Password user TIDAK VALID
        </p>
        ";
    }
}


echo "<hr>";

echo "<h3>🔐 Silakan login dengan:</h3>";

echo "
<ul>
    <li>
        <strong>Admin:</strong>
        username = admin ,
        password = admin123
    </li>

    <li>
        <strong>User:</strong>
        username = user ,
        password = user123
    </li>
</ul>
";

echo "
<a href='login.php'
style='
background:#1e5631;
color:white;
padding:10px 20px;
text-decoration:none;
border-radius:5px'>
➡️ Klik untuk Login
</a>
";

?>