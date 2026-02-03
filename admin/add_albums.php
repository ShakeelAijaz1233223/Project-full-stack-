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
            return ['error' => "Invalid type: {$file['name']}"];
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
        $error = "Media file required.";
    } else {
        // Updated Query with Year, Genre, Language
        $query = "INSERT INTO albums (title, artist, year, genre, language, cover, audio, video) 
                  VALUES ('$title', '$artist', '$year', '$genre', '$language', '{$cover['name']}', '{$audio['name']}', '{$video['name']}')";

        if (mysqli_query($conn, $query)) {
            $success = "Album published successfully!";
        } else {
            $error = "DB Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Album | Compact Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --accent-color: #e14eca; 
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        /* Compact Zoom-out Feel */
        html { font-size: 13px; }

        body {
            margin: 0; font-family: 'Outfit', sans-serif;
            background: #0f111a;
            min-height: 100vh; display: flex; justify-content: center; align-items: center;
            color: #fff; padding: 15px;
        }

        #pageLoader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: #0f111a; display: flex; justify-content: center; align-items: center;
            z-index: 9999; transition: opacity 0.4s ease;
        }
        .loader { width: 40px; height: 40px; border: 3px solid rgba(255,255,255,0.1); border-top: 3px solid var(--accent-color); border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .back-btn {
            position: absolute; top: 20px; left: 20px;
            background: var(--glass-bg); padding: 8px 16px; border-radius: 10px;
            color: #fff; text-decoration: none; font-size: 12px; border: 1px solid var(--glass-border);
        }

        .upload-card {
            background: var(--glass-bg); backdrop-filter: blur(15px);
            padding: 25px; border-radius: 20px; border: 1px solid var(--glass-border);
            width: 100%; max-width: 480px; box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        h5 { font-weight: 700; margin-bottom: 20px; text-align: center; letter-spacing: 0.5px; }
        
        label { font-size: 11px; color: #aaa; margin: 0 0 5px 5px; text-transform: uppercase; font-weight: 600; }

        .form-control {
            background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border);
            border-radius: 10px; color: #fff; padding: 10px 14px; margin-bottom: 12px;
            font-size: 13px;
        }
        .form-control:focus { background: rgba(255,255,255,0.1); border-color: var(--accent-color); color: #fff; box-shadow: none; }

        .row-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        .btn-upload {
            width: 100%; background: var(--accent-color); border: none; padding: 12px;
            font-weight: 700; border-radius: 10px; color: #fff; margin-top: 10px;
            text-transform: uppercase; font-size: 12px; transition: 0.3s;
        }
        .btn-upload:hover { background: #ff54e5; transform: translateY(-2px); }

        .alert { padding: 10px; border-radius: 10px; font-size: 12px; }
    </style>
</head>
<body>

    <div id="pageLoader"><div class="loader"></div></div>

    <a href="dashboard.php" class="back-btn"><i class="fa fa-arrow-left me-2"></i>Studio Dashboard</a>

    <div class="upload-card">
        <h5><i class="fa fa-compact-disc me-2 text-info"></i> PUBLISH ALBUM</h5>

        <?php if($error): ?>
            <div class="alert alert-danger"><i class="fa fa-circle-xmark me-2"></i><?= $error ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="alert alert-success"><i class="fa fa-check-double me-2"></i><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row-grid">
                <div>
                    <label>Album Title</label>
                    <input type="text" name="title" class="form-control" placeholder="Midnight Sun" required>
                </div>
                <div>
                    <label>Artist</label>
                    <input type="text" name="artist" class="form-control" placeholder="Artist Name">
                </div>
            </div>

            <div class="row-grid" style="grid-template-columns: 1fr 1fr 1fr;">
                <div>
                    <label>Year</label>
                    <input type="number" name="year" class="form-control" value="<?= date('Y') ?>">
                </div>
                <div>
                    <label>Genre</label>
                    <select name="genre" class="form-control">
                        <option value="Pop">Pop</option>
                        <option value="Rock">Rock</option>
                        <option value="Jazz">Jazz</option>
                        <option value="Hip Hop">Hip Hop</option>
                        <option value="Classic">Classic</option>
                    </select>
                </div>
                <div>
                    <label>Language</label>
                    <input type="text" name="language" class="form-control" placeholder="English">
                </div>
            </div>
            
            <label><i class="fa fa-image me-1"></i> Album Artwork</label>
            <input type="file" name="cover" class="form-control" accept="image/*" required>
            
            <div class="row-grid">
                <div>
                    <label><i class="fa fa-music me-1"></i> Audio</label>
                    <input type="file" name="audio" class="form-control" accept="audio/*">
                </div>
                <div>
                    <label><i class="fa fa-video me-1"></i> Video</label>
                    <input type="file" name="video" class="form-control" accept="video/*">
                </div>
            </div>
            
            <button type="submit" name="upload" class="btn btn-upload">
                Publish Master Release
            </button>
        </form>
    </div>

    <script>
        window.addEventListener("load", () => {
            document.getElementById("pageLoader").style.opacity = "0";
            setTimeout(() => { document.getElementById("pageLoader").style.display = "none"; }, 400);
        });
    </script>
</body>
</html>