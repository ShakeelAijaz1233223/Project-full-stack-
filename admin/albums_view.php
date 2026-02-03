<?php
session_start();
include "../config/db.php";

/* --- 1. ADMIN AUTH --- */
if (!isset($_SESSION['email']) || !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

/* --- 2. DELETE LOGIC --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("SELECT video, cover, audio FROM albums WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        // Updated paths to match your upload structure
        $videoPath = "uploads/albums/" . $row['video'];
        $coverPath = "uploads/albums/" . $row['cover']; // Adjust if using /covers/
        $audioPath = "uploads/albums/" . $row['audio']; // Adjust if using /audio/

        if (!empty($row['video']) && file_exists($videoPath)) unlink($videoPath);
        if (!empty($row['cover']) && file_exists($coverPath)) unlink($coverPath);
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
    <title>Manage Albums | Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --accent-color: #e14eca; 
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.12);
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            min-height: 100vh;
            color: #fff;
            padding-top: 80px; /* Space for fixed sidebar/nav if needed */
        }

        /* Sidebar Styling to match Glass UI */
        .sidebar {
            position: fixed; left: 0; top: 0; width: 260px; height: 100vh;
            background: rgba(0, 0, 0, 0.3); backdrop-filter: blur(15px);
            border-right: 1px solid var(--glass-border);
            padding: 40px 20px; z-index: 1000;
        }

        .sidebar a {
            display: flex; align-items: center; color: rgba(255,255,255,0.7);
            padding: 12px 18px; margin-bottom: 10px; border-radius: 15px;
            text-decoration: none; transition: 0.3s; font-weight: 500;
        }

        .sidebar a:hover, .sidebar a.active {
            background: var(--accent-color); color: #fff;
            box-shadow: 0 5px 15px rgba(225, 78, 202, 0.3);
        }

        .main-content { margin-left: 260px; padding: 40px; }

        /* Search Box to match Upload Form inputs */
        .search-box {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 50px; color: #fff;
            padding: 14px 25px; margin-bottom: 40px;
            width: 100%; max-width: 500px; backdrop-filter: blur(10px);
            transition: 0.3s;
        }
        .search-box:focus {
            outline: none; border-color: var(--accent-color);
            box-shadow: 0 0 20px rgba(225, 78, 202, 0.2);
        }

        /* Glass Cards */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }

        .card {
            background: var(--glass-bg); backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border); border-radius: 24px;
            overflow: hidden; transition: 0.4s ease; position: relative;
        }
        .card:hover {
            transform: translateY(-10px);
            border-color: var(--accent-color);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .thumbnail { 
            width: 100%; aspect-ratio: 1/1; background: #111; 
            position: relative; overflow: hidden;
        }
        .thumbnail img, .thumbnail video { 
            width: 100%; height: 100%; object-fit: cover; 
        }

        .info { padding: 20px; }
        .title { font-size: 16px; font-weight: 600; color: #fff; margin-bottom: 5px; display: block; }
        .artist { font-size: 13px; color: var(--accent-color); font-weight: 500; }
        .meta { font-size: 12px; color: rgba(255,255,255,0.5); margin-top: 8px; }

        /* Floating Delete Button */
        .delete-btn {
            position: absolute; top: 15px; right: 15px;
            background: #ff4757; color: #fff; border: none;
            width: 35px; height: 35px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; opacity: 0; transform: scale(0.8); transition: 0.3s;
            z-index: 10;
        }
        .card:hover .delete-btn { opacity: 1; transform: scale(1); }
        .delete-btn:hover { background: #ff6b81; box-shadow: 0 0 15px rgba(255, 71, 87, 0.5); }

        @media (max-width: 992px) {
            .sidebar { width: 80px; padding: 30px 10px; }
            .sidebar span { display: none; }
            .main-content { margin-left: 80px; }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="mb-5 text-center">
            <h4 class="fw-bold"><i class="fa fa-compact-disc text-primary"></i> <span class="ms-2">Studio</span></h4>
        </div>
        <a href="dashboard.php"><i class="fa fa-th-large me-3"></i> <span>Dashboard</span></a>
        <a href="add_albums.php"><i class="fa fa-cloud-upload-alt me-3"></i> <span>Upload</span></a>
        <a href="albums_library.php" class="active"><i class="fa fa-music me-3"></i> <span>Library</span></a>
        <a href="logout.php" class="mt-5 text-danger"><i class="fa fa-power-off me-3"></i> <span>Logout</span></a>
    </aside>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Your Library</h2>
            <input type="text" id="search" class="search-box mb-0" placeholder="Search title or artist...">
        </div>

        <div class="grid" id="albumGrid">
            <?php
            $result = $conn->query("SELECT * FROM albums ORDER BY id DESC");
            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
                    $coverImg = "uploads/albums/" . $row['cover'];
                    $videoFile = "uploads/albums/" . $row['video'];
            ?>
                <div class="card" data-search="<?= strtolower($row['title'] . ' ' . $row['artist']) ?>">
                    <form method="POST">
                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                        <button type="button" class="delete-btn btn-confirm-delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                    
                    <div class="thumbnail">
                        <?php if (!empty($row['video']) && file_exists($videoFile)): ?>
                            <video src="<?= $videoFile ?>" muted loop onmouseover="this.play()" onmouseout="this.pause()"></video>
                        <?php elseif (!empty($row['cover']) && file_exists($coverImg)): ?>
                            <img src="<?= $coverImg ?>" alt="Cover">
                        <?php else: ?>
                            <div class="h-100 d-flex align-items-center justify-content-center bg-dark">
                                <i class="fa fa-music fa-2x opacity-20"></i>
                            </div>
                        <?php endif; ?>
                    </div> 
                    
                    <div class="info">
                        <span class="title text-truncate"><?= htmlspecialchars($row['title']) ?></span>
                        <span class="artist"><?= htmlspecialchars($row['artist']) ?></span>
                        <div class="meta">
                            <span class="me-3"><i class="fa fa-calendar-alt me-1"></i> <?= $row['album_year'] ?></span>
                            <span><i class="fa fa-tag me-1"></i> <?= htmlspecialchars($row['genre']) ?></span>
                        </div>
                    </div>  
                </div>
            <?php endwhile; else: ?>
                <div class="col-12 text-center opacity-50">
                    <i class="fa fa-folder-open fa-3x mb-3"></i>
                    <p>No content published yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Search functionality
        document.getElementById("search").addEventListener("input", function() {
            const val = this.value.toLowerCase().trim();
            document.querySelectorAll(".card").forEach(card => {
                card.style.display = card.dataset.search.includes(val) ? "block" : "none";
            });
        });

        // SweetAlert Delete
        document.querySelectorAll('.btn-confirm-delete').forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('form');
                Swal.fire({
                    title: 'Delete this item?',
                    text: "This cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    background: '#203a43',
                    color: '#fff',
                    confirmButtonColor: '#ff4757',
                    confirmButtonText: 'Yes, delete it'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    </script>
</body>
</html>