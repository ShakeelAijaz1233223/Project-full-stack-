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
    3. DELETE VIDEO (POST Method)
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    
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
    // PHP_SELF use karne se "Not Found" error kabhi nahi aayega
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=deleted");
    exit;
}

// Fetch videos for display
$videos = mysqli_query($conn, "SELECT * FROM videos ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Videos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #080808; color: #fff; font-family: 'Inter', sans-serif; }
        .table-container { background: #121212; border-radius: 15px; padding: 20px; border: 1px solid #222; }
        .btn-delete { color: #ff0055; border: 1px solid #ff0055; background: none; }
        .btn-delete:hover { background: #ff0055; color: #fff; }
    </style>
</head>
<body>
<div class="container py-5">
    
    <?php if(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
        <div class="alert alert-danger bg-danger text-white border-0 py-2 mb-4">
            <i class="bi bi-trash me-2"></i> Video and file deleted successfully!
        </div>
    <?php endif; ?>

    <div class="table-container shadow-lg">
        <h4 class="mb-4">MANAGE VIDEOS</h4>
        <table class="table table-dark table-hover align-middle">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>File Name</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($v = mysqli_fetch_assoc($videos)): ?>
                <tr>
                    <td><?= htmlspecialchars($v['title']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($v['file']) ?></td>
                    <td class="text-center">
                        <form method="POST" onsubmit="return confirm('Pakka delete karna hai?');" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?= $v['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-delete">
                                <i class="bi bi-trash3"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>