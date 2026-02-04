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
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #050505;
            --card-bg: #111111;
            --accent: #ff0055;
            --accent-gradient: linear-gradient(45deg, #ff0055, #ff5e00);
            --text-muted: #777777;
            --border-glass: rgba(255, 255, 255, 0.1);
        }

        body {
            background: var(--bg-dark);
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        /* --- HEADER STYLING --- */
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
            border-bottom: 1px solid var(--border-glass);
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
            transition: 0.3s;
        }
        .search-box:focus {
            outline: none;
            border-color: var(--accent);
            width: 250px;
        }

        .studio-wrapper {
            width: 90%;
            margin: 0 auto;
            padding: 40px 0;
        }

        /* --- GRID & CARDS --- */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
        }

        .music-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid var(--border-glass);
            text-align: center;
            position: relative;
        }
        .music-card:hover {
            background: #181818;
            transform: translateY(-8px);
            border-color: var(--accent);
            box-shadow: 0 15px 30px rgba(0,0,0,0.5);
        }

        .disc-wrapper {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto 15px;
            border-radius: 50%;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid #1a1a1a;
            transition: 0.3s;
        }

        .disc-wrapper i {
            font-size: 2.5rem;
            color: var(--accent);
        }

        /* Animation when playing */
        .playing .disc-wrapper {
            animation: rotateDisc 3s linear infinite;
            border-color: var(--accent);
            box-shadow: 0 0 20px rgba(255, 0, 85, 0.2);
        }

        @keyframes rotateDisc {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .play-trigger {
            position: absolute;
            width: 40px;
            height: 40px;
            background: var(--accent-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            border: none;
            opacity: 0;
            transform: scale(0.5);
            transition: 0.3s;
            cursor: pointer;
        }
        .music-card:hover .play-trigger { 
            opacity: 1; 
            transform: scale(1);
        }

        .title {
            font-weight: 700;
            font-size: 0.9rem;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #fff;
        }

        .artist {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        audio {
            width: 100%;
            height: 30px;
            margin-top: 15px;
            filter: invert(1) hue-rotate(180deg) brightness(1.5);
            opacity: 0.1;
            transition: 0.3s;
        }
        .music-card:hover audio { opacity: 0.8; }

        .btn-back {
            background: transparent;
            border: 1px solid var(--border-glass);
            color: #fff;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            transition: 0.3s;
            text-transform: uppercase;
        }
        .btn-back:hover {
            background: #fff;
            color: #000;
        }

        /* --- MOBILE RESPONSIVENESS --- */
        @media (max-width: 768px) {
            header { padding: 15px 15px; }
            .search-box { width: 120px; }
            .search-box:focus { width: 150px; }
            .grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 15px;
            }
            .disc-wrapper { width: 70px; height: 70px; }
            .disc-wrapper i { font-size: 1.8rem; }
            .studio-wrapper { width: 95%; }
        }

        footer {
            padding: 40px;
            text-align: center;
            font-size: 0.7rem;
            color: #444;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">SOU<span>N</span>D</a>
    
    <div class="d-flex align-items-center gap-2">
        <input type="text" id="search" class="search-box" placeholder="Search track...">
        <a href="javascript:history.back()" class="btn-back">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
</header>

<div class="studio-wrapper">
    <div class="mb-5">
        <h2 class="fw-bold" style="font-family: 'Syncopate'; font-size: 1.5rem;">MUSIC <span style="color: var(--accent);">STUDIO</span></h2>
        <p class="text-muted small">Manage and preview your sound library</p>
    </div>

    <div class="grid" id="musicGrid">
        <?php if(mysqli_num_rows($music) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($music)):
                $title = htmlspecialchars($row['title']);
                $artist = htmlspecialchars($row['artist']);
                $file  = $row['file'];
            ?>
            <div class="music-card" data-title="<?php echo strtolower($title); ?>" data-artist="<?php echo strtolower($artist); ?>">
                
                <div class="disc-wrapper">
                    <i class="bi bi-vinyl-fill"></i>
                    <button class="play-trigger" onclick="toggleMusic(this)">
                        <i class="bi bi-play-fill"></i>
                    </button>
                </div>

                <div class="info-section">
                    <p class="title"><?php echo $title; ?></p>
                    <p class="artist"><?php echo $artist; ?></p>
                </div>

                <audio class="audio-player" onplay="handlePlay(this)" onpause="handlePause(this)">
                    <source src="../admin/uploads/music/<?php echo $file; ?>" type="audio/mpeg">
                </audio>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center w-100 py-5">
                <i class="bi bi-music-note-beamed" style="font-size: 3rem; color: #222;"></i>
                <p class="text-muted mt-3">No tracks found in the studio.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer>SOUND ENTERTAINMENT &bull; 2026 &bull; DESIGNED FOR THE FUTURE</footer>

<script>
// Live Search Logic
document.getElementById("search").addEventListener("input", function() {
    let val = this.value.toLowerCase();
    document.querySelectorAll(".music-card").forEach(card => {
        let text = card.dataset.title + " " + card.dataset.artist;
        card.style.display = text.includes(val) ? "block" : "none";
    });
});

// Audio Controller Logic
function toggleMusic(btn) {
    const card = btn.closest('.music-card');
    const audio = card.querySelector('audio');
    
    if (audio.paused) {
        // Stop all other playing tracks
        document.querySelectorAll('audio').forEach(a => {
            if(a !== audio) {
                a.pause();
                a.currentTime = 0; // Reset others
            }
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