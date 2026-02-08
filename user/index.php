<?php
session_start();
include_once("../config/db.php");

function getMediaImage($fileName, $type)
{
    $musicPath = "../admin/uploads/music_covers/";
    $videoPath = "../admin/uploads/video_thumbnails/";

    if ($type == 'music') {
        $fullPath = $musicPath . $fileName;
        $default = "https://images.unsplash.com/photo-1614613535308-eb5fbd3d2c17?q=80&w=600&auto=format&fit=crop";
    } else {
        $fullPath = $videoPath . $fileName;
        $default = "https://images.unsplash.com/photo-1611162617474-5b21e879e113?q=80&w=600&auto=format&fit=crop";
    }
    return (!empty($fileName) && file_exists($fullPath)) ? $fullPath : $default;
}

$latestMusic = mysqli_query($conn, "SELECT * FROM music ORDER BY id DESC LIMIT 5");
$latestVideos = mysqli_query($conn, "SELECT * FROM videos ORDER BY id DESC LIMIT 5");

$user = null;
if (isset($_SESSION['email'])) {
    $email = mysqli_real_escape_string($conn, $_SESSION['email']);
    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if ($res && mysqli_num_rows($res) > 0) {
        $user = mysqli_fetch_assoc($res);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SOUND | Ultimate Entertainment Hub</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
:root{
--primary:#ff0055;
--secondary:#00d4ff;
--dark:#050505;
--card:#0f0f0f;
--glass:rgba(255,255,255,0.05);
--border:rgba(255,255,255,0.1);
}

*{margin:0;padding:0;box-sizing:border-box}
body{background:var(--dark);color:#fff;font-family:Arial,Helvetica,sans-serif}

a{text-decoration:none;color:inherit}

header{
position:fixed;top:0;width:100%;z-index:1000;
background:rgba(5,5,5,0.9);
backdrop-filter:blur(20px);
border-bottom:1px solid var(--border);
}

.container{max-width:1400px;margin:auto;padding:0 5%}

.nav{
display:flex;justify-content:space-between;align-items:center;
padding:18px 0;
}

.logo{font-size:22px;font-weight:800;letter-spacing:2px}
.logo span{color:var(--primary)}

.nav-links{
display:flex;gap:30px;
}

.nav-links a{
font-size:12px;font-weight:700;
text-transform:uppercase;
opacity:.8;
}
.nav-links a:hover{color:var(--primary)}

.menu-btn{
display:none;
font-size:22px;
cursor:pointer;
}

/* MOBILE MENU */
.mobile-nav{
position:fixed;
top:70px;
left:0;
width:100%;
background:#050505;
display:flex;
flex-direction:column;
align-items:center;
gap:20px;
padding:30px 0;
transform:translateY(-120%);
transition:.4s;
z-index:999;
border-bottom:1px solid var(--border);
}
.mobile-nav.active{transform:translateY(0)}
.mobile-nav a{
font-size:14px;
font-weight:700;
letter-spacing:2px;
text-transform:uppercase;
}

/* HERO */
.hero{
margin-top:80px;
min-height:100vh;
display:flex;
align-items:center;
justify-content:center;
background:url('https://images.unsplash.com/photo-1514525253440-b393452e23f9?q=80&w=1920') center/cover no-repeat;
position:relative;
text-align:center;
}
.hero::after{
content:"";
position:absolute;inset:0;
background:rgba(0,0,0,0.6);
}
.hero-content{position:relative;z-index:2;max-width:800px}

.hero h1{
font-size:clamp(40px,7vw,80px);
font-weight:900;
margin-bottom:20px;
}
.hero p{opacity:.8;margin-bottom:30px}

.btn{
padding:14px 40px;
border-radius:30px;
font-size:12px;
font-weight:800;
letter-spacing:2px;
cursor:pointer;
border:none;
}
.btn-primary{background:var(--primary)}
.btn-outline{
background:transparent;
border:1px solid #fff;
}

/* MEDIA */
.section{padding:100px 0}
.section h2{text-align:center;margin-bottom:10px}
.section p{text-align:center;opacity:.6;margin-bottom:50px}

.grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:30px;
}

.card{
background:var(--card);
border-radius:16px;
overflow:hidden;
transition:.4s;
}
.card:hover{transform:translateY(-10px)}
.card img{width:100%;height:220px;object-fit:cover}
.card .info{padding:15px}
.card h4{font-size:15px}
.card span{font-size:12px;opacity:.6}

/* FOOTER */
footer{
background:#020202;
padding:60px 0 20px;
border-top:1px solid var(--border);
text-align:center;
font-size:12px;
opacity:.6;
}

/* RESPONSIVE */
@media(max-width:992px){
.nav-links{display:none}
.menu-btn{display:block}
}
</style>
</head>

<body>

<header>
<div class="container nav">
<a href="index.php" class="logo">SOU<span>N</span>D</a>

<nav class="nav-links">
<a href="#home">Home</a>
<a href="about.php">About</a>
<a href="user_music_view.php">Music</a>
<a href="user_video_view.php">Videos</a>
<a href="contact.php">Contact</a>
</nav>

<div class="menu-btn" id="menuToggle">
<i class="fas fa-bars"></i>
</div>
</div>
</header>

<!-- MOBILE MENU -->
<div class="mobile-nav" id="mobileNav">
<a href="#home">Home</a>
<a href="about.php">About</a>
<a href="user_music_view.php">Music</a>
<a href="user_video_view.php">Videos</a>
<a href="contact.php">Contact</a>
<?php if($user): ?>
<a href="user_setting.php">Settings</a>
<a href="user_logout.php" style="color:#ff4d4d">Logout</a>
<?php else: ?>
<a href="login.php" style="color:var(--secondary)">Login</a>
<?php endif; ?>
</div>

<!-- HERO -->
<section class="hero" id="home">
<div class="hero-content">
<h1>VISUAL AUDIO<br><span style="color:#ff0055">REVOLUTION</span></h1>
<p>Stream • Review • Rate • Experience</p>
<button class="btn btn-primary" onclick="location.href='#music'">START LISTENING</button>
</div>
</section>

<!-- MUSIC -->
<section class="section" id="music">
<div class="container">
<h2>Latest Music</h2>
<p>Top 5 newly added tracks</p>

<div class="grid">
<?php while($m=mysqli_fetch_assoc($latestMusic)): ?>
<div class="card">
<img src="<?= getMediaImage($m['cover_image'],'music') ?>">
<div class="info">
<h4><?= htmlspecialchars($m['title']) ?></h4>
<span><?= htmlspecialchars($m['artist']) ?></span>
</div>
</div>
<?php endwhile; ?>
</div>
</div>
</section>

<!-- VIDEOS -->
<section class="section" style="background:#080808">
<div class="container">
<h2>Trending Videos</h2>
<p>Latest HD music videos</p>

<div class="grid">
<?php while($v=mysqli_fetch_assoc($latestVideos)): ?>
<div class="card">
<img src="<?= getMediaImage($v['thumbnail'],'video') ?>">
<div class="info">
<h4><?= htmlspecialchars($v['title']) ?></h4>
<span><?= htmlspecialchars($v['artist']) ?></span>
</div>
</div>
<?php endwhile; ?>
</div>
</div>
</section>

<footer>
© 2026 SOUND Project • All Rights Reserved
</footer>

<script>
const btn=document.getElementById("menuToggle");
const nav=document.getElementById("mobileNav");

btn.onclick=()=>{
nav.classList.toggle("active");
btn.innerHTML=nav.classList.contains("active")
?'<i class="fas fa-times"></i>'
:'<i class="fas fa-bars"></i>';
}

document.querySelectorAll(".mobile-nav a").forEach(a=>{
a.onclick=()=>{
nav.classList.remove("active");
btn.innerHTML='<i class="fas fa-bars"></i>';
}
});
</script>

</body>
</html>
