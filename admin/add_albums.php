<?php

include "db.php";

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if (isset($_POST['upload'])) {
    $title  = mysqli_real_escape_string($conn, $_POST['title']);
    $artist = mysqli_real_escape_string($conn, $_POST['artist'] ?? '');

    $coverFile = $_FILES['cover'] ?? null;
    $audioFile = $_FILES['audio'] ?? null;
    $videoFile = $_FILES['video'] ?? null;

    $uploadDir = "uploads/albums/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    // Function to handle file upload
    function uploadFile($file, $allowedExt) {
        if ($file && $file['error'] === 0) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt)) return ['error'=>"Invalid file type: {$file['name']}"];
            $newName = time().'_'.uniqid().'.'.$ext;
            global $uploadDir;
            if (move_uploaded_file($file['tmp_name'], $uploadDir.$newName)) {
                return ['name'=>$newName];
            } else {
                return ['error'=>"Failed to upload file: {$file['name']}"];
            }
        }
        return ['name'=>null];
    }

    // Upload files
    $cover = uploadFile($coverFile, ['jpg','jpeg','png','webp','gif']);
    $audio = uploadFile($audioFile, ['mp3','wav','ogg']);
    $video = uploadFile($videoFile, ['mp4','webm','ogv']);

    if (!empty($cover['error'])) $error = $cover['error'];
    elseif (!empty($audio['error'])) $error = $audio['error'];
    elseif (!empty($video['error'])) $error = $video['error'];
    elseif (empty($cover['name'])) $error = "Cover image is required.";
    elseif (empty($audio['name']) && empty($video['name'])) $error = "At least one media file (audio or video) is required.";
    else {
        // Insert into DB
        $query = "INSERT INTO albums (title, artist, cover, audio, video) 
                  VALUES ('$title','$artist','{$cover['name']}','{$audio['name']}','{$video['name']}')";
        if (mysqli_query($conn, $query)) {
            $success = "Album uploaded successfully!";
        } else {
            $error = "Database error: ".mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Upload Album</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { margin:0; font-family:Poppins, sans-serif; background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); min-height:100vh; display:flex; justify-content:center; align-items:center; color:#fff; }
        .container { background: rgba(0,0,0,0.75); padding:40px 30px; border-radius:20px; width:100%; max-width:500px; box-shadow:0 10px 25px rgba(0,0,0,0.5); }
        .back-btn { position:absolute; top:20px; left:20px; background: rgba(255,255,255,0.1); color:#fff; padding:12px 18px; border-radius:12px; text-decoration:none; font-weight:600; }
        .back-btn:hover { background:#1daa35; color:#fff; transform:translateY(-2px); }
        h2 { text-align:center; margin-bottom:30px; font-size:26px; font-weight:600; }
        label { font-weight:500; margin-bottom:8px; display:block; color:#ccc; }
        .form-control { background: rgba(255,255,255,0.05); border:1px solid #444; border-radius:12px; color:#fff; padding:12px; margin-bottom:20px; }
        .btn-primary { width:100%; background:#1daa35; border:none; padding:12px; font-weight:600; font-size:16px; border-radius:12px; }
        .btn-primary:hover { background:#14a429; transform:translateY(-2px); }
        .alert { padding:10px 15px; margin-bottom:20px; border-radius:10px; }
    </style>
</head>
<body>

<a href="dashboard.php" class="back-btn"><i class="fa fa-arrow-left"></i> Back</a>

<div class="container">
    <h2><i class="fa fa-plus"></i> Add Album</h2>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if(!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" class="form-control" placeholder="Album Title" required>
        <input type="text" name="artist" class="form-control" placeholder="Artist Name">
        <label>Cover Image</label>
        <input type="file" name="cover" class="form-control" required>
        <label>Audio File</label>
        <input type="file" name="audio" class="form-control">
        <label>Video File</label>
        <input type="file" name="video" class="form-control">
        <button type="submit" name="upload" class="btn btn-primary"><i class="fa fa-upload"></i> Upload Album</button>
    </form>
</div>

</body>
</html>
