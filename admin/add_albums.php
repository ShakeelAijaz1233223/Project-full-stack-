<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['email']) || !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

$uploadDir = "uploads/albums/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

function uploadFile($file, $allowedExt) {
    global $uploadDir;
    if ($file && $file['error'] === 0) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt)) {
            return ['error' => "Invalid file: {$file['name']}"];
        }
        $newName = time() . '_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
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

    $cover = uploadFile($_FILES['cover'] ?? null, ['jpg','jpeg','png','webp']);
    $audio = uploadFile($_FILES['audio'] ?? null, ['mp3','wav','ogg']);
    $video = uploadFile($_FILES['video'] ?? null, ['mp4','webm','ogv']);

    if (empty($audio['name']) && empty($video['name'])) {
        $error = "Please upload at least one Audio or Video file.";
    } else {
        $query = "INSERT INTO albums (title, artist, year, genre, language, cover, audio, video) 
                  VALUES ('$title', '$artist', '$year', '$genre', '$language', '{$cover['name']}', '{$audio['name']}', '{$video['name']}')";

        if (mysqli_query($conn, $query)) {
            $success = "Album published successfully!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Album | Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --accent-color: #e14eca; 
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.12);
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
        }

        #pageLoader {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: #121212;
            display: flex; justify-content: center; align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }
        .loader {
            width: 50px; height: 50px;
            border: 5px solid rgba(255,255,255,0.1);
            border-top: 5px solid var(--accent-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .back-btn {
            position: absolute;
            top: 30px; left: 30px;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            color: #fff;
            padding: 10px 22px;
            border-radius: 15px;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
            border: 1px solid var(--glass-border);
            z-index: 100;
        }
        .back-btn:hover {
            background: var(--accent-color);
            color: #fff;
            transform: translateX(-5px);
            box-shadow: 0 0 20px rgba(225, 78, 202, 0.4);
        }

        .upload-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 580px;
            border: 1px solid var(--glass-border);
            animation: fadeInUp 0.8s ease forwards;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 { text-align: center; margin-bottom: 30px; font-weight: 600; font-size: 24px; }
        h2 i { color: var(--accent-color); }

        label {
            font-size: 13px; font-weight: 500;
            color: rgba(255,255,255,0.6);
            margin-bottom: 8px; display: block; margin-left: 5px;
        }

        .form-control, .form-select {
            background: rgba(255,255,255,0.07);
            border: 1px solid var(--glass-border);
            border-radius: 14px; color: #fff;
            padding: 12px 16px; margin-bottom: 18px;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(255,255,255,0.12);
            border-color: var(--accent-color);
            box-shadow: 0 0 15px rgba(225, 78, 202, 0.3);
            color: #fff;
            outline: none;
        }

        /* Styling for dropdown options */
        .form-select option { background: #203a43; color: #fff; }

        .btn-upload {
            width: 100%; background: var(--accent-color);
            border: none; padding: 15px; font-weight: 600;
            border-radius: 14px; color: #fff;
            transition: 0.4s; box-shadow: 0 10px 20px rgba(225, 78, 202, 0.3);
            margin-top: 10px; text-transform: uppercase; letter-spacing: 1px;
        }
        .btn-upload:hover {
            background: #c23bad; transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(225, 78, 202, 0.5);
        }

        .alert { border-radius: 14px; border: none; font-size: 14px; background: rgba(255,255,255,0.1); color: #fff; }
    </style>
</head>
<body>

    <div id="pageLoader">
        <div class="loader"></div>
    </div>

    <a href="dashboard.php" class="back-btn"><i class="fa fa-arrow-left me-2"></i>Dashboard</a>

    <div class="upload-card">
        <h2><i class="fa fa-compact-disc me-2"></i>Publish New Content</h2>

        <?php if($error): ?>
            <div class="alert alert-danger mb-4"><i class="fa fa-exclamation-triangle me-2"></i><?= $error ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="alert alert-success mb-4"><i class="fa fa-check-circle me-2"></i><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <label>Album/Song Title</label>
                    <input type="text" name="title" class="form-control" placeholder="Enter title" required>
                </div>
                <div class="col-md-4">
                    <label>Year</label>
                    <input type="number" name="year" class="form-control" value="<?= date('Y') ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label>Artist</label>
                    <input type="text" name="artist" class="form-control" placeholder="Artist name">
                </div>
                <div class="col-md-6">
                    <label>Genre</label>
                    <select name="genre" class="form-select">
                        <option value="Pop">Pop</option>
                        <option value="Hip Hop">Hip Hop</option>
                        <option value="Rock">Rock</option>
                        <option value="Lofi">Lofi</option>
                        <option value="Classical">Classical</option>
                    </select>
                </div>
            </div>

            <label>Language</label>
            <input type="text" name="language" class="form-control" placeholder="e.g. English, Hindi">

            <label><i class="fa fa-image me-2 text-warning"></i>Album Artwork</label>
            <input type="file" name="cover" class="form-control" accept="image/*" required>
            
            <div class="row">
                <div class="col-md-6">
                    <label><i class="fa fa-music me-2 text-info"></i>Audio Track</label>
                    <input type="file" name="audio" class="form-control" accept="audio/*">
                </div>
                <div class="col-md-6">
                    <label><i class="fa fa-video me-2 text-danger"></i>Video File</label>
                    <input type="file" name="video" class="form-control" accept="video/*">
                </div>
            </div>

            <button type="submit" name="upload" class="btn btn-upload">
                <i class="fa fa-cloud-upload-alt me-2"></i> Publish Now
            </button>
        </form>
    </div>

    <script>
        window.addEventListener("load", function() {
            const loader = document.getElementById("pageLoader");
            loader.style.opacity = "0";
            setTimeout(() => { loader.style.display = "none"; }, 500);
        });
    </script>
</body>
</html>