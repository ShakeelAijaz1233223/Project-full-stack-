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
    3. DELETE ALBUM & FILES
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("SELECT video, cover, audio FROM albums WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        foreach(['video' => '../uploads/albums/', 'cover' => '../uploads/covers/', 'audio' => '../uploads/audio/'] as $key => $path) {
            if (!empty($row[$key]) && file_exists($path . $row[$key])) unlink($path . $row[$key]);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Library Manager | Admin</title>
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
        }

        body {
            background: var(--bg-color);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding-bottom: 80px; /* Space for Mobile Nav */
        }

        /* --- Sidebar & Desktop Nav --- */
        .sidebar {
            position: fixed; left: 0; top: 0; width: 260px; height: 100vh;
            background: var(--sidebar-color); border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding: 30px 15px; z-index: 1000; overflow-y: auto;
        }

        .sidebar a, .nav-link-mobile {
            display: flex; align-items: center; color: var(--text-secondary);
            padding: 12px 16px; margin-bottom: 5px; border-radius: 12px;
            text-decoration: none; transition: 0.3s; font-weight: 500;
        }

        .sidebar a:hover, .sidebar a.active { background: rgba(59, 130, 246, 0.1); color: var(--text-primary); }

        /* --- Filter Group --- */
        .filter-section { margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .filter-label { font-size: 11px; text-transform: uppercase; color: var(--accent-color); letter-spacing: 1px; font-weight: 800; margin-bottom: 10px; display: block; }
        .filter-select { background: #0f172a; border: 1px solid #334155; color: white; border-radius: 8px; font-size: 13px; width: 100%; padding: 8px; margin-bottom: 15px; }

        /* --- Mobile Navigation --- */
        .mobile-nav {
            display: none; position: fixed; bottom: 0; left: 0; width: 100%;
            background: var(--sidebar-color); border-top: 1px solid rgba(255,255,255,0.1);
            z-index: 2000; padding: 10px 0; justify-content: space-around;
        }
        .mobile-nav a { color: var(--text-secondary); text-align: center; font-size: 20px; text-decoration: none; }
        .mobile-nav a span { display: block; font-size: 10px; }
        .mobile-nav a.active { color: var(--accent-color); }

        /* --- Main Content --- */
        .main-content { padding: 30px; margin-left: 260px; }

        .search-container { position: sticky; top: 0; z-index: 900; background: var(--bg-color); padding: 10px 0 20px 0; }
        .search-box { width: 100%; max-width: 600px; padding: 12px 25px; border-radius: 50px; border: 1px solid #334155; background: #1e293b; color: white; outline: none; }

        /* --- Responsive Grid --- */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }

        .card {
            background: var(--card-bg); border-radius: 20px; border: 1px solid rgba(255,255,255,0.05);
            overflow: hidden; transition: 0.3s; position: relative;
        }
        .card:hover { transform: translateY(-5px); border-color: var(--accent-color); }

        .thumbnail { width: 100%; aspect-ratio: 16/9; background: #000; }
        .thumbnail video { width: 100%; height: 100%; object-fit: cover; }

        .info { padding: 15px; }
        .title { font-size: 16px; font-weight: 700; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .meta-tags { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 8px; }
        .tag { font-size: 10px; background: rgba(59, 130, 246, 0.2); color: #60a5fa; padding: 2px 8px; border-radius: 4px; }

        .delete-btn { position: absolute; top: 10px; right: 10px; background: #ef4444; border: none; color: white; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; display: none; }
        .card:hover .delete-btn { display: block; }

        @media (max-width: 992px) {
            .sidebar { display: none; }
            .mobile-nav { display: flex; }
            .main-content { margin-left: 0; padding: 15px; }
            .grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 10px; }
            .delete-btn { display: block; opacity: 0.8; }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <h4 class="fw-bold mb-4 px-3"><i class="fa-solid fa-compact-disc me-2 text-primary"></i> Admin</h4>
        <a href="dashboard.php"><i class="fa fa-home me-3"></i> Dashboard</a>
        <a href="add_albums.php"><i class="fa fa-upload me-3"></i> Upload New</a>
        <a href="albums_library.php" class="active"><i class="fa fa-music me-3"></i> Media Library</a>
        
        <div class="filter-section">
            <span class="filter-label">Quick Filters</span>
            
            <label class="filter-label">Language</label>
            <select class="filter-select js-filter" data-filter="language">
                <option value="all">All Languages</option>
                <option value="english">English</option>
                <option value="hindi">Hindi</option>
                <option value="spanish">Spanish</option>
            </select>

            <label class="filter-label">Genre</label>
            <select class="filter-select js-filter" data-filter="genre">
                <option value="all">All Genres</option>
                <option value="pop">Pop</option>
                <option value="rock">Rock</option>
                <option value="hip-hop">Hip Hop</option>
            </select>
        </div>

        <a href="logout.php" class="text-danger mt-5"><i class="fa fa-sign-out me-3"></i> Logout</a>
    </aside>

    <nav class="mobile-nav">
        <a href="dashboard.php"><i class="fa fa-home"></i><span>Home</span></a>
        <a href="add_albums.php"><i class="fa fa-plus-circle"></i><span>Upload</span></a>
        <a href="albums_library.php" class="active"><i class="fa fa-layer-group"></i><span>Library</span></a>
        <a href="logout.php"><i class="fa fa-user"></i><span>Logout</span></a>
    </nav>

    <main class="main-content">
        <div class="search-container">
            <input type="text" id="search" class="search-box" placeholder="Search by Album, Artist, or Year...">
        </div>

        <div class="grid" id="albumGrid">
            <?php
            // Assuming columns: id, title, artist, album_year, genre, language, video
            $result = $conn->query("SELECT * FROM albums ORDER BY id DESC");
            while ($row = $result->fetch_assoc()):
                $searchStr = strtolower($row['title'].' '.$row['artist'].' '.$row['album_year'].' '.$row['genre'].' '.$row['language']);
            ?>
                <div class="card" 
                     data-search="<?= $searchStr ?>" 
                     data-genre="<?= strtolower($row['genre']) ?>" 
                     data-language="<?= strtolower($row['language']) ?>">
                    
                    <form method="POST">
                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                        <button type="button" class="delete-btn btn-confirm-delete"><i class="fa fa-trash-can"></i></button>
                    </form>

                    <div class="thumbnail">
                        <?php $v = "uploads/albums/".$row['video']; ?>
                        <?php if(!empty($row['video']) && file_exists("../".$v)): ?>
                            <video src="../<?= $v ?>" preload="metadata" muted loop onmouseover="this.play()" onmouseout="this.pause(); this.currentTime=0;"></video>
                        <?php else: ?>
                            <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                                <i class="fa fa-music mb-2 fs-2"></i>
                                <small>No Preview</small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="info">
                        <div class="title" title="<?= htmlspecialchars($row['title']) ?>"><?= htmlspecialchars($row['title']) ?></div>
                        <small class="text-secondary d-block mt-1"><?= htmlspecialchars($row['artist']) ?></small>
                        
                        <div class="meta-tags">
                            <span class="tag"><?= htmlspecialchars($row['album_year']) ?></span>
                            <span class="tag"><?= htmlspecialchars($row['genre']) ?></span>
                            <span class="tag"><?= htmlspecialchars($row['language']) ?></span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Delete Confirmation
        document.querySelectorAll('.btn-confirm-delete').forEach(btn => {
            btn.onclick = function() {
                Swal.fire({
                    title: 'Delete this item?',
                    text: "Files will be removed from server.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    background: '#1e293b',
                    color: '#fff'
                }).then((res) => { if(res.isConfirmed) this.closest('form').submit(); });
            }
        });

        // Combined Search and Dynamic Filters
        const searchInput = document.getElementById('search');
        const filterDropdowns = document.querySelectorAll('.js-filter');
        const cards = document.querySelectorAll('.card');

        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase();
            const activeFilters = Array.from(filterDropdowns).map(f => ({
                type: f.dataset.filter,
                value: f.value.toLowerCase()
            }));

            cards.forEach(card => {
                const matchesSearch = card.dataset.search.includes(searchTerm);
                const matchesFilters = activeFilters.every(f => 
                    f.value === 'all' || card.getAttribute(`data-${f.type}`) === f.value
                );

                card.style.display = (matchesSearch && matchesFilters) ? "block" : "none";
            });
        }

        searchInput.oninput = applyFilters;
        filterDropdowns.forEach(f => f.onchange = applyFilters);
    </script>
</body>
</html>