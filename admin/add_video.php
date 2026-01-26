<?php
include "db.php";
if (!isset($_SESSION['email'])) header("Location: login.php");

if (isset($_POST['upload'])) {

    $title = $_POST['title'];
    $file = $_FILES['video']['name'];
    $tmp  = $_FILES['video']['tmp_name'];

    $folder = "uploads/videos/";

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $newName = time() . "_" . $file;

    if (move_uploaded_file($tmp, $folder . $newName)) {
        mysqli_query($conn, "INSERT INTO videos(title,file) VALUES('$title','$newName')");
        echo "<script>alert('Video Uploaded Successfully');</script>";
    } else {
        echo "<script>alert('Upload Failed');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload Video</title>

<!-- Bootstrap & Font Awesome -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
/* GENERAL STYLES */
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow-x: hidden;
    color: #fff;
}

.container {
    background: rgba(0,0,0,0.8);
    padding: 40px 30px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    width: 100%;
    max-width: 500px;
    animation: fadeInUp 1s ease forwards;
    transform: translateY(50px);
    opacity: 0;
}

@keyframes fadeInUp {
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* BACK BUTTON */
.back-btn {
    position: absolute;
    top: 20px;
    left: 20px;
    background: rgba(255,255,255,0.1);
    color: #fff;
    padding: 12px 18px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.back-btn:hover {
    background: #1daa35;
    color: #fff;
    transform: translateY(-2px);
}

/* HEADING */
h2 {
    text-align: center;
    margin-bottom: 30px;
    font-size: 26px;
    font-weight: 600;
    position: relative;
}

h2 i {
    margin-right: 10px;
    color: #1daa35;
    animation: bounce 1.5s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0);}
    50% { transform: translateY(-10px);}
}

/* FORM FIELDS */
label {
    font-weight: 500;
    margin-bottom: 8px;
    display: block;
    color: #ccc;
}

.form-control {
    background: rgba(255,255,255,0.05);
    border: 1px solid #444;
    border-radius: 12px;
    color: #fff;
    padding: 12px;
    margin-bottom: 20px;
    transition: 0.3s;
}

.form-control:focus {
    border-color: #1daa35;
    box-shadow: 0 0 12px #1daa35;
    background: rgba(255,255,255,0.1);
    color: #fff;
}

/* FILE INPUT */
input[type="file"] {
    padding: 6px;
    border-radius: 10px;
    cursor: pointer;
}

/* BUTTON */
.btn-primary {
    width: 100%;
    background: #1daa35;
    border: none;
    padding: 12px;
    font-weight: 600;
    font-size: 16px;
    border-radius: 12px;
    transition: 0.3s;
}

.btn-primary:hover {
    background: #14a429;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

/* RESPONSIVE */
@media (max-width: 576px) {
    .container {
        padding: 30px 20px;
        border-radius: 15px;
    }

    h2 {
        font-size: 22px;
    }

    .btn-primary {
        font-size: 14px;
        padding: 10px;
    }
}
</style>
</head>

<body>

<!-- BACK BUTTON -->
<a href="dashboard.php" class="back-btn">
    <i class="fa fa-arrow-left"></i> Back
</a>

<div class="container">
    <h2><i class="fa fa-video"></i> Upload Video</h2>
    <form method="POST" enctype="multipart/form-data">
        <label for="title">Video Title</label>
        <input id="title" class="form-control" type="text" name="title" placeholder="Enter video title" required>

        <label for="video">Select Video</label>
        <input id="video" class="form-control" type="file" name="video" required>

        <button class="btn btn-primary" name="upload">
            <i class="fa fa-upload"></i> Upload Video
        </button>
    </form>
</div>

</body>
</html>
