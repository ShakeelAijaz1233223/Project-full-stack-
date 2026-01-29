<?php
include "../config/db.php";

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

// Delete logic
if (isset($_POST['delete'])) {
    $id = intval($_POST['delete']);
    $res = mysqli_query($conn, "SELECT video FROM albums WHERE id=$id");
    if ($row = mysqli_fetch_assoc($res)) {
        if (!empty($row['video'])) {
            $filePath = "uploads/albums/" . $row['video'];
            if (file_exists($filePath)) unlink($filePath);
        }
    }
    mysqli_query($conn, "DELETE FROM albums WHERE id=$id");
    $deleted = true;
}

// Fetch albums
$albums = mysqli_query($conn, "SELECT * FROM albums ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Studio Pro | Manage Library</title>
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
            --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background: var(--bg-color);
            background-image: radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            color: var(--text-primary);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background: var(--sidebar-color);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding: 30px 15px;
            box-sizing: border-box;
            z-index: 1000;
        }

        .sidebar h2 {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--accent-color);
            letter-spacing: 1px;
            margin-bottom: 40px;
            padding-left: 15px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: var(--text-secondary);
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 10px;
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
        }

        .sidebar a i { margin-right: 12px; width: 20px; text-align: center; }

        .sidebar a:hover {
            background: rgba(59, 130, 246, 0.1);
            color: var(--text-primary);
            transform: translateX(4px);
        }

        /* ===== MAIN CONTENT ===== */
        .container-fluid-custom {
            padding: 40px;
            margin-left: 240px;
            transition: margin-left var(--transition);
        }

        .search-box {
            width: 100%;
            max-width: 450px;
            padding: 12px 20px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: white;
            font-size: 16px;
            margin-bottom: 35px;
            outline: none;
            transition: var(--transition);
        }

        .search-box:focus {
            border-color: var(--accent-color);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }

        /* ===== VIDEO GRID ===== */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }

        .card {
            background: var(--card-bg);
            -webkit-backdrop-filter: blur(8px);
            backdrop-filter: blur(8px);
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            overflow: hidden;
            position: relative;
            transition: var(--transition);
            animation: fadeUp 0.6s ease-out;
        }

        .card:hover {
            transform: translateY(-6px);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
        }

        .thumbnail {
            width: 100%;
            aspect-ratio: 16 / 9;
            background: #000;
            position: relative;
            overflow: hidden;
        }

        .thumbnail video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .info { padding: 18px; }

        .title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        .meta {
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* ===== DELETE BUTTON ===== */
        .delete-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 20;
            background: rgba(239, 68, 68, 0.9);
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 11px;
            font-weight: bold;
            opacity: 0;
            transition: var(--transition);
            -webkit-appearance: none;
        }

        .card:hover .delete-btn { opacity: 1; }

        .delete-btn:hover {
            background: #ef4444;
            transform: scale(1.05);
        }

        /* ===== ANIMATION ===== */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .sidebar { width: 80px; padding: 20px 10px; }
            .sidebar h2, .sidebar a span { display: none; }
            .container-fluid-custom { margin-left: 80px; }
        }

        @media (max-width: 600px) {
            .sidebar { display: none; }
            .container-fluid-custom { margin-left: 0; padding: 20px; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
   
    <a href="dashboard.php"><i class="fa-solid fa-house"></i> <span>Dashboard</span></a>
    <a href="add_albums.php"><i class="fa-solid fa-cloud-arrow-up"></i> <span>Upload</span></a>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span></a>
</aside>

<main class="container-fluid-custom">
    <?php if(isset($deleted)): ?>
        <div class="alert alert-primary border-0 shadow-sm mb-4" style="background: var(--accent-color); color: white;">
            <i class="fa-solid fa-check-circle me-2"></i> Video removed from library.
        </div>
    <?php endif; ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 2rem;">Video Library</h1>
            <p class="text-secondary small mb-4">Manage your studio collection</p>
        </div>
        <input type="text" id="search" class="search-box" placeholder="🔍 Search by title or artist...">
    </div>

    <div class="grid" id="videoGrid">
        <?php if(mysqli_num_rows($albums) > 0):
            while ($row = mysqli_fetch_assoc($albums)):
                $title = htmlspecialchars($row['title']);
                $artist = htmlspecialchars($row['artist']);
                $video = $row['video'];
                $id    = $row['id'];
        ?>
        <div class="card" data-search="<?php echo strtolower($title . ' ' . $artist); ?>">
            <form method="POST" onsubmit="return confirm('Permanently delete this video?');">
                <input type="hidden" name="delete" value="<?php echo $id; ?>">
                <button type="submit" class="delete-btn">DELETE</button>
            </form>

            <div class="thumbnail">
                <?php if(!empty($video)): ?>
                    <video controls preload="metadata">
                        <source src="uploads/albums/<?php echo $video; ?>" type="video/mp4">
                    </video>
                <?php else: ?>
                    <div class="h-100 d-flex align-items-center justify-content-center bg-dark text-muted">
                        <i class="fa-solid fa-video-slash me-2"></i> No Media
                    </div>
                <?php endif; ?>
            </div>

            <div class="info">
                <span class="title"><?php echo $title; ?></span>
                <span class="meta"><?php echo $artist; ?></span>
            </div>
        </div>
        <?php endwhile; else: ?>
            <div class="text-center py-5 w-100" style="grid-column: 1 / -1;">
                <h4 class="text-secondary">Library is empty</h4>
                <a href="add_albums.php" class="btn btn-primary mt-3 px-4">Upload First Video</a>
            </div>
        <?php endif; ?>
    </div>

    <div id="noResults" class="text-center py-5 w-100 d-none">
        <p class="text-secondary">No videos match your search.</p>
    </div>
</main>

<script>
// Search logic
const searchInput = document.getElementById("search");
const cards = document.querySelectorAll(".card");
const noResults = document.getElementById("noResults");

searchInput.addEventListener("input", function() {
    let val = this.value.toLowerCase().trim();
    let visibleCount = 0;

    cards.forEach(card => {
        const searchText = card.getAttribute('data-search');
        if (searchText.includes(val)) {
            card.style.display = "block";
            visibleCount++;
        } else {
            card.style.display = "none";
        }
    });

    noResults.classList.toggle('d-none', visibleCount > 0 || val === "");
});

// Single Play Logic
document.querySelectorAll('video').forEach(vid => {
    vid.addEventListener('play', () => {
        document.querySelectorAll('video').forEach(otherVid => {
            if (otherVid !== vid) otherVid.pause();
        });
    });
});
</script>

</body>
</html>