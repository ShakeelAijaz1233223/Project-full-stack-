<?php
session_start();
include "../config/db.php";

/* ===============================
    1. ADMIN AUTH
================================ */
if (!isset($_SESSION['email']) || !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = (int)$_SESSION['admin_id'];
$stmt = $conn->prepare("UPDATE admin_users SET last_seen = NOW() WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();

/* ===============================
    2. DELETE LOGIC
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("SELECT video, cover, audio FROM albums WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $files = [$row['video'], $row['cover'], $row['audio']];
        foreach($files as $file) {
            if(!empty($file) && file_exists("uploads/albums/" . $file)) unlink("uploads/albums/" . $file);
        }
        $del = $conn->prepare("DELETE FROM albums WHERE id = ?");
        $del->bind_param("i", $id);
        $del->execute();
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?deleted=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Library | Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-color: #f4f7fe;
            --sidebar-color: #0f111a;
            --accent-color: #e14eca;
            --card-bg: #ffffff;
        }

        body {
            background: var(--bg-color);
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding-bottom: 80px; /* Space for mobile nav */
        }

        /* --- Sidebar (Desktop) --- */
        .sidebar {
            position: fixed; left: 0; top: 0; width: 240px; height: 100vh;
            background: var(--sidebar-color); padding: 30px 15px; z-index: 1000;
            transition: 0.3s;
        }
        .sidebar a {
            display: flex; align-items: center; color: #a0aec0;
            padding: 12px 16px; margin-bottom: 8px; border-radius: 12px;
            text-decoration: none; font-weight: 500;
        }
        .sidebar a:hover, .sidebar a.active { background: rgba(225, 78, 202, 0.15); color: #fff; }

        /* --- Mobile Navigation (Bottom Bar) --- */
        .mobile-nav {
            display: none; position: fixed; bottom: 0; left: 0; width: 100%;
            background: var(--sidebar-color); padding: 10px 0;
            justify-content: space-around; z-index: 1001;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .mobile-nav a { color: #a0aec0; text-decoration: none; font-size: 1.2rem; display: flex; flex-direction: column; align-items: center; }
        .mobile-nav a.active { color: var(--accent-color); }
        .mobile-nav span { font-size: 0.7rem; margin-top: 4px; }

        /* --- Main Layout --- */
        .main-content { margin-left: 240px; padding: 30px; transition: 0.3s; }

        .search-box {
            width: 100%; border: none; padding: 12px 20px; border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); outline: none; margin-bottom: 25px;
        }

        /* --- Grid System --- */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }

        .card {
            background: var(--card-bg); border-radius: 18px; border: none;
            overflow: hidden; transition: 0.3s; position: relative;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        }
        .card:hover { transform: translateY(-5px); }

        .thumbnail { width: 100%; aspect-ratio: 1/1; background: #000; position: relative; }
        .thumbnail video, .thumbnail img { width: 100%; height: 100%; object-fit: cover; }

        .info { padding: 15px; }
        .title { font-size: 1.05rem; font-weight: 700; color: #1a202c; display: block; }
        .artist { font-size: 0.85rem; color: var(--accent-color); font-weight: 600; }

        .badge-group { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 10px; }
        .badge-info { background: #edf2f7; color: #4a5568; padding: 3px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; }

        .delete-btn {
            position: absolute; top: 10px; right: 10px; z-index: 5;
            background: rgba(239, 68, 68, 0.9); color: white; border: none;
            width: 32px; height: 32px; border-radius: 8px; font-size: 0.9rem;
        }

        /* --- Responsive Queries --- */
        @media (max-width: 992px) {
            .sidebar { width: 80px; padding: 20px 10px; }
            .sidebar span, .sidebar h5 { display: none; }
            .main-content { margin-left: 80px; }
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .mobile-nav { display: flex; }
            .main-content { margin-left: 0; padding: 20px; }
            .grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
            .info { padding: 10px; }
            .title { font-size: 0.9rem; }
            .delete-btn { opacity: 1; width: 28px; height: 28px; }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <h5 class="text-white fw-bold mb-5 px-3">STUDIO</h5>
        <a href="dashboard.php"><i class="fa fa-home me-3"></i> <span>Home</span></a>
        <a href="add_albums.php"><i class="fa fa-plus me-3"></i> <span>Upload</span></a>
        <a href="#" class="active"><i class="fa fa-compact-disc me-3"></i> <span>Library</span></a>
        <a href="logout.php" class="text-danger mt-5"><i class="fa fa-power-off me-3"></i> <span>Logout</span></a>
    </aside>

    <nav class="mobile-nav">
        <a href="dashboard.php"><i class="fa fa-home"></i><span>Home</span></a>
        <a href="add_albums.php"><i class="fa fa-plus-square"></i><span>Add</span></a>
        <a href="#" class="active"><i class="fa fa-compact-disc"></i><span>Library</span></a>
        <a href="logout.php"><i class="fa fa-sign-out-alt"></i><span>Exit</span></a>
    </nav>

    <main class="main-content">
        <div class="mb-4">
            <h2 class="fw-bold mb-3">Albums Library</h2>
            <input type="text" id="search" class="search-box" placeholder="Search albums...">
        </div>

        <div class="grid" id="albumGrid">
            <?php
            $result = $conn->query("SELECT * FROM albums ORDER BY id DESC");
            while ($row = $result->fetch_assoc()):
                $title = htmlspecialchars($row['title']);
                $artist = htmlspecialchars($row['artist']);
                $videoFile = "uploads/albums/" . $row['video'];
                $coverFile = "uploads/albums/" . $row['cover'];
            ?>
                <div class="card" data-search="<?= strtolower("$title $artist {$row['genre']}") ?>">
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                        <button type="button" class="delete-btn btn-confirm-delete"><i class="fa fa-trash"></i></button>
                    </form>
                    
                    <div class="thumbnail">
                        <?php if (!empty($row['video']) && file_exists($videoFile)): ?>
                            <video src="<?= $videoFile ?>" muted playsinline loop></video>
                        <?php else: ?>
                            <img src="<?= $coverFile ?>" alt="cover">
                        <?php endif; ?>
                    </div>
                    
                    <div class="info">
                        <span class="title text-truncate"><?= $title ?></span>
                        <span class="artist"><?= $artist ?></span>
                        <div class="badge-group">
                            <span class="badge-info"><?= $row['year'] ?></span>
                            <span class="badge-info"><?= $row['genre'] ?></span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Responsive Delete
        document.querySelectorAll('.btn-confirm-delete').forEach(btn => {
            btn.onclick = function() {
                Swal.fire({
                    title: 'Are you sure?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, Delete'
                }).then((res) => { if(res.isConfirmed) this.closest('form').submit(); });
            };
        });

        // Search Logic
        document.getElementById("search").oninput = function() {
            let val = this.value.toLowerCase();
            document.querySelectorAll(".card").forEach(c => {
                c.style.display = c.dataset.search.includes(val) ? "block" : "none";
            });
        };

        // Mobile-friendly Video Play (Tap/Hover)
        document.querySelectorAll('.card').forEach(card => {
            const video = card.querySelector('video');
            if(video) {
                const playVideo = () => video.play();
                const stopVideo = () => { video.pause(); video.currentTime = 0; };
                card.onmouseenter = playVideo;
                card.onmouseleave = stopVideo;
                card.onclick = () => { if(video.paused) playVideo(); else stopVideo(); };
            }
        });
    </script>
</body>
</html>