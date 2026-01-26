<?php

include "db.php";

/* ---- LOGIN CHECK ---- */
if(!isset($_SESSION['email'])){
    header("Location: login.php");
    exit;
}

/* ---- GET USER DATA ---- */
$email = $_SESSION['email'];
$user_query = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");
$user = mysqli_fetch_assoc($user_query);

/* ---- HANDLE PROFILE UPDATE ---- */
$msg = "";

if(isset($_POST['update_profile'])){
    $name = mysqli_real_escape_string($conn,$_POST['name'] ?? '');
    $phone = mysqli_real_escape_string($conn,$_POST['phone'] ?? '');
    $address = mysqli_real_escape_string($conn,$_POST['address'] ?? '');

    /* ---- Handle avatar upload ---- */
    $avatar_name = $user['avatar'] ?? 'default.png';
    if(isset($_FILES['avatar']) && $_FILES['avatar']['name'] != ''){
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatar_name = 'avatar_'.$user['id'].'.'.$ext;
        if(!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['avatar']['tmp_name'], 'uploads/'.$avatar_name);
    }

    mysqli_query($conn,"UPDATE users SET name='$name', phone='$phone', address='$address', avatar='$avatar_name' WHERE id=".$user['id']);
    $msg = "Profile updated successfully!";
    $user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE email='$email'"));
}

/* ---- HANDLE PASSWORD CHANGE ---- */
if(isset($_POST['change_password'])){
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Plain text password check (matches current DB)
    if($current === $user['password']){
        if($new === $confirm){
            mysqli_query($conn,"UPDATE users SET password='$new' WHERE id=".$user['id']);
            $msg = "Password changed successfully!";
            $user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE email='$email'"));
        } else {
            $msg = "New password and confirm password do not match!";
        }
    } else {
        $msg = "Current password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .avatar-preview { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border:2px solid #ddd; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header bg-dark text-white">
            <h3 class="mb-0">👤 My Profile</h3>
        </div>

        <div class="card-body">
            <?php if($msg != ""): ?>
                <div class="alert alert-success"><?= $msg ?></div>
            <?php endif; ?>

            <div class="row">
                <!-- Avatar & Upload -->
                <div class="col-md-4 text-center">
                    <img src="uploads/<?= !empty($user['avatar']) ? $user['avatar'] : 'default.png' ?>" class="avatar-preview mb-3">
                    <form method="post" enctype="multipart/form-data">
                        <input type="file" name="avatar" class="form-control mb-2">
                        <button type="submit" name="update_profile" class="btn btn-primary w-100">Update Avatar</button>
                    </form>
                </div>

                <!-- Profile Info & Password -->
                <div class="col-md-8">
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label>Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?= $user['name'] ?? '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" class="form-control" value="<?= $user['email'] ?? '' ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= $user['phone'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label>Address</label>
                            <textarea name="address" class="form-control"><?= $user['address'] ?? '' ?></textarea>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-success">Update Profile</button>
                    </form>

                    <hr>
                    <h5>Change Password</h5>
                    <form method="post">
                        <div class="mb-3">
                            <label>Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-footer text-end">
            <a href="dashboard.php" class="btn btn-secondary me-2">⬅ Back</a>
            <a href="logout.php" class="btn btn-outline-dark">Logout</a>
        </div>
    </div>
</div>

</body>
</html>
