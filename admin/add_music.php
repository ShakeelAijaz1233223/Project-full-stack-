<?php
session_start();
include "../config/db.php";

/* ===============================
   ADMIN AUTH (SAME AS DASHBOARD)
================================ */
if (!isset($_SESSION['email']) || !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Database Connection Include
include "../config/db.php";

$success = "";
$error = "";

if (isset($_POST['upload'])) {
    // Database connection check
    if (!$conn) {
        $error = "Database connection failed.";
    } else {
        $title = mysqli_real_escape_string($conn, $_POST['title']);

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
            // Unique Names to avoid overwriting
            $newMusicName = time() . "_" . uniqid() . "." . $musicExt;
            $newImageName = time() . "_" . uniqid() . "." . $imageExt;

            if (
                move_uploaded_file($musicTmp, $musicFolder . $newMusicName) &&
                move_uploaded_file($imageTmp, $imageFolder . $newImageName)
            ) {
                // INSERT query (Make sure your 'music' table columns match these)
                $query = "INSERT INTO music (title, file, cover_image) VALUES ('$title', '$newMusicName', '$newImageName')";

                if (mysqli_query($conn, $query)) {
                    $adminName = $_SESSION['name'] ?? 'Admin';  // fallback if 'name' not set
                    $success = "Music published successfully by " . $adminName . "!";
                } else {
                    $error = "Database error: " . mysqli_error($conn);
                }
            } else {
                $error = "Upload failed. Check folder permissions (777) or file size.";
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
            padding: 10px;
        }

        #pageLoader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #1e1e2f;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        .loader {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(255, 255, 255, 0.1);
            border-top: 5px solid var(--accent-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .upload-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            padding: 25px 20px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 500px;
            border: 1px solid var(--glass-border);
        }

        .back-btn {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--glass-bg);
            color: #fff;
            padding: 8px 15px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid var(--glass-border);
            transition: 0.3s;
            font-size: 14px;
            z-index: 1000;
        }

        .back-btn:hover {
            background: var(--accent-color);
            transform: translateX(-5px);
            color: #fff;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 1.5rem;
        }

        h2 i {
            color: var(--accent-color);
            margin-right: 10px;
        }

        label {
            font-weight: 500;
            margin-bottom: 5px;
            display: block;
            color: #aaa;
            font-size: 13px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            color: #fff;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: var(--accent-color);
            box-shadow: none;
            color: #fff;
        }

        .btn-primary {
            width: 100%;
            background: var(--accent-color);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
            margin-top: 10px;
        }

        #preview-container {
            width: 100%;
            height: 150px;
            border: 2px dashed var(--glass-border);
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.2);
        }

        #preview-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
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
            <div class="alert alert-danger py-2 small"><i class="fa fa-circle-xmark me-2"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success py-2 small"><i class="fa fa-circle-check me-2"></i> <?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Song Title</label>
            <input class="form-control" type="text" name="title" placeholder="e.g. Midnight City" required>

            <label>Cover Image (Preview Below)</label>
            <div id="preview-container">
                <span id="placeholder-text" class="text-muted small">No image selected</span>
                <img id="preview-img" src="" alt="Preview">
            </div>
            <input class="form-control" type="file" name="cover_image" id="imageInput" accept="image/*" required>

            <label>Audio File (MP3, WAV)</label>
            <input class="form-control" type="file" name="music" accept="audio/*" required>

            <button class="btn btn-primary" name="upload">
                <i class="fa fa-cloud-arrow-up me-2"></i> Publish to Music Table
            </button>
        </form>
    </div>

    <script>
        // Loader script
        window.addEventListener('load', () => {
            const loader = document.getElementById('pageLoader');
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
            }, 500);
        });

        // Image Preview script
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