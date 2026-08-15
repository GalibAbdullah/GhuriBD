// GhuriBD — hero drone-video slideshow
//
// Cycles through the background videos on the welcome page, crossfading
// between them and updating the location caption. Respects reduced motion.

document.addEventListener('DOMContentLoaded', () => {
    const videos = Array.from(document.querySelectorAll('.hero-video'));
    const label = document.getElementById('hero-location-label');

    if (videos.length < 2 || !label) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const INTERVAL = prefersReducedMotion ? 12000 : 8000;
    let current = 0;

    // Start playback on the first video.
    const play = (video) => {
        const p = video.play();
        if (p && p.catch) {
            p.catch(() => { /* autoplay blocked — fallback layer shows */ });
        }
    };

    play(videos[0]);

    const showNext = () => {
        const prev = videos[current];
        current = (current + 1) % videos.length;
        const next = videos[current];

        // Update caption.
        label.textContent = next.dataset.location || '';

        // Fade out current, fade in next.
        prev.classList.remove('hero-video-active');
        next.classList.add('hero-video-active');

        play(next);
    };

    setInterval(showNext, INTERVAL);
});