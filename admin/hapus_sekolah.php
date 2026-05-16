<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Cek role admin
if($_SESSION['role'] != 'admin') {
    echo "error: not admin";
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id > 0) {
    // Ambil nama sekolah untuk log
    $query_nama = db_query("SELECT nama_sekolah FROM smpn WHERE id_sekolah = $id");
    $sekolah = db_fetch_assoc($query_nama);
    $nama_sekolah = $sekolah ? $sekolah['nama_sekolah'] : 'Sekolah';
    
    // Hapus fasilitas terlebih dahulu
    $hapus_fasilitas = db_query("DELETE FROM fasilitas WHERE id_sekolah = $id");
    
    // Hapus sekolah
    $hapus_sekolah = db_query("DELETE FROM smpn WHERE id_sekolah = $id");
    
    if($hapus_sekolah) {
        // Set session message untuk ditampilkan di halaman admin
        $_SESSION['message'] = "✅ $nama_sekolah berhasil dihapus!";
        $_SESSION['message_type'] = "success";
        
        // Cek apakah request dari AJAX atau langsung
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo "success";
        } else {
            header("Location: sekolah.php");
        }
        exit();
    } else {
        $_SESSION['message'] = "❌ Gagal menghapus $nama_sekolah! Error: " . db_error();
        $_SESSION['message_type'] = "error";
        
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo "error: " . db_error();
        } else {
            header("Location: sekolah.php");
        }
        exit();
    }
} else {
    $_SESSION['message'] = "❌ ID sekolah tidak valid!";
    $_SESSION['message_type'] = "error";
    
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo "error: invalid id";
    } else {
        header("Location: sekolah.php");
    }
    exit();
}
?>
