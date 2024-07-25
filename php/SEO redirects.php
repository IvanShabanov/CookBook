<?
define('REDIRECT_DEBUG', 'N');
/* Пример использования:
SEOredirects(
	[
		'IGNORE_POST'			=> 'Y',		// Игнорировать POST запросы
		'IGNORE_AJAX'			=> 'Y',		// Игнорировать AJAX запросы
		'IGNORE_REQUEST'		=> [		// Игнорировать если в запросе есть параметры
			'ajax_action',
			'is_ajax',
			'ajax'
		],
		'IGNORE_REQUEST_BY_VALUE'	=> [	// Игнорировать если в запросе параметры имеют значения
			'ajax_action' => 'Y',
			'is_ajax' => 'Y',
			'ajax' => 'Y'
		],
		'IGNORE_URLS' 			=> [		// Игнорировать если в урле присуптвуют строчки
			'/bitrix'
		],
		'USE_HTTPS' 			=> 'Y',		// Редирект на https
		'USE_WWW' 				=> 'Y',		// Редирект на wwww или убирать www
		'REMOVE_INDEX' 			=> 'Y',		// Редирект с index.php / html
		'REMOVE_DOUBLE_SLASH' 	=> 'Y',		// Редирект с адресов с двойными слешами
		'ADD_TRAILING_SLASH'	=> 'Y',		// Добавлять завершающий слеш
		'REMOVE_GET_PARAMS' 	=> [],		// Массив удаляемых GET параметров
		'TEST_URLS'				=> [],
		'TEST_PARAMS'			=> [
			'USE_HTTPS' 			=> 'N',
			'USE_WWW' 				=> 'N',
		]
	]
);
*/
function SEOredirects(array	$arParams = [])
{

	/* Получаем текущий url */
	function getCurrentUrl($server, $useForwardedHost = false)
	{
		$ssl      = (!empty($server['HTTPS']) && $server['HTTPS'] == 'on');
		$sp       = strtolower($server['SERVER_PROTOCOL']);
		$protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
		$host     = ($useForwardedHost && isset($server['HTTP_X_FORWARDED_HOST'])) ? $server['HTTP_X_FORWARDED_HOST']
			: (isset($server['HTTP_HOST']) ? $server['HTTP_HOST'] : null);
		[$host, $port] = explode(':', $host);
		$host = isset($host) ? $host : $server['SERVER_NAME'];

		return $protocol . '://' . $host . $server['REQUEST_URI'];
	}

	/* А вдруг мы не в битрикс ))) */
	if (!function_exists('LocalRedirect')) {
		function LocalRedirect($url, $secure, $http_code)
		{
			header("HTTP/1.1 " . $http_code);
			header("Location: " . $url);
		}
	}

	/* Параметры по умолчанию оптимальны для Битрикс */
	$defaultParams = [
		'IGNORE_POST'			=> 'Y',
		'IGNORE_AJAX'			=> 'Y',
		'IGNORE_REQUEST'		=> [
			'ajax_action',
			'is_ajax',
			'ajax'
		],
		'IGNORE_REQUEST_BY_VALUE'	=> [
			'ajax_action' => 'Y',
			'is_ajax' => 'Y',
			'ajax' => 'Y'
		],
		'IGNORE_URLS' 			=> [
			'/bitrix'
		],
		'USE_HTTPS' 			=> 'Y',
		'USE_WWW' 				=> 'Y',
		'REMOVE_INDEX' 			=> 'Y',
		'REMOVE_DOUBLE_SLASH' 	=> 'Y',
		'ADD_TRAILING_SLASH'	=> 'Y',
		'REMOVE_GET_PARAMS' 	=> []
	];

	foreach ($defaultParams as $key => $param) {
		if (!isset($arParams[$key])) {
			$arParams[$key] = $param;
		}
	}

	if (($arParams['IGNORE_POST'] == 'Y') && (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')) {
		return;
	}

	if (($arParams['IGNORE_AJAX'] == 'Y') && (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
		return;
	}

	if (is_array($arParams['IGNORE_REQUEST'])) {
		foreach ($arParams['IGNORE_REQUEST'] as $query_key) {
			if (isset($_REQUEST[$query_key])) {
				return;
			}
		}
	}

	if (is_array($arParams['IGNORE_REQUEST_BY_VALUE'])) {
		foreach ($arParams['IGNORE_REQUEST_BY_VALUE'] as $query_key => $quesry_val) {
			if (isset($_REQUEST[$query_key]) && ($_REQUEST[$query_key] == $quesry_val)) {
				return;
			}
		}
	}


	$cururl = getCurrentUrl($_SERVER);

	/* Если сайт на тестовом домене */
	if (is_array($arParams['TEST_URLS'])) {
		foreach ($arParams['TEST_URLS'] as $ignoreUrl) {
			if (mb_strpos($cururl, $ignoreUrl) !== false) {
				$arParams = array_merge($arParams, $arParams['TEST_PARAMS']);
			}
		}
	}


	if (is_array($arParams['IGNORE_URLS'])) {
		foreach ($arParams['IGNORE_URLS'] as $ignoreUrl) {
			if (mb_strpos($cururl, $ignoreUrl) !== false) {
				return;
			}
		}
	}

	$newurl = $cururl;

	if ($arParams['USE_HTTPS'] == 'Y') {
		$newurl = str_replace('http://', 'https://', $newurl);
	}

	if ($arParams['USE_WWW'] == 'Y') {
		if (mb_strpos($newurl, '://www.') === false) {
			$newurl = str_replace('://', '://www.', $newurl);
		}
	} else {
		$newurl = str_replace('://wwww', '://', $newurl);
	}

	if ($arParams['REMOVE_INDEX'] == 'Y') {
		$newurl = str_replace('index.php', '', $newurl);
		$newurl = str_replace('index.html', '', $newurl);
	}

	if ($arParams['REMOVE_DOUBLE_SLASH'] == 'Y') {
		while (mb_strpos($newurl, '//') !== false) {
			$newurl = str_replace('//', '/', $newurl);
		}
		$newurl = str_replace(':/', '://', $newurl);
	}

	if ($arParams['ADD_TRAILING_SLASH'] == 'Y') {
		$arUrl = parse_url($newurl);
		if (substr($arUrl['path'], -1) != '/') {
			if (!file_exists($_SERVER['DOCUMENT_ROOT'].$arUrl['path'])) {
				$newurl = str_replace($arUrl['path'], $arUrl['path'].'/', $newurl);
			}
		}
	}

	if (is_array($arParams['REMOVE_GET_PARAMS'])) {
		list($path, $query) = explode('?', $newurl);
		if (mb_strlen($query) > 0) {
			parse_str($query, $q);
			foreach ($arParams['REMOVE_GET_PARAMS'] as $getParam) {
				unset($q[$getParam]);
			}
			$query = http_build_query($q);
			$newurl = trim($path . '?' . $query, '?');
		}
	}

	if (file_exists(__DIR__ . '/redirects.csv')) {
		$arRedirects = file(__DIR__ . '/redirects.csv');
		if (is_array($arRedirects)) {
			$preparedRedirects = array();
			foreach ($arRedirects as $redirect) {
				$arRedirect = array();
				$arRedirect = explode(';', $redirect);
				if (((!empty($arRedirect[0])) && (!empty($arRedirect[1]))) && (trim($arRedirect[0]) !== trim($arRedirect[1]))) {
					$preparedRedirects[$arRedirect[0]] = $arRedirect[1];
					$preparedRedirectsKeys[] = $arRedirect[0];
				}
			}

			if (!empty($preparedRedirects)) {
				array_unique($preparedRedirectsKeys);
				array_multisort(array_map('strlen', $preparedRedirectsKeys), $preparedRedirectsKeys);
				$preparedRedirectsKeys = array_reverse($preparedRedirectsKeys);
				$arUrl = parse_url($newurl);
				$comparedUrl = trim($arUrl['path'] . '?' . $arUrl['query'], '?');
				if ((defined('REDIRECT_DEBUG')) && (REDIRECT_DEBUG == 'Y')) {
					echo '<p>$comparedUrl = "' . $comparedUrl . '"</p>';
				};
				foreach ($preparedRedirectsKeys as $key) {
					$arUrl = parse_url($key);
					$compareStr = trim($arUrl['path'] . '?' . $arUrl['query'], '?');
					if (preg_match('|^' . $compareStr . '|', $comparedUrl)) {
						if ((defined('REDIRECT_DEBUG')) && (REDIRECT_DEBUG == 'Y')) {
							echo '<p>' . $key . ' == ' . $comparedUrl . '</p>';
						}
						$newurl = $preparedRedirects[$key];
						break;
					}
				}
			}
		}
	}

	if ($cururl != $newurl) {
		if ((defined('REDIRECT_DEBUG')) && (REDIRECT_DEBUG == 'Y')) {
			echo '<p>REDIRECT <br>' . $cururl . '<br>to<br><a href="' . $newurl . '">' . $newurl . '</a></p>';
		} else {
			LocalRedirect($newurl, false, "301 Moved Permanently");
		}
		exit();
	}
}
