<?php
include "../config/db.php";
$albums = mysqli_query($conn, "SELECT * FROM albums ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Albums Studio | Fixed Audio</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    :root {
        --bg-dark: #0f0f0f;
        --card-bg: #1e1e1e;
        --accent: #ff0055;
        --accent-gradient: linear-gradient(45deg, #ff0055, #ff5e00);
        --text-muted: #aaaaaa;
    }

    body { 
        background-color: var(--bg-dark); 
        color: #ffffff; 
        font-family: 'Segoe UI', Roboto, sans-serif;
        margin: 0;
        padding: 0;
    }

    .studio-wrapper {
        width: 95%;
        margin: 0 auto;
        padding: 20px 0;
    }

    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .search-box {
        background: #2a2a2a;
        border: 1px solid #3d3d3d;
        color: white;
        border-radius: 4px;
        padding: 8px 15px;
        width: 300px;
    }
     .search-box:focus {
        background: #333;
        border-color: var(--accent);
        box-shadow: none;
        color: white;
    }

    .grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); 
        gap: 15px; 
    }

    .card { 
        background: var(--card-bg); 
        border: none; 
        border-radius: 8px; 
        overflow: hidden; 
        transition: transform 0.2s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        background: #252525;
    }

    .media-wrapper {
        position: relative;
        width: 100%;
        aspect-ratio: 1/1; 
        background: #000;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .play-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 45px;
        height: 45px;
        background: var(--accent-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0.9;
        transition: 0.2s;
        z-index: 10;
        border: none;
        color: white;
    }

    .card-body { padding: 10px; }
    .card .title { font-weight: 600; font-size: 0.9rem; margin-bottom: 2px; }
    .card .artist { font-size: 0.75rem; color: var(--text-muted); }

    audio {
        width: 100%;
        height: 25px;
        margin-top: 5px;
        filter: invert(1) hue-rotate(180deg) brightness(1.2);
    }

    footer { padding: 40px; text-align: center; font-size: 0.8rem; color: #555; }
</style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h4 class="m-0 fw-bold">Albums <span style="color: var(--accent);">Studio</span></h4>
        <input type="text" id="search" class="form-control search-box" placeholder="Search library...">
    </div>

    <div class="grid" id="albumGrid">
        <?php if(mysqli_num_rows($albums) > 0):
            while ($row = mysqli_fetch_assoc($albums)):
                $title = htmlspecialchars($row['title']);
                $artist = htmlspecialchars($row['artist']);
                $audio = $row['audio'];
                $video = $row['video'];
                $id = $row['id'];
        ?>
        <div class="card" data-title="<?php echo strtolower($title); ?>" data-artist="<?php echo strtolower($artist); ?>">
            <div class="media-wrapper">
                <?php if(!empty($video)): ?>
                    <video id="vid-<?php echo $id; ?>" preload="metadata" playsinline>
                        <source src="../admin/uploads/albums/<?php echo $video; ?>" type="video/mp4">
                    </video>
                    <button class="play-overlay" onclick="toggleVideo('vid-<?php echo $id; ?>', this)">
                        <i class="bi bi-play-fill"></i>
                    </button>
                <?php else: ?>
                    <div class="text-center">
                        <i class="bi bi-music-note-beamed text-muted" style="font-size: 2.5rem;"></i>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <div class="title text-truncate"><?php echo $title; ?></div>
                <div class="artist"><?php echo $artist; ?></div>

                <?php if(!empty($audio)): ?>
                    <audio controls id="aud-<?php echo $id; ?>">
                        <source src="../admin/uploads/albums/<?php echo $audio; ?>" type="audio/mpeg">
                    </audio>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; else: ?>
            <div class="text-center w-100 py-5"><p class="text-muted">Empty Studio</p></div>
        <?php endif; ?>
    </div>
</div>

<footer>&copy; 2026 Albums Studio</footer>

<script>
// Search Logic
document.getElementById("search").addEventListener("input", function() {
    let val = this.value.toLowerCase();
    document.querySelectorAll(".card").forEach(card => {
        let title = card.dataset.title;
        let artist = card.dataset.artist;
        card.style.display = (title.includes(val) || artist.includes(val)) ? "block" : "none";
    });
});

// Fixed Video/Audio Logic
function toggleVideo(id, btn) {
    const video = document.getElementById(id);
    const icon = btn.querySelector('i');
    
    if (video.paused) {
        // 1. Pause all other media (videos AND audios)
        document.querySelectorAll('video, audio').forEach(m => {
            m.pause();
            // Reset icons on all play buttons
            if(m.tagName === 'VIDEO' && m.nextElementSibling && m.nextElementSibling.classList.contains('play-overlay')) {
                const otherIcon = m.nextElementSibling.querySelector('i');
                otherIcon.className = 'bi bi-play-fill';
                m.nextElementSibling.style.opacity = '0.9';
            }
        });

        // 2. UNMUTE and Play
        video.muted = false; // Key Fix: Unmute on interaction
        video.play().catch(error => {
            console.log("Autoplay unmuted blocked, playing muted instead.");
            video.muted = true;
            video.play();
        });

        icon.className = 'bi bi-pause-fill';
        btn.style.opacity = '0'; 
    } else {
        video.pause();
        icon.className = 'bi bi-play-fill';
        btn.style.opacity = '0.9';
    }
}

// Silent Hover Preview Fix
document.querySelectorAll('.card').forEach(card => {
    const video = card.querySelector('video');
    if(!video) return;

    card.addEventListener('mouseenter', () => {
        // Only trigger silent preview if the video isn't already playing with sound
        const btnIcon = card.querySelector('.play-overlay i');
        if(video.paused && btnIcon.classList.contains('bi-play-fill')) {
            video.muted = true; // Stay silent on hover
            video.play();
        }
    });

    card.addEventListener('mouseleave', () => {
        const btnIcon = card.querySelector('.play-overlay i');
        // Stop the silent preview, but don't stop if user clicked Play (pause-fill)
        if(btnIcon.classList.contains('bi-play-fill')) {
            video.pause();
            video.currentTime = 0;
        }
    });
});
</script>

</body>
</html>