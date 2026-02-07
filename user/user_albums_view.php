:root {
    --bg: #0d0d0d;                    /* Darker background */
    --card: #1b1b1b;                  /* Slightly lighter cards */
    --accent: #ff3366;                /* Strong accent color */
    --accent-grad: linear-gradient(135deg, #ff3366, #ff9933);
    --text-main: #f5f5f5;             /* Softer white for text */
    --text-muted: #999;               /* Muted text */
    --shadow: rgba(0,0,0,0.6);        /* Card shadow */
}

/* --- Body & Wrapper --- */
body {
    background: var(--bg);
    color: var(--text-main);
    font-family: 'Inter', sans-serif;
    margin: 0;
}
.studio-wrapper {
    width: 95%;
    margin: 0 auto;
    padding: 25px 0;
}

/* --- Header --- */
.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #222;
    padding-bottom: 15px;
    margin-bottom: 30px;
}
.search-box {
    background: #1f1f1f;
    border: 1px solid #333;
    color: var(--text-main);
    border-radius: 10px;
    padding: 8px 16px;
    width: 280px;
    transition: all 0.3s;
}
.search-box:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 8px var(--accent);
}
.btn-back {
    background: #222;
    border: none;
    color: var(--text-main);
    padding: 7px 15px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: 0.3s;
}
.btn-back:hover {
    background: var(--accent);
    color: #fff;
}

/* --- Video Grid --- */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 25px;
}
.video-card {
    background: var(--card);
    border-radius: 20px;
    overflow: hidden;
    padding: 12px;
    position: relative;
    border: 1px solid #2a2a2a;
    box-shadow: 0 4px 15px var(--shadow);
    transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s;
}
.video-card:hover {
    transform: translateY(-6px);
    border-color: var(--accent);
    box-shadow: 0 8px 20px var(--shadow);
}

/* --- Media Wrapper --- */
.media-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: 16/9;
    background: #000;
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 12px;
}
.media-wrapper img,
.media-wrapper video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 15px;
    transition: transform 0.5s ease;
}
.video-card:hover .media-wrapper img,
.video-card:hover .media-wrapper video {
    transform: scale(1.07);
}

/* --- Play Button --- */
.play-btn {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 45px;
    height: 45px;
    background: var(--accent-grad);
    border-radius: 50%;
    border: none;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    cursor: pointer;
    z-index: 5;
    transition: 0.3s;
}
.video-card:hover .play-btn {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1.1);
}

/* --- Custom Controls --- */
.custom-controls {
    position: absolute;
    bottom: 6px;
    left: 6px;
    right: 6px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 5px 12px;
    background: rgba(30, 30, 30, 0.75);
    backdrop-filter: blur(6px);
    opacity: 0;
    border-radius: 0 0 12px 12px;
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.media-wrapper:hover .custom-controls {
    opacity: 1;
}
.custom-controls button {
    background: none;
    border: none;
    color: #fff;
    cursor: pointer;
    font-size: 1.2rem;
    transition: 0.25s;
}
.custom-controls button:hover {
    color: var(--accent);
    transform: scale(1.25);
}
.custom-controls input[type="range"] {
    flex: 1;
    margin: 0 6px;
    accent-color: var(--accent);
    background: rgba(255, 255, 255, 0.12);
    border-radius: 4px;
}
.custom-controls input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--accent);
    border: 2px solid #1b1b1b;
    transition: transform 0.2s;
}
.custom-controls input[type="range"]::-webkit-slider-thumb:hover {
    transform: scale(1.3);
}

/* --- Titles & Stars --- */
.title {
    font-size: 0.88rem;
    font-weight: 600;
    margin: 6px 0 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.stars-display {
    font-size: 0.78rem;
    color: #ffd700;
    margin-bottom: 8px;
}

/* --- Review Button --- */
.rev-btn {
    background: #2b2b2b;
    color: #fff;
    border: none;
    font-size: 0.75rem;
    width: 100%;
    padding: 7px;
    border-radius: 8px;
    transition: 0.3s;
}
.rev-btn:hover {
    background: var(--accent);
    color: #fff;
}

/* --- Review Overlay --- */
#reviewOverlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.95);
    backdrop-filter: blur(6px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.review-box {
    background: var(--card);
    width: 90%;
    max-width: 420px;
    padding: 35px;
    border-radius: 22px;
    border: 1px solid #2a2a2a;
}

/* --- Star Rating --- */
.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
    gap: 10px;
    margin-bottom: 18px;
}
.star-rating label {
    font-size: 2.2rem;
    color: #333;
    cursor: pointer;
    transition: 0.3s;
}
.star-rating label:hover,
.star-rating label:hover~label,
.star-rating input:checked~label {
    color: #ffd700;
}

/* --- Footer --- */
footer {
    text-align: center;
    padding: 50px 0;
    font-size: 0.75rem;
    color: #555;
}
