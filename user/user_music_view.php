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
        --text-muted: #777777;
    }

    body {
        background: var(--bg-dark);
        color: #fff;
        font-family: 'Inter', sans-serif;
        margin: 0;
    }

    .studio-wrapper {
        width: 95%;
        margin: 0 auto;
        padding: 15px 0;
    }

    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 1px solid #1a1a1a;
        padding-bottom: 15px;
    }

    h2 { font-size: 1.2rem; letter-spacing: 1px; }

    .search-box {
        background: #151515;
        border: 1px solid #222;
        color: white;
        border-radius: 4px;
        padding: 6px 12px;
        width: 240px;
        font-size: 0.8rem;
    }

    /* Back Button Styling */
    .btn-back {
        background: #151515;
        border: 1px solid #222;
        color: #fff;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.8rem;
        text-decoration: none;
        transition: 0.3s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-back:hover {
        background: #222;
        border-color: var(--accent);
        color: #fff;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 12px;
    }

    .music-card {
        background: var(--card-bg);
        border-radius: 8px;
        padding: 12px;
        transition: 0.3s;
        border: 1px solid transparent;
        text-align: center;
    }
    .music-card:hover {
        background: #181818;
        transform: translateY(-3px);
    }

    .disc-wrapper {
        position: relative;
        width: 80px;
        height: 80px;
        margin: 0 auto 10px;
        border-radius: 50%;
        background: #000;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #1a1a1a;
        box-shadow: 0 4px 10px rgba(0,0,0,0.4);
    }

    .disc-wrapper i {
        font-size: 2rem;
        color: var(--accent);
    }

    .playing .disc-wrapper {
        animation: rotateDisc 3s linear infinite;
        border-color: var(--accent);
    }

    @keyframes rotateDisc {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .play-trigger {
        position: absolute;
        width: 30px;
        height: 30px;
        background: var(--accent-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        border: none;
        opacity: 0;
        transition: 0.3s;
    }
    .music-card:hover .play-trigger { opacity: 1; }

    
        .studio-wrapper {
            width: 90%;
            margin: 0 auto;
            padding: 40px 0;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 25px;
        }

        /* --- UPDATED CARD DESIGN --- */
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
            box-shadow: 0 10px 30px rgba(255, 0, 85, 0.1);
        }

        /* Reference Image Style */
        .image-container {
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

        /* Inner glowing circle from your image */
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
    .title {
        font-weight: 600;
        font-size: 0.8rem;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .artist {
        font-size: 0.7rem;
        color: var(--text-muted);
        margin-top: 1px;
    }

    audio {
        width: 100%;
        height: 24px;
        margin-top: 8px;
        filter: invert(1) hue-rotate(180deg) brightness(1.5);
        opacity: 0.3;
    }
    .music-card:hover audio { opacity: 1; }

    footer {
        padding: 30px;
        text-align: center;
        font-size: 0.65rem;
        color: #333;
    }
</style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h2 class="m-0 fw-bold">MUSIC<span style="color: var(--accent);">STUDIO</span></h2>
        
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search track...">
            <a href="javascript:history.back()" class="btn-back">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

   <div class="studio-wrapper">
    <div class="grid" id="musicGrid">
        <?php if (mysqli_num_rows($music) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($music)):
                $title = htmlspecialchars($row['title']);
                $artist = htmlspecialchars($row['artist']);
                $file  = $row['file'];
            ?>
                <div class="music-card" data-title="<?= strtolower($title); ?>" data-artist="<?= strtolower($artist); ?>">

                    <div class="image-container">
                        <div class="inner-glow">
                            <i class="bi bi-music-note-beamed text-white"></i>
                        </div>
                    </div>

                    <div class="info">
                        <p class="title"><?= $title; ?></p>
                        <p class="artist"><?= $artist; ?></p>
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

                    <audio class="audio-player" onplay="handlePlay(this)" onpause="handlePause(this)">
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
document.getElementById("search").addEventListener("input", function() {
    let val = this.value.toLowerCase();
    document.querySelectorAll(".music-card").forEach(card => {
        let text = card.dataset.title + " " + card.dataset.artist;
        card.style.display = text.includes(val) ? "block" : "none";
    });
});

function toggleMusic(btn) {
    const card = btn.closest('.music-card');
    const audio = card.querySelector('audio');
    if (audio.paused) {
        document.querySelectorAll('audio').forEach(a => {
            a.pause();
            a.closest('.music-card').classList.remove('playing');
        });
        audio.play();
    } else {
        audio.pause();
    }
}

function handlePlay(el) {
    el.closest('.music-card').classList.add('playing');
    el.closest('.music-card').querySelector('.play-trigger i').className = 'bi bi-pause-fill';
}

function handlePause(el) {
    el.closest('.music-card').classList.remove('playing');
    el.closest('.music-card').querySelector('.play-trigger i').className = 'bi bi-play-fill';
}
</script>

</body>
</html>