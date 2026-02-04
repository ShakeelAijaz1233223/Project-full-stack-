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
        }
        .logo span { color: var(--accent); }

        .search-box {
            background: #151515;
            border: 1px solid #222;
            color: white;
            border-radius: 50px;
            padding: 6px 18px;
            width: 200px;
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

        .music-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            text-align: center;
            transition: 0.3s ease;
        }

        .music-card:hover {
            border-color: var(--accent);
            transform: translateY(-5px);
        }

        .image-container {
            width: 100px;
            height: 100px;
            margin: 0 auto 15px;
            border-radius: 20px;
            border: 2px solid var(--border-glass);
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
            position: relative;
        }

        .inner-glow {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: radial-gradient(circle, #ff0055 20%, #80002b 60%, #000 100%);
            box-shadow: 0 0 15px #ff0055;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .playing .inner-glow { animation: pulse 1.5s infinite alternate; }

        @keyframes pulse {
            from { transform: scale(1); }
            to { transform: scale(1.1); }
        }

        /* --- PROGRESS LINE (SEEKBAR) --- */
        .progress-container {
            width: 100%;
            margin: 15px 0;
            display: flex;
            flex-direction: column;
            gap: 5px;
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
            cursor: pointer;
            box-shadow: 0 0 10px var(--accent);
        }

        .time-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.65rem;
            color: #555;
        }

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
            border-radius: 50%;
            border: none;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-btn { background: none; border: none; color: #555; font-size: 1.2rem; cursor: pointer; }
        .nav-btn:hover { color: #fff; }

        .title { font-weight: 700; font-size: 0.9rem; margin-bottom: 2px; color: #fff; }
        .artist { font-size: 0.7rem; color: #555; text-transform: uppercase; }

        footer { padding: 40px; text-align: center; font-size: 0.7rem; color: #333; }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">SOU<span>N</span>D</a>
    <div class="d-flex align-items-center gap-2">
        <input type="text" id="search" class="search-box" placeholder="Search...">
        <a href="index.php" class="btn-back"><i class="bi bi-arrow-left"></i></a>
    </div>
</header>

<div class="studio-wrapper">
    <div class="grid" id="musicGrid">
        <?php if(mysqli_num_rows($music) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($music)):
                $title = htmlspecialchars($row['title']);
                $artist = htmlspecialchars($row['artist']);
                $file  = $row['file'];
            ?>
            <div class="music-card" data-title="<?= strtolower($title); ?>">
                
                <div class="image-container">
                    <div class="inner-glow">
                        <i class="bi bi-music-note-beamed text-white"></i>
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
                    <button class="play-btn" onclick="toggleMusic(this)"><i class="bi bi-play-fill"></i></button>
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
            <p class="text-center w-100 py-5 text-muted">No music available.</p>
        <?php endif; ?>
    </div>
</div>

<footer>SOUND ENTERTAINMENT &bull; 2026</footer>

<script>
// Format time in MM:SS
function formatTime(secs) {
    let min = Math.floor(secs / 60);
    let sec = Math.floor(secs % 60);
    if (sec < 10) sec = `0${sec}`;
    return `${min}:${sec}`;
}

// Set total duration when audio loads
function initDuration(audio) {
    const card = audio.closest('.music-card');
    card.querySelector('.duration').innerText = formatTime(audio.duration);
}

// Update line (progress bar) as music plays
function updateProgress(audio) {
    const card = audio.closest('.music-card');
    const seekBar = card.querySelector('.seek-bar');
    const currentTimeText = card.querySelector('.current-time');
    
    const percentage = (audio.currentTime / audio.duration) * 100;
    seekBar.value = percentage;
    currentTimeText.innerText = formatTime(audio.currentTime);
}

// Logic to move line "ahgi/peshi" manually
function seekAudio(slider) {
    const card = slider.closest('.music-card');
    const audio = card.querySelector('audio');
    const seekTo = (slider.value / 100) * audio.duration;
    audio.currentTime = seekTo;
}

function toggleMusic(btn) {
    const card = btn.closest('.music-card');
    const audio = card.querySelector('audio');
    if (audio.paused) {
        document.querySelectorAll('audio').forEach(a => { if(a !== audio) a.pause(); });
        audio.play();
    } else {
        audio.pause();
    }
}

function skip(btn, secs) {
    const audio = btn.closest('.music-card').querySelector('audio');
    audio.currentTime += secs;
}

function handlePlay(el) {
    el.closest('.music-card').classList.add('playing');
    el.closest('.music-card').querySelector('.play-btn i').className = 'bi bi-pause-fill';
}

function handlePause(el) {
    el.closest('.music-card').classList.remove('playing');
    el.closest('.music-card').querySelector('.play-btn i').className = 'bi bi-play-fill';
}
</script>

</body>
</html>