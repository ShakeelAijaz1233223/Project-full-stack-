<?php
include "../config/db.php";

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $music_id = $_POST['music_id'];
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    
    mysqli_query($conn, "INSERT INTO reviews (music_id, rating, comment) VALUES ('$music_id', '$rating', '$comment')");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all music with average rating
$query = "SELECT music.*, 
          (SELECT AVG(rating) FROM reviews WHERE reviews.music_id = music.id) as avg_rating,
          (SELECT COUNT(*) FROM reviews WHERE reviews.music_id = music.id) as total_reviews
          FROM music ORDER BY id DESC";
$music = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Studio | Reviews & Ratings</title>
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

        body { background: var(--bg-dark); color: #fff; font-family: 'Inter', sans-serif; margin: 0; }
        .studio-wrapper { width: 95%; margin: 0 auto; padding: 15px 0; }
        
        .header-section { 
            display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: 20px; border-bottom: 1px solid #1a1a1a; padding-bottom: 15px; gap: 10px;
        }

        .search-box { background: #151515; border: 1px solid #222; color: white; border-radius: 4px; padding: 6px 12px; width: 240px; font-size: 0.8rem; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; }
        
        .music-card { 
            background: var(--card-bg); border-radius: 8px; padding: 15px; 
            transition: 0.3s; border: 1px solid #1a1a1a; text-align: center; position: relative;
        }
        .music-card:hover { background: #181818; transform: translateY(-3px); border-color: #333; }

        .disc-wrapper {
            position: relative; width: 80px; height: 80px; margin: 0 auto 10px;
            border-radius: 50%; background: #000; display: flex; align-items: center;
            justify-content: center; border: 3px solid #1a1a1a;
        }
        .disc-wrapper i { font-size: 2rem; color: var(--accent); }
        
        .playing .disc-wrapper { animation: rotateDisc 3s linear infinite; border-color: var(--accent); }
        @keyframes rotateDisc { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .play-trigger {
            position: absolute; width: 30px; height: 30px; background: var(--accent-gradient);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            color: white; border: none; opacity: 0; transition: 0.3s;
        }
        .music-card:hover .play-trigger { opacity: 1; }

        .title { font-weight: 600; font-size: 0.85rem; margin: 5px 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .artist { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 8px; }

        /* Rating Stars Style */
        .stars { color: #ffca08; font-size: 0.7rem; margin-bottom: 5px; }
        .review-btn { 
            background: transparent; border: 1px solid #333; color: #777; 
            font-size: 0.65rem; padding: 2px 8px; border-radius: 4px; transition: 0.3s;
        }
        .review-btn:hover { border-color: var(--accent); color: #fff; }

        .modal-content { background: #111; border: 1px solid #333; color: #fff; }
        .form-control, .form-select { background: #1a1a1a; border: 1px solid #333; color: #fff; }
        .form-control:focus { background: #1a1a1a; color: #fff; border-color: var(--accent); box-shadow: none; }

        audio { width: 100%; height: 25px; margin-top: 10px; }
        footer { padding: 30px; text-align: center; font-size: 0.65rem; color: #333; }
    </style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h2 class="m-0 fw-bold">MUSIC<span style="color: var(--accent);">STUDIO</span></h2>
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search track...">
            <a href="javascript:history.back()" class="btn btn-dark btn-sm border-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="grid" id="musicGrid">
        <?php if (mysqli_num_rows($music) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($music)): 
                $avg = round($row['avg_rating'], 1);
            ?>
                <div class="music-card" data-title="<?= strtolower($row['title']); ?>" data-artist="<?= strtolower($row['artist']); ?>">
                    <div class="disc-wrapper">
                        <i class="bi bi-disc-fill"></i>
                        <button class="play-trigger" onclick="toggleMusic(this)">
                            <i class="bi bi-play-fill"></i>
                        </button>
                    </div>

                    <div class="info-section">
                        <p class="title"><?= htmlspecialchars($row['title']); ?></p>
                        <p class="artist"><?= htmlspecialchars($row['artist']); ?></p>
                        
                        <div class="stars">
                            <?php for($i=1; $i<=5; $i++) echo ($i <= $avg) ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>'; ?>
                            <span class="ms-1">(<?= $row['total_reviews'] ?>)</span>
                        </div>

                        <button class="review-btn" data-bs-toggle="modal" data-bs-target="#reviewModal<?= $row['id'] ?>">
                            <i class="bi bi-chat-left-text"></i> Review
                        </button>
                    </div>

                    <audio class="audio-player" onplay="handlePlay(this)" onpause="handlePause(this)">
                        <source src="../admin/uploads/music/<?= $row['file']; ?>" type="audio/mpeg">
                    </audio>

                    <div class="modal fade" id="reviewModal<?= $row['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header border-secondary">
                                        <h6 class="modal-title">Rate: <?= htmlspecialchars($row['title']) ?></h6>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-start">
                                        <input type="hidden" name="music_id" value="<?= $row['id'] ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label small">Rating</label>
                                            <select name="rating" class="form-select form-select-sm" required>
                                                <option value="5">5 - Excellent</option>
                                                <option value="4">4 - Very Good</option>
                                                <option value="3">3 - Good</option>
                                                <option value="2">2 - Fair</option>
                                                <option value="1">1 - Poor</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label small">Your Review</label>
                                            <textarea name="comment" class="form-control form-control-sm" rows="3" placeholder="Write something..." required></textarea>
                                        </div>

                                        <div class="previous-reviews" style="max-height: 150px; overflow-y: auto;">
                                            <p class="small border-bottom border-secondary pb-1">Recent Reviews:</p>
                                            <?php 
                                            $m_id = $row['id'];
                                            $rev_res = mysqli_query($conn, "SELECT * FROM reviews WHERE music_id = $m_id ORDER BY id DESC LIMIT 3");
                                            while($rev = mysqli_fetch_assoc($rev_res)): ?>
                                                <div class="mb-2">
                                                    <div class="text-warning" style="font-size: 0.6rem;">
                                                        <?php for($k=1; $k<=5; $k++) echo ($k <= $rev['rating']) ? '★' : '☆'; ?>
                                                    </div>
                                                    <p class="small mb-0" style="font-size: 0.7rem; color: #ccc;"><?= htmlspecialchars($rev['comment']) ?></p>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-secondary">
                                        <button type="submit" name="submit_review" class="btn btn-sm w-100" style="background: var(--accent); color:white;">Post Review</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-muted text-center w-100 py-5">No tracks found.</p>
        <?php endif; ?>
    </div>
</div>

<footer>SOUND ENTERTAINMENT &bull; 2026</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Search Functionality
    document.getElementById("search").addEventListener("input", function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll(".music-card").forEach(card => {
            let text = card.dataset.title + " " + card.dataset.artist;
            card.style.display = text.includes(val) ? "block" : "none";
        });
    });

    // Audio Controls
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