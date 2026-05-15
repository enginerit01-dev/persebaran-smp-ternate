<?php

// create_user.php

require_once 'config/database.php';


// Data admin
$admin_user  = 'admin';
$admin_pass  = 'admin123';
$admin_email = 'admin@webgis.com';


// Data user biasa
$normal_user  = 'user';
$normal_pass  = 'user123';
$normal_email = 'user@webgis.com';


// Hash password
$hash_admin = password_hash($admin_pass, PASSWORD_DEFAULT);
$hash_user  = password_hash($normal_pass, PASSWORD_DEFAULT);


// Hapus semua user lama
pg_query($conn, "TRUNCATE TABLE users RESTART IDENTITY");


// Insert admin
$sql_admin = "
    INSERT INTO users
    (
        username,
        email,
        password,
        role
    )
    VALUES
    (
        '$admin_user',
        '$admin_email',
        '$hash_admin',
        'admin'
    )
";


// Insert user biasa
$sql_user = "
    INSERT INTO users
    (
        username,
        email,
        password,
        role
    )
    VALUES
    (
        '$normal_user',
        '$normal_email',
        '$hash_user',
        'user'
    )
";


// Eksekusi admin
if(pg_query($conn, $sql_admin)) {

    echo "
    <p style='color:green'>
        ✅ User ADMIN berhasil dibuat
    </p>
    ";

} else {

    echo "
    <p style='color:red'>
        ❌ Gagal membuat admin
    </p>
    ";
}


// Eksekusi user
if(pg_query($conn, $sql_user)) {

    echo "
    <p style='color:green'>
        ✅ User biasa berhasil dibuat
    </p>
    ";

} else {

    echo "
    <p style='color:red'>
        ❌ Gagal membuat user
    </p>
    ";
}


echo "<hr>";

echo "<h3>🔐 Login Info:</h3>";

echo "
Admin:
username =
<strong>admin</strong>
,
password =
<strong>admin123</strong>
<br><br>
";

echo "
User:
username =
<strong>user</strong>
,
password =
<strong>user123</strong>
<br><br>
";


echo "
<a href='login.php'
style='
background:#1e5631;
color:white;
padding:10px 20px;
text-decoration:none;
border-radius:5px;
'>
➡️ Klik untuk Login
</a>
";

?>