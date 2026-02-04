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
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">

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
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .logo {
            font-family: 'Syncopate', sans-serif;
            font-size: 1.2rem;
            color: #fff;
            text-decoration: none;
            letter-spacing: 3px;
        }

        .logo span {
            color: var(--accent);
        }

        .search-box {
            background: #151515;
            border: 1px solid #222;
            color: white;
            border-radius: 50px;
            padding: 6px 18px;
            width: 200px;
            font-size: 0.8rem;
        }

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

        @keyframes pulse {
            from {
                transform: scale(1);
                box-shadow: 0 0 15px #ff0055;
            }

            to {
                transform: scale(1.1);
                box-shadow: 0 0 25px #ff0055;
            }
        }

        /* --- NAVIGATION CONTROLS --- */
        .controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
        }

        .nav-btn {
            background: none;
            border: none;
            color: #777;
            font-size: 1.2rem;
            cursor: pointer;
            transition: 0.2s;
        }

        .nav-btn:hover {
            color: #fff;
        }

        .play-btn {
            width: 40px;
            height: 40px;
            background: var(--accent-gradient);
            border-radius: 50%;
            border: none;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(255, 0, 85, 0.3);
        }

        .title {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 2px;
        }

        .artist {
            font-size: 0.75rem;
            color: #555;
            text-transform: uppercase;
        }

        footer {
            padding: 40px;
            text-align: center;
            font-size: 0.7rem;
            color: #333;
            letter-spacing: 2px;
        }
    </style>
</head>

<body>

    <header>
        <a href="index.php" class="logo">SOU<span>N</span>D</a>
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search...">
            <a href="javascript:history.back()" class="btn-back"><i class="bi bi-arrow-left"></i></a>
        </div>
    </header>

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
        // Toggle Play/Pause
        function toggleMusic(btn) {
            const card = btn.closest('.music-card');
            const audio = card.querySelector('audio');

            if (audio.paused) {
                document.querySelectorAll('audio').forEach(a => {
                    if (a !== audio) {
                        a.pause();
                        a.currentTime = 0;
                    }
                });
                audio.play();
            } else {
                audio.pause();
            }
        }

        // Skip Forward/Backward (Aghi/Pisha logic)
        function skip(btn, seconds) {
            const card = btn.closest('.music-card');
            const audio = card.querySelector('audio');
            audio.currentTime += seconds;
        }

        function handlePlay(el) {
            el.closest('.music-card').classList.add('playing');
            el.closest('.music-card').querySelector('.play-btn i').className = 'bi bi-pause-fill';
        }

        function handlePause(el) {
            el.closest('.music-card').classList.remove('playing');
            el.closest('.music-card').querySelector('.play-btn i').className = 'bi bi-play-fill';
        }

        // Search Logic
        document.getElementById("search").addEventListener("input", function() {
            let val = this.value.toLowerCase();
            document.querySelectorAll(".music-card").forEach(card => {
                let text = card.dataset.title + " " + card.dataset.artist;
                card.style.display = text.includes(val) ? "block" : "none";
            });
        });
    </script>

</body>

</html>