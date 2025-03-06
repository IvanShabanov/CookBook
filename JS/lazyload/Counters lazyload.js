document.addEventListener('DOMContentLoaded', () => {
	let CountersInited = false;
	let CountersInit = () => {
		if (CountersInited) {
			return;
		}
		CountersInited = true;
		/*
			Сюда вставить код счетчиков
		*/
	}

	const events = ["click", "scroll", "keydown", "keyup", "touchstart", "touchmove", "touchend", "mousemove", "mouseenter", "mouseleave"];
	events.forEach(event => {
		document.addEventListener(event, function (e) {
			CountersInit();
		}, {
			once: true
		});
	});
});