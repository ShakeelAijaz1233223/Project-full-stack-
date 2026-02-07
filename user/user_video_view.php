/* --- Media Wrapper (Square like Image Card) --- */
.media-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1; /* 🔥 Square */
    background: #000;
    border-radius: 18px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
}

.thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    z-index: 2;
    transition: opacity 0.4s ease;
}

/* Hide native video controls look */
video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Center Play Button (Music Style) */
.play-btn {
    position: absolute;
    z-index: 5;
    width: 65px;
    height: 65px;
    background: var(--accent-grad);
    border-radius: 50%;
    border: none;
    color: #fff;
    font-size: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 25px rgba(255, 51, 102, 0.7);
    transition: transform 0.3s ease;
}

.play-btn:hover {
    transform: scale(1.1);
}
