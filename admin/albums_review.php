<?php
// session_start();
include "../config/db.php";

// 1. DYNAMIC DELETE LOGIC
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $delete = mysqli_query($conn, "DELETE FROM album_reviews WHERE id = $id");

    if ($delete) {
        $current_file = basename($_SERVER['PHP_SELF']);
        header("Location: $current_file?status=deleted");
        exit();
    }
}

// 2. FETCH REVIEWS
$query = "SELECT album_reviews.*, albums.title as album_name, albums.cover 
          FROM album_reviews 
          JOIN albums ON album_reviews.album_id = albums.id 
          ORDER BY album_reviews.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Album Review Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');

        :root {
            --bg-dark: #050505;
            --card-bg: #0f0f0f;
            --accent: #ff0055;
            --accent-glow: rgba(255, 0, 85, 0.4);
            --border: #222;
        }

        body {
            background-color: var(--bg-dark);
            color: #e0e0e0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.2px;
        }

        /* Container & Header */
        .admin-wrapper { padding: 40px 0; }
        .page-title { font-weight: 800; font-size: 1.8rem; letter-spacing: -1px; }
        .accent-glow { color: var(--accent); text-shadow: 0 0 15px var(--accent-glow); }

        /* Modern Dashboard Button */
        .dash-btn {
            background: #151515;
            border: 1px solid var(--border);
            color: #fff;
            padding: 8px 20px;
            border-radius: 12px;
            transition: 0.3s;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .dash-btn:hover { border-color: var(--accent); color: var(--accent); background: #1a1a1a; }

        /* Alert Styling */
        .alert-modern {
            background: rgba(25, 135, 84, 0.1);
            border: 1px solid rgba(25, 135, 84, 0.2);
            color: #2ecc71;
            border-radius: 14px;
            backdrop-filter: blur(10px);
            font-size: 0.9rem;
        }

        /* Table Card & Content */
        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .table { margin-bottom: 0; }
        .table thead th {
            background: #161616;
            color: #666;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 20px;
            border: none;
        }

        .table tbody td {
            padding: 20px;
            border-bottom: 1px solid #1a1a1a;
            vertical-align: middle;
            transition: 0.2s;
        }

        .table tbody tr:hover td { background: #141414; }

        /* Album Item */
        .album-img {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            object-fit: cover;
            margin-right: 15px;
            border: 1px solid #333;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        /* Rating Stars */
        .stars { color: #ffca08; font-size: 0.9rem; }
        .star-off { color: #222; }
        .rating-num { 
            background: #1a1a1a; 
            color: #888; 
            font-size: 0.7rem; 
            padding: 2px 8px; 
            border-radius: 5px; 
            margin-top: 5px;
            display: inline-block;
        }

        /* Action Button */
        .btn-trash {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 68, 68, 0.05);
            color: #ff4444;
            border: 1px solid rgba(255, 68, 68, 0.15);
            border-radius: 12px;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
        }
        .btn-trash:hover {
            background: #ff4444;
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 68, 68, 0.3);
        }

        .comment-box {
            color: #999;
            font-size: 0.85rem;
            line-height: 1.6;
            max-width: 320px;
        }

    </style>
</head>

<body>

    <div class="container admin-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="page-title m-0">ALBUM <span class="accent-glow">REVIEWS</span></h3>
                <p class="text-muted small m-0 mt-1">Manage and moderate customer album feedback</p>
            </div>
            <a href="dashboard.php" class="dash-btn">
                <i class="bi bi-grid-fill me-2"></i>Dashboard
            </a>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
            <div class="alert alert-modern alert-dismissible fade show mb-4 shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-shield-check fs-4 me-3"></i>
                    <span><strong>System Update:</strong> Review record has been purged successfully.</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="glass-card">
            <div class="table-responsive">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>Album Details</th>
                            <th>Rating Score</th>
                            <th>Feedback Comment</th>
                            <th>Timestamp</th>
                            <th class="text-center">Control</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="uploads/albums/<?= $row['cover'] ?>" class="album-img" onerror="this.src='https://via.placeholder.com/48/111/fff?text=No+Img'">
                                            <div>
                                                <div class="fw-bold text-white"><?= htmlspecialchars($row['album_name']) ?></div>
                                                <code class="text-muted" style="font-size: 0.65rem;">REF: #<?= $row['id'] ?></code>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++) 
                                                echo ($i <= $row['rating']) ? '★' : '<span class="star-off">★</span>'; 
                                            ?>
                                        </div>
                                        <span class="rating-num"><?= $row['rating'] ?>.0 / 5.0</span>
                                    </td>
                                    <td>
                                        <div class="comment-box text-truncate" title="<?= htmlspecialchars($row['comment']) ?>">
                                            <i class="bi bi-quote opacity-25 me-1"></i>
                                            <?= htmlspecialchars($row['comment']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-muted small">
                                            <i class="bi bi-calendar3 me-2"></i><?= date('d M, Y', strtotime($row['created_at'])) ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= basename($_SERVER['PHP_SELF']) ?>?delete=<?= $row['id'] ?>"
                                           class="btn-trash"
                                           onclick="return confirm('Purge this review from database?')">
                                            <i class="bi bi-trash3-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="bi bi-layers text-muted fs-1 mb-3 d-block opacity-25"></i>
                                    <span class="text-muted fs-6">No album reviews available to moderate.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>