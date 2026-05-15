<?php
session_start();
require_once 'config/database.php';

if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = pg_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Cek username atau email
    $query = "
        SELECT *
        FROM users
        WHERE username = '$username'
        OR email = '$username'
    ";

    $result = pg_query($conn, $query);

    if(pg_num_rows($result) == 1) {

        $user = pg_fetch_assoc($result);

        // Verifikasi password
        if(password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            header("Location: dashboard.php");
            exit();

        } else {

            $error = "Password salah!";
        }

    } else {

        $error = "Username/Email tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WebGIS SMPN Ternate</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:'Poppins', sans-serif;
            background:linear-gradient(135deg,#1a472a 0%, #0d2818 100%);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .login-card{
            background:white;
            border-radius:20px;
            box-shadow:0 25px 50px rgba(0,0,0,0.3);
            width:400px;
            padding:40px;
        }

        .login-header{
            text-align:center;
            margin-bottom:30px;
        }

        .login-header h1{
            color:#1e5631;
            font-size:28px;
        }

        .login-header p{
            color:#666;
            font-size:14px;
        }

        .input-group{
            margin-bottom:20px;
        }

        .input-group label{
            display:block;
            margin-bottom:8px;
            font-weight:500;
        }

        .input-wrapper{
            display:flex;
            align-items:center;
            border:2px solid #e0e0e0;
            border-radius:12px;
            padding:12px 15px;
        }

        .input-wrapper i{
            color:#999;
            margin-right:12px;
        }

        .input-wrapper input{
            border:none;
            outline:none;
            flex:1;
            font-family:'Poppins', sans-serif;
        }

        .input-wrapper:focus-within{
            border-color:#1e5631;
        }

        .btn-login{
            background:linear-gradient(135deg,#1e5631,#2d6a4f);
            color:white;
            border:none;
            padding:14px;
            border-radius:12px;
            font-size:16px;
            font-weight:600;
            cursor:pointer;
            width:100%;
        }

        .alert{
            background:#fee2e2;
            color:#dc2626;
            padding:12px;
            border-radius:10px;
            font-size:13px;
            margin-bottom:20px;
            text-align:center;
        }

        .info{
            background:#e8f5e9;
            padding:15px;
            border-radius:10px;
            margin-top:20px;
            font-size:12px;
            text-align:center;
        }

    </style>
</head>

<body>

<div class="login-card">

    <div class="login-header">
        <h1>WEBGIS SMPN</h1>
        <p>Ternate Tengah & Ternate Selatan</p>
    </div>

    <?php if($error): ?>
        <div class="alert">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">

        <div class="input-group">

            <label>Username / Email</label>

            <div class="input-wrapper">
                <i class="fas fa-user"></i>

                <input
                    type="text"
                    name="username"
                    placeholder="Masukkan username atau email"
                    required
                >
            </div>

        </div>

        <div class="input-group">

            <label>Password</label>

            <div class="input-wrapper">
                <i class="fas fa-lock"></i>

                <input
                    type="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                >
            </div>

        </div>

        <button type="submit" class="btn-login">
            <i class="fas fa-sign-in-alt"></i>
            Login
        </button>

    </form>

    <div class="info">
        <strong>Info Login:</strong><br>
        Admin: admin / admin123<br>
        User: user / user123
    </div>

</div>

</body>
</html>