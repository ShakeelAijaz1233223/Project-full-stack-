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
    3. DELETE REVIEW (GET Method FIX)
================================ */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("SELECT file FROM videos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $filepath = "../uploads/videos/" . $row['file'];
        if (!empty($row['file']) && file_exists($filepath)) {
            unlink($filepath);
        }

        $del = $conn->prepare("DELETE FROM videos WHERE id = ?");
        $del->bind_param("i", $id);
        $del->execute();
    }

    header("Location: " . $_SERVER['PHP_SELF'] . "?status=deleted");
    exit;
}

/* ===============================
    4. FETCH DATA (VARIABLE FIX)
================================ */
$videos = mysqli_query($conn, "SELECT * FROM videos ORDER BY id DESC");
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Album Reviews | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --bg-dark: #080808;
            --card-bg: #121212;
            --accent: #ff0055;
        }

        body {
            background-color: var(--bg-dark);
            color: #fff;
            font-family: 'Inter', sans-serif;
        }

        .table-container {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid #222;
            margin-top: 30px;
        }

        .table {
            color: #fff;
            border-color: #222;
        }

        .table thead {
            background: #1a1a1a;
        }

        .album-img {
            width: 40px;
            height: 40px;
            border-radius: 5px;
            object-fit: cover;
            margin-right: 10px;
        }

        .stars {
            color: #ffca08;
        }

        .btn-action {
            padding: 4px 8px;
            font-size: 0.8rem;
        }

        .status-msg {
            font-size: 0.85rem;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .badge-rating {
            background: var(--accent);
            color: white;
            font-size: 0.75rem;
        }
    </style>
</head>
<tbody>
<?php if (mysqli_num_rows($videos) > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($videos)): ?>
        <tr>
            <td>
                <div class="d-flex align-items-center">
                    <img src="../uploads/albums/<?= $row['cover'] ?>" class="album-img">
                    <span class="fw-semibold"><?= htmlspecialchars($row['album_name']) ?></span>
                </div>
            </td>
            <td>
                <div class="stars">
                    <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $row['rating']) ? '★' : '☆'; ?>
                </div>
                <small class="text-muted"><?= $row['rating'] ?>/5</small>
            </td>
            <td style="max-width: 300px;">
                <div class="text-truncate" title="<?= htmlspecialchars($row['comment']) ?>">
                    <?= htmlspecialchars($row['comment']) ?>
                </div>
            </td>
            <td>
                <small class="text-muted">
                    <?= date('d M, Y', strtotime($row['created_at'])) ?>
                </small>
            </td>
            <td class="text-center">
                <a href="?delete=<?= $row['id'] ?>"
                   class="btn btn-outline-danger btn-action"
                   onclick="return confirm('Are you sure you want to delete this review?')">
                    <i class="bi bi-trash"></i>
                </a>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="5" class="text-center py-5 text-muted">
            No reviews found yet.
        </td>
    </tr>
<?php endif; ?>
</tbody>


</html>