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
    <title>Add Album | Sound Music</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-bg: #0f111a;
            --accent-color: #e14eca;
            --secondary-accent: #357ffa;
            --body-bg: #f4f7fe;
            --glass-white: rgba(255, 255, 255, 0.9);
        }

        /* 90% Scale / Zoom-out Effect */
        html { font-size: 14px; }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--body-bg);
            color: #2d3748;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        #pageLoader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: #fff; display: flex; flex-direction: column; 
            justify-content: center; align-items: center; z-index: 9999;
        }
        .loader-ring {
            width: 45px; height: 45px; border: 4px solid #f3f3f3;
            border-top: 4px solid var(--accent-color); border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .back-btn {
            position: fixed; top: 20px; left: 20px;
            background: #fff; padding: 8px 18px; border-radius: 12px;
            color: var(--sidebar-bg); text-decoration: none; font-weight: 600;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s;
            border: 1px solid rgba(0,0,0,0.05); font-size: 0.85rem;
        }
        .back-btn:hover { background: var(--sidebar-bg); color: #fff; transform: translateX(-3px); }

        .upload-card {
            background: #fff; border-radius: 20px; padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            width: 100%; max-width: 550px; border: none;
            animation: fadeIn 0.6s ease-out;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        h4 { font-weight: 700; color: var(--sidebar-bg); margin-bottom: 25px; display: flex; align-items: center; }
        h4 i { color: var(--accent-color); margin-right: 12px; }

        label { font-size: 0.75rem; font-weight: 700; color: #8898aa; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; margin-left: 2px; }

        .form-control, .form-select {
            border: 2px solid #f4f7fe; border-radius: 12px; padding: 10px 15px;
            font-size: 0.9rem; background: #fbfcfe; transition: 0.3s;
        }
        .form-control:focus { border-color: var(--secondary-accent); box-shadow: none; background: #fff; }

        .btn-publish {
            background: linear-gradient(45deg, var(--accent-color), var(--secondary-accent));
            border: none; color: #fff; padding: 12px; border-radius: 12px;
            width: 100%; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            margin-top: 15px; box-shadow: 0 8px 20px rgba(225, 78, 202, 0.2); transition: 0.3s;
        }
        .btn-publish:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(225, 78, 202, 0.3); opacity: 0.9; }

        .alert { border-radius: 12px; font-size: 0.85rem; border: none; }
    </style>
</head>
<body>

    <div id="pageLoader">
        <div class="loader-ring"></div>
        <p class="mt-2 fw-bold text-muted small">Harmonix Studio</p>
    </div>

    <a href="dashboard.php" class="back-btn"><i class="fa fa-chevron-left me-2"></i> Dashboard</a>

    <div class="upload-card">
        <h4><i class="fa-solid fa-cloud-arrow-up"></i> Add New Album</h4>

        <?php if($error): ?>
            <div class="alert alert-danger mb-3"><i class="fa fa-circle-exclamation me-2"></i><?= $error ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="alert alert-success mb-3"><i class="fa fa-circle-check me-2"></i><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row g-2">
                <div class="col-md-7">
                    <label>Album Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Summer Hits" required>
                </div>
                <div class="col-md-5">
                    <label>Release Year</label>
                    <input type="number" name="year" class="form-control" value="<?= date('Y') ?>">
                </div>
            </div>

            <div class="row g-2 mt-2">
                <div class="col-md-6">
                    <label>Artist Name</label>
                    <input type="text" name="artist" class="form-control" placeholder="e.g. Alan Walker">
                </div>
                <div class="col-md-6">
                    <label>Genre</label>
                    <select name="genre" class="form-select">
                        <option value="Pop">Pop</option>
                        <option value="Hip Hop">Hip Hop</option>
                        <option value="Rock">Rock</option>
                        <option value="Electronic">Electronic</option>
                        <option value="Classical">Classical</option>
                        <option value="Lofi">Lofi</option>
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <label>Language</label>
                <input type="text" name="language" class="form-control" placeholder="e.g. English, Hindi">
            </div>

            <div class="mt-3">
                <label><i class="fa-regular fa-image me-1"></i> Album Cover</label>
                <input type="file" name="cover" class="form-control" accept="image/*" required>
            </div>

            <div class="row g-2 mt-2">
                <div class="col-md-6">
                    <label><i class="fa-solid fa-music me-1"></i> Audio File</label>
                    <input type="file" name="audio" class="form-control" accept="audio/*">
                </div>
                <div class="col-md-6">
                    <label><i class="fa-solid fa-circle-play me-1"></i> Video File</label>
                    <input type="file" name="video" class="form-control" accept="video/*">
                </div>
            </div>

            <button type="submit" name="upload" class="btn btn-publish">
                Publish Album Entry
            </button>
        </form>
    </div>

    <script>
        window.addEventListener('load', () => {
            const loader = document.getElementById('pageLoader');
            loader.style.opacity = '0';
            setTimeout(() => { loader.style.display = 'none'; }, 500);
        });
    </script>
</body>
</html>