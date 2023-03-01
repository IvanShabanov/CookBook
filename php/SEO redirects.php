
<?
define('REDIRECT_DEBUG', 'N');
function SEOredirects(array
		$arParams = [
			'IGNORE_URLS' 			=> array('/bitrix'),
			'USE_HTTPS' 			=> 'Y',
			'USE_WWW' 				=> 'Y',
			'REMOVE_INDEX' 			=> 'Y',
			'REMOVE_DOUBLE_SLASH' 	=> 'Y',
			'REMOVE_GET_PARAMS' 	=> array()

			]
	) {


	function getCurrentUrl($server, $useForwardedHost = false, $return = 'full')
	{
		$ssl      = (!empty($server['HTTPS']) && $server['HTTPS'] == 'on');
		$sp       = strtolower($server['SERVER_PROTOCOL']);
		$protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
		$port     = $server['SERVER_PORT'];
		$port     = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
		$host     = ($useForwardedHost && isset($server['HTTP_X_FORWARDED_HOST'])) ? $server['HTTP_X_FORWARDED_HOST'] : (isset($server['HTTP_HOST']) ? $server['HTTP_HOST'] : null);
		$host     = isset($host) ? $host : $server['SERVER_NAME'] . $port;
		return $protocol . '://' . $host . $server['REQUEST_URI'];
	}


	$cururl = getCurrentUrl($_SERVER);
	$enableRedirect = true;
	if (is_array($arParams['IGNORE_URLS'])) {
		foreach ($arParams['IGNORE_URLS'] as $ignoreUrl) {
			if (mb_strpos($cururl, $ignoreUrl) !== false) {
				$enableRedirect = false;
				break;
			}
		}
	}

	if ($enableRedirect) {
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

		if (is_array($arParams['REMOVE_GET_PARAMS'])) {
			list($path, $query) = explode('?', $newurl);
			if (mb_strlen($query) > 0) {
				parse_str($query, $q);
				foreach ($arParams['REMOVE_GET_PARAMS'] as $getParam) {
					unset($q[$getParam]);
				}
				$query = http_build_query($q);
				$newurl = trim("{$path}?$query", '?');
			}
		}

		if (file_exists(__DIR__.'/redirects.csv')) {
			$arRedirects = file(__DIR__.'/redirects.csv');
			if (is_array($arRedirects)) {
				$preparedRedirects = array();
				foreach($arRedirects as $redirect) {
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
					$comparedUrl = trim($arUrl['path']. '?'. $arUrl['query'], '?');
					if ((defined('REDIRECT_DEBUG')) && (REDIRECT_DEBUG=='Y')) {
						echo '<p>$comparedUrl = "'.$comparedUrl.'"</p>';
					};
					foreach ($preparedRedirectsKeys as $key) {
						$arUrl = parse_url($key);
						$compareStr = trim($arUrl['path']. '?'. $arUrl['query'], '?');
						if (preg_match('|^'.$compareStr.'|', $comparedUrl)) {
							if ((defined('REDIRECT_DEBUG')) && (REDIRECT_DEBUG=='Y')) {
								echo '<p>'.$key.' == '.$comparedUrl.'</p>';
							}
							$newurl = $preparedRedirects[$key];
							break;
						}
					}
				}


			}
		}

		if ($cururl != $newurl) {
			if ((defined('REDIRECT_DEBUG')) && (REDIRECT_DEBUG=='Y')) {
				echo '<p>REDIRECT <br>'.$cururl.'<br>to<br><a href="'. $newurl .'">'. $newurl.'</a></p>';
			} else {
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: ". $newurl);
			}
			exit();
		}
	}
}