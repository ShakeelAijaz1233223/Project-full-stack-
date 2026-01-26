<?php

include "db.php";

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

// Delete album
if (isset($_POST['delete'])) {
    $id = intval($_POST['delete']);
    $res = mysqli_query($conn, "SELECT cover, audio, video FROM albums WHERE id=$id");
    if ($row = mysqli_fetch_assoc($res)) {
        $files = ['cover', 'audio', 'video'];
        foreach ($files as $f) {
            if (!empty($row[$f])) {
                $filePath = "uploads/albums/" . $row[$f];
                if (file_exists($filePath)) unlink($filePath);
            }
        }
    }
    mysqli_query($conn, "DELETE FROM albums WHERE id=$id");
    $deleted = true;
}

// Fetch albums
$albums = mysqli_query($conn, "SELECT * FROM albums ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Albums Gallery</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family:Segoe UI, sans-serif; background:#f0f3f8; margin:0; min-height:100vh; color:#000; }
.sidebar { position:fixed; left:0; top:0; width:220px; height:100vh; background: linear-gradient(180deg, #1f2a48, #3e4a76); padding:20px; }
.sidebar a { display:block; color:#fff; padding:12px; margin-bottom:10px; border-radius:12px; text-decoration:none; transition:0.3s; }
.sidebar a:hover { background: rgba(255,255,255,0.15); }
.container { padding:30px 20px; margin-left:240px; }
.search-box { width:100%; max-width:400px; padding:10px; border-radius:10px; border:none; margin-bottom:20px; }

/* Grid and Cards */
.grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:25px; }
.card { background:#fff; border-radius:15px; overflow:hidden; position:relative; transition:0.3s; padding-bottom:10px; }
.card img.thumbnail { width:100%; height:160px; object-fit:cover; display:block; border-radius:10px; cursor:pointer; }
.card .title { font-weight:600; margin:10px 10px 0 10px; }
.card .artist { font-size:14px; opacity:0.7; margin:0 10px 10px 10px; }
.delete-btn { position:absolute; top:10px; right:10px; background:red; color:#fff; border:none; padding:5px 8px; border-radius:5px; cursor:pointer; opacity:0.8; font-size:12px; z-index:10; }
.delete-btn:hover { opacity:1; }

.card audio, .card video { width:100%; margin-top:10px; border-radius:10px; display:none; }
.card.active audio, .card.active video { display:block; }

/* Footer */
footer { text-align:center; padding:20px; opacity:0.6; }

@media(max-width:768px){ .container{ margin-left:0; padding:20px 10px; } .sidebar{ width:100%; height:auto; position:relative; } }
</style>
</head>
<body>

<div class="sidebar">
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="add_albums.php">⬆ Upload Album</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container">
    <?php if(!empty($deleted)): ?>
        <div class="alert alert-success">Album deleted successfully!</div>
    <?php endif; ?>

    <input type="text" id="search" class="form-control mb-3" placeholder="🔍 Search album title or artist">

    <div class="grid" id="albumGrid">
        <?php if(mysqli_num_rows($albums) > 0):
            while ($row = mysqli_fetch_assoc($albums)):
                $title = htmlspecialchars($row['title']);
                $artist = htmlspecialchars($row['artist']);
                $cover = $row['cover'];
                $audio = $row['audio'];
                $video = $row['video'];
                $id    = $row['id'];
        ?>
        <div class="card" data-title="<?php echo strtolower($title); ?>" data-artist="<?php echo strtolower($artist); ?>">
            <!-- Delete button -->
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this album?');">
                <input type="hidden" name="delete" value="<?php echo $id; ?>">
                <button type="submit" class="delete-btn">Delete</button>
            </form>

            <!-- Thumbnail Image -->
            <?php if(!empty($cover)): ?>
                <img src="uploads/albums/<?php echo $cover; ?>" alt="<?php echo $title; ?>" class="thumbnail">
            <?php endif; ?>

            <div class="title"><?php echo $title; ?></div>
            <div class="artist"><?php echo $artist; ?></div>

            <!-- Audio Player -->
            <?php if(!empty($audio)): ?>
                <audio controls>
                    <source src="uploads/albums/<?php echo $audio; ?>" type="audio/<?php echo pathinfo($audio, PATHINFO_EXTENSION); ?>">
                    Your browser does not support the audio element.
                </audio>
            <?php endif; ?>

            <!-- Video Player -->
            <?php if(!empty($video)): ?>
                <video controls poster="uploads/albums/<?php echo $cover; ?>">
                    <source src="uploads/albums/<?php echo $video; ?>" type="video/<?php echo pathinfo($video, PATHINFO_EXTENSION); ?>">
                    Your browser does not support the video element.
                </video>
            <?php endif; ?>
        </div>
        <?php endwhile; else: ?>
            <p>No albums uploaded yet.</p>
        <?php endif; ?>
    </div>
</div>

<footer>© 2026 Music & Albums Platform</footer>

<script>
// Search functionality
document.getElementById("search").addEventListener("keyup", function() {
    let val = this.value.toLowerCase();
    document.querySelectorAll(".card").forEach(card => {
        let title = card.dataset.title;
        let artist = card.dataset.artist;
        card.style.display = (title.includes(val) || artist.includes(val)) ? "block" : "none";
    });
});

// Handle click to toggle media visibility (audio/video)
document.querySelectorAll(".card img.thumbnail").forEach(img => {
    img.addEventListener("click", function() {
        const card = img.closest('.card');
        const isActive = card.classList.contains('active');

        // Deactivate all cards
        document.querySelectorAll('.card').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.card audio').forEach(audio => audio.pause());  // Pause all audio
        document.querySelectorAll('.card video').forEach(video => video.pause());  // Pause all video

        // If this card was not already active, activate it
        if (!isActive) {
            card.classList.add('active');
            const audio = card.querySelector('audio');
            const video = card.querySelector('video');

            // Show the respective media
            if (audio) {
                audio.style.display = 'block';
                audio.play();
            }
            if (video) {
                video.style.display = 'block';
                video.play();
            }
        }
    });
});

// Ensure only one audio/video plays at a time
document.querySelectorAll('.card audio, .card video').forEach(media => {
    media.addEventListener('play', function() {
        document.querySelectorAll('.card audio, .card video').forEach(otherMedia => {
            if (otherMedia !== media) {
                otherMedia.pause();  // Pause all other media
            }
        });
    });
});
</script>

</body>
</html>
