<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['email']) || !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Specialized upload function to handle different subdirectories
function uploadFile($file, $allowedExt, $subFolder) {
    $targetDir = "../uploads/" . $subFolder . "/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    if ($file && $file['error'] === 0) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt)) {
            return ['error' => "Invalid file extension"];
        }
        $newName = time() . '_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $targetDir . $newName)) {
            return ['name' => $newName];
        }
    }
    return ['name' => null];
}

if (isset($_POST['upload'])) {
    $title    = mysqli_real_escape_string($conn, $_POST['title']);
    $artist   = mysqli_real_escape_string($conn, $_POST['artist']);
    $year     = mysqli_real_escape_string($conn, $_POST['year']);
    $genre    = mysqli_real_escape_string($conn, $_POST['genre']);
    $language = mysqli_real_escape_string($conn, $_POST['language']);

    // Upload to specific folders
    $cover = uploadFile($_FILES['cover'] ?? null, ['jpg','jpeg','png','webp'], 'covers');
    $audio = uploadFile($_FILES['audio'] ?? null, ['mp3','wav','ogg'], 'audio');
    $video = uploadFile($_FILES['video'] ?? null, ['mp4','webm','ogv'], 'albums');

    if (empty($audio['name']) && empty($video['name'])) {
        $error = "Please upload at least one Audio or Video file.";
    } else {
        // Standardized column name to 'album_year' to match your library view
        $query = "INSERT INTO albums (title, artist, album_year, genre, language, cover, audio, video) 
                  VALUES ('$title', '$artist', '$year', '$genre', '$language', 
                          '{$cover['name']}', '{$audio['name']}', '{$video['name']}')";

        if (mysqli_query($conn, $query)) {
            $success = "Album published successfully!";
        } else {
            $error = "Database Error: " . mysqli_error($conn);
        }
    }
}
?>