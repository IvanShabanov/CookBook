document.addEventListener('DOMContentLoaded', function () {
	const initSaveDada = () => {
		if (window.localStorage) {
			let elements = document.querySelectorAll("*[name]");
			elements.forEach((element) => {
				let name = element.getAttribute("name");
				if (element.value == "") {
					element.value = localStorage.getItem(name) || "";
				}
				if (!element.classList.contains("saveDataInited")) {
					element.addEventListener("input", function() {
						localStorage.setItem(name, element.value);
					});
					element.classList.add("saveDataInited");
				}
			})
		}
	}

	initSaveDada();

	/** Следим за изменениями на странице */
	const doc = document.querySelector('body');
	const MutationObserver = window.MutationObserver;
	const myObserver = new MutationObserver(initSaveDada);
	const obsConfig = {
		childList: true,
		subtree: true
	};
	myObserver.observe(doc, obsConfig);
})