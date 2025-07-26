document.addEventListener('DOMContentLoaded', () => {
    const tags = document.querySelectorAll('.tag');

    tags.forEach(tag => {
        tag.addEventListener('click', () => {
            //"pulse" animation on click
            tag.classList.add('pulse');
            setTimeout(() => tag.classList.remove('pulse'), 300);
        });
    });
});

let player;

window.onYouTubeIframeAPIReady = function() {
    console.log('YouTube API ready 123');
    player = new YT.Player('yt-player', {
        height: '240',
        width: '480',
        videoId: 'VIs_82VxUHE',
        playerVars: {
            autoplay: 0,
            controls: 1,
            modestbranding: 1,
            start: 288
        },
        events: {
            onReady: onPlayerReady,
        }
    });
};

function onPlayerReady() {
    console.log('Player ready 123');
    createControls();
}

function createControls() {
    const container = document.getElementById('yt-player-container');
    const playBtn = document.createElement('button');
    playBtn.textContent = 'Play';
    playBtn.onclick = () => player.playVideo();

    const pauseBtn = document.createElement('button');
    pauseBtn.textContent = 'Pause';
    pauseBtn.onclick = () => player.pauseVideo();

    container.appendChild(playBtn);
    container.appendChild(pauseBtn);
}
