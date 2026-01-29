<?php
include "../config/db.php";

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
    $audio = uploadFile($audioFile, ['mp3','wav','ogg']);
    $video = uploadFile($videoFile, ['mp4','webm','ogv']);

    if (!empty($audio['error'])) $error = $audio['error'];
    elseif (!empty($video['error'])) $error = $video['error'];
    elseif (empty($audio['name']) && empty($video['name'])) $error = "At least one media file (audio or video) is required.";
    else {
        // Insert into DB
        $query = "INSERT INTO albums (title, artist, cover, audio, video) 
                  VALUES ('$title','$artist',NULL,'{$audio['name']}','{$video['name']}')";
        if (mysqli_query($conn, $query)) {
            $success = "Album uploaded successfully!";
        } else {
            $error = "Database error: ".mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Album | Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
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

        /* --- PAGE LOADER --- */
        #pageLoader {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: #1e1e2f;
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

        /* --- CONTAINER --- */
        .upload-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            width: 90%;
            max-width: 500px;
            border: 1px solid var(--glass-border);
            animation: fadeInUp 0.8s ease forwards;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- BACK BUTTON --- */
        .back-btn {
            position: absolute;
            top: 30px;
            left: 30px;
            background: var(--glass-bg);
            color: #fff;
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            border: 1px solid var(--glass-border);
            z-index: 100;
        }

        .back-btn:hover {
            background: var(--accent-color);
            color: #fff;
            transform: translateX(-5px);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        h2 i {
            margin-right: 10px;
            color: var(--accent-color);
            display: inline-block;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* --- FORM --- */
        label {
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
            color: #aaa;
            font-size: 14px;
        }

        .form-control {
            background: rgba(255,255,255,0.08);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: #fff;
            padding: 12px;
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .form-control:focus {
            background: rgba(255,255,255,0.12);
            border-color: var(--accent-color);
            box-shadow: 0 0 15px rgba(225, 78, 202, 0.2);
            color: #fff;
        }

        /* Custom File Input Styling */
        input[type="file"]::file-selector-button {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 10px;
            transition: 0.3s;
        }

        .btn-primary {
            width: 100%;
            background: var(--accent-color);
            border: none;
            padding: 14px;
            font-weight: 600;
            border-radius: 12px;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(225, 78, 202, 0.3);
            margin-top: 10px;
        }

        .btn-primary:hover {
            background: #c23bad;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(225, 78, 202, 0.4);
        }

        .alert {
            border-radius: 12px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-weight: 500;
        }
        .alert-danger { background: rgba(220, 53, 69, 0.2); border: 1px solid rgba(220, 53, 69, 0.3); }
        .alert-success { background: rgba(25, 135, 84, 0.2); border: 1px solid rgba(25, 135, 84, 0.3); }
    </style>
</head>
<body>

    <div id="pageLoader">
        <div class="loader"></div>
    </div>

    <a href="dashboard.php" class="back-btn"><i class="fa fa-arrow-left"></i> Back</a>

    <div class="upload-card">
        <h2><i class="fa fa-cloud-upload-alt"></i> Add Album</h2>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><i class="fa fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if(!empty($success)): ?>
            <div class="alert alert-success"><i class="fa fa-check-circle me-2"></i><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Album Title</label>
            <input type="text" name="title" class="form-control" placeholder="Enter title" required>
            
            <label>Artist Name</label>
            <input type="text" name="artist" class="form-control" placeholder="Enter artist name">
            
            <label><i class="fa fa-music me-2"></i>Audio File (mp3, wav)</label>
            <input type="file" name="audio" class="form-control" accept=".mp3,.wav,.ogg">
            
            <label><i class="fa fa-video me-2"></i>Video File (mp4, webm)</label>
            <input type="file" name="video" class="form-control" accept=".mp4,.webm,.ogv">
            
            <button type="submit" name="upload" class="btn btn-primary">
                <i class="fa fa-upload me-2"></i> Upload to Studio
            </button>
        </form>
    </div>

    <script>
        // Handle Loader Removal
        window.addEventListener("load", function() {
            const loader = document.getElementById("pageLoader");
            loader.style.opacity = "0";
            setTimeout(() => {
                loader.style.display = "none";
            }, 500);
        });
    </script>
</body>
</html>