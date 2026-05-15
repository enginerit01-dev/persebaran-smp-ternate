<?php

$host = "aws-1-ap-northeast-1.pooler.supabase.com";
$port = "6543";
$dbname = "postgres";
$user = "postgres.ldpxzpuxopdshgxzuycv";
$password = "Ridwanhanafi";

$conn = pg_connect("
    host=$host
    port=$port
    dbname=$dbname
    user=$user
    password=$password
");

if(!$conn){
    die("Koneksi database gagal!");
}

?>