<?php
session_start();
include "../config/db.php";

// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $album = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM albums WHERE id=$delete_id"));
    if ($album) {
        @unlink("../admin/uploads/albums/" . $album['cover']);
        @unlink("../admin/uploads/albums/" . $album['audio']);
        @unlink("../admin/uploads/albums/" . $album['video']);
        mysqli_query($conn, "DELETE FROM albums WHERE id=$delete_id");
        $msg = "Album deleted successfully!";
    }
}

$albums = mysqli_query($conn, "SELECT * FROM albums ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Albums Studio | Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&family=Syncopate:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #050505;
            --card-bg: #0a0a0a;
            --accent: #ff0055;
            --accent-gradient: linear-gradient(45deg, #ff0055, #ff5e00);
            --text-muted: #555;
        }

        body {
            background: var(--bg-dark);
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
        }

        header {
            background: rgba(5, 5, 5, 0.9);
            backdrop-filter: blur(20px);
            padding: 15px 5%;
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .logo {
            font-family: 'Syncopate', sans-serif;
            font-size: 1.2rem;
            color: #fff;
            text-decoration: none;
            letter-spacing: 3px;
            text-transform: uppercase;
        }
        .logo span { color: var(--accent); }

        .search-box {
            background: #151515;
            border: 1px solid #222;
            color: white;
            border-radius: 50px;
            padding: 6px 18px;
            width: 220px;
            font-size: 0.8rem;
            outline: none;
        }

        .studio-wrapper {
            width: 90%;
            margin: 0 auto;
            padding: 40px 0;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
        }

        .music-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: 0.3s ease;
            position: relative;
            text-align: center;
        }

        .music-card:hover {
            border-color: rgba(255, 0, 85, 0.3);
            transform: translateY(-5px);
        }

        .image-container {
            width: 100%;
            aspect-ratio: 1/1;
            background: #000;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 15px;
            position: relative;
            overflow: hidden;
        }

        .inner-glow {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: radial-gradient(circle, #ff0055 20%, #80002b 60%, #000 100%);
            box-shadow: 0 0 20px #ff0055;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        /* Animation when playing */
        .playing .inner-glow {
            animation: pulse 1.2s infinite alternate;
        }

        @keyframes pulse {
            from { transform: scale(1); }
            to { transform: scale(1.15); }
        }

        .card-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
            z-index: 10;
        }

        .btn-action {
            background: rgba(0,0,0,0.5);
            border: none;
            color: #fff;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            text-decoration: none;
        }

        .btn-action:hover { background: var(--accent); }

        .title { font-weight: 700; font-size: 0.95rem; margin: 10px 0 2px; color: #fff; }
        .artist { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }

        .controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
        }

        .play-btn {
            width: 45px;
            height: 45px;
            background: var(--accent-gradient);
            border: none;
            border-radius: 50%;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(255, 0, 85, 0.3);
        }

        footer { padding: 60px; text-align: center; font-size: 0.7rem; color: #333; letter-spacing: 2px; }
    </style>
</head>
<body>

<header>
    <a href="#" class="logo">ALBUMS<span>STUDIO</span></a>
    <div class="d-flex align-items-center gap-3">
        <input type="text" id="search" class="search-box" placeholder="Search albums...">
        <a href="index.php" class="btn-action" title="Back"><i class="bi bi-arrow-left"></i></a>
    </div>
</header>

<div class="studio-wrapper">
    <?php if (isset($msg)): ?>
        <div class="alert alert-success bg-dark text-success border-success mb-4"><?= $msg ?></div>
    <?php endif; ?>

    <div class="grid" id="albumGrid">
        <?php if (mysqli_num_rows($albums) > 0):
            while ($row = mysqli_fetch_assoc($albums)):
                $id = $row['id'];
                $title = htmlspecialchars($row['title']);
                $artist = htmlspecialchars($row['artist']);
                $cover = $row['cover'];
                $audio = $row['audio'];
                $video = $row['video'];
        ?>
        <div class="music-card album-card" id="card-<?php echo $id; ?>" data-title="<?php echo strtolower($title); ?>" data-artist="<?php echo strtolower($artist); ?>">
            <div class="card-actions">
                <a href="edit_album.php?id=<?php echo $id; ?>" class="btn-action"><i class="bi bi-pencil"></i></a>
                <a href="?delete=<?php echo $id; ?>" class="btn-action" onclick="return confirm('Delete?');"><i class="bi bi-trash"></i></a>
            </div>

            <div class="image-container">
                <?php if(!empty($video)): ?>
                    <video id="vid-<?php echo $id; ?>" loop muted playsinline poster="../admin/uploads/albums/<?php echo $cover; ?>" style="position:absolute; width:100%; height:100%; object-fit:cover; opacity:0.4;">
                        <source src="../admin/uploads/albums/<?php echo $video; ?>" type="video/mp4">
                    </video>
                <?php else: ?>
                    <img src="../admin/uploads/albums/<?php echo $cover; ?>" style="position:absolute; width:100%; height:100%; object-fit:cover; opacity:0.5;">
                <?php endif; ?>
                
                <div class="inner-glow">
                    <i class="bi bi-music-note-beamed" style="font-size: 1.5rem;"></i>
                </div>
            </div>

            <div class="title"><?php echo $title; ?></div>
            <div class="artist"><?php echo $artist; ?></div>

            <div class="controls">
                <?php if(!empty($audio)): ?>
                    <audio id="aud-<?php echo $id; ?>" src="../admin/uploads/albums/<?php echo $audio; ?>"></audio>
                <?php endif; ?>
                <button class="play-btn" onclick="togglePlay('<?php echo $id; ?>', this)">
                    <i class="bi bi-play-fill"></i>
                </button>
            </div>
        </div>
        <?php endwhile; else: ?>
            <div class="text-center w-100 py-5"><p class="text-muted">Empty Studio</p></div>
        <?php endif; ?>
    </div>
</div>

<footer>&copy; 2026 ALBUMS STUDIO &bull; SOUND SYSTEM</footer>

<script>
document.getElementById("search").addEventListener("input", function(){
    let val = this.value.toLowerCase().trim();
    document.querySelectorAll(".album-card").forEach(card=>{
        let title = card.dataset.title;
        let artist = card.dataset.artist;
        card.style.display = (title.includes(val) || artist.includes(val)) ? "block" : "none";
    });
});

function togglePlay(id, btn){
    const audio = document.getElementById('aud-'+id);
    const video = document.getElementById('vid-'+id);
    const card = document.getElementById('card-'+id);
    const icon = btn.querySelector('i');

    // Reset others
    document.querySelectorAll('audio, video').forEach(m => {
        if(m !== audio && m !== video) m.pause();
    });
    document.querySelectorAll('.music-card').forEach(c => c.classList.remove('playing'));
    document.querySelectorAll('.play-btn i').forEach(i => i.className = 'bi bi-play-fill');

    if(audio.paused){
        audio.play();
        if(video) video.play();
        card.classList.add('playing');
        icon.className = 'bi bi-pause-fill';
    } else {
        audio.pause();
        if(video) video.pause();
        card.classList.remove('playing');
        icon.className = 'bi bi-play-fill';
    }
}
</script>

</body>
</html>