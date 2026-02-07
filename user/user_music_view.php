<?php
include "../config/db.php";

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $music_id = $_POST['music_id'];
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    mysqli_query($conn, "INSERT INTO reviews (music_id, rating, comment) VALUES ('$music_id', '$rating', '$comment')");
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=reviewed");
    exit();
}

// Fetch Music with Average Ratings
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
    <title>Music Studio | Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --bg: #080808;
            --card: #111;
            --accent: #ff0055;
            --accent-grad: linear-gradient(45deg, #ff0055, #ff5e00);
            --text-muted: #888;
        }

        body {
            background: var(--bg);
            color: #fff;
            font-family: 'Inter', sans-serif;
            margin: 0;
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
            color: #fff;
            border-radius: 6px;
            padding: 6px 15px;
            width: 250px;
        }

        .btn-back {
            background: #1a1a1a;
            border: 1px solid #222;
            color: #fff;
            padding: 6px 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }

        /* Grid & Cards */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .album-card {
            background: var(--card);
            border-radius: 15px;
            padding: 12px;
            border: 1px solid #1a1a1a;
            transition: 0.3s;
            position: relative;
            overflow: hidden;
        }

        .album-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent);
        }

        /* Disc / Media wrapper */
        .disc-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 16/9;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: floatDisc 2s ease-in-out infinite;
        }

        @keyframes floatDisc {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-5px);
            }
        }

        .album-card audio {
            display: none;
        }

        /* Play Button */
        .play-btn {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
            border-radius: 50%;
            border: none;
            background: var(--accent-grad);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 0 15px var(--accent);
            transition: transform 0.3s, opacity 0.3s;
            opacity: 0;
        }

        .album-card:hover .play-btn {
            opacity: 1;
        }

        .play-btn:hover {
            transform: translate(-50%, -50%) scale(1.2);
        }

        /* Titles & stars */
        .title {
            font-weight: 600;
            font-size: 0.85rem;
            margin: 5px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: color 0.3s, transform 0.3s;
        }

        .artist {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 5px;
            transition: color 0.3s, transform 0.3s;
        }

        .album-card:hover .title {
            color: var(--accent);
            transform: translateY(-2px);
        }

        .album-card:hover .artist {
            color: #ff416c;
            transform: translateY(-2px);
        }

        .stars-display {
            color: #ffca08;
            font-size: 0.75rem;
            margin-bottom: 8px;
            text-align: center;
            transition: transform 0.3s;
        }

        .album-card:hover .stars-display {
            transform: scale(1.1);
        }

        .rev-btn {
            background: #222;
            color: #fff;
            border: none;
            font-size: 0.7rem;
            width: 100%;
            padding: 6px;
            border-radius: 6px;
            transition: 0.3s;
        }

        .rev-btn:hover {
            background: var(--accent);
        }

        /* Custom controls */
        .custom-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
            padding: 5px 0;
        }

        .custom-controls input[type="range"] {
            -webkit-appearance: none;
            width: 100%;
            height: 6px;
            background: #222;
            border-radius: 5px;
            cursor: pointer;
        }

        .custom-controls input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 14px;
            height: 14px;
            background: var(--accent);
            border-radius: 50%;
            border: 2px solid #111;
            transition: transform 0.2s;
        }

        .custom-controls input[type="range"]::-webkit-slider-thumb:hover {
            transform: scale(1.2);
        }

        .custom-controls input[type="range"]::-moz-range-thumb {
            width: 14px;
            height: 14px;
            background: var(--accent);
            border-radius: 50%;
            border: 2px solid #111;
        }

        .custom-controls button {
            background: #1a1a1a;
            border: 1px solid #333;
            color: #fff;
            padding: 6px 10px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .custom-controls button:hover {
            background: var(--accent);
            transform: scale(1.1);
            color: #fff;
        }
        .custom-controls button {
    background: none;
    border: none;
    color: #fff;
    cursor: pointer;
    font-size: 1.1rem;
    transition: color 0.2s, transform 0.2s;
}
.custom-controls button:hover {
    color: var(--accent);
    transform: scale(1.2);
}

/* Progress Slider */
.custom-controls input[type="range"] {
    flex: 1;
    margin: 0 8px;
    height: 6px;
    border-radius: 5px;
    accent-color: var(--accent);
    cursor: pointer;
    background: rgba(255, 255, 255, 0.15);
    transition: background 0.3s;
}
.custom-controls input[type="range"]:hover {
    background: rgba(255, 255, 255, 0.25);
}
.custom-controls input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: var(--accent);
    border: 2px solid #111;
    transition: transform 0.2s;
}
.custom-controls input[type="range"]::-webkit-slider-thumb:hover {
    transform: scale(1.3);
}
.custom-controls input[type="range"]::-moz-range-thumb {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: var(--accent);
    border: 2px solid #111;
}

.custom-controls {
    position: absolute;
    bottom: 5px;
    left: 0;
    right: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 10px;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 0 0 8px 8px;
}
.media-wrapper:hover .custom-controls { opacity: 1; }
.custom-controls button {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
}
.custom-controls input[type="range"] {
    flex: 1;
    margin: 0 5px;
    accent-color: var(--accent);
}

        /* Review overlay */
        #reviewOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(5px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .review-box {
            background: #151515;
            width: 90%;
            max-width: 400px;
            padding: 30px;
            border-radius: 20px;
            border: 1px solid #333;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 8px;
            margin-bottom: 15px;
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
            <h4 class="m-0 fw-bold">MUSIC<span style="color: var(--accent);">STUDIO</span></h4>
            <div class="d-flex align-items-center gap-2">
                <input type="text" id="search" class="search-box" placeholder="Search music...">
                <a href="javascript:history.back()" class="btn-back"><i class="bi bi-arrow-left"></i> Back</a>
            </div>
        </div>

        <div class="grid" id="musicGrid">
            <?php while ($row = mysqli_fetch_assoc($music)):
                $avg = round($row['avg_rating'], 1);
            ?>
                <div class="album-card" data-title="<?= strtolower($row['title']); ?>" data-artist="<?= strtolower($row['artist']); ?>">
                    <div class="disc-wrapper">
                        <button class="play-btn"><i class="bi bi-play-fill"></i></button>
                    </div>
                    <div class="custom-controls">
                        <input type="range" class="progress" min="0" max="100" value="0">
                        <button class="mute-btn"><i class="bi bi-volume-up"></i></button>
                    </div>
                    <div class="title"><?= htmlspecialchars($row['title']); ?></div>
                    <div class="artist"><?= htmlspecialchars($row['artist']); ?></div>
                    <div class="stars-display">
                        <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                        <span class="text-white opacity-50 ms-1">(<?= $row['total_reviews']; ?>)</span>
                    </div>
                    <button class="rev-btn" onclick="openReview('<?= $row['id']; ?>','<?= addslashes($row['title']); ?>')">REVIEW</button>

                    <audio id="audio-<?= $row['id']; ?>">
                        <source src="../admin/uploads/music/<?= $row['file']; ?>" type="audio/mpeg">
                    </audio>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div id="reviewOverlay">
        <div class="review-box">
            <div class="text-center mb-4">
                <h5 class="fw-bold m-0" id="revTitle">Music Name</h5>
                <small class="text-muted">How was your listening experience?</small>
            </div>
            <form method="POST">
                <input type="hidden" name="music_id" id="revMusicId">
                <div class="star-rating">
                    <input type="radio" name="rating" value="5" id="r5" required><label for="r5">★</label>
                    <input type="radio" name="rating" value="4" id="r4"><label for="r4">★</label>
                    <input type="radio" name="rating" value="3" id="r3"><label for="r3">★</label>
                    <input type="radio" name="rating" value="2" id="r2"><label for="r2">★</label>
                    <input type="radio" name="rating" value="1" id="r1"><label for="r1">★</label>
                </div>
                <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" placeholder="Write your review..." required></textarea>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-dark w-100" onclick="closeReview()">CANCEL</button>
                    <button type="submit" name="submit_review" class="btn w-100" style="background: var(--accent); color:white;">POST NOW</button>
                </div>
            </form>
        </div>
    </div>

    <footer>&copy; 2026 MUSIC STUDIO &bull; SOUND SYSTEM</footer>

    <script>
        const audios = document.querySelectorAll('audio');
        const albumCards = document.querySelectorAll('.album-card');

        albumCards.forEach(card => {
            const btn = card.querySelector('.play-btn');
            const audio = card.querySelector('audio');
            const progress = card.querySelector('.progress');
            const muteBtn = card.querySelector('.mute-btn');

            btn.addEventListener('click', () => {
                // Pause others
                audios.forEach(a => {
                    if (a !== audio) {
                        a.pause();
                        a.closest('.album-card').classList.remove('playing');
                        a.closest('.album-card').querySelector('.play-btn i').className = 'bi bi-play-fill';
                    }
                });

                if (audio.paused) {
                    audio.play();
                    card.classList.add('playing');
                    btn.querySelector('i').className = 'bi bi-pause-fill';
                } else {
                    audio.pause();
                    card.classList.remove('playing');
                    btn.querySelector('i').className = 'bi bi-play-fill';
                }

                audio.addEventListener('timeupdate', () => {
                    progress.value = (audio.currentTime / audio.duration) * 100;
                });
            });

            // Progress seek
            progress.addEventListener('input', () => {
                audio.currentTime = (progress.value / 100) * audio.duration;
            });

            // Mute toggle
            muteBtn.addEventListener('click', () => {
                audio.muted = !audio.muted;
                muteBtn.innerHTML = audio.muted ? '<i class="bi bi-volume-mute"></i>' : '<i class="bi bi-volume-up"></i>';
            });
        });

        // Search filter
        document.getElementById('search').addEventListener('input', function() {
            const val = this.value.toLowerCase();
            albumCards.forEach(card => {
                let txt = card.dataset.title + ' ' + card.dataset.artist;
                card.style.display = txt.includes(val) ? 'block' : 'none';
            });
        });

        // Review overlay
        function openReview(id, title) {
            document.getElementById('revMusicId').value = id;
            document.getElementById('revTitle').innerText = title;
            document.getElementById('reviewOverlay').style.display = 'flex';
        }

        function closeReview() {
            document.getElementById('reviewOverlay').style.display = 'none';
        }
    </script>

</body>

</html>