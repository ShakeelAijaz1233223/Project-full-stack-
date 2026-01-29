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
        --bg-dark: #080808;
        --card-bg: #121212;
        --accent: #ff0055;
        --accent-gradient: linear-gradient(45deg, #ff0055, #ff5e00);
        --text-muted: #888888;
    }

    body {
        background: var(--bg-dark);
        color: #fff;
        font-family: 'Inter', 'Segoe UI', sans-serif;
        margin: 0;
        padding: 0;
    }

    /* 95% Browser Width */
    .studio-wrapper {
        width: 95%;
        margin: 0 auto;
        padding: 30px 0;
    }

    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
        border-bottom: 1px solid #1a1a1a;
        padding-bottom: 20px;
    }

    .search-box {
        background: #1a1a1a;
        border: 1px solid #222;
        color: white;
        border-radius: 6px;
        padding: 10px 18px;
        width: 320px;
        font-size: 0.85rem;
        transition: 0.3s;
    }
    .search-box:focus {
        background: #222;
        border-color: var(--accent);
        box-shadow: 0 0 15px rgba(255, 0, 85, 0.2);
        outline: none;
        color: white;
    }

    /* High-Density Grid */
    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
    }

    /* Music Card Design */
    .music-card {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 15px;
        transition: all 0.3s ease;
        border: 1px solid transparent;
        text-align: center;
    }
    .music-card:hover {
        background: #1c1c1c;
        border-color: #333;
        transform: translateY(-5px);
    }

    /* Circular Disc Art */
    .disc-wrapper {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto 15px;
        border-radius: 50%;
        background: #000;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 4px solid #1a1a1a;
        box-shadow: 0 8px 20px rgba(0,0,0,0.5);
    }

    .disc-wrapper i {
        font-size: 3rem;
        color: var(--accent);
        transition: 0.3s;
    }

    /* Spinning Animation when playing */
    .playing .disc-wrapper {
        animation: rotateDisc 3s linear infinite;
        border-color: var(--accent);
    }

    @keyframes rotateDisc {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Centered Play Button */
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
        transition: 0.3s;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }
    .music-card:hover .play-trigger {
        opacity: 1;
    }

    .info-section {
        margin-bottom: 15px;
    }

    .title {
        font-weight: 700;
        font-size: 0.95rem;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .artist {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 2px;
    }

    /* Custom Minimal Audio Player */
    audio {
        width: 100%;
        height: 30px;
        filter: invert(1) hue-rotate(180deg) brightness(1.5);
        opacity: 0.5;
        transition: 0.3s;
    }
    .music-card:hover audio {
        opacity: 1;
    }

    footer {
        padding: 60px;
        text-align: center;
        border-top: 1px solid #1a1a1a;
        margin-top: 50px;
        font-size: 0.75rem;
        color: #444;
        letter-spacing: 2px;
    }
</style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h2 class="m-0 fw-bold">MUSIC<span style="color: var(--accent);">STUDIO</span></h2>
        <input type="text" id="search" class="search-box" placeholder="Search track or artist...">
    </div>

    <div class="grid" id="musicGrid">
        <?php if(mysqli_num_rows($music) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($music)):
                $id = $row['id'];
                $title = htmlspecialchars($row['title']);
                $artist = htmlspecialchars($row['artist']);
                $file  = $row['file'];
            ?>
            <div class="music-card" data-title="<?php echo strtolower($title); ?>" data-artist="<?php echo strtolower($artist); ?>">
                
                <div class="disc-wrapper">
                    <i class="bi bi-disc-fill"></i>
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
            <div class="text-center py-5 w-100">
                <p class="text-muted">Studio is empty. Upload music to begin.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer>
    SOUND ENTERTAINMENT &bull; MUSIC SYSTEM &bull; 2026
</footer>

<script>
// Search functionality
document.getElementById("search").addEventListener("input", function() {
    let val = this.value.toLowerCase();
    document.querySelectorAll(".music-card").forEach(card => {
        let text = card.dataset.title + " " + card.dataset.artist;
        card.style.display = text.includes(val) ? "block" : "none";
    });
});

// Custom Play Toggle
function toggleMusic(btn) {
    const card = btn.closest('.music-card');
    const audio = card.querySelector('audio');
    
    if (audio.paused) {
        // Stop all other audio first
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
    const btnIcon = el.closest('.music-card').querySelector('.play-trigger i');
    btnIcon.className = 'bi bi-pause-fill';
}

function handlePause(el) {
    el.closest('.music-card').classList.remove('playing');
    const btnIcon = el.closest('.music-card').querySelector('.play-trigger i');
    btnIcon.className = 'bi bi-play-fill';
}
</script>

</body>
</html>