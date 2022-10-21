// Если на сайте полно видео которые нужно запускать автоматически, то лучше воспроизводить его когда он попадает во вьюпорт.
// Для этого воспользуемся Intersection Observer

function playPauseVideo() {
    let videos = document.querySelectorAll("video");
    videos.forEach((video) => {
        // ВАЖНО. Мы не можем контролировать автоплей видео если оно не замьючено
        video.muted = true;
        // ВАЖНО. Play - это промис, поэтому нужно проверять его на существование
        let playPromise = video.play();
        if (playPromise !== undefined) {
            playPromise.then((_) => {
                let observer = new IntersectionObserver(
                    (entries) => {
                        entries.forEach((entry) => {
                            if (
                                entry.intersectionRatio !== 1 &&
                                !video.paused
                            ) {
                                video.pause()
                            } else if (video.paused) {
                                video.play();
                            }
                        });
                    },
                    { threshold: 0.2 }
                );
                observer.observe(video);
            });
        }
    });
}

// И вызываем его в нужном нам месте
playPauseVideo();

