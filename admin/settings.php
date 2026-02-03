<?php
session_start();
include "../config/db.php";

/* ===============================
    ADMIN AUTH (SAME AS DASHBOARD)
================================ */
if (!isset($_SESSION['email']) || !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
$email = mysqli_real_escape_string($conn, $_SESSION['email']);

/* ===== FETCH ADMIN ===== */
$admin_q = mysqli_query($conn, "SELECT * FROM admin_users WHERE email='$email' LIMIT 1");
$admin = mysqli_fetch_assoc($admin_q);

if (!$admin) {
    session_destroy();
    header("Location: login.php");
    exit;
}

/* ===== FETCH SETTINGS ===== */
$settings_q = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
$settings = mysqli_fetch_assoc($settings_q);

$msg = "";
$type = "success";

/* ===== UPDATE SETTINGS ===== */
if (isset($_POST['save_settings'])) {
    $site_name  = mysqli_real_escape_string($conn, $_POST['site_name']);
    $site_email = mysqli_real_escape_string($conn, $_POST['site_email']);

    if ($site_name == "" || $site_email == "") {
        $msg = "All fields are required!";
        $type = "danger";
    } else {
        $update_settings = mysqli_query($conn, "UPDATE settings SET site_name='$site_name', site_email='$site_email' WHERE id=1");
        $update_admin = mysqli_query($conn, "UPDATE admin_users SET email='$site_email' WHERE id=".$admin['id']);

        if ($update_settings && $update_admin) {
            session_regenerate_id(true);
            $_SESSION['email'] = $site_email;
            $msg = "Settings updated successfully!";
        } else {
            $msg = "Database error!";
            $type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Professional Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
        }

        body {
            background: #f4f7f6;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        /* 95% Width Container */
        .wrapper {
            width: 95%;
            margin: 20px auto;
            display: flex;
            gap: 20px;
        }

        /* Sidebar Style */
        .sidebar {
            width: 280px;
            background: #1e1e2d;
            border-radius: 20px;
            padding: 30px 20px;
            color: #fff;
            min-height: 85vh;
        }

        .main-content {
            flex: 1;
        }

        .card-custom {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            background: var(--glass-bg);
        }

        .card-header-custom {
            background: transparent;
            border-bottom: 1px solid #eee;
            padding: 25px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            background: #f9f9f9;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.25);
            border-color: #667eea;
        }

        .btn-update {
            background: var(--primary-gradient);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: transform 0.2s;
            color: white;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 5px 15px rgba(118, 75, 162, 0.3);
        }

        .nav-link-custom {
            color: #a2a3b7;
            padding: 12px 15px;
            border-radius: 10px;
            display: block;
            text-decoration: none;
            margin-bottom: 5px;
            transition: all 0.3s;
        }

        .nav-link-custom:hover, .nav-link-custom.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }

        .alert-custom {
            border-radius: 12px;
            border: none;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="sidebar shadow">
        <h3 class="mb-5 px-3">Admin Panel</h3>
        <nav>
            <a href="dashboard.php" class="nav-link-custom"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a>
            <a href="#" class="nav-link-custom active"><i class="fa-solid fa-gears me-2"></i> Site Settings</a>
            <a href="user.php" class="nav-link-custom"><i class="fa-solid fa-users me-2"></i> Users</a>
            <div class="mt-5 pt-5">
                <a href="logout.php" class="nav-link-custom text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
            </div>
        </nav>
    </div>

    <div class="main-content">
        <div class="card card-custom">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <h4 class="mb-0 fw-bold text-dark">System Configuration</h4>
                <span class="badge bg-light text-dark border p-2">v2.0.4</span>
            </div>
            
            <div class="card-body p-4">
                <?php if ($msg != ""): ?>
                    <div class="alert alert-<?= $type ?> alert-custom animate__animated animate__fadeIn">
                        <i class="fa-solid fa-circle-info me-2"></i> <?= $msg ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-7">
                        <p class="text-muted mb-4">Manage your website's primary identity and administrative contact information below.</p>
                        
                        <form method="post">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Global Site Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-globe text-muted"></i></span>
                                    <input type="text" name="site_name" class="form-control border-start-0"
                                           value="<?= htmlspecialchars($settings['site_name']) ?>" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Master Admin Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                                    <input type="email" name="site_email" class="form-control border-start-0"
                                           value="<?= htmlspecialchars($settings['site_email']) ?>" required>
                                </div>
                                <small class="text-muted mt-2 d-block">This email will be used for system notifications and login.</small>
                            </div>

                            <hr class="my-4 opacity-50">

                            <div class="d-flex align-items-center mt-4">
                                <button type="submit" name="save_settings" class="btn btn-update">
                                    Save Changes
                                </button>
                                <a href="dashboard.php" class="btn btn-link text-secondary ms-3 text-decoration-none">
                                    Discard Changes
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="col-lg-5 border-start d-none d-lg-block ps-5">
                        <h5 class="fw-bold">Why update these?</h5>
                        <ul class="list-unstyled mt-3">
                            <li class="mb-3 d-flex">
                                <i class="fa-solid fa-check text-success me-2 mt-1"></i>
                                <span><strong>Branding:</strong> Your site name appears in SEO titles and email headers.</span>
                            </li>
                            <li class="mb-3 d-flex">
                                <i class="fa-solid fa-check text-success me-2 mt-1"></i>
                                <span><strong>Security:</strong> Changing the admin email also updates your login credentials.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>