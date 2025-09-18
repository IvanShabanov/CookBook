/**************************** */
/* Класс для хранения функций */
/**************************** */

class CGoals {
	/**
	 * addGoalEvent - добавление события на отправку цели в YM
	 * @param {*} querySelector
	 * @param {*} eventName
	 * @param {*} goalName - any string or function
	 * @returns bool
	 */
	static addGoalEvent(querySelector, eventName, goalName) {
		const _THIS = CGoals;
		const className = '___GoalsInited_' + eventName;
		const elements = document.querySelectorAll(querySelector);
		if (typeof elements == "undefined" || !elements || elements.length == 0) {
			return false;
		}
		elements.forEach((el) => {
			if (el.classList.contains(className)) {
				return true;
			}
			el.classList.add(className);
			el.addEventListener(eventName, (event) => {
				if (typeof goalName == 'function') {
					goalName(el, event);
				} else {
					_THIS.Goal(goalName);
				}
			});
		});
		return true;
	}

	/**
	 * Goal - отправка цели всем счетчикам YM на странице
	 * @param {*} goalName - string
	 * @param {*} callback - function
	 * @param {*} trycount - integer
	 * @returns bool
	 */
	static Goal(goalName, callback = null, trycount = 3) {
		const _THIS = CGoals;
		trycount--;
		if (trycount < 0) {
			return false;
		}
		if (typeof Ya === 'undefined') {
			setTimeout(() => {
				_THIS.Goal(goalName, callback, trycount);
			}, 500);
			return false;
		}
		const counters = Ya._metrika.getCounters();
		if ((!counters) || (typeof counters === 'undefined') || (counters.length == 0)) {
			setTimeout(() => {
				_THIS.Goal(goalName, callback, trycount);
			}, 500);
			return false;
		}
		counters.forEach((counter) => {
			ym(counter.id, 'params', {
				url: window.location.href,
				referrer: document.referrer,
			});
			ym(counter.id, 'reachGoal', goalName);
		});
		if (typeof callback == 'function') {
			callback();
		}
		return true;
	}
}


/*************************/
/* Пример использования */
/*************************/

document.addEventListener('DOMContentLoaded', function () {

	/**
	 * GoalsOnStart - срабатываение целей на старте страницы
	 */
	function GoalsOnStart() {
		const _THIS = CGoals;

		/** Успешно отправлена форма регистрации */
		if (window.location.href.indexOf('register=yes') > 0) {
			if (document.querySelectorAll('.alert.alert-success').length > 0) {
				_THIS.Goal('registration');
			}
		} else if (window.location.href.indexOf('confirm_registration=yes') > 0) {
			if (document.querySelectorAll('.alert.alert-success').length > 0) {
				_THIS.Goal('registration');
			}
		}

		/** Вход */
		if (window.location.href.indexOf('?login=yes') > 0) {
			if (document.querySelectorAll('form[name="form_auth"]').length == 0) {
				_THIS.Goal('enter');
			}
		}

		/** Оформить заказ */
		if (window.location.href.indexOf('/order/make/?ORDER_ID=') > 0
			&& document.referrer.indexOf('/order/make/') > 0
			) {
			let params = new URLSearchParams(document.location.search);
			let value = params.get('ORDER_ID');
			if ((!value) || (typeof value === 'undefined') || (value.length == 0)) {
			} else {
				_THIS.Goal('order');
			}
		}
	}


	/**
	 * GoalsInit - инициализация целей на события элементов DOM
	 */
	function GoalsInit() {
		const _THIS = CGoals;

		/** Копирование номера телефона */
		_THIS.addGoalEvent('a[href^="tel:"]', 'copy', 'copy_phone');

		/** Копирование номера емайла */
		_THIS.addGoalEvent('a[href^="mailto:"]', 'copy', 'copy_email');

		/** Добавление в избранное */
		_THIS.addGoalEvent('a.favorite', 'click', 'favourites');

		/** Добавление в корзину */
		_THIS.addGoalEvent('a[id$="_buy_link"], a[id$="_add_basket_link"]', 'click', 'basket');

		/** Купить в один клик */
		_THIS.addGoalEvent('form[action="/buy1click/"]', 'submit', 'buy');

		/** Оптравка формы Обратная связь */
		_THIS.addGoalEvent('.b24-form form button.submit', 'click', 'feedback');

		/** Задать вопрос */
		_THIS.addGoalEvent('form[action^="/forms/ask/"]', 'submit', 'question');
	}


	GoalsOnStart();
	GoalsInit();

	/** Следим за изменениями на странице и проставляем цели на новые элементы */
	const doc = document.querySelector('body');
	const MutationObserver = window.MutationObserver;
	const myObserver = new MutationObserver(GoalsInit);
	const obsConfig = {
		childList: true,
		subtree: true
	};
	myObserver.observe(doc, obsConfig);
})