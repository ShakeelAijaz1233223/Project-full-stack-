<?php
session_start();
include "../config/db.php";

// Fetch all music
$music = mysqli_query($conn, "SELECT * FROM music ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Studio | Pro Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #050505;
            --card-bg: #0a0a0a;
            --accent: #ff0055;
            --accent-gradient: linear-gradient(45deg, #ff0055, #ff5e00);
            --border-glass: rgba(255, 0, 85, 0.3);
        }

        body {
            background: var(--bg-dark);
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
        }

        /* --- HEADER --- */
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

        /* --- GRID --- */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
        }

        /* --- MUSIC CARD --- */
        .music-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: 0.3s ease;
            position: relative;
        }

        .music-card:hover {
            border-color: var(--accent);
            transform: translateY(-5px);
        }

        .image-container {
            width: 100%;
            aspect-ratio: 1/1;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            border: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 15px;
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
            transition: 0.3s;
        }

        .playing .inner-glow {
            animation: pulse 1.2s infinite alternate;
            box-shadow: 0 0 35px #ff0055;
        }

        @keyframes pulse {
            from { transform: scale(1); opacity: 0.8; }
            to { transform: scale(1.15); opacity: 1; }
        }

        /* --- PROGRESS LINE (MOVING LINE) --- */
        .progress-container {
            width: 100%;
            margin: 15px 0 10px;
        }

        .seek-bar {
            width: 100%;
            height: 4px;
            -webkit-appearance: none;
            background: #222;
            border-radius: 10px;
            cursor: pointer;
            outline: none;
        }

        .seek-bar::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 12px;
            height: 12px;
            background: var(--accent);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--accent);
        }

        .time-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.65rem;
            color: #555;
            margin-top: 5px;
        }

        /* --- CONTROLS --- */
        .controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
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
            box-shadow: 0 4px 15px rgba(255, 0, 85, 0.3);
            cursor: pointer;
        }

        .nav-btn { background: none; border: none; color: #555; font-size: 1.2rem; cursor: pointer; }
        .nav-btn:hover { color: #fff; }

        .title { font-weight: 700; font-size: 0.95rem; margin: 0; text-align: center; color: #fff; }
        .artist { font-size: 0.75rem; color: #555; text-transform: uppercase; text-align: center; margin-bottom: 5px; }

        footer { padding: 40px; text-align: center; font-size: 0.7rem; color: #333; letter-spacing: 2px; }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">SOU<span>N</span>D</a>
    <div class="d-flex align-items-center gap-2">
        <input type="text" id="search" class="search-box" placeholder="Search music...">
        <a href="index.php" style="color:#fff;"><i class="bi bi-arrow-left"></i></a>
    </div>
</header>

<div class="studio-wrapper">
    <div class="mb-4">
        <h4 class="fw-bold">MUSIC<span style="color: var(--accent);">STUDIO</span></h4>
        <p class="text-muted small">Professional Audio Dashboard</p>
    </div>

    <div class="grid" id="musicGrid">
        <?php if(mysqli_num_rows($music) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($music)):
                $id = $row['id'];
                $title = htmlspecialchars($row['title']);
                $artist = htmlspecialchars($row['artist']);
                $file  = $row['file'];
            ?>
            <div class="music-card" data-title="<?= strtolower($title); ?>" data-artist="<?= strtolower($artist); ?>">
                
                <div class="image-container">
                    <div class="inner-glow">
                        <i class="bi bi-music-note-beamed text-white fs-4"></i>
                    </div>
                </div>

                <div class="info">
                    <p class="title"><?= $title; ?></p>
                    <p class="artist"><?= $artist; ?></p>
                </div>

                <div class="progress-container">
                    <input type="range" class="seek-bar" value="0" max="100" oninput="seekAudio(this)">
                    <div class="time-info">
                        <span class="current-time">0:00</span>
                        <span class="duration">0:00</span>
                    </div>
                </div>

                <div class="controls">
                    <button class="nav-btn" onclick="skip(this, -10)"><i class="bi bi-rewind-fill"></i></button>
                    <button class="play-btn" onclick="toggleMusic(this)">
                        <i class="bi bi-play-fill"></i>
                    </button>
                    <button class="nav-btn" onclick="skip(this, 10)"><i class="bi bi-fast-forward-fill"></i></button>
                </div>

                <audio class="audio-player" 
                       onplay="handlePlay(this)" 
                       onpause="handlePause(this)" 
                       ontimeupdate="updateProgress(this)" 
                       onloadedmetadata="initDuration(this)">
                    <source src="../admin/uploads/music/<?= $file; ?>" type="audio/mpeg">
                </audio>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center w-100 py-5">
                <p class="text-muted">No music tracks found in your studio.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer>SOUND ENTERTAINMENT &bull; 2026</footer>

<script>
// Format time utility
function formatTime(secs) {
    if (isNaN(secs)) return "0:00";
    let min = Math.floor(secs / 60);
    let sec = Math.floor(secs % 60);
    if (sec < 10) sec = `0${sec}`;
    return `${min}:${sec}`;
}

// Initial total time
function initDuration(audio) {
    const card = audio.closest('.music-card');
    card.querySelector('.duration').innerText = formatTime(audio.duration);
}

// Update the "Line" and Time as it plays
function updateProgress(audio) {
    const card = audio.closest('.music-card');
    const seekBar = card.querySelector('.seek-bar');
    const currentTimeText = card.querySelector('.current-time');
    
    if (!audio.duration) return;
    const percentage = (audio.currentTime / audio.duration) * 100;
    seekBar.value = percentage;
    currentTimeText.innerText = formatTime(audio.currentTime);
}

// Manual Seek (moving line "ahgi/peshi")
function seekAudio(slider) {
    const card = slider.closest('.music-card');
    const audio = card.querySelector('audio');
    const seekTo = (slider.value / 100) * audio.duration;
    audio.currentTime = seekTo;
}

// Master Play/Pause
function toggleMusic(btn) {
    const card = btn.closest('.music-card');
    const audio = card.querySelector('audio');
    
    if (audio.paused) {
        // Stop all other playing music cards
        document.querySelectorAll('audio').forEach(a => { 
            if(a !== audio) {
                a.pause();
                a.closest('.music-card').classList.remove('playing');
                a.closest('.music-card').querySelector('.play-btn i').className = 'bi bi-play-fill';
            }
        });
        audio.play();
    } else {
        audio.pause();
    }
}

// Skip 10s
function skip(btn, secs) {
    const audio = btn.closest('.music-card').querySelector('audio');
    audio.currentTime += secs;
}

// Visual Handlers
function handlePlay(el) {
    el.closest('.music-card').classList.add('playing');
    el.closest('.music-card').querySelector('.play-btn i').className = 'bi bi-pause-fill';
}

function handlePause(el) {
    el.closest('.music-card').classList.remove('playing');
    el.closest('.music-card').querySelector('.play-btn i').className = 'bi bi-play-fill';
}

// Real-time Search
document.getElementById("search").addEventListener("input", function() {
    let val = this.value.toLowerCase().trim();
    document.querySelectorAll(".music-card").forEach(card => {
        let text = card.dataset.title + " " + card.dataset.artist;
        card.style.display = text.includes(val) ? "block" : "none";
    });
});
</script>

</body>
</html>