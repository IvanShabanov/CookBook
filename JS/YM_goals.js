/**
 * Goal - отправка цели всем счетчикам YM на странице
 * @param {*} goalName - string
 * @returns
 */
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

/**
 * addGoalEvent - добавление события на отправку цели в YM
 * @param {*} querySelector
 * @param {*} eventName
 * @param {*} goalName - string or function
 * @returns
 */
function addGoalEvent(querySelector, eventName, goalName) {
	let elements = document.querySelectorAll(querySelector);
	if (typeof elements == "undefined" || !elements || elements.length == 0) {
		return;
	}
	elements.forEach((el) => {
		el.addEventListener(eventName, (event) => {
			if (typeof goalName == 'function') {
				goalName(el, event);
			} else {
				Goal(goalName);
			}
		});
	});
}

/* Примеры использования */

document.addEventListener('DOMContentLoaded', () => {

	addGoalEvent('.myform', 'submit', 'form_submit');

	addGoalEvent('a[href^="mailto:"]', 'click', 'click_email');

	addGoalEvent('a[href^="tel:"]', 'click', (el, event) => {
		let phone = el.getAttribute('href');
		if (typeof phone !== 'undefined') {
			phone = phone.replace(/\D+/g, "");
			Goal('click_phone_' + phone);
		}
	});

	addGoalEvent('a[href^="mailto:"]', 'copy', 'copy_email');

	addGoalEvent('a[href^="tel:"]', 'copy', 'copy_tel');

	addGoalEvent('a[href^="https://wa.me/"]', 'click', 'WhatsApp');
	addGoalEvent('a[href^="https://t.me"]', 'click', 'Telegram');
	addGoalEvent('a[href^="https://vk.com"]', 'click', 'VK');
	addGoalEvent('a[href^="https://www.youtube.com"]', 'click', 'Youtube');

});
