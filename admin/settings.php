<?php
include "db.php";


/* ---- LOGIN CHECK ---- */
if(!isset($_SESSION['email'])){
    header("Location: login.php");
    exit;
}

/* ---- GET LOGGED IN USER ---- */
$email = $_SESSION['email'];
$user_query = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");
$logged_user = mysqli_fetch_assoc($user_query);

$msg = "";
$msg_type = "success";

/* ---- ADMIN SETTINGS ---- */
if($logged_user['role'] == 'admin'){
    // Update site settings
    if(isset($_POST['save_settings'])){
        $site_name  = mysqli_real_escape_string($conn,$_POST['site_name']);
        $site_email = mysqli_real_escape_string($conn,$_POST['site_email']);

        $update = mysqli_query($conn,"UPDATE settings SET site_name='$site_name', site_email='$site_email' WHERE id=1");
        $update_user_email = mysqli_query($conn,"UPDATE users SET email='$site_email' WHERE id=".$logged_user['id']);

        if($update && $update_user_email){
            $_SESSION['email'] = $site_email;
            $msg = "✅ Site settings updated successfully!";
            $msg_type = "success";
        } else {
            $msg = "❌ Failed to update site settings: ".mysqli_error($conn);
            $msg_type = "danger";
        }
    }

    // Change admin password
    if(isset($_POST['change_admin_password'])){
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $fresh = mysqli_fetch_assoc(mysqli_query($conn,"SELECT password FROM users WHERE id=".$logged_user['id']));

        if(!password_verify($current, $fresh['password'])){
            $msg = "❌ Current password incorrect!";
            $msg_type = "danger";
        } elseif($new !== $confirm){
            $msg = "❌ New password and confirm password do not match!";
            $msg_type = "danger";
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $update = mysqli_query($conn,"UPDATE users SET password='$hash' WHERE id=".$logged_user['id']);
            $msg = $update ? "✅ Password updated successfully!" : "❌ Failed to update password!";
            $msg_type = $update ? "success" : "danger";
        }
    }

    $settings_query = mysqli_query($conn,"SELECT * FROM settings WHERE id=1");
    $settings = mysqli_fetch_assoc($settings_query);
}

/* ---- USER PROFILE UPDATE ---- */
if(isset($_POST['update_profile'])){
    $name = mysqli_real_escape_string($conn,$_POST['name'] ?? '');
    $phone = mysqli_real_escape_string($conn,$_POST['phone'] ?? '');
    $address = mysqli_real_escape_string($conn,$_POST['address'] ?? '');

    $avatar_name = $logged_user['avatar'] ?? 'default.png';
    if(isset($_FILES['avatar']) && $_FILES['avatar']['name'] != ''){
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatar_name = 'avatar_'.$logged_user['id'].'.'.$ext;
        if(!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['avatar']['tmp_name'], 'uploads/'.$avatar_name);
    }

    mysqli_query($conn,"UPDATE users SET name='$name', phone='$phone', address='$address', avatar='$avatar_name' WHERE id=".$logged_user['id']);
    $msg = "✅ Profile updated successfully!";
    $logged_user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id=".$logged_user['id']));
}

/* ---- USER PASSWORD CHANGE ---- */
if(isset($_POST['change_user_password'])){
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if(!password_verify($current, $logged_user['password'])){
        $msg = "❌ Current password is incorrect!";
        $msg_type = "danger";
    } elseif($new !== $confirm){
        $msg = "❌ New password and confirm password do not match!";
        $msg_type = "danger";
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $update = mysqli_query($conn,"UPDATE users SET password='$hash' WHERE id=".$logged_user['id']);
        $msg = $update ? "✅ Password changed successfully!" : "❌ Failed to change password!";
        $msg_type = $update ? "success" : "danger";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .avatar-preview { width:120px; height:120px; object-fit:cover; border-radius:50%; border:2px solid #ddd; }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5">

    <?php if($msg != ""): ?>
        <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
    <?php endif; ?>

    <?php if($logged_user['role'] == 'admin'): ?>
        <!-- ADMIN SETTINGS -->
        <div class="card shadow mb-4">
            <div class="card-header bg-dark text-white">⚙️ Admin Settings</div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label>Site Name</label>
                        <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($settings['site_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Site Email</label>
                        <input type="email" name="site_email" class="form-control" value="<?= htmlspecialchars($settings['site_email']) ?>" required>
                    </div>
                    <button type="submit" name="save_settings" class="btn btn-primary">Save Settings</button>
                </form>

                <hr>
              
            <div class="card-footer text-end">
                <a href="dashboard.php" class="btn btn-secondary me-2">⬅ Back</a>
                <a href="logout.php" class="btn btn-outline-dark">Logout</a>
            </div>
        </div>
    <?php endif; ?>


</div>
        </div>

</div>
</body>
</html>
