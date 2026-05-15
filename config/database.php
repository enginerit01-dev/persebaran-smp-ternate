<?php

$host = getenv('DB_HOST') ?: "aws-1-ap-northeast-1.pooler.supabase.com";
$port = getenv('DB_PORT') ?: "6543";
$dbname = getenv('DB_NAME') ?: "postgres";
$user = getenv('DB_USER') ?: "postgres.ldpxzpuxopdshgxzuycv";
$password = getenv('DB_PASSWORD') ?: "Ridwanhanafi";

$conn = pg_connect("
    host=$host
    port=$port
    dbname=$dbname
    user=$user
    password=$password
    sslmode=require
");

if(!$conn){
    die("Koneksi database gagal!");
}

?>