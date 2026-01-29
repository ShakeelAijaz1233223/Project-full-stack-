<?php
include "../config/db.php";



// Fetch all videos
$videos = mysqli_query($conn, "SELECT * FROM videos ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Video Studio | Pro Gallery</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

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

    /* 95% Browser Width */
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

    /* Small, compact Search Bar */
    .search-box {
        background: #2a2a2a;
        border: 1px solid #3d3d3d;
        color: white;
        border-radius: 4px;
        padding: 8px 15px;
        width: 300px;
        font-size: 0.9rem;
    }
    .search-box:focus {
        background: #333;
        border-color: var(--accent);
        box-shadow: none;
        color: white;
    }

    /* High-Density Grid (Small Cards) */
    .grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); 
        gap: 15px; 
    }

    /* Compact Card Design */
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

    /* Centered Play Button (Smaller for small cards) */
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
        opacity: 0; /* Hidden by default, shown on hover */
        transition: 0.3s;
        z-index: 10;
        border: none;
        color: white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }
    .card:hover .play-overlay {
        opacity: 0.9;
    }
    .play-overlay i { font-size: 1.2rem; }

    .card-body { 
        padding: 10px; 
    }

    .card .title { 
        font-weight: 600; 
        font-size: 0.9rem; 
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: white;
    }

    .card .meta { 
        font-size: 0.75rem; 
        color: var(--text-muted); 
        margin-bottom: 4px;
    }

    footer {
        padding: 40px;
        text-align: center;
        font-size: 0.8rem;
        color: #555;
    }
</style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h4 class="m-0 fw-bold">Video <span style="color: var(--accent);">Studio</span></h4>
        <input type="text" id="search" class="form-control search-box" placeholder="Search library...">
    </div>

    <div class="grid" id="videoGrid">
        <?php if(mysqli_num_rows($videos) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($videos)): 
                $id = $row['id'];
                $title = htmlspecialchars($row['title']);
                $file  = $row['file'];
            ?>
            <div class="card" data-title="<?php echo strtolower($title); ?>">
                <div class="media-wrapper">
                    <video id="vid-<?php echo $id; ?>" preload="metadata" loop muted>
                        <source src="../admin/uploads/videos/<?php echo $file; ?>" type="video/mp4">
                    </video>
                    <button class="play-overlay" onclick="togglePlay('vid-<?php echo $id; ?>', this)">
                        <i class="bi bi-play-fill"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="title"><?php echo $title; ?></div>
                    <div class="meta"><i class="bi bi-clock-history"></i> Uploaded</div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5 w-100">
                <p class="text-muted">No visual content in studio.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer>
    &copy; 2026 Video Studio &bull; Pro Dashboard View
</footer>

<script>
// Search functionality
document.getElementById("search").addEventListener("input", function() {
    let val = this.value.toLowerCase();
    document.querySelectorAll(".card").forEach(card => {
        let title = card.dataset.title;
        card.style.display = title.includes(val) ? "block" : "none";
    });
});

// Video Control Logic
function togglePlay(vidId, btn) {
    const video = document.getElementById(vidId);
    const icon = btn.querySelector('i');

    if (video.paused) {
        // Pause all other videos
        document.querySelectorAll('video').forEach(v => {
            v.pause();
            v.muted = true;
            // Reset icons of other buttons
            const otherBtn = v.nextElementSibling;
            if(otherBtn) otherBtn.querySelector('i').className = 'bi bi-play-fill';
        });

        video.muted = false; // Unmute when user explicitly plays
        video.play();
        icon.className = 'bi bi-pause-fill';
    } else {
        video.pause();
        icon.className = 'bi bi-play-fill';
    }
}

// Hover Effect: Silent Preview
document.querySelectorAll('.card').forEach(card => {
    const video = card.querySelector('video');
    card.addEventListener('mouseenter', () => {
        if(video.paused) {
            video.muted = true; // Ensure silent preview
            video.play();
        }
    });
    card.addEventListener('mouseleave', () => {
        const btnIcon = card.querySelector('.play-overlay i');
        // Only stop on leave if it's not the "active" playing video (optional logic)
        if(btnIcon.classList.contains('bi-play-fill')) {
            video.pause();
            video.currentTime = 0;
        }
    });
});
</script>

</body>
</html>