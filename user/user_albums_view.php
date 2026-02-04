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

// Fetch albums
$albums = mysqli_query($conn, "SELECT * FROM albums ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Albums Studio | Music Style</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">

<style>
:root {
    --bg-dark: #050505;
    --card-bg: #0a0a0a;
    --accent: #ff0055;
    --accent-gradient: linear-gradient(45deg, #ff0055, #ff5e00);
    --border-glass: rgba(255, 0, 85, 0.3);
    --text-muted: #555;
}

body {
    background: var(--bg-dark);
    color: #fff;
    font-family: 'Plus Jakarta Sans', sans-serif;
    margin: 0;
}

header {
    background: rgba(5,5,5,0.9);
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

.album-card {
    background: var(--card-bg);
    border-radius: 20px;
    padding: 20px;
    border: 1px solid rgba(255,255,255,0.05);
    text-align: center;
    transition: 0.3s ease;
    position: relative;
}

.album-card:hover {
    border-color: var(--accent);
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(255,0,85,0.1);
}

.media-container {
    width: 120px;
    height: 120px;
    margin: 0 auto 15px;
    border-radius: 20px;
    border: 2px solid var(--border-glass);
    display: flex;
    align-items: center;
    justify-content: center;
    background: #000;
    position: relative;
    overflow: hidden;
}

.inner-glow {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: radial-gradient(circle, #ff0055 20%, #80002b 60%, #000 100%);
    box-shadow: 0 0 15px #ff0055;
    display: flex;
    align-items: center;
    justify-content: center;
}

.playing .inner-glow {
    animation: pulse 1.5s infinite alternate;
}

@keyframes pulse {
    from { transform: scale(1); box-shadow: 0 0 15px #ff0055; }
    to { transform: scale(1.1); box-shadow: 0 0 25px #ff0055; }
}

.title { font-weight: 700; font-size: 0.95rem; margin-bottom: 2px; }
.artist { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; }

.controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-top: 10px;
}

.nav-btn {
    background: none;
    border: none;
    color: #777;
    font-size: 1.2rem;
    cursor: pointer;
}

.nav-btn:hover { color: #fff; }

.play-btn {
    width: 45px;
    height: 45px;
    background: var(--accent-gradient);
    border-radius: 50%;
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(255,0,85,0.3);
}

.card-actions {
    position: absolute;
    top: 8px;
    right: 8px;
    display: flex;
    gap: 6px;
}

.action-btn {
    background: rgba(0,0,0,0.6);
    border-radius: 8px;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 0.8rem;
    text-decoration: none;
}

footer {
    padding: 40px;
    text-align: center;
    font-size: 0.7rem;
    color: #333;
}
</style>
</head>
<body>

<header>
    <a href="index.php" class="logo">SOU<span>N</span>D</a>
    <div class="d-flex align-items-center gap-2">
        <input type="text" id="search" class="search-box" placeholder="Search albums...">
        <a href="index.php" style="color:#fff;"><i class="bi bi-arrow-left"></i></a>
    </div>
</header>

<div class="studio-wrapper">

<?php if(isset($msg)): ?>
<div class="alert alert-success bg-dark text-success"><?= $msg ?></div>
<?php endif; ?>

<div class="grid" id="albumGrid">
<?php if(mysqli_num_rows($albums) > 0):
    while($row = mysqli_fetch_assoc($albums)):
        $id = $row['id'];
        $title = htmlspecialchars($row['title']);
        $artist = htmlspecialchars($row['artist']);
        $cover = $row['cover'];
        $audio = $row['audio'];
        $video = $row['video'];
?>
<div class="album-card" data-title="<?= strtolower($title) ?>" data-artist="<?= strtolower($artist) ?>">

    <div class="card-actions">
        <a href="edit_album.php?id=<?= $id ?>" class="action-btn"><i class="bi bi-pencil"></i></a>
        <a href="?delete=<?= $id ?>" class="action-btn" onclick="return confirm('Delete this album?');"><i class="bi bi-trash"></i></a>
    </div>

    <div class="media-container">
        <div class="inner-glow"><i class="bi bi-music-note-beamed"></i></div>
        <?php if(!empty($video)): ?>
            <video id="vid-<?= $id ?>" preload="metadata" muted loop poster="../admin/uploads/albums/<?= $cover ?>"></video>
        <?php else: ?>
            <img src="../admin/uploads/albums/<?= $cover ?>" alt="<?= $title ?>">
        <?php endif; ?>
    </div>

    <div class="title"><?= $title ?></div>
    <div class="artist"><?= $artist ?></div>

    <?php if(!empty($audio)): ?>
    <audio id="aud-<?= $id ?>" onplay="handlePlay(this)" onpause="handlePause(this)">
        <source src="../admin/uploads/albums/<?= $audio ?>" type="audio/mpeg">
    </audio>

    <div class="controls">
        <button class="nav-btn" onclick="skipMedia('<?= $id ?>', -10)"><i class="bi bi-rewind-fill"></i></button>
        <button class="play-btn" onclick="togglePlayback('<?= $id ?>', this)"><i class="bi bi-play-fill"></i></button>
        <button class="nav-btn" onclick="skipMedia('<?= $id ?>', 10)"><i class="bi bi-fast-forward-fill"></i></button>
    </div>
    <?php endif; ?>

</div>
<?php endwhile; else: ?>
<p class="text-muted text-center w-100">No Albums Found</p>
<?php endif; ?>
</div>
</div>

<footer>SOUND ENTERTAINMENT &bull; 2026</footer>

<script>
// Search
document.getElementById("search").addEventListener("input", function(){
    let val = this.value.toLowerCase();
    document.querySelectorAll(".album-card").forEach(card => {
        let text = card.dataset.title + " " + card.dataset.artist;
        card.style.display = text.includes(val) ? "block" : "none";
    });
});

// Play/Pause & Glow Logic
function togglePlayback(id, btn){
    const audio = document.getElementById('aud-'+id);
    const card = btn.closest('.album-card');
    const glow = card.querySelector('.inner-glow');
    const icon = btn.querySelector('i');

    // Pause all others
    document.querySelectorAll('audio').forEach(a=>{ if(a!==audio) a.pause(); });
    document.querySelectorAll('.album-card').forEach(c=>c.classList.remove('playing'));
    document.querySelectorAll('.play-btn i').forEach(i=>i.className='bi bi-play-fill');

    if(audio.paused){
        audio.play().catch(()=>{});
        card.classList.add('playing');
        icon.className='bi bi-pause-fill';
    } else {
        audio.pause();
        card.classList.remove('playing');
        icon.className='bi bi-play-fill';
    }
}

function skipMedia(id, secs){
    const audio = document.getElementById('aud-'+id);
    if(audio) audio.currentTime += secs;
}

function handlePlay(current){ document.querySelectorAll('audio').forEach(a=>{ if(a!==current) a.pause(); }); }
function handlePause(current){}
</script>

</body>
</html>
