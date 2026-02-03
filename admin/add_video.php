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
    // Database connection validation
    if (!$conn) {
        $error = "Database connection lost.";
    } else {
        $title = mysqli_real_escape_string($conn, $_POST['title']);

        // Video File Info (Safe fix)
        $videoFile = $_FILES['video']['name'] ?? '';
        $videoTmp  = $_FILES['video']['tmp_name'] ?? '';
        $videoSize = $_FILES['video']['size'] ?? 0;

        // Image/Thumbnail Info (Safe fix)
        $imageFile = $_FILES['thumbnail']['name'] ?? '';
        $imageTmp  = $_FILES['thumbnail']['tmp_name'] ?? '';

        // Folders setup
        $videoFolder = "uploads/videos/";
        $imageFolder = "uploads/video_thumbnails/";

        if (!is_dir($videoFolder)) mkdir($videoFolder, 0777, true);
        if (!is_dir($imageFolder)) mkdir($imageFolder, 0777, true);

        // Extensions check
        $videoExt = strtolower(pathinfo($videoFile, PATHINFO_EXTENSION));
        $imageExt = strtolower(pathinfo($imageFile, PATHINFO_EXTENSION));

        $allowedVideo = ['mp4', 'webm', 'ogv', 'mov'];
        $allowedImage = ['jpg', 'jpeg', 'png', 'webp'];

        // Validation Checks
        if ($videoSize == 0 && empty($videoFile)) {
            $error = "Video file is too large for the server to process.";
        } elseif (!in_array($videoExt, $allowedVideo)) {
            $error = "Invalid video format. Use MP4, WEBM, or OGV.";
        } elseif (!in_array($imageExt, $allowedImage)) {
            $error = "Invalid image format. Use JPG, PNG, or WEBP.";
        } else {
            // Unique Names
            $newVideoName = time() . "_" . uniqid() . "." . $videoExt;
            $newImageName = time() . "_" . uniqid() . "." . $imageExt;

            if (
                move_uploaded_file($videoTmp, $videoFolder . $newVideoName) &&
                move_uploaded_file($imageTmp, $imageFolder . $newImageName)
            ) {

                // Query for 'videos' table
                $query = "INSERT INTO videos (title, file, thumbnail) VALUES ('$title', '$newVideoName', '$newImageName')";

                if (mysqli_query($conn, $query)) {
                    $adminName = $_SESSION['name'] ?? 'Admin'; // fallback if session name is not set
                    $success = "Video published successfully by Admin: " . $adminName;
                } else {
                    $error = "Database error: " . mysqli_error($conn);
                }
            } else {
                $error = "Upload failed. Check 'upload_max_filesize' in PHP settings or folder permissions.";
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
            overflow-x: hidden;
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
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            width: 95%;
            max-width: 500px;
            border: 1px solid var(--glass-border);
            animation: fadeInUp 0.8s ease forwards;
            margin: 20px 0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--glass-bg);
            color: #fff;
            padding: 8px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            border: 1px solid var(--glass-border);
            z-index: 100;
        }

        .back-btn:hover {
            background: var(--accent-color);
            color: #fff;
            transform: translateX(-3px);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
        }

        h2 i {
            color: var(--accent-color);
            margin-right: 10px;
        }

        label {
            font-weight: 500;
            margin-bottom: 5px;
            display: block;
            color: #ccc;
            font-size: 13px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            color: #fff;
            margin-bottom: 15px;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: var(--accent-color);
            box-shadow: 0 0 10px rgba(225, 78, 202, 0.2);
            color: #fff;
        }

        .btn-primary {
            width: 100%;
            background: var(--accent-color);
            border: none;
            padding: 14px;
            font-weight: 600;
            border-radius: 12px;
            margin-top: 5px;
        }

        #preview-container {
            width: 100%;
            height: 160px;
            border: 2px dashed var(--glass-border);
            border-radius: 12px;
            margin-bottom: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.3);
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
            <p class="text-muted small fw-bold">Setting up video encoder...</p>
        </div>
    </div>

    <a href="dashboard.php" class="back-btn">
        <i class="fa fa-chevron-left me-2"></i> Dashboard
    </a>

    <div class="upload-card">
        <h2><i class="fa fa-video"></i> Upload Video</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small">
                <i class="fa fa-triangle-exclamation me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success py-2 small">
                <i class="fa fa-check-double me-2"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Video Title</label>
            <input class="form-control" type="text" name="title" placeholder="Studio Session #1" required>

            <label>Video Thumbnail (Preview)</label>
            <div id="preview-container">
                <span id="placeholder-text" class="text-muted small">No thumbnail selected</span>
                <img id="preview-img" src="" alt="Thumbnail Preview">
            </div>
            <input class="form-control" type="file" name="thumbnail" id="imageInput" accept="image/*" required>

            <label>Video File (Max server limit applies)</label>
            <input class="form-control" type="file" name="video" accept="video/*" required>

            <button class="btn btn-primary" name="upload">
                <i class="fa fa-upload me-2"></i> Upload to Videos Table
            </button>
        </form>
    </div>

    <script>
        window.addEventListener('load', () => {
            const loader = document.getElementById('pageLoader');
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
            }, 500);
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