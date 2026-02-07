<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Albums Studio | Pro Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #ff0055, #7000ff);
            --bg-dark: #080808;
            --card-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #ffffff;
            --text-dim: #a0a0a0;
            --accent: #ff0055;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
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
            border-radius: 8px;
            padding: 8px 15px;
            width: 250px;
            font-size: 0.85rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            padding: 20px 0;
        }

        .album-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            padding: 20px;
            transition: transform 0.5s ease, box-shadow 0.5s ease, border-color 0.5s ease, background 0.5s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .album-card:hover {
            transform: translateY(-15px) scale(1.05);
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 0, 85, 0.6);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6);
        }

        .media-wrapper {
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
            position: relative;
            aspect-ratio: 16/9;
            overflow: hidden;
            background: #000;
            margin-bottom: 15px;
        }

        .media-wrapper img,
        .media-wrapper video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .album-card:hover .media-wrapper img,
        .album-card:hover .media-wrapper video {
            transform: scale(1.15);
        }

        .album-card h3,
        .title {
            color: #fff;
            font-size: 1.5rem;
            margin: 8px 0 5px;
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .artist {
            font-size: 0.85rem;
            color: var(--text-dim);
            margin-bottom: 10px;
        }

        .stars-row {
            color: #ffca08;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 3px;
            margin-bottom: 15px;
        }

        .btn-rev-pop {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 10px;
            width: 100%;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 0, 85, 0.3);
        }

        .btn-rev-pop:hover {
            box-shadow: 0 6px 20px rgba(255, 0, 85, 0.5);
            filter: brightness(1.2);
        }

        .btn-back {
            background: #1a1a1a;
            border: 1px solid #222;
            color: #fff;
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .custom-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            padding: 5px 10px;
            gap: 8px;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 5;
        }

        .media-wrapper:hover .custom-controls {
            opacity: 1;
        }

        .custom-controls button {
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
        }

        .custom-controls input[type="range"] {
            flex: 1;
            accent-color: var(--accent);
        }

        #reviewOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        .review-modal {
            background: #111;
            width: 90%;
            max-width: 380px;
            padding: 30px;
            border-radius: 20px;
            border: 1px solid var(--glass-border);
        }

        .star-input {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .star-input input {
            display: none;
        }

        .star-input label {
            font-size: 2.5rem;
            color: #222;
            cursor: pointer;
        }

        .star-input label:hover,
        .star-input label:hover~label,
        .star-input input:checked~label {
            color: #ffca08;
        }

        footer {
            padding: 40px;
            text-align: center;
            font-size: 0.7rem;
            color: #444;
        }
    </style>
</head>

<body>
    <div class="studio-wrapper">
        <div class="header-section">
            <h4>ALBUMS<span style="color: var(--accent);">STUDIO</span></h4>
            <div class="d-flex align-items-center gap-2">
                <input type="text" id="search" class="search-box" placeholder="Search albums...">
                <a href="index.php" class="btn-back"><i class="bi bi-arrow-left"></i> Back</a>
            </div>
        </div>

        <div class="grid" id="albumGrid">
            <!-- PHP Loop -->
            <?php while ($row = mysqli_fetch_assoc($albums)):
                $avg = round($row['avg_rating'], 1);
            ?>
                <div class="album-card" data-title="<?= strtolower($row['title']) ?>" data-artist="<?= strtolower($row['artist']) ?>">
                    <div class="media-wrapper">
                        <?php if (!empty($row['video'])): ?>
                            <video id="vid-<?= $row['id'] ?>" preload="metadata" poster="../admin/uploads/albums/<?= $row['cover'] ?>">
                                <source src="../admin/uploads/albums/<?= $row['video'] ?>" type="video/mp4">
                            </video>
                            <button class="play-btn"><i class="bi bi-play-fill"></i></button>
                            <div class="custom-controls">
                                <input type="range" class="progress" min="0" max="100" value="0">
                                <button class="mute-btn"><i class="bi bi-volume-up"></i></button>
                                <button class="fullscreen-btn"><i class="bi bi-arrows-fullscreen"></i></button>
                            </div>
                        <?php else: ?>
                            <img src="../admin/uploads/albums/<?= $row['cover'] ?>" alt="Album Cover">
                        <?php endif; ?>
                    </div>

                    <div class="card-bodya">
                        <div class="title"><?= htmlspecialchars($row['title']) ?></div>
                        <div class="artist"><?= htmlspecialchars($row['artist']) ?></div>
                        <div class="stars-row">
                            <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                            <span class="ms-2 text-muted" style="font-size: 0.7rem;">(<?= $row['total_reviews'] ?>)</span>
                        </div>
                        <button class="btn-rev-pop" onclick="popReview('<?= $row['id'] ?>','<?= addslashes($row['title']) ?>')">Rate Album</button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div id="reviewOverlay">
        <div class="review-modal">
            <h5 class="text-center mb-1" id="popTitle">Album Name</h5>
            <p class="text-center text-muted small mb-4">Leave your rating</p>
            <form method="POST">
                <input type="hidden" name="album_id" id="popId">
                <div class="star-input">
                    <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                    <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                    <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                    <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                    <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
                </div>
                <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" placeholder="Write feedback..." required></textarea>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary w-100" onclick="closePop()">Cancel</button>
                    <button type="submit" name="submit_review" class="btn btn-danger w-100" style="background: var(--accent);">Post</button>
                </div>
            </form>
        </div>
    </div>

    <footer>&copy; 2026 ALBUMS STUDIO &bull; SOUND SYSTEM</footer>

    <script>
        // Search
        document.getElementById("search").addEventListener("input", function () {
            const val = this.value.toLowerCase().trim();
            document.querySelectorAll(".album-card").forEach(card => {
                let text = card.dataset.title + " " + card.dataset.artist;
                card.style.display = text.includes(val) ? "block" : "none";
            });
        });

        // Review Modal
        function popReview(id, title) {
            document.getElementById('popId').value = id;
            document.getElementById('popTitle').innerText = title;
            document.getElementById('reviewOverlay').style.display = 'flex';
        }
        function closePop() {
            document.getElementById('reviewOverlay').style.display = 'none';
        }

        // Video Controls
        document.querySelectorAll('.media-wrapper').forEach(wrapper => {
            const video = wrapper.querySelector('video');
            if (!video) return;

            const playBtn = wrapper.querySelector('.play-btn');
            const progress = wrapper.querySelector('.progress');
            const muteBtn = wrapper.querySelector('.mute-btn');
            const fullscreenBtn = wrapper.querySelector('.fullscreen-btn');

            playBtn.addEventListener('click', e => {
                e.stopPropagation();
                if (video.paused) {
                    document.querySelectorAll('video').forEach(v => v.pause());
                    video.play();
                    playBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
                } else {
                    video.pause();
                    playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
                }
            });

            video.addEventListener('timeupdate', () => {
                progress.value = (video.currentTime / video.duration) * 100;
            });

            progress.addEventListener('input', () => {
                video.currentTime = (progress.value / 100) * video.duration;
            });

            muteBtn.addEventListener('click', e => {
                e.stopPropagation();
                video.muted = !video.muted;
                muteBtn.innerHTML = video.muted ? '<i class="bi bi-volume-mute"></i>' : '<i class="bi bi-volume-up"></i>';
            });

            fullscreenBtn.addEventListener('click', e => {
                e.stopPropagation();
                if (video.requestFullscreen) video.requestFullscreen();
            });
        });
    </script>
</body>

</html>
