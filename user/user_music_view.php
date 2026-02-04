<?php
include "../config/db.php";

// Fetch all music
$music = mysqli_query($conn, "SELECT * FROM music ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Music Studio | Compact Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
:root {
    --bg-dark: #080808;
    --card-bg: #111111;
    --accent: #ff0055;
    --accent-gradient: linear-gradient(45deg, #ff0055, #ff5e00);
    --text-muted: #777;
    --border-glass: rgba(255,0,85,0.2);
}

body {
    background: var(--bg-dark);
    color: #fff;
    font-family: 'Inter', sans-serif;
    margin: 0;
}

.studio-wrapper {
    width: 90%;
    margin: 0 auto;
    padding: 40px 0;
}

.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    border-bottom: 1px solid #1a1a1a;
    padding-bottom: 15px;
}

h2 { font-size: 1.3rem; }

.search-box {
    background: #151515;
    border: 1px solid #222;
    color: white;
    border-radius: 50px;
    padding: 6px 18px;
    width: 220px;
    font-size: 0.85rem;
}

.btn-back {
    background: #151515;
    border: 1px solid #222;
    color: #fff;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 0.85rem;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: 0.3s;
}
.btn-back:hover { background: #222; border-color: var(--accent); }

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 25px;
}

/* Music Card */
.music-card {
    background: var(--card-bg);
    border-radius: 20px;
    padding: 20px;
    border: 1px solid rgba(255,255,255,0.05);
    text-align: center;
    transition: 0.3s ease;
    position: relative;
}
.music-card:hover {
    transform: translateY(-5px);
    border-color: var(--accent);
    box-shadow: 0 10px 30px rgba(255,0,85,0.1);
}

/* Disc / Inner Glow */
.image-container {
    width: 120px;
    height: 120px;
    margin: 0 auto 15px;
    border-radius: 20px;
    border: 2px solid var(--border-glass);
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
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

/* Title & Artist */
.title {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.artist {
    font-size: 0.75rem;
    color: var(--text-muted);
}

/* Controls */
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
    font-size: 1.3rem;
    box-shadow: 0 4px 15px rgba(255,0,85,0.3);
    cursor: pointer;
}

audio {
    display: none; /* Hidden, controlled via JS */
}

footer {
    padding: 30px;
    text-align: center;
    font-size: 0.7rem;
    color: #333;
}
</style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h2>MUSIC<span style="color: var(--accent);"> STUDIO</span></h2>
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search track...">
            <a href="javascript:history.back()" class="btn-back"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>

    <div class="grid" id="musicGrid">
        <?php if(mysqli_num_rows($music) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($music)):
                $title = htmlspecialchars($row['title']);
                $artist = htmlspecialchars($row['artist']);
                $file  = $row['file'];
            ?>
            <div class="music-card" data-title="<?= strtolower($title) ?>" data-artist="<?= strtolower($artist) ?>">
                <div class="image-container">
                    <div class="inner-glow"><i class="bi bi-music-note-beamed"></i></div>
                </div>
                <div class="title"><?= $title ?></div>
                <div class="artist"><?= $artist ?></div>

                <div class="controls">
                    <button class="nav-btn" onclick="skip(this,-10)"><i class="bi bi-rewind-fill"></i></button>
                    <button class="play-btn" onclick="toggleMusic(this)"><i class="bi bi-play-fill"></i></button>
                    <button class="nav-btn" onclick="skip(this,10)"><i class="bi bi-fast-forward-fill"></i></button>
                </div>
                <audio class="audio-player" onplay="handlePlay(this)" onpause="handlePause(this)">
                    <source src="../admin/uploads/music/<?= $file ?>" type="audio/mpeg">
                </audio>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-muted w-100 py-5">No music available.</p>
        <?php endif; ?>
    </div>
</div>

<footer>SOUND ENTERTAINMENT &bull; 2026</footer>

<script>
// Search
document.getElementById("search").addEventListener("input", function() {
    let val = this.value.toLowerCase();
    document.querySelectorAll(".music-card").forEach(card => {
        let text = card.dataset.title + " " + card.dataset.artist;
        card.style.display = text.includes(val) ? "block" : "none";
    });
});

// Play/Pause
function toggleMusic(btn) {
    const card = btn.closest('.music-card');
    const audio = card.querySelector('audio');
    const icon = btn.querySelector('i');

    // Pause all other audio
    document.querySelectorAll('audio').forEach(a=>{
        if(a!==audio) { a.pause(); a.closest('.music-card').classList.remove('playing'); a.closest('.music-card').querySelector('.play-btn i').className='bi bi-play-fill'; }
    });

    if(audio.paused){
        audio.play();
    } else {
        audio.pause();
    }
}

function handlePlay(audio){
    const card = audio.closest('.music-card');
    card.classList.add('playing');
    card.querySelector('.play-btn i').className='bi bi-pause-fill';
}

function handlePause(audio){
    const card = audio.closest('.music-card');
    card.classList.remove('playing');
    card.querySelector('.play-btn i').className='bi bi-play-fill';
}

// Skip forward/backward
function skip(btn, seconds){
    const card = btn.closest('.music-card');
    const audio = card.querySelector('audio');
    if(audio){
        audio.currentTime += seconds;
    }
}
</script>

</body>
</html>
