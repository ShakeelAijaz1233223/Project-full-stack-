<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['email']) || !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";

if (isset($_POST['upload'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $artist = mysqli_real_escape_string($conn, $_POST['artist']);
    $album = mysqli_real_escape_string($conn, $_POST['album_name']);
    $year = (int)$_POST['year'];
    $genre = mysqli_real_escape_string($conn, $_POST['genre']);
    $language = mysqli_real_escape_string($conn, $_POST['language']);

    $musicFile = $_FILES['music']['name'];
    $musicTmp  = $_FILES['music']['tmp_name'];
    $imageFile = $_FILES['cover_image']['name'];
    $imageTmp  = $_FILES['cover_image']['tmp_name'];

    $musicFolder = "uploads/music/";
    $imageFolder = "uploads/music_covers/";

    if (!is_dir($musicFolder)) mkdir($musicFolder, 0777, true);
    if (!is_dir($imageFolder)) mkdir($imageFolder, 0777, true);

    $musicExt = strtolower(pathinfo($musicFile, PATHINFO_EXTENSION));
    $imageExt = strtolower(pathinfo($imageFile, PATHINFO_EXTENSION));

    if (!in_array($musicExt, ['mp3', 'wav', 'm4a'])) {
        $error = "Invalid audio format.";
    } elseif (!in_array($imageExt, ['jpg', 'jpeg', 'png', 'webp'])) {
        $error = "Invalid image format.";
    } else {
        $newMusicName = time() . "_" . uniqid() . "." . $musicExt;
        $newImageName = time() . "_" . uniqid() . "." . $imageExt;

        if (move_uploaded_file($musicTmp, $musicFolder . $newMusicName) && move_uploaded_file($imageTmp, $imageFolder . $newImageName)) {
            $query = "INSERT INTO music (title, artist, album_name, year, genre, language, file, cover_image) 
                      VALUES ('$title', '$artist', '$album', '$year', '$genre', '$language', '$newMusicName', '$newImageName')";

            if (mysqli_query($conn, $query)) {
                $success = "Music published successfully!";
            } else {
                $error = "DB Error: " . mysqli_error($conn);
            }
        } else {
            $error = "Upload failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Music | Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --accent-color: #3b82f6;
            --glass-bg: rgba(15, 23, 42, 0.8);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        /* --- ZOOM 95% (SMALL) --- */
        html {
            zoom: 0.95;
            -moz-transform: scale(0.95);
            -moz-transform-origin: 0 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            background-image: radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.15) 0px, transparent 50%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .upload-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            padding: 25px;
            border-radius: 24px;
            width: 100%;
            max-width: 650px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .back-btn {
            position: fixed; top: 20px; left: 20px;
            color: #94a3b8; text-decoration: none; font-size: 14px;
            transition: 0.3s;
        }
        .back-btn:hover { color: #fff; transform: translateX(-3px); }

        h2 { font-size: 20px; font-weight: 700; margin-bottom: 20px; text-align: center; }
        
        label { font-size: 12px; color: #94a3b8; margin-bottom: 5px; font-weight: 600; text-transform: uppercase; }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            color: #fff; border-radius: 10px; font-size: 14px; padding: 10px;
            margin-bottom: 15px;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent-color);
            box-shadow: none; color: #fff;
        }

        .btn-publish {
            background: var(--accent-color);
            border: none; width: 100%; padding: 12px; font-weight: 700;
            border-radius: 12px; transition: 0.3s;
        }
        .btn-publish:hover { opacity: 0.9; transform: translateY(-2px); }

        #preview-container {
            width: 120px; height: 120px; border-radius: 12px;
            border: 2px dashed var(--glass-border);
            margin-bottom: 15px; overflow: hidden;
            display: flex; justify-content: center; align-items: center;
            background: rgba(0,0,0,0.2);
        }
        #preview-img { width: 100%; height: 100%; object-fit: cover; display: none; }
    </style>
</head>
<body>

    <a href="dashboard.php" class="back-btn"><i class="fa fa-arrow-left me-2"></i> Exit Studio</a>

    <div class="upload-card">
        <h2><i class="fa-solid fa-compact-disc text-primary me-2"></i> Music Publisher</h2>

        <?php if ($error) echo "<div class='alert alert-danger py-2 small'>$error</div>"; ?>
        <?php if ($success) echo "<div class='alert alert-success py-2 small'>$success</div>"; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <label>Song Title</label>
                    <input class="form-control" type="text" name="title" placeholder="Song Name" required>
                </div>
                <div class="col-md-6">
                    <label>Artist Name</label>
                    <input class="form-control" type="text" name="artist" placeholder="Singer / Band" required>
                </div>
                <div class="col-md-6">
                    <label>Album Name</label>
                    <input class="form-control" type="text" name="album_name" placeholder="Collection Name">
                </div>
                <div class="col-md-3">
                    <label>Release Year</label>
                    <input class="form-control" type="number" name="year" value="2024">
                </div>
                <div class="col-md-3">
                    <label>Genre</label>
                    <input class="form-control" type="text" name="genre" placeholder="Pop, Rock...">
                </div>
                <div class="col-md-6">
                    <label>Language</label>
                    <select class="form-control" name="language">
                        <option value="English">English</option>
                        <option value="Urdu/Hindi">Urdu/Hindi</option>
                        <option value="Punjabi">Punjabi</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Audio File</label>
                    <input class="form-control" type="file" name="music" accept="audio/*" required>
                </div>
            </div>

            <label>Cover Art</label>
            <div class="d-flex align-items-center gap-3">
                <div id="preview-container">
                    <span id="placeholder-text" class="text-muted small">No Preview</span>
                    <img id="preview-img" src="" alt="Preview">
                </div>
                <input class="form-control" type="file" name="cover_image" id="imageInput" accept="image/*" required>
            </div>

            <button class="btn btn-publish text-white" name="upload">
                <i class="fa fa-cloud-arrow-up me-2"></i> PUBLISH TO LIBRARY
            </button>
        </form>
    </div>

    <script>
        const imageInput = document.getElementById('imageInput');
        const previewImg = document.getElementById('preview-img');
        const placeholderText = document.getElementById('placeholder-text');

        imageInput.onchange = evt => {
            const [file] = imageInput.files;
            if (file) {
                previewImg.src = URL.createObjectURL(file);
                previewImg.style.display = 'block';
                placeholderText.style.display = 'none';
            }
        }
    </script>
</body>
</html>