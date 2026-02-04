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
<title>Video Studio | Compact Pro</title>
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
    background-color: var(--bg-dark);
    color: #fff;
    font-family: 'Inter', sans-serif;
    margin: 0;
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
    margin-bottom: 25px;
    border-bottom: 1px solid #1a1a1a;
    padding-bottom: 15px;
}
.search-box {
    background: #1a1a1a;
    border: 1px solid #222;
    color: white;
    border-radius: 4px;
    padding: 6px 15px;
    width: 250px;
    font-size: 0.85rem;
}
.btn-back {
    background: #1a1a1a;
    border: 1px solid #222;
    color: #fff;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 0.85rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: 0.3s ease;
}
.btn-back:hover {
    background: #222;
    border-color: var(--accent);
    color: #fff;
}
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
}
.card {
    background: var(--card-bg);
    border: 1px solid transparent;
    border-radius: 10px;
    overflow: hidden;
    transition: 0.3s ease;
    text-align: center;
    padding: 10px;
    position: relative;
}
.card:hover {
    transform: translateY(-5px);
    background: #1a1a1a;
    border-color: #333;
}
.media-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: 1/1;
    background: #000;
    border-radius: 8px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}
.play-overlay {
    position: absolute;
    width: 35px;
    height: 35px;
    background: var(--accent-gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    opacity: 0;
    transition: 0.3s;
    z-index: 10;
    border: none;
    color: white;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
}
.card:hover .play-overlay {
    opacity: 1;
}
.card-body {
    padding: 5px 0;
}
.title {
    font-weight: 600;
    font-size: 0.85rem;
    margin-bottom: 1px;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.meta {
    font-size: 0.75rem;
    color: var(--text-muted);
}
footer {
    padding: 40px;
    text-align: center;
    font-size: 0.7rem;
    color: #444;
    letter-spacing: 1px;
}
/* ============================= */
/* RESPONSIVE ADJUSTMENTS */
/* ============================= */
@media (max-width: 1200px) {
    .studio-wrapper {
        width: 90%;
    }

    .search-box {
        width: 200px;
    }
}

@media (max-width: 992px) {
    .studio-wrapper {
        width: 95%;
        padding: 15px 0;
    }

    .header-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .search-box {
        width: 100%;
    }

    .btn-back {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 10px;
    }

    .card {
        padding: 8px;
    }

    .media-wrapper {
        aspect-ratio: 1/1;
    }

    .card-body .title {
        font-size: 0.8rem;
    }

    .card-body .meta {
        font-size: 0.7rem;
    }
}

@media (max-width: 576px) {
    .grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 8px;
    }

    .header-section h4 {
        font-size: 1.1rem;
    }

    .search-box {
        font-size: 0.8rem;
    }

    .btn-back {
        font-size: 0.75rem;
        padding: 5px 8px;
    }

    .play-overlay {
        width: 30px;
        height: 30px;
    }

    footer {
        padding: 20px;
        font-size: 0.65rem;
    }
}

</style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h4 class="m-0 fw-bold">VIDEO<span style="color: var(--accent);">STUDIO</span></h4>
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search videos...">
            <a href="javascript:history.back()" class="btn-back"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>

    <div class="grid" id="videoGrid">
        <?php if(mysqli_num_rows($videos) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($videos)): 
                $id = $row['id'];
                $title = htmlspecialchars($row['title']);
                $file  = $row['file'];
            ?>
            <div class="card video-card" data-title="<?php echo strtolower($title); ?>">
                <div class="media-wrapper">
                    <video id="vid-<?php echo $id; ?>" preload="metadata" loop muted playsinline>
                        <source src="../admin/uploads/videos/<?php echo $file; ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <button class="play-overlay" onclick="togglePlay('<?php echo $id; ?>', this)">
                        <i class="bi bi-play-fill"></i>
                    </button>
                </div>

                <div class="card-body">
                    <div class="title"><?php echo $title; ?></div>
                    <div class="meta"><i class="bi bi-broadcast"></i> Visual Content</div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center w-100 py-5">
                <p class="text-muted">Studio is currently empty.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer>&copy; 2026 VIDEO STUDIO &bull; SOUND SYSTEM</footer>

<script>
// ===============================
// REAL-TIME SEARCH
// ===============================
document.getElementById("search").addEventListener("input", function () {
    let val = this.value.toLowerCase().trim();
    document.querySelectorAll(".video-card").forEach(card => {
        let title = card.dataset.title;
        card.style.display = title.includes(val) ? "block" : "none";
    });
});

// ===============================
// MASTER PLAY / PAUSE
// ===============================
function togglePlay(id, btn) {
    const video = document.getElementById('vid-' + id);
    const icon = btn.querySelector('i');

    // Stop all other videos
    document.querySelectorAll('video').forEach(v => {
        if (v !== video) {
            v.pause();
            v.muted = true;
        }
    });
    document.querySelectorAll('.play-overlay i').forEach(i => i.className = 'bi bi-play-fill');

    // Toggle current video
    if(video.paused) {
        video.muted = false;
        video.play().catch(()=>{});
        icon.className = 'bi bi-pause-fill';
    } else {
        video.pause();
        icon.className = 'bi bi-play-fill';
    }
}


</script>

</body>
</html>
