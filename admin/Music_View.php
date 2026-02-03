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
    3. DELETE MUSIC (POST ONLY)
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];

    $stmt = $conn->prepare("SELECT file FROM music WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $filePath = "../uploads/music/" . $row['file'];
        if (!empty($row['file']) && file_exists($filePath)) {
            unlink($filePath);
        }

        $del = $conn->prepare("DELETE FROM music WHERE id = ?");
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
    <title>Pro Music Library | Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        :root {
            --bg-color: #0f172a;
            --sidebar-color: #1e293b;
            --accent-color: #3b82f6;
            --secondary-accent: #00f2ff;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            /* Smooth Bounce Transition */
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
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background: var(--sidebar-color);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding: 30px 15px;
            z-index: 1000;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: var(--text-secondary);
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 12px;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .sidebar a:hover {
            background: rgba(59, 130, 246, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .main {
            margin-left: 240px;
            padding: 40px;
        }

        /* --- Advanced Grid & Card Design --- */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            perspective: 1200px;
          
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 28px;
            padding: 30px;
            position: relative;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            transform-style: preserve-3d;
            
        }

       
        .card:hover {
            border-color: rgba(59, 130, 246, 0.6);
            box-shadow:
                0 25px 50px rgba(0, 0, 0, 0.6),
                0 0 30px rgba(59, 130, 246, 0.3);
            
        }

        /* --- Floating Icon Box --- */
        .icon-box {
            width: 90px;
            height: 90px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            position: relative;
            transition: var(--transition);
        }

        .card:hover .icon-box {
            transform: translateZ(40px) scale(1.1);
            /* Floats towards the user */
            background: rgba(59, 130, 246, 0.15);
        }

        .icon-box i {
            font-size: 35px;
            color: var(--accent-color);
            transition: 0.3s;
        }

        .card:hover .icon-box i {
            color: var(--secondary-accent);
            filter: drop-shadow(0 0 8px var(--accent-color));
        }

        /* --- Spinning & Visualizer Logic --- */
        .is-playing .icon-box {
            animation: spin 3s linear infinite;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, transparent 70%);
        }



        .visualizer {
            display: flex;
            align-items: flex-end;
            gap: 4px;
            height: 25px;
            position: absolute;
            bottom: -30px;
            transition: 0.5s ease;
        }

        .is-playing .visualizer {
            bottom: 65px;
        }

        .bar {
            width: 4px;
            height: 5px;
            background: var(--secondary-accent);
            border-radius: 4px;
        }

        .is-playing .bar {
            animation: bounce 0.6s ease-in-out infinite alternate;
        }

        @keyframes bounce {
            0% {
                height: 5px;
            }

            100% {
                height: 25px;
            }
        }

        .bar:nth-child(2) {
            animation-delay: 0.1s;
        }

        .bar:nth-child(3) {
            animation-delay: 0.2s;
        }

        .bar:nth-child(4) {
            animation-delay: 0.3s;
        }

        /* --- Text & Labels --- */
        .card h5 {
            font-size: 19px;
            font-weight: 700;
            margin: 15px 0 5px;
            text-align: center;
        }

        .artist {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 20px;
        }

        /* --- Audio Player (Opaque on Hover) --- */
        .audio {
            width: 100%;
            height: 40px;
            filter: invert(1) hue-rotate(180deg) brightness(1.5);
            border-radius: 30px;
            opacity: 0.3;
            /* Subtle when not focused */
            transition: 0.3s ease;
        }

        .card:hover .audio,
        .is-playing .audio {
            opacity: 1;
            transform: translateZ(20px);
        }

        /* --- DELETE BUTTON (RIGHT SLIDE ENTRY) --- */
        .delete-btn {
            position: absolute;
            top: 20px;
            right: -100px;
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 14px;
            font-size: 11px;
            font-weight: 900;
            transition: var(--transition);
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
            opacity: 0;
        }

        .card:hover .delete-btn {
            right: 20px;
            /* Slides into view */
            opacity: 1;
        }

        .delete-btn:hover {
            transform: scale(1.1) translateZ(30px);
            background: #ff0000;
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.6);
        }

        /* --- Search Bar --- */
        .search {
            width: 100%;
            max-width: 550px;
            padding: 12px 20px;
            border-radius: 100px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: white;
            font-size: 15px;
            margin-bottom: 25px;
            outline: none;
            transition: 0.3s ease;
        }

        .search:focus {
            border-color: var(--accent-color);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 25px rgba(59, 130, 246, 0.2);
        }

        .is-playing {
            border-color: var(--secondary-accent) !important;
            background: rgba(59, 130, 246, 0.12) !important;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }

            .sidebar span {
                display: none;
            }

            .main {
                margin-left: 80px;
            }
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="mb-5 px-3">
            <h4 class="fw-bold text-white"><i class="fa-solid fa-compact-disc text-primary me-2"></i><span>Studio</span></h4>
        </div>
        <a href="dashboard.php"><i class="fa-solid fa-house"></i> <span>Dashboard</span></a>
        <a href="add_music.php"><i class="fa-solid fa-cloud-arrow-up"></i> <span>Upload</span></a>
        <a href="logout.php" class="mt-4 text-danger"><i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span></a>
    </aside>

    <div class="main">

        <input type="text" id="search" class="search" placeholder="🔍 Search track title, artist or genre...">


        <div class="grid">
            <?php
            $result = $conn->query("SELECT * FROM music ORDER BY id DESC");
            while ($row = $result->fetch_assoc()):
                $musicFile = "uploads/music/" . $row['file'];
                $searchKey = strtolower($row['title'] . ' ' . ($row['artist'] ?? '') . ' ' . ($row['genre'] ?? ''));
            ?>
                <div class="card animate__animated animate__fadeIn" data-search="<?= $searchKey ?>">

                    <form method="post">
                        <input type="hidden" name="delete_id" value="<?= (int)$row['id'] ?>">
                        <button type="button" class="delete-btn btn-confirm-delete">
                            <i class="fa-solid fa-trash-can"></i> REMOVE
                        </button>
                    </form>

                    <div class="icon-box">
                        <i class="fa-solid fa-music"></i>
                        <div class="visualizer">
                            <div class="bar"></div>
                            <div class="bar"></div>
                            <div class="bar"></div>
                            <div class="bar"></div>
                        </div>
                    </div>

                    <h5 class="text-truncate px-2" title="<?= htmlspecialchars($row['title']) ?>">
                        <?= htmlspecialchars($row['title']) ?>
                    </h5>
                    <span class="artist"><?= htmlspecialchars($row['artist'] ?? 'Unknown Artist') ?></span>

                    <audio class="audio" controls preload="none">
                        <source src="<?= htmlspecialchars($musicFile) ?>" type="audio/mpeg">
                    </audio>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        /* ==========================================
           1. PREMIUM AUDIO CONTROLS & ANIMATION
        ========================================== */
        const audios = document.querySelectorAll('.audio');

        audios.forEach(audio => {
            const card = audio.closest('.card');

            audio.addEventListener('play', () => {
                // Stop other tracks
                audios.forEach(other => {
                    if (other !== audio) {
                        other.pause();
                        other.closest('.card').classList.remove('is-playing');
                    }
                });
                card.classList.add('is-playing');
            });

            audio.addEventListener('pause', () => card.classList.remove('is-playing'));
            audio.addEventListener('ended', () => card.classList.remove('is-playing'));
        });

        /* ==========================================
           2. LIVE SEARCH WITH ANIMATION
        ========================================== */
        document.getElementById("search").addEventListener("input", function(e) {
            const query = e.target.value.toLowerCase().trim();
            document.querySelectorAll(".card").forEach(card => {
                const isMatch = card.dataset.search.includes(query);
                if (isMatch) {
                    card.style.display = "flex";
                    card.classList.add('animate__fadeIn');
                } else {
                    card.style.display = "none";
                }
            });
        });

        /* ==========================================
           3. DELETE & URL CLEANUP
        ========================================== */
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('deleted')) {
            Swal.fire({
                title: 'Removed!',
                text: 'Track deleted successfully.',
                icon: 'success',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#3b82f6',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                const cleanUrl = window.location.origin + window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            });
        }

        document.querySelectorAll('.btn-confirm-delete').forEach(button => {
            button.addEventListener('click', (e) => {
                const form = e.currentTarget.closest('form');
                Swal.fire({
                    title: 'Delete this track?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    background: '#0f172a',
                    color: '#fff',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#334155',
                    confirmButtonText: 'Yes, Delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Deleting...',
                            background: '#0f172a',
                            didOpen: () => Swal.showLoading()
                        });
                        form.submit();
                    }
                });
            });
        });
    </script>
</body>

</html>