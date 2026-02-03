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
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $artist = mysqli_real_escape_string($conn, $_POST['artist']);
    $album = mysqli_real_escape_string($conn, $_POST['album_name']);
    $year = (int)$_POST['year'];
    $genre = mysqli_real_escape_string($conn, $_POST['genre']);
    $language = mysqli_real_escape_string($conn, $_POST['language']);

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

    $musicExt = strtolower(pathinfo($musicFile, PATHINFO_EXTENSION));
    $imageExt = strtolower(pathinfo($imageFile, PATHINFO_EXTENSION));

    if (!in_array($musicExt, ['mp3', 'wav', 'm4a'])) {
        $error = "Invalid audio format. Use MP3, WAV, or M4A.";
    } elseif (!in_array($imageExt, ['jpg', 'jpeg', 'png', 'webp'])) {
        $error = "Invalid image format. Use JPG, PNG, or WEBP.";
    } else {
        $newMusicName = time() . "_" . uniqid() . "." . $musicExt;
        $newImageName = time() . "_" . uniqid() . "." . $imageExt;

        if (move_uploaded_file($musicTmp, $musicFolder . $newMusicName) && 
            move_uploaded_file($imageTmp, $imageFolder . $newImageName)) {
            
            $query = "INSERT INTO music (title, artist, album_name, year, genre, language, file, cover_image) 
                      VALUES ('$title', '$artist', '$album', '$year', '$genre', '$language', '$newMusicName', '$newImageName')";

            if (mysqli_query($conn, $query)) {
                $success = "Music published successfully to Studio Library!";
            } else {
                $error = "Database error: " . mysqli_error($conn);
            }
        } else {
            $error = "Upload failed. Check permissions or file size.";
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --accent-color: #e14eca;
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        /* --- COMPACT ZOOM (105% effect) --- */
        html {
            zoom: 0.95;
            -moz-transform: scale(0.95);
            -moz-transform-origin: 0 0;
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
            padding: 20px;
            overflow-x: hidden;
        }

        /* Loader */
        #pageLoader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: #1e1e2f; display: flex; justify-content: center; align-items: center;
            z-index: 9999; transition: opacity 0.5s ease;
        }

        .loader {
            width: 50px; height: 50px; border: 5px solid rgba(255, 255, 255, 0.1);
            border-top: 5px solid var(--accent-color); border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Card & UI */
        .upload-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            padding: 30px;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 700px;
            border: 1px solid var(--glass-border);
        }

        .back-btn {
            position: absolute; top: 20px; left: 20px;
            background: var(--glass-bg); color: #fff; padding: 8px 15px;
            border-radius: 12px; text-decoration: none; font-weight: 600;
            border: 1px solid var(--glass-border); transition: 0.3s; font-size: 13px;
        }
        .back-btn:hover { background: var(--accent-color); color: #fff; transform: translateX(-5px); }

        h2 { text-align: center; margin-bottom: 25px; font-weight: 600; font-size: 1.6rem; letter-spacing: 1px; }
        h2 i { color: var(--accent-color); margin-right: 10px; }

        label { font-weight: 500; margin-bottom: 5px; display: block; color: #ccc; font-size: 12px; text-transform: uppercase; }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--glass-border);
            border-radius: 12px; color: #fff; margin-bottom: 15px; font-size: 14px; padding: 10px 15px;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.12); border-color: var(--accent-color);
            box-shadow: 0 0 10px rgba(225, 78, 202, 0.2); color: #fff;
        }

        .btn-primary {
            width: 100%; background: var(--accent-color); border: none;
            padding: 14px; font-weight: 600; border-radius: 12px;
            transition: 0.3s; box-shadow: 0 5px 15px rgba(225, 78, 202, 0.3);
        }
        .btn-primary:hover { transform: translateY(-3px); opacity: 0.9; }

        /* Preview Area */
        #preview-container {
            width: 100%; height: 160px; border: 2px dashed var(--glass-border);
            border-radius: 15px; margin-bottom: 15px; display: flex;
            justify-content: center; align-items: center; overflow: hidden;
            background: rgba(0, 0, 0, 0.3);
        }
        #preview-img { width: 100%; height: 100%; object-fit: cover; display: none; }

        /* Responsive Mobile Fixes */
        @media (max-width: 768px) {
            .upload-card { padding: 20px; margin-top: 60px; }
            .back-btn { top: 10px; left: 10px; font-size: 12px; }
            h2 { font-size: 1.3rem; }
        }
    </style>
</head>
<body>

    <div id="pageLoader">
        <div class="text-center">
            <div class="loader mb-3"></div>
            <p class="text-muted small fw-bold">Connecting to Studio Database...</p>
        </div>
    </div>

    <a href="dashboard.php" class="back-btn"><i class="fa fa-chevron-left me-2"></i> Dashboard</a>

    <div class="upload-card">
        <h2><i class="fa fa-music"></i> Upload Music</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small bg-danger text-white border-0"><i class="fa fa-circle-xmark me-2"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success py-2 small bg-success text-white border-0"><i class="fa fa-circle-check me-2"></i> <?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <label>Song Title</label>
                    <input class="form-control" type="text" name="title" placeholder="e.g. Midnight City" required>
                </div>
                <div class="col-md-6">
                    <label>Artist Name</label>
                    <input class="form-control" type="text" name="artist" placeholder="e.g. M83" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label>Album Name</label>
                    <input class="form-control" type="text" name="album_name" placeholder="Collection Name">
                </div>
                <div class="col-md-3 col-6">
                    <label>Year</label>
                    <input class="form-control" type="number" name="year" value="2024">
                </div>
                <div class="col-md-3 col-6">
                    <label>Genre</label>
                    <input class="form-control" type="text" name="genre" placeholder="Pop / Lo-Fi">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label>Language</label>
                    <select class="form-select" name="language">
                        <option value="English">English</option>
                        <option value="Urdu/Hindi">Urdu/Hindi</option>
                        <option value="Punjabi">Punjabi</option>
                        <option value="Instrumental">Instrumental</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Audio File (MP3/WAV)</label>
                    <input class="form-control" type="file" name="music" accept="audio/*" required>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <label>Cover Artwork Preview</label>
                    <div id="preview-container">
                        <span id="placeholder-text" class="text-muted small">No image selected</span>
                        <img id="preview-img" src="" alt="Preview">
                    </div>
                    <label>Choose Cover Image</label>
                    <input class="form-control" type="file" name="cover_image" id="imageInput" accept="image/*" required>
                </div>
            </div>

            <button class="btn btn-primary" name="upload">
                <i class="fa fa-cloud-arrow-up me-2"></i> PUBLISH TO STUDIO
            </button>
        </form>
    </div>

    <script>
        // Page Loader FadeOut
        window.addEventListener('load', () => {
            const loader = document.getElementById('pageLoader');
            loader.style.opacity = '0';
            setTimeout(() => { loader.style.display = 'none'; }, 500);
        });

        // Instant Image Preview
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


<!--
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
            padding: 10px;
        }  -->