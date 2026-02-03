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

/* ===============================
    2. UPDATE ADMIN LAST SEEN
================================ */
$admin_id = (int)$_SESSION['admin_id'];
$stmt = $conn->prepare("UPDATE admin_users SET last_seen = NOW() WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();

/* ===============================
    3. DELETE ALBUM & FILES (POST)
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];

    $stmt = $conn->prepare("SELECT video, cover, audio FROM albums WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $videoPath = "../uploads/albums/" . $row['video'];
        if (!empty($row['video']) && file_exists($videoPath)) unlink($videoPath);

        $coverPath = "../uploads/covers/" . $row['cover'];
        if (!empty($row['cover']) && file_exists($coverPath)) unlink($coverPath);

        $audioPath = "../uploads/audio/" . $row['audio'];
        if (!empty($row['audio']) && file_exists($audioPath)) unlink($audioPath);

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
    <title>Albums Library | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-color: #0f172a;
            --sidebar-color: #1e293b;
            --accent-color: #3b82f6;
            --secondary-accent: #00f2ff;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background: var(--bg-color);
            background-image: radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.15) 0px, transparent 50%);
            color: var(--text-primary);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding-bottom: 80px; /* Space for Mobile Nav */
        }

        /* --- Sidebar (Desktop) --- */
        .sidebar {
            position: fixed; left: 0; top: 0; width: 240px; height: 100vh;
            background: var(--sidebar-color); border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding: 30px 15px; box-sizing: border-box; z-index: 1000;
        }
        .sidebar a {
            display: flex; align-items: center; color: var(--text-secondary);
            padding: 12px 16px; margin-bottom: 8px; border-radius: 12px;
            text-decoration: none; transition: var(--transition); font-weight: 500;
        }
        .sidebar a:hover, .sidebar a.active { background: rgba(59, 130, 246, 0.1); color: var(--text-primary); transform: translateX(5px); }

        /* --- Mobile Navigation (Bottom Bar) --- */
        .mobile-nav {
            display: none; position: fixed; bottom: 0; left: 0; width: 100%;
            background: var(--sidebar-color); padding: 12px 0;
            justify-content: space-around; z-index: 1001;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        .mobile-nav a { color: var(--text-secondary); text-decoration: none; font-size: 1.2rem; display: flex; flex-direction: column; align-items: center; }
        .mobile-nav a.active { color: var(--accent-color); }
        .mobile-nav span { font-size: 0.65rem; margin-top: 4px; }

        /* --- Main Content --- */
        .main-content { margin-left: 240px; padding: 40px; transition: var(--transition); }

        .search-box {
            width: 100%; max-width: 550px; padding: 12px 25px; border-radius: 100px;
            border: 1px solid rgba(255, 255, 255, 0.1); background: rgba(255, 255, 255, 0.05);
            color: white; font-size: 15px; margin-bottom: 30px; outline: none; transition: var(--transition);
        }

        .search-box:focus {
            border-color: var(--accent-color);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.2);
        }

        /* --- Responsive Grid --- */
        .grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
            gap: 25px; 
            width: 100%; 
        }

        .card {
            background: var(--card-bg); backdrop-filter: blur(20px); border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05); overflow: hidden;
            position: relative; transition: var(--transition);
            display: flex; flex-direction: column;
        }

        .card:hover { 
            transform: translateY(-10px); 
            border-color: rgba(59, 130, 246, 0.5); 
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 20px rgba(59, 130, 246, 0.2); 
        }

        .thumbnail { width: 100%; aspect-ratio: 16 / 9; background: #000; position: relative; overflow: hidden; }
        .thumbnail video { width: 100%; height: 100%; object-fit: cover; cursor: pointer; transition: 0.3s; }

        .info { padding: 20px; }
        .title { font-size: 17px; font-weight: 700; color: white; margin-bottom: 5px; display: block; }
        .meta { font-size: 13px; color: var(--text-secondary); line-height: 1.6; }
        .meta i { color: var(--accent-color); width: 18px; }

        .delete-btn {
            position: absolute; top: 15px; right: -100px;
            z-index: 30;
            background: linear-gradient(135deg, #ef4444, #b91c1c); color: #fff;
            border: none; padding: 6px 15px; border-radius: 10px;
            cursor: pointer; font-size: 11px; font-weight: 800;
            opacity: 0; transition: var(--transition);
        }

        .card:hover .delete-btn { right: 15px; opacity: 1; }

        /* --- RESPONSIVE QUERIES --- */
        @media (max-width: 992px) {
            .sidebar { width: 80px; }
            .sidebar a span, .sidebar h4 span { display: none; }
            .main-content { margin-left: 80px; padding: 20px; }
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .mobile-nav { display: flex; }
            .main-content { margin-left: 0; padding: 20px; margin-top: 10px; }
            .grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
            .info { padding: 12px; }
            .title { font-size: 14px; }
            .meta { font-size: 11px; }
            .delete-btn { right: 10px; opacity: 1; padding: 4px 10px; }
            .thumbnail { aspect-ratio: 1/1; } /* Square on mobile for better fit */
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="mb-5 px-3">
            <h4 class="fw-bold text-white"><i class="fa-solid fa-compact-disc text-primary me-2"></i><span>Admin</span></h4>
        </div>
        <a href="dashboard.php"><i class="fa-solid fa-house"></i> <span>Dashboard</span></a>
        <a href="add_albums.php"><i class="fa-solid fa-cloud-arrow-up"></i> <span>Upload</span></a>
        <a href="#" class="active"><i class="fa-solid fa-layer-group"></i> <span>Library</span></a>
        <a href="logout.php" class="text-danger mt-4"><i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span></a>
    </aside>

    <nav class="mobile-nav">
        <a href="dashboard.php"><i class="fa-solid fa-house"></i><span>Home</span></a>
        <a href="add_albums.php"><i class="fa-solid fa-cloud-arrow-up"></i><span>Add</span></a>
        <a href="#" class="active"><i class="fa-solid fa-layer-group"></i><span>Library</span></a>
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i><span>Exit</span></a>
    </nav>

    <main class="main-content">
        <input type="text" id="search" class="search-box" placeholder="🔍 Search albums or artists...">

        <div class="grid" id="albumGrid">
            <?php
            $result = $conn->query("SELECT * FROM albums ORDER BY id DESC");
            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
                    $title = htmlspecialchars($row['title']);
                    $artist = htmlspecialchars($row['artist']);
                    $videoFile = "../uploads/albums/" . $row['video'];
            ?>
                    <div class="card" data-search="<?= strtolower($title . ' ' . $artist) ?>">
                        <form method="POST">
                            <input type="hidden" name="delete_id" value="<?= (int)$row['id'] ?>">
                            <button type="button" class="delete-btn btn-confirm-delete">DELETE</button>
                        </form>
                        
                        <div class="thumbnail">
                            <?php if (!empty($row['video']) && file_exists($videoFile)): ?>
                                <video src="<?= $videoFile ?>" preload="metadata" playsinline loop muted></video>
                            <?php else: ?>
                                <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                                    <i class="fa fa-video-slash mb-2 fs-4"></i>
                                    <small>No Video</small>
                                </div>
                            <?php endif; ?>
                        </div>  
                        
                        <div class="info">
                            <span class="title text-truncate" title="<?= $title ?>"><?= $title ?></span>
                            <div class="meta">
                                <i class="fa fa-user"></i> <?= $artist ?> <br>
                                <i class="fa-regular fa-calendar-days"></i> <?= htmlspecialchars($row['album_year']) ?>
                            </div>
                        </div>  
                    </div>
            <?php 
                endwhile;
            endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // 1. Success Message Alert
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('deleted')) {
            Swal.fire({
                title: 'Deleted!',
                text: 'Album and files removed successfully.',
                icon: 'success',
                background: '#1e293b',
                color: '#f8fafc',
                confirmButtonColor: '#3b82f6',
                timer: 2000
            }).then(() => {
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        }

        // 2. Confirm Delete Alert
        document.querySelectorAll('.btn-confirm-delete').forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "The album files will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    background: '#1e293b',
                    color: '#f8fafc',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#334155',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        // 3. Live Search
        document.getElementById("search").addEventListener("input", function() {
            const val = this.value.toLowerCase().trim();
            document.querySelectorAll(".card").forEach(card => {
                card.style.display = card.dataset.search.includes(val) ? "flex" : "none";
            });
        });

        // 4. Video Hover & Click Logic (Enhanced for mobile)
        document.querySelectorAll('.card').forEach(card => {
            const video = card.querySelector('video');
            if(video) {
                // Desktop Hover
                card.addEventListener('mouseenter', () => video.play());
                card.addEventListener('mouseleave', () => { video.pause(); video.currentTime = 0; });
                
                // Mobile Click
                video.addEventListener('click', function() {
                    if (this.paused) {
                        document.querySelectorAll('video').forEach(v => v.pause());
                        this.play();
                        this.controls = true;
                    } else {
                        this.pause();
                        this.controls = false;
                    }
                });
            }
        });
    </script>
</body>
</html>