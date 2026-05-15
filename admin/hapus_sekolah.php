<?php

session_start();

require_once '../config/database.php';


// =========================
// CEK LOGIN
// =========================

if(!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();
}


// =========================
// CEK ROLE ADMIN
// =========================

if($_SESSION['role'] != 'admin') {

    echo "error: not admin";
    exit();
}


// =========================
// AMBIL ID SEKOLAH
// =========================

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


// =========================
// VALIDASI ID
// =========================

if($id > 0) {


    // =========================
    // AMBIL NAMA SEKOLAH
    // =========================

    $query_nama = pg_query(
        $conn,
        "
        SELECT nama_sekolah
        FROM smpn
        WHERE id_sekolah = $id
        "
    );

    $sekolah = pg_fetch_assoc(
        $query_nama
    );

    $nama_sekolah =
        $sekolah
        ?
        $sekolah['nama_sekolah']
        :
        'Sekolah';


    // =========================
    // HAPUS FASILITAS
    // =========================

    pg_query(
        $conn,
        "
        DELETE FROM fasilitas
        WHERE id_sekolah = $id
        "
    );


    // =========================
    // HAPUS SEKOLAH
    // =========================

    $hapus_sekolah = pg_query(
        $conn,
        "
        DELETE FROM smpn
        WHERE id_sekolah = $id
        "
    );


    // =========================
    // JIKA BERHASIL
    // =========================

    if($hapus_sekolah) {

        $_SESSION['message'] =
            "✅ $nama_sekolah berhasil dihapus!";

        $_SESSION['message_type'] =
            "success";


        // AJAX REQUEST
        if(
            isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])
            == 'xmlhttprequest'
        ){

            echo "success";

        } else {

            header("Location: sekolah.php");
        }

        exit();

    } else {

        $_SESSION['message'] =
            "❌ Gagal menghapus $nama_sekolah!";

        $_SESSION['message_type'] =
            "error";


        // AJAX REQUEST
        if(
            isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])
            == 'xmlhttprequest'
        ){

            echo "error: " . pg_last_error($conn);

        } else {

            header("Location: sekolah.php");
        }

        exit();
    }

} else {

    $_SESSION['message'] =
        "❌ ID sekolah tidak valid!";

    $_SESSION['message_type'] =
        "error";


    // AJAX REQUEST
    if(
        isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])
        == 'xmlhttprequest'
    ){

        echo "error: invalid id";

    } else {

        header("Location: sekolah.php");
    }

    exit();
}

?>