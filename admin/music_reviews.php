<?php
include "../config/db.php";

// 1. DYNAMIC DELETE LOGIC
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $delete_query = mysqli_query($conn, "DELETE FROM reviews WHERE id = $id");
    
    if($delete_query) {
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
    <title>Premium Admin | Reviews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');

        :root { 
            --bg-dark: #060606; 
            --card-bg: #111111; 
            --accent: #ff0055; 
            --accent-glow: rgba(255, 0, 85, 0.3);
            --border-color: #222222;
        }

        body { 
            background: var(--bg-dark); 
            color: #eee; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            padding: 40px 20px; 
        }

        /* Header Section */
        .page-header { margin-bottom: 40px; }
        .page-title { font-weight: 800; letter-spacing: -1px; font-size: 1.75rem; }
        .accent-text { color: var(--accent); text-shadow: 0 0 15px var(--accent-glow); }

        /* Card & Table Styling */
        .admin-card { 
            background: var(--card-bg); 
            border: 1px solid var(--border-color); 
            border-radius: 20px; 
            overflow: hidden; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7); 
        }

        .table { margin-bottom: 0; border-collapse: separate; border-spacing: 0; }
        .table thead th { 
            background: #181818; 
            color: #888; 
            font-size: 0.75rem; 
            font-weight: 700;
            text-transform: uppercase; 
            letter-spacing: 1.5px; 
            padding: 20px;
            border: none;
        }

        .table tbody td { 
            padding: 20px; 
            border-bottom: 1px solid var(--border-color); 
            vertical-align: middle; 
            transition: all 0.2s ease;
        }

        .table tbody tr:hover td { background: #161616; }

        /* Rating Stars */
        .rating-star { color: #ffca08; font-size: 1rem; letter-spacing: 2px; }
        .star-empty { color: #333; }
        .rating-badge { 
            background: rgba(255, 202, 8, 0.1); 
            color: #ffca08; 
            font-weight: 700; 
            border: 1px solid rgba(255, 202, 8, 0.2);
        }

        /* Action Buttons */
        .btn-delete { 
            height: 40px;
            width: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 68, 68, 0.1);
            color: #ff4444; 
            border: 1px solid rgba(255, 68, 68, 0.2); 
            border-radius: 12px; 
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-delete:hover { 
            background: #ff4444; 
            color: #fff; 
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(255, 68, 68, 0.4); 
        }

        /* Modern Success Alert */
        .status-alert { 
            background: rgba(40, 167, 69, 0.1); 
            border: 1px solid rgba(40, 167, 69, 0.2);
            color: #28a745; 
            padding: 16px 24px; 
            border-radius: 16px; 
            margin-bottom: 30px; 
            display: flex;
            align-items: center;
            backdrop-filter: blur(10px);
        }

        .dashboard-btn {
            background: #1a1a1a;
            border: 1px solid var(--border-color);
            color: #fff;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600;
            transition: 0.3s;
        }

        .dashboard-btn:hover { background: #252525; color: var(--accent); border-color: var(--accent); }

    </style>
</head>
<body>

    <div class="container-fluid px-lg-5">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title m-0">USER <span class="accent-text">REVIEWS</span></h1>
                <p class="text-muted small mt-1">Manage feedback for your music tracks</p>
            </div>
            <a href="dashboard.php" class="dashboard-btn text-decoration-none">
                <i class="bi bi-grid-1x2-fill me-2 small"></i> Dashboard
            </a>
        </div>

        <?php if(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
            <div class="status-alert shadow-sm alert-dismissible fade show" role="alert">
                <i class="bi bi-check2-circle fs-4 me-3"></i>
                <div>
                    <span class="fw-bold">Success!</span> Review has been permanently removed.
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="table-responsive">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>Music Track</th>
                            <th>Rating Analysis</th>
                            <th>Commentary</th>
                            <th>Publication Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($res) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($res)): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-white fs-6"><?= htmlspecialchars($row['music_title']) ?></div>
                                    <code class="text-muted" style="font-size: 0.7rem;">UID: #<?= $row['id'] ?></code>
                                </td>
                                <td>
                                    <div class="rating-star mb-1">
                                        <?php 
                                        for($i=1; $i<=5; $i++) {
                                            if($i <= $row['rating']) echo '★';
                                            else echo '<span class="star-empty">★</span>';
                                        }
                                        ?>
                                    </div>
                                    <span class="badge rating-badge px-2 py-1 small"><?= $row['rating'] ?>.0</span>
                                </td>
                                <td>
                                    <div style="max-width: 350px; font-size: 0.88rem; line-height: 1.6; color: #aaa;">
                                        <i class="bi bi-quote fs-5 text-secondary opacity-25"></i>
                                        <?= htmlspecialchars($row['comment']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        <i class="bi bi-clock me-1"></i> <?= date('d M, Y', strtotime($row['created_at'])) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="<?= basename($_SERVER['PHP_SELF']) ?>?delete=<?= $row['id'] ?>" 
                                       onclick="return confirm('Do you want to delete this feedback?')" 
                                       class="btn-delete text-decoration-none">
                                        <i class="bi bi-trash3-fill"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="opacity-25 mb-3"><i class="bi bi-folder-x display-1"></i></div>
                                    <p class="text-muted">No reviews available in the database.</p>
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