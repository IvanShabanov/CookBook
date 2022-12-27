/* Копируем все в консоль, нажимем Enter -  выводит все ссылки, на текущей странице */
var countlink = 0;
$('a').each(function () {
	console.log(countlink + ') ' + $(this).attr('href') + '  (target=' + $(this).attr('target') + ') ,(rel=' + $(this).attr('rel') + ') ' + $(this).text());
	countlink++;
});

/* тоже само на чистом JS */
let countlink = 0;
document.querySelectorAll('a').forEach(element => {
	console.log(countlink + ') ' + element.getAttribute('href') + '  (target=' + element.getAttribute('target') + ') ,(rel=' + element.getAttribute('rel') + ') ' + element.textContent);
	countlink++;
});




var countlink = 0;
$('a[rel*="nofollow"]').each(function () {
	console.log(countlink + ') ' + $(this).attr('href') + '  (target=' + $(this).attr('target') + ') ,(rel=' + $(this).attr('rel') + ')');
	countlink++;
});

var countlink = 0;
$('a[rel*="noindex"]').each(function () {
	console.log(countlink + ') ' + $(this).attr('href') + '  (target=' + $(this).attr('target') + ') ,(rel=' + $(this).attr('rel') + ')');
	countlink++;
});



/* Картинки без ALT */
var countlink = 0;
$('img:not([alt]), img[alt=""]').each(function () {
	console.log(countlink + ') ' + $(this).attr('src'));
	countlink++;
});