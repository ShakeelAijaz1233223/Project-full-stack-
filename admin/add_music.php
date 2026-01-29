<?php
include "../config/db.php";

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$success = "";
$error = "";

if (isset($_POST['upload'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $file = $_FILES['music']['name'];
    $tmp  = $_FILES['music']['tmp_name'];

    $folder = "uploads/music/";
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $allowed = ['mp3', 'wav', 'ogg', 'm4a'];

    if (!in_array($ext, $allowed)) {
        $error = "Invalid file type. Please upload MP3, WAV, or OGG.";
    } else {
        $newName = time() . "_" . uniqid() . "." . $ext;
        if (move_uploaded_file($tmp, $folder . $newName)) {
            $query = "INSERT INTO music (title, file) VALUES ('$title','$newName')";
            if (mysqli_query($conn, $query)) {
                $success = "Music uploaded successfully!";
            } else {
                $error = "Database error: " . mysqli_error($conn);
            }
        } else {
            $error = "Failed to move uploaded file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Music | Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --accent-color: #e14eca; /* Same Magenta as Dashboard */
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
            overflow: hidden; /* Hidden for loader, script re-enables it */
            color: #fff;
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
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            width: 100%;
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
        }

        .back-btn:hover {
            background: var(--accent-color);
            color: #fff;
            transform: translateX(-5px);
        }

        /* --- HEADING --- */
        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        h2 i {
            margin-right: 10px;
            color: var(--accent-color);
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

        .btn-primary {
            width: 100%;
            background: var(--accent-color);
            border: none;
            padding: 14px;
            font-weight: 600;
            border-radius: 12px;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(225, 78, 202, 0.3);
        }

        .btn-primary:hover {
            background: #c23bad;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(225, 78, 202, 0.4);
        }

        .alert {
            border-radius: 12px;
            border: none;
            font-weight: 500;
        }
    </style>
</head>

<body>

<div id="pageLoader">
    <div class="text-center">
        <div class="loader mb-3"></div>
        <p class="text-muted small fw-bold">Tuning the workspace...</p>
    </div>
</div>

<a href="dashboard.php" class="back-btn">
    <i class="fa fa-chevron-left me-2"></i> Back to Dashboard
</a>

<div class="upload-card">
    <h2><i class="fa fa-music"></i> Upload Music</h2>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger animate__animated animate__shakeX">
            <i class="fa fa-exclamation-circle me-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($success)): ?>
        <div class="alert alert-success animate__animated animate__fadeIn">
            <i class="fa fa-check-circle me-2"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="title">Song Title</label>
        <input class="form-control" type="text" id="title" name="title" placeholder="e.g. Midnight City" required>

        <label for="music">Select Music File (MP3, WAV, OGG)</label>
        <input class="form-control" type="file" id="music" name="music" accept=".mp3,.wav,.ogg,.m4a" required>

        <button class="btn btn-primary" name="upload">
            <i class="fa fa-cloud-upload-alt me-2"></i> Publish Music
        </button>
    </form>
</div>

<script>
    // Professional Loader Script
    window.addEventListener('load', () => {
        const loader = document.getElementById('pageLoader');
        loader.style.opacity = '0';
        setTimeout(() => {
            loader.style.display = 'none';
            document.body.style.overflow = 'auto'; // Re-enable scrolling
        }, 500);
    });
</script>

</body>
</html>