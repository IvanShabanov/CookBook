function Goal(GOALNAME) {
	if (typeof Ya === 'undefined') {
		return;
	}
	const counters = Ya._metrika.getCounters();
	if ((!counters) || (typeof counters === 'undefined') || (counters.length == 0)) {
		return;
	}
	counters.forEach((counter) => {
		ym(counter.id, 'reachGoal', GOALNAME);
	});
}