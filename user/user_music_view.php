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

    <style>
        :root {
            --bg-dark: #0a0a0a;
            --card-bg: #141414;
            --accent: #ff0055;
            --accent-glow: rgba(255, 0, 85, 0.5);
            --text-muted: #888;
        }

        body {
            background: var(--bg-dark);
            color: #fff;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        .studio-wrapper {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 0;
        }

        /* --- HEADER --- */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #222;
        }

        .search-box {
            background: #1a1a1a;
            border: 1px solid #333;
            color: white;
            border-radius: 8px;
            padding: 8px 15px;
            width: 250px;
            transition: 0.3s;
        }

        .search-box:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 10px var(--accent-glow);
        }

        /* --- GRID & CARDS --- */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .music-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid #222;
            transition: 0.3s all ease;
            position: relative;
        }

        .music-card:hover {
            border-color: var(--accent);
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }

        .image-container {
            width: 100px;
            height: 100px;
            margin: 0 auto 15px;
            border-radius: 50%;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            border: 2px solid #222;
        }

        .inner-glow {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--accent) 0%, #330011 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.5s;
        }

        /* Animation when playing */
        .playing .inner-glow {
            animation: pulse 1.5s infinite alternate;
            box-shadow: 0 0 20px var(--accent);
        }

        @keyframes pulse {
            from { transform: scale(1); opacity: 0.8; }
            to { transform: scale(1.1); opacity: 1; }
        }

        .title { font-weight: 700; font-size: 0.95rem; margin-bottom: 2px; }
        .artist { font-size: 0.8rem; color: var(--text-muted); margin-bottom: 15px; }

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
            border-radius: 50%;
            background: var(--accent);
            border: none;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: 0.2s;
        }

        .nav-btn {
            background: transparent;
            border: none;
            color: #777;
            font-size: 1.1rem;
            transition: 0.2s;
        }

        .nav-btn:hover { color: #fff; }
        .play-btn:hover { transform: scale(1.1); background: #ff3377; }

        .btn-back {
            background: #1a1a1a;
            border: 1px solid #333;
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
        }

        footer {
            text-align: center;
            padding: 40px;
            color: #444;
            font-size: 0.75rem;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h2 class="m-0 fw-bold">MUSIC<span style="color: var(--accent);">STUDIO</span></h2>
        
        <div class="d-flex align-items-center gap-3">
            <input type="text" id="search" class="search-box" placeholder="Search tracks...">
            <a href="javascript:history.back()" class="btn-back">
                <i class="bi bi-arrow-left"></i> BACK
            </a>
        </div>
    </div>

    <div class="grid" id="musicGrid">
        <?php if (mysqli_num_rows($music) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($music)): ?>
                <div class="music-card" 
                     data-title="<?= strtolower(htmlspecialchars($row['title'])); ?>" 
                     data-artist="<?= strtolower(htmlspecialchars($row['artist'])); ?>">

                    <div class="image-container">
                        <div class="inner-glow">
                            <i class="bi bi-music-note-beamed text-white fs-4"></i>
                        </div>
                    </div>

                    <div class="info text-center">
                        <p class="title m-0 text-truncate"><?= htmlspecialchars($row['title']); ?></p>
                        <p class="artist text-truncate"><?= htmlspecialchars($row['artist']); ?></p>
                    </div>

                    <div class="controls">
                        <button class="nav-btn" onclick="skip(this, -10)">
                            <i class="bi bi-rewind-fill"></i>
                        </button>

                        <button class="play-btn" onclick="toggleMusic(this)">
                            <i class="bi bi-play-fill"></i>
                        </button>

                        <button class="nav-btn" onclick="skip(this, 10)">
                            <i class="bi bi-fast-forward-fill"></i>
                        </button>
                    </div>

                    <audio class="audio-player">
                        <source src="../admin/uploads/music/<?= $row['file']; ?>" type="audio/mpeg">
                    </audio>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center w-100 py-5">
                <i class="bi bi-disc text-muted fs-1"></i>
                <p class="mt-3 text-muted">No music found in library.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer>SOUND ENTERTAINMENT &bull; 2026</footer>

<script>
// Search Filter
document.getElementById("search").addEventListener("input", function() {
    let val = this.value.toLowerCase();
    document.querySelectorAll(".music-card").forEach(card => {
        let text = card.dataset.title + " " + card.dataset.artist;
        card.style.display = text.includes(val) ? "block" : "none";
    });
});

// Play/Pause Toggle
function toggleMusic(btn) {
    const card = btn.closest('.music-card');
    const audio = card.querySelector('audio');
    const icon = btn.querySelector('i');

    if (audio.paused) {
        // Stop all other audios
        document.querySelectorAll('audio').forEach(a => {
            a.pause();
            a.closest('.music-card').classList.remove('playing');
            a.closest('.music-card').querySelector('.play-btn i').className = 'bi bi-play-fill';
        });

        audio.play();
        card.classList.add('playing');
        icon.className = 'bi bi-pause-fill';
    } else {
        audio.pause();
        card.classList.remove('playing');
        icon.className = 'bi bi-play-fill';
    }
}

// Skip 10s Forward/Back
function skip(btn, seconds) {
    const audio = btn.closest('.music-card').querySelector('audio');
    audio.currentTime += seconds;
}

// Reset UI when audio ends
document.querySelectorAll('.audio-player').forEach(player => {
    player.onended = function() {
        const card = this.closest('.music-card');
        card.classList.remove('playing');
        card.querySelector('.play-btn i').className = 'bi bi-play-fill';
    };
});
</script>

</body>
</html>