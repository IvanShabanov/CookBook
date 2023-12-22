<?
\CAdminNotify::Add([
	'MESSAGE' => '', /* Текст сообщения луше использовать языковые функции \Bitrix\Main\Localization\Loc::getMessage( */
	'TAG' => 'mytag', /* Если добавить два уведомления с одинаковым тэгом (пустой тэг не считается) останется только последнее уведомление. */
	'MODULE_ID' => 'module_id',
	'NOTIFY_TYPE' => 'E'
]);


/*
пример
проверяет наличие файл __error.log в корне сайта
и выводит сообщение об ошибках нв админке
*/
$erFileExists = \Bitrix\Main\IO\File::isFileExists($_SERVER['DOCUMENT_ROOT'] . '/__error.log');
if ($erFileExists) {
	\CAdminNotify::Add([
		'MESSAGE' => 'На сайте обнаружены ошибки <a href="/__error.log" target="_blank">__error.log</a>',
		'TAG' => 'exists__error.log', /* Если добавить два уведомления с одинаковым тэгом (пустой тэг не считается) останется только последнее уведомление. */
		'NOTIFY_TYPE' => 'E'
	]);
}

