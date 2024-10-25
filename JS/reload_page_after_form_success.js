/* Если форма отправлена успешно при любом клике перезагружаем страницу, для обновления каптчи */
document.addEventListener('click', function () {
	const success = document.querySelectorAll('.form_result.success')
	if (success.length > 0) {
		window.location.reload();
	};
}, true);
