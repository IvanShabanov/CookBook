<?php
@session_start();
if (!isset($_SESSION['token'])) {
	$_SESSION['token'] = uniqid();
}
$token = $_SESSION['token'];

if ($_GET['debug'] == 'debug') {
	$_SESSION['debug'] = '1';
}

function logit($text)
{
	if ($_SESSION['debug'] != '1') {
		return;
	}
	if (is_array($text) || is_object($text)) {
		$text = print_r($text, true);
	}
	$bagtrace = debug_backtrace();
	file_put_contents(__DIR__ . '/links_checker.log', date('Y.m.d H:i:s') . "\t" . $bagtrace[0]['line'] . "\t" . $text, FILE_APPEND);
}

function get_contents($url, $params = array())
{
	$url    = urldecode($url);
	$result = array();
	$ch     = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	if ((isset($params['USER_AGENT'])) && ($params['USER_AGENT'] != '')) {
		curl_setopt($ch, CURLOPT_USERAGENT, $params['USER_AGENT']);
	} else {
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36');
	}
	if ((isset($params['POST'])) && ($params['POST'] != '')) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params['POST']);
	}
	if ((isset($params['REFER'])) && ($params['REFER'] != '')) {
		curl_setopt($ch, CURLOPT_REFERER, $params['REFER']);
	} else {
		curl_setopt($ch, CURLOPT_REFERER, 'https://ya.ru');
	}
	if ((isset($params['COOKIEFILE'])) && ($params['COOKIEFILE'] != '')) {
		curl_setopt($ch, CURLOPT_COOKIEJAR, $params['COOKIEFILE']);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $params['COOKIEFILE']);
	} else {
		$params['COOKIEFILE'] = __DIR__ . '/links_checker.cookie.txt';
		curl_setopt($ch, CURLOPT_COOKIEJAR, $params['COOKIEFILE']);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $params['COOKIEFILE']);
	}
	if ((isset($params['IGNORE_SSL_ERRORS'])) && ($params['IGNORE_SSL_ERRORS'] != '')) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	}
	$response = curl_exec($ch);

	if (!$response) {
		$result['error'] = curl_error($ch);
	}
	if (empty($result['error'])) {
		$info = curl_getinfo($ch);
		if ($response) {
			$result['content'] = $response;
		}
		$result['info'] = $info;
		if (is_array($result['info'])) {
			foreach ($result['info'] as $key => $val) {
				$result['info'][mb_strtolower($key)] = $val;
			}
		}
	}
	curl_close($ch);
	logit($result);
	return $result;
}

function http_header($code)
{
	$codes = [
		100 => "Continue",
		101 => "Switching Protocols",
		200 => "OK",
		201 => "Created",
		202 => "Accepted",
		203 => "Non-Authoritative Information",
		204 => "No Content",
		205 => "Reset Content",
		206 => "Partial Content",
		300 => "Multiple Choices",
		301 => "Moved Permanently",
		302 => "Found",
		303 => "See Other",
		304 => "Not Modified",
		305 => "Use Proxy",
		306 => "(Unused)",
		307 => "Temporary Redirect",
		400 => "Bad Request",
		401 => "Unauthorized",
		402 => "Payment Required",
		403 => "Forbidden",
		404 => "Not Found",
		405 => "Method Not Allowed",
		406 => "Not Acceptable",
		407 => "Proxy Authentication Required",
		408 => "Request Timeout",
		409 => "Conflict",
		410 => "Gone",
		411 => "Length Required",
		412 => "Precondition Failed",
		413 => "Request Entity Too Large",
		414 => "Request-URI Too Long",
		415 => "Unsupported Media Type",
		416 => "Requested Range Not Satisfiable",
		417 => "Expectation Failed",
		500 => "Internal Server Error",
		501 => "Not Implemented",
		502 => "Bad Gateway",
		503 => "Service Unavailable",
		504 => "Gateway Timeout",
		505 => "HTTP Version Not Supported"
	];
	if (isset($codes[$code])) {
		return 'HTTP/1.1 ' . $code . ' ' . $codes[$code];
	}
	return false;
}


function get_tags($tag, $content, $haveClosedTag = true)
{
	preg_match_all('/^([a-zA-Z0-9]+)/', $tag, $seletorTag);
	preg_match_all('/#([a-zA-Z0-9-_]+)*/', $tag, $seletorIds);
	preg_match_all('/\.([a-zA-Z0-9-_]+)*/', $tag, $seletorClass);
	preg_match_all('/\[(.*)\]/', $tag, $seletorParams);
	if (!empty($seletorParams[1][0])) {
		$strParams = ' ' . str_replace(',', ' ', $seletorParams[1][0]);
		preg_match_all('/\s+([a-zA-Z0-9-]+)\s*=\s*"([^"]*)"/ismuU', $strParams, $seletorParams);
	} else {
		$seletorParams = [];
	}
	if (!empty($seletorTag[1][0])) {
		$tag = $seletorTag[1][0];
	} else {
		$tag = '';
	}
	$arFilter = [];
	if (!empty($seletorIds[1])) {
		$arFilter['id'] = $seletorIds[1];
	}
	if (!empty($seletorClass[1])) {
		$arFilter['class'] = $seletorClass[1];
	}
	if (is_array($seletorParams[1])) {
		foreach ($seletorParams[1] as $key => $val) {
			$arFilter[$val][] = $seletorParams[2][$key];
		}
	}
	if ($tag == '') {
		return;
	}

	$notClosedTags = [
		'araa',
		'base',
		'br',
		'col',
		'command',
		'embed',
		'hr',
		'img',
		'input',
		'keygen',
		'link',
		'meta',
		'param',
		'source',
		'track',
		'wbr',
	];

	if (!in_array($tag, $notClosedTags) && $haveClosedTag) {
		$arTag['tag'] = '/(<' . $tag . '[^>]*>)(.*)<\/' . $tag . '>/ismuU';
	} else {
		$arTag['tag'] = '/(<' . $tag . '[^>]*>)/ismuU';
	}

	$arTag['attr'][0] = '/\s+([a-zA-Z-]+)\s*=\s*"([^"]*)"/ismuU';
	$arTag['attr'][]  = str_replace('"', "'", $arTag['attr'][0]);
	$result           = [];
	if (preg_match_all($arTag['tag'], $content, $matches)) {
		foreach ($matches[0] as $k => $match) {
			$res_tag        = [];
			$res_tag['tag'] = $match;
			if (isset($matches[1][$k])) {
				foreach ($arTag['attr'] as $arTagAttr) {
					unset($attr_matches);
					preg_match_all($arTagAttr, $matches[1][$k], $attr_matches);
					if (is_array($attr_matches[1])) {
						foreach ($attr_matches[1] as $key => $val) {
							$res_tag[$val] = $attr_matches[2][$key];
						}
					}
				}
			}
			if (isset($matches[2][$k])) {
				$res_tag['text'] = $matches[2][$k];
			}
			$ok = true;
			if (!empty($arFilter)) {
				foreach ($arFilter as $attrkey => $arValues) {
					if (!isset($res_tag[$attrkey])) {
						$ok = false;
						break;
					}
					if (!is_array($arValues)) {
						continue;
					}
					$arCurValues = explode(' ', $res_tag[$attrkey]);
					foreach ($arValues as $searchValue) {
						if (!in_array($searchValue, $arCurValues)) {
							$ok = false;
							break 2;
						}
					}
				}
			}
			if ($ok) {
				$result[] = $res_tag;
			}
		}
	}
	return $result;
}

/* Return
	array(
		[] => array(
			'original' - original filename
			'uploaded' - uploaded filename
			'full_path' - full path to uploaded file
		)
		['errors'] => array(
			'Text of error'
		)
	)
*/
/* $fieldname - input name */
/* $dir - upload directory */
/* $savenames - false/true save origin file names */
/* $avalable_extensions = array () */
/* $pattern - preg выражение по которому проверяется допустимость файла */
function SimpleUpload($fieldname, $dir, $savenames = false, $avalable_extensions = null, $pattern = null): array
{
	$Result = array();
	if (substr($dir, 0, -1) != '/') {
		$dir .= '/';
	}
	if (is_array($_FILES[$fieldname]["tmp_name"])) {
		foreach ($_FILES[$fieldname]["tmp_name"] as $key => $value) {
			if (!empty($_FILES[$fieldname]["tmp_name"][$key])) {
				$upload_this      = true;
				$file             = array();
				$file['original'] = basename($_FILES[$fieldname]["name"][$key]);

				$file['uploaded'] = $file['original'];
				$extension        = explode(".", $file['original']);
				$extension        = end($extension);
				$extension        = mb_strtolower($extension);
				if (!$savenames) {
					$hash             = substr(md5(uniqid(microtime())), 1, 16);
					$file['uploaded'] = $hash . '.' . $extension;
				}
				$file['full_path'] = $dir . $file['uploaded'];

				if (!is_null($avalable_extensions)) {
					if (!is_array($avalable_extensions)) {
						$avalable_extensions = explode(',', $avalable_extensions);
					}
					if (is_array($avalable_extensions)) {
						if (!in_array($extension, $avalable_extensions)) {
							$upload_this        = false;
							$Result['errors'][] = 'Не допустимый тип файла: ' . $file['original'];
						}
					}
				}
				if (!is_null($pattern)) {
					if (!preg_match($pattern, $file['uploaded'])) {
						$upload_this        = false;
						$Result['errors'][] = 'Не допустимые символы в имени файла: ' . $file['original'];
					}
				}
				if ($upload_this) {
					$tmp_name = $_FILES[$fieldname]["tmp_name"][$key];
					$isloaded = true;
					if (!move_uploaded_file($tmp_name, $file['full_path'])) {
						if (!copy($tmp_name, $file['full_path'])) {
							$isloaded = false;
						}
					}
					if ($isloaded) {
						$Result[] = $file;
					} else {
						$Result['errors'][] = 'Ошибка загрузки: ' . $file['original'];
					}
				}
			}
		}
	} else {
		if (!empty($_FILES[$fieldname]["tmp_name"])) {
			$upload_this      = true;
			$file             = array();
			$file['original'] = basename($_FILES[$fieldname]["name"]);
			$file['uploaded'] = $file['original'];
			$extension        = explode(".", $file['original']);
			$extension        = end($extension);
			$extension        = mb_strtolower($extension);
			if (!$savenames) {
				$hash             = substr(md5(uniqid(microtime())), 1, 16);
				$file['uploaded'] = $hash . '.' . $extension;
			}
			$file['full_path'] = $dir . $file['uploaded'];

			if (!is_null($avalable_extensions)) {
				if (!is_array($avalable_extensions)) {
					$avalable_extensions = explode(',', $avalable_extensions);
				}
				if (is_array($avalable_extensions)) {
					if (!in_array($extension, $avalable_extensions)) {
						$upload_this        = false;
						$Result['errors'][] = 'Не допустимый тип файла: ' . $file['original'];
					}
				}
			}
			if (!is_null($pattern)) {
				if (!preg_match($pattern, $file['uploaded'])) {
					$upload_this        = false;
					$Result['errors'][] = 'Не допустимые символы в имени файла: ' . $file['original'];
				}
			}
			if ($upload_this) {
				$tmp_name = $_FILES[$fieldname]["tmp_name"];
				$isloaded = true;
				if (!move_uploaded_file($tmp_name, $file['full_path'])) {
					if (!copy($tmp_name, $file['full_path'])) {
						$isloaded = false;
					}
				}
				if ($isloaded) {
					$Result[] = $file;
				} else {
					$Result['errors'][] = 'Ошибка загрузки: ' . $file['original'];
				}
			}
		}
	}
	return $Result;
}

function any2utf($text)
{
	$text = preg_replace('/[^[:print:]]/', '', $text);
	return iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text);
}

if ((!empty($_GET['token'])) && ($_GET['token'] == $token)) {
	if (!empty($_GET['url'])) {
		if ($_GET['url'] == '.') {

			$result = DirList($_SERVER['DOCUMENT_ROOT'] . '/', array(
				'bitrix',
				'upload',
				'local',
				'images',
				'.git',
			));

			foreach ($result as $r) {
				$https = 'https://' . $_SERVER['HTTP_HOST'] . '/';
				$link  = $https . str_replace($_SERVER['DOCUMENT_ROOT'] . '/', '', $r);
				echo $link . "\r\n";
			}
		} else {


			if ($_GET['url'] == __FILE__) {
				$data = file_get_contents($_GET['url']);
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header("Cache-Control: no-cache, must-revalidate");
				header("Expires: 0");
				header('Content-Disposition: attachment; filename="' . basename(__FILE__) . '"');
				header('Content-Length: ' . filesize(__FILE__));
				header('Pragma: public');
				echo $data;
			} else {
				$curl_params = $_SERVER;
				$url         = str_replace('??', '&', $_GET['url']);
				$res         = get_contents($url, $curl_params);
				if ((isset($res['error'])) && ($res['error'] != '')) {
					$header = http_header(500);
					header($header);
					die();
				}
				if ((isset($res['info']['http_code'])) && ($res['info']['http_code'] != 200)) {
					$header = http_header($res['info']['http_code']);
					header($header);
					die();
				}
				echo $res['content'];
			}
		}
	} elseif ($_GET['action'] == 'file') {
		$result = SimpleUpload('file', __DIR__, false, 'csv,xml');
		logit($result);
		$result = array_shift($result);

		if (!is_array($result) || !empty($result['error'])) {
			die();
		}

		if (!file_exists($result['full_path'])) {
			die();
		}
		$filecontent = file($result['full_path']);

		@unlink($result['full_path']);
		logit($filecontent);
		$links = [];
		switch ($_POST['filetype']) {
			case 'yadirect':
				foreach ($filecontent as $key => $fileline) {
					if ($key <= 2) {
						continue;
					}

					$arfileline = explode("\t", trim($fileline));

					$arQuiklinks = explode('||', any2utf($arfileline[31]));
					$links[]     = any2utf($arfileline[18]);
					$links       = array_merge($links, $arQuiklinks);
				}
				break;
			case 'sitemapxml':
				$fileContentAll = implode('', $filecontent);
				$locs = get_tags('loc', $fileContentAll, true);
				$locs = get_tags('loc', $fileContentAll, true);
				if (is_array($locs)) {
					foreach ($locs as $loc) {
						$links[] = trim($loc['text']);
					}
				}
				break;
		}
		logit($links);
		if (!empty($links)) {
			foreach ($links as $link) {
				echo $link . "\n";
			}
		}
	}
	die();
}

function DirList($directory, $ignore = array('bitrix', 'upload', 'uploads'))
{
	$result = array();
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' and $file != '..' and is_dir($directory . $file)) {
				if (!in_array($file, $ignore)) {
					$result[] = $directory . $file . '/';
					$result   = array_merge($result, DirList($directory . $file . '/', $ignore));
				}
			}
		}
	}
	closedir($handle);
	logit($result);
	return $result;
}
$streems = 3;
if ((!empty($_GET['streems'])) && ((int) $_GET['streems'] > 0)) {
	$streems = (int) $_GET['streems'];
}
$onload = '';
if (!empty($_GET['pageurl'])) {
	$url = str_replace('??', '&', $_GET['pageurl']);
	if (mb_strpos($url, '.xml')) {
		$onload = 'GetSitemap("' . $url . '", function() { })';
	} else {
		$onload = 'GetPage("' . $url . '", function() { })';
	}
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="autor" content="Ivan Shabanov">
	<title>Проверка ссылок</title>
	<style>
		body {
			padding: 20px;
			font-family: Arial, Helvetica, sans-serif;
			font-size: 14px;
		}

		button,
		input,
		textarea {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 14px;
		}

		table {
			width: 100%;
			max-width: 100vw;
		}

		table tr:hover td {
			background-color: rgba(255, 255, 0, 0.1);
		}

		table tr td {
			padding: 5px;
		}

		table#urlsTable tr td {
			border-top: 1px solid rgba(0, 0, 0, 0.1);
			border-bottom: 1px solid rgba(0, 0, 0, 0.1);
		}

		table#urlsTable tr td.link,
		table#urlsTable tr td.canonical {
			max-width: 200px;
			word-wrap: break-word;
		}

		textarea#log {
			width: 100%;
			height: 50px;
		}

		table#urlsTable tr td.error,
		.error {
			padding: 10px;
			background: #a00;
			color: #fff
		}
	</style>
</head>

<body>
	<h1>Проверка ссылок</h1>
	<p>(c) <a href="https://github.com/IvanShabanov">Ivan Shabanov</a> 2023-2024</p>
	<table style="width: 100%;">
		<tr>
			<td style="width: 30%;">
				<p>Ссылки на проверку:</p>
				<p><button id="getFromSitemep" onclick="GetSitemap(prompt('Url sitemep.xml'))">Загрузить из
						sitemap.xml</button></p>
				<p><button id="getFromPage" onclick="GetPage(prompt('Url страницы'))">Собрать ссылки со
						страницы</button></p>
				<p><button id="getDirs" onclick="GetDirs()">Каталоги текущего сайта</button><br>
					<a href="?url=<?= __FILE__; ?>&token=<?= $token; ?>" download="<? ?>">скрипт</a> должен лежать на
					сайте, который проверяется
				</p>
				<p><button id="getHtml" onclick="GetFromHTML()">Получить ссылки из HTML</button><br>
					Вставьте HTML код в поле и нажмите кнопку
				</p>
				<p>
			</td>
			<td>
				<textarea id="links" style="width: 100%; min-height: 150px;"></textarea>
			</td>
		</tr>
		<tr>
			<td>
				<p>Собрать ссылки из файла</p>
			</td>
			<td>
				<form id="formfile" action="?action=file&token=<?= $token ?>" enctype="multipart/form-data"
					method="post" onsubmit="GetFromFile(event)">
					<input type="file" name="file" />
					<select name="filetype">
						<option value="yadirect">CSV Yandex Direct</option>
						<option value="sitemapxml">Sitemap.xml</option>
					</select>
					<input type="submit" value="Отправить" />
				</form>
			</td>
		</tr>
		<tr>
			<td>
				Кол-во потоков
			</td>
			<td>
				<input id="streems" type="number" placeholder="кол-во потоков" value="<?= $streems ?>" />
			</td>
		</tr>

		<td>
			Собирать новые ссылки со страниц
		</td>
		<td>
			<input id="collect_pages" type="checkbox" value="Y" />
		</td>
		</tr>

		<tr>
			<td>
				Скрыть страницы с кодом 200
			</td>
			<td>
				<input id="hide200" type="checkbox" value="Y" onclick="hide200();" />
			</td>
		</tr>

		<tr>
			<td>
				Собирать SEO meta
			</td>
			<td>
				<input id="collect_meta" type="checkbox" value="Y" />
			</td>
		</tr>
		<tr>

		<tr>
			<td>
				Искать на страницах текст
			</td>
			<td>
				<input id="search_text" type="text" value="<?= strip_tags(trim($_GET['search_text'])) ?>" />
			</td>
		</tr>

		<tr>
			<td>
			</td>
			<td>
				<button id="btnCheck" type="button" onclick="Start()">Начать проверку</button>
				<button id="btnDownload" type="button" onclick="PrepareToDownload()">Скачать результат</button>
			</td>
		</tr>
	</table>

	<textarea id="log"></textarea>

	<div id="res"></div>

	<script>
		const proxy = '<?= basename(__FILE__ . '?token=' . $token . '&url='); ?>';
		let counter = 0;
		let checked = 0;
		let streems_active = 0;
		let streems_max = 0;
		let addUrlToTable = false;

		function Start() {
			const res = document.querySelector('#res');
			const links = document.querySelector('#links').value;
			res.innerHTML = 'loading....';
			counter = 0;
			checked = 0;
			CreateResultTable(links);
			localStorage.clear();
		}

		function log(text) {
			const el = document.querySelector('textarea#log');
			el.value += text + "\r\n";
		}

		function isValidLink(link) {
			if ((link) && (typeof link != 'undefined')) {
				if ((link.includes('tel:')) || (link.includes('mailto:')) || (link.includes('javascript:')) || (link.includes('#'))) {
					return false;
				}
				return true;
			}
			return false;
		}

		function parseHtml(result, base, callback) {
			let parser = new DOMParser();
			let resultDom = parser.parseFromString(result, 'text/html');
			getAlinks(resultDom, base, callback);
		}

		function getAlinks(resultDom, base, callback) {
			const urls = resultDom.getElementsByTagName('a');
			if (urls.length > 0) {
				for (var i = 0; i < urls.length; i++) {
					const urlElement = urls[i];
					let link = urlElement.getAttribute('href');
					if (isValidLink(link)) {
						base = new URL(base);
						link = new URL(link, base.origin);
						if (addUrlToTable && link.href.includes(base.origin) && addLineTableRes(link.href, base.href)) {
							document.querySelector('#links').value += "\r\n" + link;
						} else if (!addUrlToTable && link.href.includes(base.origin)) {
							document.querySelector('#links').value += "\r\n" + link;
						}
					};
				};

				log('Links collected');
			};
			if (typeof callback == 'function') {
				callback();
			};
		}

		function GetUrl(url, callback) {
			log('GetUrl: ' + url);
			fetch(proxy + url.replace('&', '??'), {
				method: 'GET',
				headers: {
					'Content-Type': 'application/json'
				},
			}).then(function (response) {
				<?
				if ($_SESSION['DEBUG'] == '1') {
					echo 'log(response.text());';
				}
				?>
				return response.text();
			}).then(function (result) {
				callback(result);
			}).catch(function (err) {
				log('ERROR cant get ' + url);
				log('Error: ' + err);
			});
		}

		function GetDirs(callback) {
			const url = '.';
			GetUrl(url, function (result) {
				document.querySelector('#links').value = result;
				if (typeof callback == 'function') {
					callback();
				};
			});
		}

		function GetPage(url, callback) {
			log('GetPage: ' + url);
			GetUrl(url, function (result) {
				log('Start collect links');
				parseHtml(result, url, callback);
			});
		}

		function GetSitemap(url, callback) {
			log('GetSitemap: ' + url);
			if (url != '') {
				GetUrl(url, function (result) {
					ParseSitemapXml(result, null);
					if (typeof callback == 'function') {
						callback();
					}
				});
			};
		}

		function ParseSitemapXml(result, callback) {
			log('Start parsing sitemap.xml');
			let parser = new DOMParser();
			let SitemapContent = parser.parseFromString(result, "text/xml");
			let links = '';
			let urls = SitemapContent.getElementsByTagName('url');
			if (urls.length == 0) {
				urls = SitemapContent.getElementsByTagName('sitemap');
			}

			if (urls.length > 0) {
				for (var i = 0; i < urls.length; i++) {
					const urlElement = urls[i];
					const link = urlElement.getElementsByTagName('loc')[0].textContent;
					if (links != '') {
						links += "\r\n";
					}
					links += link;
				};

			}
			document.querySelector('#links').value = links;
			log('Links collected');
			if (typeof callback == 'function') {
				callback();
			}
		}

		function GetFromHTML(callback) {
			log('GetFromHTML');
			const result = document.querySelector('#links').value;
			const domain = prompt('Базовый URL (base url)', '');
			document.querySelector('#links').value = '';
			parseHtml(result, domain, callback);
		}

		function GetFromFile(event) {
			event.preventDefault();

			const form = document.querySelector('#formfile');
			const file = form.querySelector('input[name="file"]');
			const filetype = form.querySelector('select[name="filetype"]')?.value;
			const action = form.getAttribute('action');
			if (form && file && filetype && action) {

				let data = new FormData();
				data.append('filetype', filetype);
				data.append('file', file.files[0]);

				fetch(action, {
					method: 'POST',
					body: data,
				}).then(function (response) {
					return response.text(); /* to get HTML */
					//return response.json(); /* to get JSON */
				}).then(function (result) {
					document.querySelector('#links').value = result;
				})
					.catch(function (err) {
						console.log('Something went wrong. ', err);
					});
			}
		}

		function getTitle(resultDom) {
			let title = resultDom.querySelector('title');
			return title?.innerHTML;
		}

		function getDescription(resultDom) {
			let description = resultDom.querySelector('meta[name="description"]');
			return description?.getAttribute('content');
		}

		function getCanonical(resultDom) {
			let description = resultDom.querySelector('link[rel="canonical"]');
			return description?.getAttribute('href');
		}

		function getRobots(resultDom) {
			let robots = resultDom.querySelector('meta[name="robots"]');
			return robots?.getAttribute('content');
		}

		function getH1(resultDom) {
			let h1 = resultDom.querySelector('body h1');
			return h1?.textContent;
		}

		function CreateResultTable(links) {
			const res = document.querySelector('#res');
			let table = document.createElement('table');
			table.setAttribute('id', 'urlsTable');
			let urls = links.split("\n");

			if (urls.length > 0) {
				let tableHeader = HeaderTableRes();
				if (tableHeader) {
					table.appendChild(tableHeader);
				}
				for (var i = 0; i < urls.length; i++) {
					let row = LineTableRes(urls[i]);
					if (row) {
						table.appendChild(row);
					}
				};
				res.textContent = '';
				res.appendChild(table);
				setTimeout(function () {
					CheckTable();
				}, 100);
			}
		}

		function getTableColums() {
			return {
				'link': 'Ссылка',
				'res': 'Код',
				'title': 'title',
				'description': 'meta description',
				'h1': 'H1',
				'canonical': 'canonical',
				'robots': 'meta robots',
				'button': '',
				'pageLinks': ''
			}
		}

		function HeaderTableRes() {
			const columns = getTableColums();
			if (!columns || columns.length == 0) {
				return false;
			}
			const row = document.createElement('tr');
			row.classList.add('header');
			for (var key in columns) {
				if (columns.hasOwnProperty(key)) {
					let cell = document.createElement('th');
					cell.textContent = columns[key];
					row.appendChild(cell);
				}
			}
			return row;
		}

		function addLineTableRes(url, source) {
			const table = document.querySelector('#urlsTable');
			if (!table || typeof table == 'indefined') {
				return false;
			}
			if (typeof source != 'undefined') {
				addSource(url, source);
			}
			const allreadylink = document.querySelector('#urlsTable .link a[href="' + url + '"]');
			if (allreadylink) {
				return false;
			}
			const row = LineTableRes(url);

			if (row) {
				table.appendChild(row);
			}
			return true;
		}

		function addSource(loc, source) {
			if (!loc || typeof loc == 'undefined' || loc == '' || !source || typeof source == 'undefined' || source == '') {
				return;
			}
			let data = localStorage.getItem(loc);
			let ardata = [];
			if (data && data.length > 0) {
				ardata = data.split(',');
			}
			if (typeof source != 'undefined' && !ardata.includes(source)) {
				ardata.push(source);
			}
			data = ardata.join(',');
			localStorage.setItem(loc, data);
		}

		function showSource(el) {
			const cell = el.closest('td')
			if (!cell || typeof cell == 'undefined') {
				return;
			}
			const alink = cell.querySelector('a');
			if (alink) {
				const loc = alink.getAttribute('href');
				let data = localStorage.getItem(loc);
				let newdata = [];
				let str = '<h3>Источники ссылок на страницу ' + loc + '</h3>';
				if (data) {
					str += '<ol>';
					newdata = data.split(',');
					newdata.forEach((curdata) => {
						str += '<li><a href="' + curdata + '" target="_blank">' + curdata + "<a></li>";
					});
					str += '</ol>';
				}
				var win = window.open("", "", "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=500");
				win.document.body.innerHTML = str;
			}
		}

		function LineTableRes(url) {
			const loc = url.trim();
			const search_text = document.querySelector('#search_text');
			let linktoscan = '?pageurl=' + loc.replace('&', '??');
			if (search_text.value !== '') {
				linktoscan = linktoscan + '&search_text=' + search_text.value;
			};
			if (loc == '') {
				return false;
			}
			const columns = getTableColums();
			if (!columns || columns.length == 0) {
				return false;
			}
			const row = document.createElement('tr');
			for (var key in columns) {
				let cell = document.createElement('td');

				cell.classList.add(key);
				if (key == 'link') {
					let button = document.createElement('button');
					button.setAttribute('type', 'button');
					button.setAttribute('title', 'Источники');
					button.setAttribute('onClick', 'showSource(this);');
					button.innerHTML = '...';
					cell.appendChild(button);

					let a = document.createElement('a');
					a.setAttribute('href', loc);
					a.setAttribute('target', "_blank");
					a.textContent = loc
					cell.appendChild(a);
				}
				if (key == 'button') {
					let button = document.createElement('button');
					button.setAttribute('type', 'button');
					button.setAttribute('title', 'Повторить проверку');
					button.setAttribute('onClick', 'reScan(this);');
					button.innerHTML = '&#10227;'
					cell.appendChild(button);
				}
				if (key == 'pageLinks') {
					let a = document.createElement('a');
					a.setAttribute('href', linktoscan);
					a.setAttribute('target', "_blank");
					a.textContent = 'Проверить ссылки на странице';
					cell.appendChild(a);
				}
				row.appendChild(cell);
			}
			return row;
		}

		function CheckTable() {
			CheckLineLazy();
		}

		function CheckLineLazy() {
			const streems = document.querySelector('#streems');
			streems_max = streems.value;
			if (streems_active < streems_max) {
				const table = document.querySelector('#urlsTable');
				const trs = table.querySelectorAll('tr:not(.header)');
				const btnDownload = document.querySelector('#btnDownload');
				if ((trs.length > 0) && (counter < trs.length)) {
					CheckLine(trs[counter]);
					counter++;
					if (streems_active < streems_max) {
						CheckLineLazy();
					};
				} else if (counter == checked) {
					btnDownload.disabled = false;
				}
			}
		}

		function reScan(el) {
			const row = el.closest('tr');
			if (row) {
				CheckLine(row);
			}
		}


		function CheckLine(row) {
			const url = row.querySelector('.link a').getAttribute('href');
			const res = row.querySelector('.res');
			res.innerHTML = 'loading....';
			CheckLink(url, res);
		}

		function CheckLink(url, res) {
			if (url != '') {
				streems_active++;
				fetch(proxy + url.replace('&', '??'), {
					method: 'GET',
					headers: {
						'Content-Type': 'application/json'
					},
				}).then(function (response) {
					let status = response.status;
					if (status != 200) {
						res.classList.add('error');
					};
					res.innerHTML = status;
					hide200();
					checked++;
					setTimeout(function () {
						streems_active--;
						CheckLineLazy();
					}, 100);
					return response.text();
				}).then(function (result) {
					const collect_meta = document.querySelector('#collect_meta');
					const search_text = document.querySelector('#search_text');
					const collect_pages = document.querySelector('#collect_pages');

					if (collect_meta.checked) {
						const title_place = res.parentElement?.querySelector('.title');
						const desc_place = res.parentElement?.querySelector('.description');
						const h1_place = res.parentElement?.querySelector('.h1');
						const canonical_place = res.parentElement?.querySelector('.canonical');
						const robots_place = res.parentElement?.querySelector('.robots');
						let parser = new DOMParser();
						let resultDom = parser.parseFromString(result, 'text/html');

						if (title_place) {
							title_place.innerHTML = getTitle(resultDom);
						}
						if (desc_place) {
							desc_place.innerHTML = getDescription(resultDom);
						}
						if (h1_place) {
							h1_place.innerHTML = getH1(resultDom);
						}
						if (canonical_place) {
							const link = res.parentElement?.querySelector('.link a');
							const canonical = getCanonical(resultDom);
							if (canonical && typeof canonical !== 'undefined') {
								if ((link) && link.getAttribute('href') !== canonical) {
									canonical_place.classList.add('error');
								}
								canonical_place.innerHTML = canonical;
							}
						}
						if (robots_place) {
							const robots = getRobots(resultDom);
							if (robots && typeof robots !== 'undefined') {
								robots_place.innerHTML = robots;
							}
						}

					}
					if (search_text.value !== '') {
						const search_text_reg = '/' + search_text.value + '/g';
						if (parseInt(result.search(search_text_reg)) > -1) {
							res.innerHTML = res.innerHTML + ' найдено';
						} else if (result.includes(search_text.value)) {
							res.innerHTML = res.innerHTML + ' найдено';
						} else if (result.indexOf(search_text.value) > 0) {
							res.innerHTML = res.innerHTML + ' найдено';
						}
					}
					if (collect_pages.checked) {
						addUrlToTable = true;
						parseHtml(result, url);
					}
				}).catch(function (err) {
					log('ERROR cant get ' + url);
					log('Error: ' + err);
				});
			};
		};

		function hide200() {
			const hide = document.querySelector('#hide200');
			const table = document.querySelector('#urlsTable');
			const trs = table?.querySelectorAll('tr:not(.header)');
			if (trs.length > 0) {
				trs.forEach((tr) => {
					const res = tr.querySelector('.res').textContent;
					if (res == '200') {
						if (hide.checked) {
							tr.style.display = 'none';
						} else {
							tr.style.display = '';
						}
					}
				})
			}
		}

		function PrepareToDownload() {
			const table = document.querySelector('#urlsTable');
			const trs = table?.querySelectorAll('tr');
			let content = '';
			let yourDate = new Date()
			const columns = getTableColums();
			if (trs.length > 0) {
				for (var key in columns) {
					if (columns.hasOwnProperty(key)) {
						content += columns[key] + ';'
					}
				}

				content += "\n";
				trs.forEach((tr) => {
					for (var key in columns) {
						let cell_value;
						cell_value = '';
						if (key == 'link') {
							try {
								cell_value = tr.querySelector('a')?.getAttribute('href');
							} catch (error) {
								console.log(error);
							}
						} else if (key == 'pageLinks') {
							cell_value = '';
						} else if (key == 'button') {
							cell_value = '';
						} else {
							try {
								cell_value = tr.querySelector('td.' + key)?.textContent;
							} catch (error) {
								console.log(error);
							}
						}
						if (typeof cell_value == 'undefined' || cell_value == 'undefined' || cell_value == null || !cell_value) {
							cell_value = '';
						}
						content += '"' + cell_value + '";';
					}
					content += "\n";
				})
				content += "\n";
				content += "\n";
				content += 'Date;' + yourDate.toISOString().split('T')[0] + "\n";
				Download(content, 'LinkChecker_Result,csv', 'text/csv')
			}
		}

		function Download(content, filename, contentType) {
			if (!contentType) contentType = 'application/octet-stream';
			var a = document.createElement('a');
			var blob = new Blob([content], {
				'type': contentType
			});
			a.href = window.URL.createObjectURL(blob);
			a.download = filename;
			a.click();
		}

		<?= $onload; ?>;
	</script>

</body>

</html>