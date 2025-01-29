function Goal(goalName) {
	if (typeof Ya === 'undefined') {
		return;
	}
	const counters = Ya._metrika.getCounters();
	if ((!counters) || (typeof counters === 'undefined') || (counters.length == 0)) {
		return;
	}
	counters.forEach((counter) => {
		ym(counter.id, 'reachGoal', goalName);
	});
}

function addGoal(querySelector, eventName, goalName) {
	let elements = document.querySelectorAll(querySelector);
	if (typeof elements == "undefined" || !elements || elements.length == 0) {
		return;
	}
	elements.forEach((el) => {
		el.addEventListener(eventName, () => {
			Goal(goalName);
		});
	});
}