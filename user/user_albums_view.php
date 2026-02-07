<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Albums Studio | Pro Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
:root {
    --bg: #080808;
    --card: #111;
    --accent: #ff0055;
    --accent-grad: linear-gradient(45deg, #ff0055, #ff5e00);
    --text-main: #fff;
    --text-dim: #a0a0a0;
}
body {
    background: var(--bg);
    color: var(--text-main);
    font-family: 'Inter', sans-serif;
    margin: 0;
    overflow-x: hidden;
}
.studio-wrapper {width: 95%; margin: 0 auto; padding: 20px 0;}
.header-section {
    display: flex; justify-content: space-between; align-items: center;
    border-bottom: 1px solid #222; padding-bottom: 15px; margin-bottom: 25px;
}
.search-box {
    background: #151515; border: 1px solid #333; color: #fff;
    border-radius: 6px; padding: 6px 15px; width: 250px;
}
.btn-back {background:#1a1a1a; border:1px solid #222; color:#fff; padding:6px 12px; border-radius:8px; text-decoration:none; display:flex; align-items:center; gap:5px;}
.grid {display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:20px;}
.video-card {
    background: var(--card); border-radius:15px; padding:12px;
    border:1px solid #1a1a1a; transition:0.3s; position:relative; overflow:hidden;
}
.video-card:hover {border-color:var(--accent); transform:translateY(-5px);}
.media-wrapper {position:relative; width:100%; aspect-ratio:16/9; background:#000; border-radius:10px; overflow:hidden; margin-bottom:10px;}
.media-wrapper video, .media-wrapper img, .media-wrapper audio {width:100%; height:100%; object-fit:cover; transition: transform 0.5s ease;}
.video-card:hover video, .video-card:hover img {transform:scale(1.05);}
.play-btn {
    position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
    width:40px; height:40px; background: var(--accent-grad); border-radius:50%;
    border:none; color:#fff; display:flex; align-items:center; justify-content:center;
    opacity:0; cursor:pointer; z-index:5; transition:0.3s;
}
.video-card:hover .play-btn {opacity:1;}
.custom-controls {
    position:absolute; bottom:5px; left:0; right:0;
    display:flex; align-items:center; justify-content:space-between;
    padding:0 10px; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px);
    opacity:0; transition:0.3s; border-radius:0 0 8px 8px;
}
.media-wrapper:hover .custom-controls {opacity:1;}
.custom-controls button {background:none; border:none; color:#fff; cursor:pointer; font-size:1rem;}
.custom-controls input[type="range"] {flex:1; margin:0 5px; accent-color: var(--accent);}
.title {font-size:0.85rem; font-weight:600; margin:5px 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;}
.artist {font-size:0.75rem; color: var(--text-dim); margin-bottom:8px;}
.stars-display {color:#ffca08; font-size:0.75rem; margin-bottom:8px;}
.rev-btn {background:#222; color:#fff; border:none; font-size:0.7rem; width:100%; padding:6px; border-radius:6px; transition:0.3s;}
.rev-btn:hover {background: var(--accent);}
#reviewOverlay {
    display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.9); backdrop-filter:blur(5px); z-index:9999; display:flex; align-items:center; justify-content:center;
}
.review-box {background:#151515; width:90%; max-width:400px; padding:30px; border-radius:20px; border:1px solid #333;}
.star-rating {display:flex; flex-direction:row-reverse; justify-content:center; gap:8px; margin-bottom:15px;}
.star-rating input {display:none;}
.star-rating label {font-size:2.5rem; color:#222; cursor:pointer; transition:0.2s;}
.star-rating label:hover, .star-rating label:hover~label, .star-rating input:checked~label {color:#ffca08;}
footer {text-align:center; padding:40px; font-size:0.7rem; color:#444;}
</style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h4 class="m-0 fw-bold">ALBUMS<span style="color: var(--accent);">STUDIO</span></h4>
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search albums...">
            <a href="index.php" class="btn-back"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>

    <div class="grid" id="albumGrid">
        <!-- Example Card -->
        <div class="video-card" data-title="Album Example" data-artist="Artist Name">
            <div class="media-wrapper">
                <video id="vid-1" preload="metadata" poster="cover.jpg">
                    <source src="video.mp4" type="video/mp4">
                </video>
                <button class="play-btn" onclick="toggleVideo(1, this)"><i class="bi bi-play-fill"></i></button>
                <div class="custom-controls">
                    <input type="range" class="progress" min="0" max="100" value="0">
                    <button class="mute-btn"><i class="bi bi-volume-up"></i></button>
                    <button class="fullscreen-btn"><i class="bi bi-arrows-fullscreen"></i></button>
                </div>
            </div>
            <div class="title">Album Example</div>
            <div class="artist">Artist Name</div>
            <div class="stars-display">★★★★☆</div>
            <button class="rev-btn" onclick="openReview(1,'Album Example')">Rate Album</button>
        </div>
        <!-- Repeat dynamic cards here -->
    </div>
</div>

<div id="reviewOverlay">
    <div class="review-box">
        <h5 class="text-center mb-2" id="revTitle">Album Example</h5>
        <form>
            <input type="hidden" id="revVideoId" name="album_id">
            <div class="star-rating">
                <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
            </div>
            <textarea class="form-control bg-dark text-white mb-3" placeholder="Write feedback..." required></textarea>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary w-100" onclick="closeReview()">Cancel</button>
                <button type="submit" class="btn btn-danger w-100" style="background: var(--accent);">Post</button>
            </div>
        </form>
    </div>
</div>

<footer>&copy; 2026 ALBUMS STUDIO &bull; SOUND SYSTEM</footer>

<script>
// Search
document.getElementById("search").addEventListener("input", function() {
    let val = this.value.toLowerCase();
    document.querySelectorAll(".video-card").forEach(card => {
        card.style.display = (card.dataset.title.toLowerCase().includes(val) || card.dataset.artist.toLowerCase().includes(val)) ? "block" : "none";
    });
});

// Toggle Video Play/Pause
function toggleVideo(id, btn) {
    const video = document.getElementById('vid-' + id);
    const icon = btn.querySelector('i');
    document.querySelectorAll('video').forEach(v => { if(v!==video)v.pause(); });
    if(video.paused){ video.play(); icon.className='bi bi-pause-fill'; }
    else { video.pause(); icon.className='bi bi-play-fill'; }
}

// Custom Controls
document.querySelectorAll('.media-wrapper').forEach(wrapper=>{
    const video = wrapper.querySelector('video');
    if(!video) return;
    const progress = wrapper.querySelector('.progress');
    const muteBtn = wrapper.querySelector('.mute-btn');
    const fullscreenBtn = wrapper.querySelector('.fullscreen-btn');
    if(progress) video.addEventListener('timeupdate',()=>{ progress.value=(video.currentTime/video.duration)*100; });
    if(progress) progress.addEventListener('input',()=>{ video.currentTime=(progress.value/100)*video.duration; });
    if(muteBtn) muteBtn.addEventListener('click', e=>{ e.stopPropagation(); video.muted=!video.muted; muteBtn.innerHTML=video.muted?'<i class="bi bi-volume-mute"></i>':'<i class="bi bi-volume-up"></i>'; });
    if(fullscreenBtn) fullscreenBtn.addEventListener('click', e=>{ e.stopPropagation(); if(video.requestFullscreen) video.requestFullscreen(); });
});

// Review Modal
function openReview(id,title){document.getElementById('revVideoId').value=id; document.getElementById('revTitle').innerText=title; document.getElementById('reviewOverlay').style.display='flex';}
function closeReview(){document.getElementById('reviewOverlay').style.display='none';}
</script>

</body>
</html>
