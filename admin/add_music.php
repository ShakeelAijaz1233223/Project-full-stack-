<?php
session_start();
include "../config/db.php";

/* ===============================
   ADMIN AUTH
================================ */
if (!isset($_SESSION['email']) || !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";

if (isset($_POST['upload'])) {
    if (!$conn) {
        $error = "Database connection failed.";
    } else {
        // Sanitize all inputs including new categories
        $title  = mysqli_real_escape_string($conn, $_POST['title']);
        $artist = mysqli_real_escape_string($conn, $_POST['artist']);
        $album  = mysqli_real_escape_string($conn, $_POST['album']);
        $year   = mysqli_real_escape_string($conn, $_POST['year']);

        // Files Info
        $musicFile = $_FILES['music']['name'];
        $musicTmp  = $_FILES['music']['tmp_name'];
        $imageFile = $_FILES['cover_image']['name'];
        $imageTmp  = $_FILES['cover_image']['tmp_name'];

        // Folders setup
        $musicFolder = "uploads/music/";
        $imageFolder = "uploads/music_covers/";

        if (!is_dir($musicFolder)) mkdir($musicFolder, 0777, true);
        if (!is_dir($imageFolder)) mkdir($imageFolder, 0777, true);

        // Extensions check
        $musicExt = strtolower(pathinfo($musicFile, PATHINFO_EXTENSION));
        $imageExt = strtolower(pathinfo($imageFile, PATHINFO_EXTENSION));

        $allowedMusic = ['mp3', 'wav', 'ogg', 'm4a'];
        $allowedImage = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($musicExt, $allowedMusic)) {
            $error = "Invalid audio format. Use MP3, WAV, or OGG.";
        } elseif (!in_array($imageExt, $allowedImage)) {
            $error = "Invalid image format. Use JPG, PNG, or WEBP.";
        } else {
            // Unique Names
            $newMusicName = time() . "_" . uniqid() . "." . $musicExt;
            $newImageName = time() . "_" . uniqid() . "." . $imageExt;

            if (
                move_uploaded_file($musicTmp, $musicFolder . $newMusicName) &&
                move_uploaded_file($imageTmp, $imageFolder . $newImageName)
            ) {
                // INSERT query with added categories: artist, album, year
                $query = "INSERT INTO music (title, artist, album, year, file, cover_image) 
                          VALUES ('$title', '$artist', '$album', '$year', '$newMusicName', '$newImageName')";

                if (mysqli_query($conn, $query)) {
                    $adminName = $_SESSION['name'] ?? 'Admin';
                    $success = "Music published successfully by " . $adminName . "!";
                } else {
                    $error = "Database error: " . mysqli_error($conn);
                }
            } else {
                $error = "Upload failed. Check folder permissions.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Music | Admin Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent-color: #e14eca;
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            padding: 40px 10px;
        }

        #pageLoader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: #1e1e2f; display: flex; justify-content: center;
            align-items: center; z-index: 9999; transition: opacity 0.5s ease;
        }

        .loader {
            width: 50px; height: 50px; border: 5px solid rgba(255, 255, 255, 0.1);
            border-top: 5px solid var(--accent-color); border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }

        .upload-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 600px;
            border: 1px solid var(--glass-border);
        }

        .back-btn {
            position: absolute; top: 15px; left: 15px;
            background: var(--glass-bg); color: #fff;
            padding: 8px 15px; border-radius: 12px;
            text-decoration: none; font-weight: 600;
            border: 1px solid var(--glass-border);
            transition: 0.3s; font-size: 14px; z-index: 1000;
        }
        .back-btn:hover { background: var(--accent-color); color: #fff; transform: translateX(-5px); }

        h2 { text-align: center; margin-bottom: 25px; font-weight: 600; }
        h2 i { color: var(--accent-color); margin-right: 10px; }

        label { font-size: 12px; color: #aaa; margin-bottom: 5px; display: block; text-transform: uppercase; letter-spacing: 1px; }

        .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            color: #fff; margin-bottom: 20px; font-size: 14px;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.12); border-color: var(--accent-color);
            box-shadow: none; color: #fff;
        }

        .btn-primary {
            width: 100%; background: var(--accent-color); border: none;
            padding: 14px; font-weight: 600; border-radius: 12px;
        }

        #preview-container {
            width: 100%; height: 180px; border: 2px dashed var(--glass-border);
            border-radius: 15px; margin-bottom: 15px;
            display: flex; justify-content: center; align-items: center;
            overflow: hidden; background: rgba(0, 0, 0, 0.2);
        }
        #preview-img { width: 100%; height: 100%; object-fit: cover; display: none; }
        
        .row-gap { display: flex; gap: 15px; }
        .row-gap > div { flex: 1; }
    </style>
</head>
<body>

    <div id="pageLoader">
        <div class="text-center">
            <div class="loader mb-3"></div>
            <p class="text-muted small fw-bold">Studio Database Syncing...</p>
        </div>
    </div>

    <a href="dashboard.php" class="back-btn"><i class="fa fa-chevron-left me-2"></i> Dashboard</a>

    <div class="upload-card">
        <h2><i class="fa fa-music"></i> Studio Publisher</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small border-0 bg-danger text-white"><i class="fa fa-circle-xmark me-2"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success py-2 small border-0 bg-success text-white"><i class="fa fa-circle-check me-2"></i> <?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-12">
                    <label>Song Title</label>
                    <input class="form-control" type="text" name="title" placeholder="e.g. Blinding Lights" required>
                </div>
            </div>

            <div class="row-gap">
                <div>
                    <label>Artist Name</label>
                    <input class="form-control" type="text" name="artist" placeholder="e.g. The Weeknd" required>
                </div>
                <div>
                    <label>Album Name</label>
                    <input class="form-control" type="text" name="album" placeholder="e.g. After Hours" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label>Release Year</label>
                    <input class="form-control" type="number" name="year" placeholder="2024" required>
                </div>
                <div class="col-md-6">
                    <label>Audio File</label>
                    <input class="form-control" type="file" name="music" accept="audio/*" required>
                </div>
            </div>

            <label>Cover Art Preview</label>
            <div id="preview-container">
                <span id="placeholder-text" class="text-muted small">Select cover image</span>
                <img id="preview-img" src="" alt="Preview">
            </div>
            <input class="form-control" type="file" name="cover_image" id="imageInput" accept="image/*" required>

            <button class="btn btn-primary" name="upload">
                <i class="fa fa-rocket me-2"></i> Publish to Music Library
            </button>
        </form>
    </div>

    <script>
        window.addEventListener('load', () => {
            const loader = document.getElementById('pageLoader');
            loader.style.opacity = '0';
            setTimeout(() => { loader.style.display = 'none'; }, 500);
        });

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