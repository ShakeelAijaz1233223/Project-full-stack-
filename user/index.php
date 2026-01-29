<?php
include '../config/db.php'; // Ensure this file connects to your MySQL database 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOUND | Ultimate Entertainment Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-color: #ff2d55; --dark-bg: #0f0f0f; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--dark-bg); color: white; scroll-behavior: smooth; }
        .navbar { background: rgba(0,0,0,0.9) !important; border-bottom: 1px solid #333; }
        .hero-slider { height: 80vh; object-fit: cover; }
        .card { background: #1e1e1e; border: none; transition: transform 0.3s; color: white; }
        .card:hover { transform: translateY(-10px); border: 1px solid var(--primary-color); }
        .badge-new { position: absolute; top: 10px; right: 10px; background: var(--primary-color); animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
        .footer { background: #000; padding: 50px 0; border-top: 2px solid var(--primary-color); }
        .section-padding { padding: 80px 0; }
        .video-container video { width: 100%; border-radius: 15px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="fas fa-headphones-alt me-2"></i>SOUND</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="user_music_view.php">Music</a></li>
                    <li class="nav-item"><a class="nav-link" href="user_albums_view.php">Albums</a></li>
                    <li class="nav-item"><a class="nav-link" href="user_video_view.php">Videos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="btn btn-outline-light ms-lg-3" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="btn btn-primary ms-lg-3" href="login.php">Login / Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <header id="home" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="https://images.unsplash.com/photo-1470225620780-dba8ba36b745?auto=format&fit=crop&w=1350&q=80" class="d-block w-100 hero-slider" alt="Music">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded">
                    <h2>Latest Regional & English Hits</h2>
                    [cite_start]<p>Experience entertainment like never before[cite: 22].</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1498038432885-c6f3f1b912ee?auto=format&fit=crop&w=1350&q=80" class="d-block w-100 hero-slider" alt="Videos">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded">
                    <h2>HD Video Collections</h2>
                    [cite_start]<p>New arrivals updated daily[cite: 25].</p>
                </div>
            </div>
        </div>
    </header>

    <section id="music" class="container section-padding">
        <h2 class="text-center mb-5"><span class="text-primary">Latest</span> Music</h2>
        <div class="row row-cols-1 row-cols-md-5 g-4">
            <!-- <?php while($song = mysqli_fetch_assoc($latestMusic)): ?> -->
            <div class="col">
                <div class="card h-100">
                    [cite_start]<span class="badge badge-new">NEW <i class="fas fa-fire"></i></span> [cite: 25]
                    <img src="<?php echo $song['image_path']; ?>" class="card-img-top" alt="Album Art">
                    <div class="card-body">
                        <h5 class="card-title text-truncate"><?php echo $song['title']; ?></h5>
                        <p class="card-text small text-muted"><?php echo $song['artist']; ?> | <?php echo $song['year']; ?></p>
                        <audio controls class="w-100 mt-2">
                            <source src="<?php echo $song['file_path']; ?>" type="audio/mpeg">
                        </audio>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section id="videos" class="bg-secondary bg-opacity-10 section-padding">
        <div class="container">
            <h2 class="text-center mb-5"><span class="text-primary">Featured</span> Videos</h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php while($vid = mysqli_fetch_assoc($latestVideos)): ?>
                <div class="col">
                    <div class="card h-100">
                        <div class="video-container">
                            <video controls poster="<?php echo $vid['image_path']; ?>">
                                <source src="<?php echo $vid['file_path']; ?>" type="video/mp4">
                            </video>
                        </div>
                        <div class="card-body">
                            <h6><?php echo $vid['title']; ?></h6>
                            <p class="small"><?php echo $vid['language']; ?> | <?php echo $vid['genre']; ?></p>
                            <div class="text-warning">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>
                            [cite_start]</div> [cite: 23]
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <section id="about" class="container section-padding text-center">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2>About SOUND Group</h2>
                <hr class="bg-primary w-25 mx-auto">
                [cite_start]<p class="lead mt-4">Music and Videos are the most common source of entertainment today[cite: 21]. [cite_start]We provide a revolutionizing eProject learning environment where students can implement concepts in a phased, laddered approach[cite: 7, 11].</p>
                [cite_start]<p>Our platform hosts English and Regional content categorized by Artist, Album, and Year[cite: 22, 23].</p>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container text-center">
            <div class="mb-4">
                <i class="fab fa-facebook fa-2x mx-3"></i>
                <i class="fab fa-twitter fa-2x mx-3"></i>
                <i class="fab fa-instagram fa-2x mx-3"></i>
            </div>
            <p>&copy; 2026 SOUND Entertainment Group. All Rights Reserved.</p>
            [cite_start]<p class="small text-muted">A project built with PHP, MySQL, and Apache[cite: 31].</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>