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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compact Library | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-color: #0f172a;
            --sidebar-color: #1e293b;
            --accent-color: #3b82f6;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --transition: all 0.3s ease;
        }

        /* --- COMPACT ZOOM (SMALL LOOK) --- */
        html {
            zoom: 0.90; /* Makes everything 10% smaller */
            -moz-transform: scale(0.90);
            -moz-transform-origin: 0 0;
        }

        body {
            background: var(--bg-color);
            background-image: radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            color: var(--text-primary);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        /* --- Sidebar (Narrower) --- */
        .sidebar {
            position: fixed;
            left: 0; top: 0; width: 200px; height: 100vh;
            background: var(--sidebar-color);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            padding: 20px 10px; z-index: 1000;
        }

        .sidebar a {
            display: flex; align-items: center; color: var(--text-secondary);
            padding: 10px 14px; margin-bottom: 5px; border-radius: 10px;
            text-decoration: none; transition: var(--transition); font-weight: 500; font-size: 14px;
        }

        .sidebar a:hover {
            background: rgba(59, 130, 246, 0.1); color: var(--text-primary);
            transform: translateX(3px);
        }

        .main-content {
            padding: 25px;
            margin-left: 200px;
            transition: var(--transition);
        }

        /* --- Compact Search --- */
        .search-box {
            width: 100%; max-width: 400px; padding: 10px 20px;
            border-radius: 50px; border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05); color: white;
            font-size: 14px; margin-bottom: 25px; outline: none;
        }

        /* --- Small Grid --- */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 15px;
            width: 100%;
        }

        .card {
            background: var(--card-bg); backdrop-filter: blur(10px);
            border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.05);
            overflow: hidden; position: relative; transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-5px);
            border-color: rgba(59, 130, 246, 0.3);
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

        .thumbnail { width: 100%; aspect-ratio: 16 / 9; background: #000; overflow: hidden; }
        .thumbnail video, .thumbnail img { width: 100%; height: 100%; object-fit: cover; }

        .info { padding: 12px; }
        .title { font-size: 14px; font-weight: 700; color: white; margin-bottom: 2px; }
        .artist { color: var(--accent-color); font-size: 12px; font-weight: 600; margin-bottom: 8px; display: block;}

        .badge-group { display: flex; flex-wrap: wrap; gap: 4px; }
        .badge-info {
            background: rgba(255, 255, 255, 0.05); color: var(--text-secondary);
            padding: 2px 8px; border-radius: 6px; font-size: 10px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .delete-btn {
            position: absolute; top: 10px; right: 10px; z-index: 30;
            background: #ef4444; color: #fff; border: none; padding: 4px 10px;
            border-radius: 6px; cursor: pointer; font-size: 9px; font-weight: 800;
            opacity: 0; transition: var(--transition);
        }

        .card:hover .delete-btn { opacity: 1; }

        @media (max-width: 768px) {
            .sidebar { width: 60px; }
            .sidebar a span, .sidebar h4 span { display: none; }
            .main-content { margin-left: 60px; padding: 15px; }
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="mb-4 px-2">
            <h5 class="fw-bold text-white"><i class="fa-solid fa-compact-disc text-primary me-1"></i><span>Studio</span></h5>
        </div>
        <a href="dashboard.php"><i class="fa-solid fa-house"></i> <span>Home</span></a>
        <a href="add_albums.php"><i class="fa-solid fa-upload"></i> <span>Upload</span></a>
        <a href="logout.php" class="text-danger mt-3"><i class="fa-solid fa-power-off"></i> <span>Logout</span></a>
    </aside>

    <main class="main-content">
        <input type="text" id="search" class="search-box" placeholder="🔍 Quick search...">

        <div class="grid" id="albumGrid">
            <?php
            $result = $conn->query("SELECT * FROM albums ORDER BY id DESC");
            while ($row = $result->fetch_assoc()):
                $videoFile = "uploads/albums/" . $row['video'];
                $coverFile = "uploads/albums/" . $row['cover'];
            ?>
                <div class="card" data-search="<?= strtolower($row['title']." ".$row['artist']) ?>">
                    <form method="POST">
                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                        <button type="button" class="delete-btn btn-confirm-delete">DEL</button>
                    </form>

                    <div class="thumbnail">
                        <?php if (!empty($row['video']) && file_exists($videoFile)): ?>
                            <video src="<?= $videoFile ?>" muted loop onmouseover="this.play()" onmouseout="this.pause()"></video>
                        <?php else: ?>
                            <img src="<?= $coverFile ?>" alt="cover">
                        <?php endif; ?>
                    </div>

                    <div class="info">
                        <div class="title text-truncate"><?= htmlspecialchars($row['title']) ?></div>
                        <span class="artist"><?= htmlspecialchars($row['artist']) ?></span>
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
        // Delete Confirmation
        document.querySelectorAll('.btn-confirm-delete').forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('form');
                Swal.fire({
                    title: 'Delete?',
                    icon: 'warning',
                    showCancelButton: true,
                    background: '#1e293b',
                    color: '#fff',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Yes'
                }).then((res) => { if (res.isConfirmed) form.submit(); });
            });
        });

        // Simple Search
        document.getElementById("search").addEventListener("input", function() {
            let v = this.value.toLowerCase();
            document.querySelectorAll(".card").forEach(c => {
                c.style.display = c.dataset.search.includes(v) ? "block" : "none";
            });
        });
    </script>
</body>
</html>