<?php
include "../config/db.php";

// DELETE LOGIC
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $delete = mysqli_query($conn, "DELETE FROM reviews WHERE id = $id");

    if ($delete) {
        $current_file = basename($_SERVER['PHP_SELF']);
        header("Location: $current_file?status=deleted");
        exit();
    }
}

// FETCH MUSIC REVIEWS
$query = "SELECT reviews.*, music.title AS music_title 
          FROM reviews 
          JOIN music ON reviews.music_id = music.id 
          ORDER BY reviews.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin | Music Reviews</title>

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
}

/* Header */
.admin-wrapper {
    padding: 40px 0;
}

.page-title {
    font-weight: 800;
    font-size: 1.8rem;
    letter-spacing: -1px;
}

.accent-glow {
    color: var(--accent);
    text-shadow: 0 0 15px var(--accent-glow);
}

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

.dash-btn:hover {
    border-color: var(--accent);
    color: var(--accent);
}

/* Alert */
.alert-modern {
    background: rgba(25, 135, 84, 0.1);
    border: 1px solid rgba(25, 135, 84, 0.2);
    color: #2ecc71;
    border-radius: 14px;
    font-size: 0.9rem;
}

/* Card */
.glass-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
}

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
}

.table tbody tr:hover td {
    background: #141414;
}

/* Rating */
.stars {
    color: #ffca08;
    font-size: 0.9rem;
}

.star-off {
    color: #222;
}

.rating-num {
    background: #1a1a1a;
    color: #888;
    font-size: 0.7rem;
    padding: 2px 8px;
    border-radius: 5px;
    margin-top: 5px;
    display: inline-block;
}

/* Comment */
.comment-box {
    color: #999;
    font-size: 0.85rem;
    line-height: 1.6;
    max-width: 340px;
}

/* Delete Button */
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
    transition: 0.3s;
    text-decoration: none;
}

.btn-trash:hover {
    background: #ff4444;
    color: #fff;
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(255,68,68,0.3);
}
</style>
</head>

<body>

<div class="container admin-wrapper">

    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="page-title m-0">USER <span class="accent-glow">REVIEWS</span></h3>
            <p class="text-muted small m-0 mt-1">Moderation panel for music feedback and ratings.</p>
        </div>
        <a href="dashboard.php" class="dash-btn">
            <i class="bi bi-grid-fill me-2"></i>Dashboard
        </a>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
        <div class="alert alert-modern alert-dismissible fade show mb-4">
            <i class="bi bi-shield-check me-2"></i>
            Review record has been purged successfully.
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table table-dark mb-0">
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

                <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>

                <tr>
                    <td>
                        <div class="fw-bold text-white"><?= htmlspecialchars($row['music_title']) ?></div>
                        <code class="text-muted small">REF: #<?= $row['id'] ?></code>
                    </td>

                    <td>
                        <div class="stars">
                            <?php
                            for ($i=1; $i<=5; $i++)
                                echo ($i <= $row['rating']) ? '★' : '<span class="star-off">★</span>';
                            ?>
                        </div>
                        <span class="rating-num"><?= $row['rating'] ?>.0 / 5.0</span>
                    </td>

                    <td>
                        <div class="comment-box text-truncate" title="<?= htmlspecialchars($row['comment']) ?>">
                            <i class="bi bi-chat-left-text me-1 opacity-25"></i>
                            <?= htmlspecialchars($row['comment']) ?>
                        </div>
                    </td>

                    <td>
                        <div class="text-muted small">
                            <i class="bi bi-calendar3 me-2"></i>
                            <?= date('d M, Y', strtotime($row['created_at'])) ?>
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
                    <td colspan="5" class="text-center py-5 text-muted">
                        No music reviews available to moderate.
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
