<?php
include "../config/db.php";

// 1. DYNAMIC DELETE LOGIC
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $delete_query = mysqli_query($conn, "DELETE FROM reviews WHERE id = $id");

    if ($delete_query) {
        $current_page = basename($_SERVER['PHP_SELF']);
        header("Location: $current_page?status=deleted");
        exit();
    }
}

// 2. FETCH REVIEWS
$query = "SELECT reviews.*, music.title as music_title 
          FROM reviews 
          JOIN music ON reviews.music_id = music.id 
          ORDER BY reviews.id DESC";
$res = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Admin | Manage Reviews</title>
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
            background: var(--bg-dark);
            color: #eee;
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 40px 20px;
            -webkit-font-smoothing: antialiased;
        }

        /* Header Section */
        .page-header {
            margin-bottom: 40px;
        }

        .page-title {
            font-weight: 800;
            letter-spacing: -1px;
            font-size: 1.85rem;
        }

        .accent-text {
            color: var(--accent);
            text-shadow: 0 0 20px var(--accent-glow);
        }

        /* Card & Table Styling */
        .admin-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8);
        }

        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            background: #181818;
            color: #666;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.8px;
            padding: 22px;
            border: none;
        }

        .table tbody td {
            padding: 24px 22px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            transition: all 0.2s ease-in-out;
        }

        .table tbody tr:hover td {
            background: #161616;
        }

        /* Rating Stars */
        .rating-star {
            color: #ffca08;
            font-size: 1rem;
            letter-spacing: 2px;
            text-shadow: 0 0 10px rgba(255, 202, 8, 0.2);
        }

        .star-empty {
            color: #2a2a2a;
        }

        .rating-badge {
            background: rgba(255, 202, 8, 0.08);
            color: #ffca08;
            font-weight: 700;
            border: 1px solid rgba(255, 202, 8, 0.15);
            border-radius: 6px;
            padding: 4px 10px;
        }

        /* Action Buttons */
        .btn-delete {
            height: 42px;
            width: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 68, 68, 0.05);
            color: #ff4444;
            border: 1px solid rgba(255, 68, 68, 0.15);
            border-radius: 14px;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
        }

        .btn-delete:hover {
            background: #ff4444;
            color: #fff;
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 10px 20px rgba(255, 68, 68, 0.3);
        }

        /* Modern Success Alert */
        .status-alert {
            background: rgba(40, 167, 69, 0.08);
            border: 1px solid rgba(40, 167, 69, 0.2);
            color: #28a745;
            padding: 18px 26px;
            border-radius: 18px;
            margin-bottom: 35px;
            display: flex;
            align-items: center;
            backdrop-filter: blur(12px);
            animation: slideDown 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dashboard-btn {
            background: #1a1a1a;
            border: 1px solid var(--border-color);
            color: #999;
            padding: 12px 24px;
            border-radius: 14px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .dashboard-btn:hover {
            background: #fff;
            color: #000;
            border-color: #fff;
            transform: translateY(-2px);
        }

        .track-title {
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .track-uid {
            font-family: 'Courier New', Courier, monospace;
            letter-spacing: 0.5px;
            opacity: 0.6;
        }
    </style>
</head>

<body>

    <div class="container-fluid px-lg-5">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title m-0 text-uppercase">User <span class="accent-text">Reviews</span></h1>
                <p class="text-muted small mt-2">Moderation panel for music feedback and ratings.</p>
            </div>
            <a href="dashboard.php" class="dashboard-btn text-decoration-none">
                <i class="bi bi-grid-1x2-fill me-2 small"></i> Admin Panel
            </a>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
            <div class="status-alert shadow-sm alert-dismissible fade show" role="alert">
                <i class="bi bi-shield-check fs-4 me-3"></i>
                <div>
                    <span class="fw-bold">Database Updated</span> &mdash; The specific review record has been purged.
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="table-responsive">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Music Track</th>
                            <th style="width: 15%;">Rating Analysis</th>
                            <th style="width: 35%;">Commentary</th>
                            <th style="width: 15%;">Publication Date</th>
                            <th style="width: 10%;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($res) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($res)): ?>
                                <tr>
                                    <td>
                                        <div class="track-title"><?= htmlspecialchars($row['music_title']) ?></div>
                                        <code class="track-uid text-muted small">ID: #<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></code>
                                    </td>
                                    <td>
                                        <div class="rating-star mb-2">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $row['rating']) echo '★';
                                                else echo '<span class="star-empty">★</span>';
                                            }
                                            ?>
                                        </div>
                                        <span class="rating-badge small"><?= $row['rating'] ?>.0 / 5.0</span>
                                    </td>
                                    <td>
                                        <div style="max-width: 400px; font-size: 0.9rem; line-height: 1.7; color: #bbb; font-weight: 300;">
                                            <i class="bi bi-chat-left-text me-2 opacity-25"></i>
                                            "<?= htmlspecialchars($row['comment']) ?>"
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-muted small d-flex align-items-center">
                                            <i class="bi bi-calendar4-event me-2 text-secondary"></i>
                                            <?= date('M d, Y', strtotime($row['created_at'])) ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= basename($_SERVER['PHP_SELF']) ?>?delete=<?= $row['id'] ?>"
                                            onclick="return confirm('Attention: Are you sure you want to delete this feedback permanently?')"
                                            class="btn-delete text-decoration-none"
                                            title="Delete Review">
                                            <i class="bi bi-trash3-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="opacity-10 mb-3"><i class="bi bi-database-exclamation display-2"></i></div>
                                    <p class="text-muted fw-light">The moderation queue is currently empty.</p>
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