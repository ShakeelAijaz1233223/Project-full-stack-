<?php
include "../config/db.php";

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $video_id = $_POST['video_id'];
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    mysqli_query($conn, "INSERT INTO video_reviews (video_id, rating, comment) VALUES ('$video_id', '$rating', '$comment')");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch videos with average rating
$query = "SELECT videos.*, 
          (SELECT AVG(rating) FROM video_reviews WHERE video_reviews.video_id = videos.id) as avg_rating,
          (SELECT COUNT(*) FROM video_reviews WHERE video_reviews.video_id = videos.id) as total_reviews
          FROM videos ORDER BY id DESC";
$videos = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Studio | Pro Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --bg: #080808;
            --card: #111;
            --accent: #ff0055;
            --accent-grad: linear-gradient(45deg, #ff0055, #ff5e00);
        }

        body {
            background: var(--bg);
            color: #fff;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        .studio-wrapper {
            width: 95%;
            margin: 0 auto;
            padding: 20px 0;
        }

        /* Header */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #222;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .search-box {
            background: #151515;
            border: 1px solid #333;
            color: white;
            border-radius: 6px;
            padding: 6px 15px;
            width: 250px;
        }

        /* Grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
        }

        .video-card {
            background: var(--card);
            border-radius: 12px;
            padding: 12px;
            border: 1px solid #1a1a1a;
            transition: 0.3s;
            position: relative;
        }

        .video-card:hover {
            border-color: var(--accent);
            transform: translateY(-5px);
        }

        /* Media */
        .media-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 1/1;
            background: #000;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            background: var(--accent-grad);
            border-radius: 50%;
            border: none;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: 0.3s;
            z-index: 5;
        }

        .video-card:hover .play-btn {
            opacity: 1;
        }

        .title {
            font-size: 0.85rem;
            font-weight: 600;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .stars-display {
            color: #ffca08;
            font-size: 0.7rem;
            margin: 5px 0;
        }

        .rev-btn {
            background: #222;
            color: #fff;
            border: none;
            font-size: 0.7rem;
            width: 100%;
            padding: 5px;
            border-radius: 5px;
            transition: 0.3s;
        }

        .rev-btn:hover {
            background: var(--accent);
        }

        /* Review Overlay Focus */
        #reviewOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }

        .review-box {
            background: #151515;
            width: 90%;
            max-width: 400px;
            padding: 30px;
            border-radius: 20px;
            border: 1px solid #333;
        }

        /* Star Input */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 2.5rem;
            color: #222;
            cursor: pointer;
            transition: 0.2s;
        }

        .star-rating label:hover,
        .star-rating label:hover~label,
        .star-rating input:checked~label {
            color: #ffca08;
        }

        footer {
            text-align: center;
            padding: 40px;
            font-size: 0.7rem;
            color: #444;
        }
    </style>
</head>

<body>

    <div class="studio-wrapper">
        <div class="header-section">
            <h4 class="m-0 fw-bold">VIDEO<span style="color: var(--accent);">STUDIO</span></h4>
            <div class="d-flex gap-2">
                <input type="text" id="search" class="search-box" placeholder="Search videos...">
                <a href="javascript:history.back()" class="btn btn-dark btn-sm border-secondary"><i class="bi bi-arrow-left"></i></a>
            </div>
        </div>

        <div class="grid" id="videoGrid">
            <?php while ($row = mysqli_fetch_assoc($videos)):
                $avg = round($row['avg_rating'], 1);
            ?>
                <div class="video-card" data-title="<?= strtolower($row['title']); ?>">
                    <div class="media-wrapper">
                        <video id="vid-<?= $row['id'] ?>" loop muted playsinline>
                            <source src="../admin/uploads/videos/<?= $row['file'] ?>" type="video/mp4">
                        </video>
                        <button class="play-btn" onclick="toggleVideo('<?= $row['id'] ?>', this)">
                            <i class="bi bi-play-fill"></i>
                        </button>
                    </div>
                    <p class="title"><?= htmlspecialchars($row['title']) ?></p>
                    <div class="stars-display">
                        <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                        <span class="text-white opacity-50 ms-1">(<?= $row['total_reviews'] ?>)</span>
                    </div>
                    <button class="rev-btn" onclick="openReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">
                        <i class="bi bi-chat-dots-fill me-1"></i> REVIEW
                    </button>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div id="reviewOverlay">
        <div class="review-box">
            <div class="text-center mb-4">
                <h5 class="fw-bold m-0" id="revTitle">Video Name</h5>
                <small class="text-muted">How was your visual experience?</small>
            </div>

            <form method="POST">
                <input type="hidden" name="video_id" id="revVideoId">

                <div class="star-rating">
                    <input type="radio" name="rating" value="5" id="v5" required><label for="v5">★</label>
                    <input type="radio" name="rating" value="4" id="v4"><label for="v4">★</label>
                    <input type="radio" name="rating" value="3" id="v3"><label for="v3">★</label>
                    <input type="radio" name="rating" value="2" id="v2"><label for="v2">★</label>
                    <input type="radio" name="rating" value="1" id="v1"><label for="v1">★</label>
                </div>

                <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" rows="4" placeholder="Write your review here..." required></textarea>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-dark w-100" onclick="closeReview()">CANCEL</button>
                    <button type="submit" name="submit_review" class="btn w-100" style="background: var(--accent); color:white;">POST NOW</button>
                </div>
            </form>
        </div>
    </div>

    <footer>&copy; 2026 VIDEO STUDIO &bull; SYSTEM PRO</footer>

    <script>
        // Search
        document.getElementById("search").addEventListener("input", function() {
            let val = this.value.toLowerCase();
            document.querySelectorAll(".video-card").forEach(card => {
                card.style.display = card.dataset.title.includes(val) ? "block" : "none";
            });
        });

        // Video Toggle
        function toggleVideo(id, btn) {
            const video = document.getElementById('vid-' + id);
            const icon = btn.querySelector('i');

            document.querySelectorAll('video').forEach(v => {
                if (v !== video) {
                    v.pause();
                    v.muted = true;
                }
            });

            if (video.paused) {
                video.muted = false;
                video.play();
                icon.className = 'bi bi-pause-fill';
            } else {
                video.pause();
                icon.className = 'bi bi-play-fill';
            }
        }

        // Review Logic
        function openReview(id, title) {
            document.getElementById('revVideoId').value = id;
            document.getElementById('revTitle').innerText = title;
            document.getElementById('reviewOverlay').style.display = 'flex';
        }

        function closeReview() {
            document.getElementById('reviewOverlay').style.display = 'none';
        }
    </script>
</body>

</html>