<?php
session_start();
include "../config/db.php";

// Admin Check
if (!isset($_SESSION['email']) || !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Delete Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("SELECT video, cover FROM albums WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        if (!empty($row['video'])) unlink("../uploads/albums/" . $row['video']);
        if (!empty($row['cover'])) unlink("../uploads/covers/" . $row['cover']);
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
    <title>Admin Library | Compact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --bg-color: #0f172a;
            --sidebar-color: #1e293b;
            --accent-color: #3b82f6;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* --- 105% COMPACT LOOK --- */
        html {
            zoom: 0.95; /* Effectively 105% smaller scale */
            -moz-transform: scale(0.95);
            -moz-transform-origin: 0 0;
        }

        body {
            background: var(--bg-color);
            background-image: radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.15) 0px, transparent 50%);
            color: var(--text-primary);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        /* --- Sidebar --- */
        .sidebar {
            position: fixed;
            left: 0; top: 0; width: 240px; height: 100vh;
            background: var(--sidebar-color);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding: 30px 15px; z-index: 1000;
            transition: var(--transition);
        }

        .sidebar a {
            display: flex; align-items: center; color: var(--text-secondary);
            padding: 12px 16px; margin-bottom: 8px; border-radius: 12px;
            text-decoration: none; transition: var(--transition); font-weight: 500;
        }

        .sidebar a:hover, .sidebar a.active {
            background: rgba(59, 130, 246, 0.1);
            color: var(--text-primary);
        }

        /* --- Main Content --- */
        .main-content {
            padding: 30px;
            margin-left: 240px;
            transition: var(--transition);
        }

        /* --- Search --- */
        .search-box {
            width: 100%; max-width: 500px; padding: 12px 20px;
            border-radius: 100px; border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05); color: white;
            font-size: 14px; margin-bottom: 30px; outline: none; transition: var(--transition);
        }
        .search-box:focus { border-color: var(--accent-color); background: rgba(255, 255, 255, 0.08); }

        /* --- Grid --- */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
            width: 100%;
        }

        .card {
            background: var(--card-bg); backdrop-filter: blur(20px);
            border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.05);
            overflow: hidden; position: relative; transition: var(--transition);
            display: flex; flex-direction: column; height: 100%;
        }

        .card:hover {
            transform: translateY(-8px);
            border-color: rgba(59, 130, 246, 0.4);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        .thumbnail { width: 100%; aspect-ratio: 16 / 9; background: #000; overflow: hidden; position: relative; }
        .thumbnail video, .thumbnail img { width: 100%; height: 100%; object-fit: cover; }

        .info { padding: 15px; flex-grow: 1; }
        .title { font-size: 15px; font-weight: 700; color: white; margin-bottom: 4px; display: block; }
        .artist { color: var(--accent-color); font-size: 13px; font-weight: 600; display: block; margin-bottom: 10px; }

        .badge-group { display: flex; flex-wrap: wrap; gap: 5px; }
        .badge-info {
            background: rgba(255, 255, 255, 0.04); color: var(--text-secondary);
            padding: 3px 8px; border-radius: 6px; font-size: 10px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* --- Action Buttons --- */
        .delete-btn {
            position: absolute; top: 10px; right: -100px; z-index: 30;
            background: #ef4444; color: #fff; border: none; padding: 5px 12px; 
            border-radius: 8px; cursor: pointer; font-size: 10px; font-weight: 800; 
            opacity: 0; transition: var(--transition);
        }
        .card:hover .delete-btn { right: 10px; opacity: 1; }

        /* --- Responsive Queries --- */
        @media (max-width: 992px) {
            .sidebar { width: 70px; padding: 20px 10px; }
            .sidebar a span, .sidebar h4 span { display: none; }
            .main-content { margin-left: 70px; padding: 20px; }
        }

        @media (max-width: 576px) {
            .sidebar { left: -100%; } /* Hide on mobile */
            .main-content { margin-left: 0; padding: 15px; }
            .grid { grid-template-columns: 1fr; } /* Single column on very small screens */
            .search-box { max-width: 100%; }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="mb-5 text-center">
            <h4 class="fw-bold text-white"><i class="fa-solid fa-compact-disc text-primary"></i> <span>Studio</span></h4>
        </div>
        <a href="dashboard.php"><i class="fa-solid fa-house"></i> <span>Home</span></a>
        <a href="upload.php"><i class="fa-solid fa-upload"></i> <span>Upload</span></a>
        <a href="logout.php" class="mt-auto text-danger"><i class="fa-solid fa-power-off"></i> <span>Logout</span></a>
    </aside>

    <main class="main-content">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <input type="text" id="search" class="search-box mb-0" placeholder="🔍 Search by Artist, Album, Genre...">
        </div>

        <div class="grid" id="albumGrid">
            <?php
            $result = $conn->query("SELECT * FROM albums ORDER BY id DESC");
            while ($row = $result->fetch_assoc()):
                $searchTag = strtolower($row['title']." ".$row['artist']." ".$row['genre']." ".$row['language']);
            ?>
                <div class="card" data-search="<?= $searchTag ?>">
                    <form method="POST" onsubmit="return confirm('Delete this album?');">
                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="delete-btn">DELETE</button>
                    </form>

                    <div class="thumbnail">
                        <?php if(!empty($row['video'])): ?>
                            <video src="../uploads/albums/<?= $row['video'] ?>" muted loop onmouseover="this.play()" onmouseout="this.pause()"></video>
                        <?php else: ?>
                            <img src="../uploads/covers/<?= $row['cover'] ?>" alt="Cover">
                        <?php endif; ?>
                    </div>

                    <div class="info">
                        <span class="title text-truncate"><?= htmlspecialchars($row['title']) ?></span>
                        <span class="artist"><?= htmlspecialchars($row['artist']) ?></span>
                        <div class="badge-group">
                            <span class="badge-info"><?= $row['year'] ?></span>
                            <span class="badge-info"><?= htmlspecialchars($row['genre']) ?></span>
                            <span class="badge-info"><?= htmlspecialchars($row['language']) ?></span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <script>
        // Live Search Logic
        document.getElementById('search').addEventListener('input', function(e) {
            let val = e.target.value.toLowerCase();
            document.querySelectorAll('.card').forEach(card => {
                let text = card.getAttribute('data-search');
                card.style.display = text.includes(val) ? "flex" : "none";
            });
        });
    </script>
</body>
</html>