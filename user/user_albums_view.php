<?php
session_start();
include "../config/db.php";

/* =======================
   ADMIN CHECK
======================= */
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

/* =======================
   CSRF TOKEN
======================= */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/* =======================
   DELETE (POST + SECURE)
======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'], $_POST['csrf'])) {

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        die("Invalid CSRF Token");
    }

    $delete_id = (int)$_POST['delete_id'];

    $stmt = $conn->prepare("SELECT cover, audio, video FROM albums WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $album = $stmt->get_result()->fetch_assoc();

    if ($album) {
        foreach (['cover','audio','video'] as $file) {
            if (!empty($album[$file])) {
                $path = "../admin/uploads/albums/" . $album[$file];
                if (file_exists($path)) unlink($path);
            }
        }

        $del = $conn->prepare("DELETE FROM albums WHERE id=?");
        $del->bind_param("i", $delete_id);
        $del->execute();

        $msg = "Album deleted successfully!";
    }
}

/* =======================
   FETCH ALBUMS
======================= */
$albums = $conn->query("SELECT * FROM albums ORDER BY created_at DESC");
?>
