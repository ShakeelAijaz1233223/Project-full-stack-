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

        .music-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: 0.3s ease;
            position: relative;
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
        }

        .playing .inner-glow {
            animation: pulse 1.2s infinite alternate;
        }

        @keyframes pulse {
            from { transform: scale(1); }
            to { transform: scale(1.1); }
        }

        .progress-container { width: 100%; margin: 15px 0 10px; }

        .seek-bar {
            width: 100%;
            height: 5px;
            -webkit-appearance: none;
            background: #222;
            border-radius: 10px;
            cursor: pointer;
            outline: none;
        }

        .seek-bar::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 14px;
            height: 14px;
            background: var(--accent);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--accent);
        }

        .time-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.7rem;
            color: #666;
            margin-top: 5px;
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
            border: none;
            border-radius: 50%;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .nav-btn { background: none; border: none; color: #555; font-size: 1.2rem; cursor: pointer; }

        .title { font-weight: 700; font-size: 0.95rem; text-align: center; margin: 0; }
        .artist { font-size: 0.75rem; color: #555; text-align: center; text-transform: uppercase; }

        footer { padding: 40px; text-align: center; font-size: 0.7rem; color: #333; }
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
    <div class="grid" id="musicGrid">
        <?php while($row = mysqli_fetch_assoc($music)): ?>
        <div class="music-card">
            <div class="image-container">
                <div class="inner-glow">
                    <i class="bi bi-music-note-beamed text-white fs-4"></i>
                </div>
            </div>

            <p class="title"><?= htmlspecialchars($row['title']) ?></p>
            <p class="artist"><?= htmlspecialchars($row['artist']) ?></p>

            <div class="progress-container">
                <input type="range" class="seek-bar" value="0" min="0" max="100" 
                       oninput="manualSeek(this)" 
                       onchange="manualSeek(this)">
                <div class="time-info">
                    <span class="curr-time">0:00</span>
                    <span class="dur-time">0:00</span>
                </div>
            </div>

            <div class="controls">
                <button class="nav-btn" onclick="skip(this, -10)"><i class="bi bi-rewind-fill"></i></button>
                <button class="play-btn" onclick="togglePlay(this)"><i class="bi bi-play-fill"></i></button>
                <button class="nav-btn" onclick="skip(this, 10)"><i class="bi bi-fast-forward-fill"></i></button>
            </div>

            <audio class="player" preload="metadata" 
                   ontimeupdate="updateUI(this)" 
                   onloadedmetadata="loadDur(this)"
                   onplay="playVisual(this, true)"
                   onpause="playVisual(this, false)">
                <source src="../admin/uploads/music/<?= $row['file'] ?>" type="audio/mpeg">
            </audio>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
    // Format Seconds to 0:00
    function fmt(s) {
        if(isNaN(s)) return "0:00";
        let m = Math.floor(s/60);
        let sec = Math.floor(s%60);
        return m + ":" + (sec < 10 ? "0"+sec : sec);
    }

    // 1. Load Duration when file metadata is ready
    function loadDur(audio) {
        const card = audio.closest('.music-card');
        card.querySelector('.dur-time').innerText = fmt(audio.duration);
    }

    // 2. Update Seekbar Line as song plays
    function updateUI(audio) {
        const card = audio.closest('.music-card');
        const bar = card.querySelector('.seek-bar');
        const curr = card.querySelector('.curr-time');
        
        // Update line position
        if(!audio.seeking) { // Prevents jumping while dragging
            let percent = (audio.currentTime / audio.duration) * 100;
            bar.value = percent || 0;
        }
        curr.innerText = fmt(audio.currentTime);
    }

    // 3. MANUAL MOVE (The Line Movement Fix)
    function manualSeek(input) {
        const card = input.closest('.music-card');
        const audio = card.querySelector('.player');
        // Calculate new time: (percent / 100) * total duration
        const newTime = (input.value / 100) * audio.duration;
        audio.currentTime = newTime;
    }

    // 4. Play/Pause Logic
    function togglePlay(btn) {
        const card = btn.closest('.music-card');
        const audio = card.querySelector('.player');

        if(audio.paused) {
            // Stop all others
            document.querySelectorAll('.player').forEach(p => {
                p.pause();
                p.closest('.music-card').querySelector('.play-btn i').className = 'bi bi-play-fill';
            });
            audio.play();
            btn.querySelector('i').className = 'bi bi-pause-fill';
        } else {
            audio.pause();
            btn.querySelector('i').className = 'bi bi-play-fill';
        }
    }

    // 5. Skip Logic
    function skip(btn, val) {
        const audio = btn.closest('.music-card').querySelector('.player');
        audio.currentTime += val;
    }

    // 6. Animation Visuals
    function playVisual(audio, isPlaying) {
        const card = audio.closest('.music-card');
        if(isPlaying) card.classList.add('playing');
        else card.classList.remove('playing');
    }

    // Search logic
    document.getElementById("search").addEventListener("input", function() {
        let v = this.value.toLowerCase();
        document.querySelectorAll(".music-card").forEach(c => {
            let t = c.innerText.toLowerCase();
            c.style.display = t.includes(v) ? "block" : "none";
        });
    });
</script>

</body>
</html>