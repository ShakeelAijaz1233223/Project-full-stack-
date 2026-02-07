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
        $error = "Database connection lost.";
    } else {
        // Sanitize new Category inputs
        $title  = mysqli_real_escape_string($conn, $_POST['title']);
        $artist = mysqli_real_escape_string($conn, $_POST['artist']);
        $album  = mysqli_real_escape_string($conn, $_POST['album']);
        $year   = mysqli_real_escape_string($conn, $_POST['year']);

        // Video and Thumbnail Info
        $videoFile = $_FILES['video']['name'] ?? '';
        $videoTmp  = $_FILES['video']['tmp_name'] ?? '';
        $videoSize = $_FILES['video']['size'] ?? 0;
        $imageFile = $_FILES['thumbnail']['name'] ?? '';
        $imageTmp  = $_FILES['thumbnail']['tmp_name'] ?? '';

        $videoFolder = "uploads/videos/";
        $imageFolder = "uploads/video_thumbnails/";

        if (!is_dir($videoFolder)) mkdir($videoFolder, 0777, true);
        if (!is_dir($imageFolder)) mkdir($imageFolder, 0777, true);

        $videoExt = strtolower(pathinfo($videoFile, PATHINFO_EXTENSION));
        $imageExt = strtolower(pathinfo($imageFile, PATHINFO_EXTENSION));

        $allowedVideo = ['mp4', 'webm', 'ogv', 'mov'];
        $allowedImage = ['jpg', 'jpeg', 'png', 'webp'];

        if ($videoSize == 0 && empty($videoFile)) {
            $error = "Video file is too large or missing.";
        } elseif (!in_array($videoExt, $allowedVideo)) {
            $error = "Invalid video format.";
        } elseif (!in_array($imageExt, $allowedImage)) {
            $error = "Invalid thumbnail format.";
        } else {
            $newVideoName = time() . "_" . uniqid() . "." . $videoExt;
            $newImageName = time() . "_" . uniqid() . "." . $imageExt;

            if (move_uploaded_file($videoTmp, $videoFolder . $newVideoName) && 
                move_uploaded_file($imageTmp, $imageFolder . $newImageName)) {

                // INSERT query with added categories
                $query = "INSERT INTO videos (title, artist, album, year, file, thumbnail) 
                          VALUES ('$title', '$artist', '$album', '$year', '$newVideoName', '$newImageName')";

                if (mysqli_query($conn, $query)) {
                    $adminName = $_SESSION['name'] ?? 'Admin';
                    $success = "Video published successfully by " . $adminName . "!";
                } else {
                    $error = "Database error: " . mysqli_error($conn);
                }
            } else {
                $error = "Upload failed. Check PHP 'post_max_size' and folder permissions.";
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
    <title>Upload Video | Admin Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --accent-color: #3b82f6;
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        body {
            margin: 0; font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            min-height: 100vh; display: flex; justify-content: center;
            align-items: center; color: #fff; padding: 40px 10px;
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
            background: var(--glass-bg); backdrop-filter: blur(15px);
            padding: 30px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            width: 100%; max-width: 600px; border: 1px solid var(--glass-border);
        }

        .back-btn {
            position: absolute; top: 20px; left: 20px;
            background: var(--glass-bg); color: #fff; padding: 8px 18px;
            border-radius: 10px; text-decoration: none; font-weight: 600;
            border: 1px solid var(--glass-border); transition: 0.3s;
        }
        .back-btn:hover { background: var(--accent-color); transform: translateX(-3px); color: #fff; }

        label { color: #94a3b8; font-size: 12px; margin-bottom: 5px; display: block; text-transform: uppercase; }

        .form-control {
            background: rgba(255, 255, 255, 0.08); border: 1px solid var(--glass-border);
            border-radius: 10px; color: #fff; margin-bottom: 15px;
        }
        .form-control:focus { background: rgba(255, 255, 255, 0.12); border-color: var(--accent-color); box-shadow: none; color: #fff; }

        .btn-primary { width: 100%; background: var(--accent-color); border: none; padding: 14px; font-weight: 600; border-radius: 12px; }

        #preview-container {
            width: 100%; height: 180px; border: 2px dashed var(--glass-border);
            border-radius: 12px; margin-bottom: 15px; display: flex;
            justify-content: center; align-items: center; overflow: hidden; background: rgba(0,0,0,0.3);
        }
        #preview-img { width: 100%; height: 100%; object-fit: cover; display: none; }
    </style>
</head>
<body>

    <div id="pageLoader">
        <div class="text-center">
            <div class="loader mb-3"></div>
            <p class="text-muted small fw-bold">Connecting Studio...</p>
        </div>
    </div>

    <a href="dashboard.php" class="back-btn"><i class="fa fa-chevron-left me-2"></i> Dashboard</a>

    <div class="upload-card">
        <h2 class="text-center mb-4"><i class="fa fa-video me-2" style="color: var(--accent-color);"></i>Studio Video Upload</h2>

        <?php if ($error): ?><div class="alert alert-danger py-2 small"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success py-2 small"><?php echo $success; ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-12">
                    <label>Video Title</label>
                    <input class="form-control" type="text" name="title" placeholder="Project Name" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label>Artist</label>
                    <input class="form-control" type="text" name="artist" placeholder="Performer Name" required>
                </div>
                <div class="col-md-6">
                    <label>Album/Category</label>
                    <input class="form-control" type="text" name="album" placeholder="Album Name" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label>Release Year</label>
                    <input class="form-control" type="number" name="year" placeholder="2026" required>
                </div>
                <div class="col-md-6">
                    <label>Video File (MP4)</label>
                    <input class="form-control" type="file" name="video" accept="video/*" required>
                </div>
            </div>

            <label>Thumbnail Preview</label>
            <div id="preview-container">
                <span id="placeholder-text" class="text-muted small">Select thumbnail</span>
                <img id="preview-img" src="" alt="Thumbnail Preview">
            </div>
            <input class="form-control" type="file" name="thumbnail" id="imageInput" accept="image/*" required>

            <button class="btn btn-primary" name="upload">
                <i class="fa fa-cloud-arrow-up me-2"></i> Publish Video
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