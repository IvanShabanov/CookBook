<?php
@session_start();
if (!isset($_SESSION['token'])) {
	$_SESSION['token'] = uniqid();
}

$token = $_SESSION['token'];

function get_contents($url, $params = array())
{
	$result = array();
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	if ((isset($params['USER_AGENT'])) && ($params['USER_AGENT'] != '')) {
		curl_setopt($ch, CURLOPT_USERAGENT, $params['USER_AGENT']);
	};
	if ((isset($params['POST'])) && ($params['POST'] != '')) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params['POST']);
	};
	if ((isset($params['REFER'])) && ($params['REFER'] != '')) {
		curl_setopt($ch, CURLOPT_REFERER, $params['REFER']);
	};
	if ((isset($params['COOKIEFILE'])) && ($params['COOKIEFILE'] != '')) {
		curl_setopt($ch, CURLOPT_COOKIEJAR, $params['COOKIEFILE']);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $params['COOKIEFILE']);
	};
	if ((isset($params['IGNORE_SSL_ERRORS'])) && ($params['IGNORE_SSL_ERRORS'] != '')) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	}
	$response = curl_exec($ch);

	if (!$response) {
		$result['error'] = curl_error($ch);
	} else {
		$info = curl_getinfo($ch);
		$result['content'] = $response;
		$result['info'] = $info;
	}
	curl_close($ch);
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
		return  'HTTP/1.1 ' . $code . ' ' . $codes[$code];
	}
	return false;
}

if ((!empty($_GET['token'])) && ($_GET['token'] == $token)) {
	if (!empty($_GET['url'])) {
		if ($_GET['url'] == '.') {

			$result = DirList($_SERVER['DOCUMENT_ROOT'] . '/', array(
				'bitrix',
				'upload',
				'local',
				'images'
			));

			foreach ($result as $r) {
				$https = 'https://' . $_SERVER['HTTP_HOST'] . '/';
				$link = $https . str_replace($_SERVER['DOCUMENT_ROOT'] . '/', '', $r);
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
				$url = $_GET['url'];
				$res = get_contents($url, $curl_params);
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
					$result = array_merge($result, DirList($directory . $file . '/', $ignore));
				};
			};
		};
	};
	closedir($handle);
	return $result;
};



$streems = 3;
if ((!empty($_GET['streems'])) && ((int) $_GET['streems'] > 0)) {
	$streems = (int) $_GET['streems'];
}
$onload = '';
if (!empty($_GET['pageurl'])) {
	$url = $_GET['pageurl'];
	if (mb_strpos($url, '.xml')) {
		$onload = 'GetSitemap("' . $url . '", function() { Start(); })';
	} else {
		$onload = 'GetPage("' . $url . '", function() { Start(); })';
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

		textarea#log {
			width: 100%;
			height: 50px;
		}
	</style>
</head>

<body>
	<h1>Проверка ссылок</h1>
	<p>(c) <a href="https://github.com/IvanShabanov">Ivan Shabanov</a> 2023</p>
	<table style="width: 100%;">
		<tr>
			<td style="width: 30%;">
				<p>Ссылки на проверку:</p>
				<p><button id="getFromSitemep" onclick="GetSitemap(prompt('Url sitemep.xml'))">Загрузить из sitemap.xml</button></p>
				<p><button id="getFromPage" onclick="GetPage(prompt('Url страницы'))">Собрать ссылки со страницы</button></p>
				<p><button id="getDirs" onclick="GetDirs()">Каталоги текущего сайта</button><br>
					<a href="?url=<?= __FILE__; ?>&token=<?= $token; ?>" download="<? ?>">скрипт</a> должен лежать на сайте, который проверяется
				</p>
				<p><button id="getHtml" onclick="GetFromHTML()">Получить ссылки из HTML</button><br>
					Вставьте HTML код в поле и нажмите кнопку
				</p>
			</td>
			<td>
				<textarea id="links" style="width: 100%; min-height: 150px;"></textarea>
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
				Собирать Title и Description
			</td>
			<td>
				<input id="title_descr" type="checkbox" value="Y" />
			</td>
		</tr>
		<tr>
			<td>
			</td>
			<td>
				<button id="btnCheck" type="button" onclick="Start()">Начать проверку</button>
				<button id="btnDownload" type="button" onclick="PrepareToDownload()" disabled>Скачать результат</button>
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

		function Start() {
			const res = document.querySelector('#res');
			const links = document.querySelector('#links').value;
			res.innerHTML = 'loading....';
			counter = 0;
			checked = 0;
			CreateResultTable(links);
		}

		function log(text) {
			const el = document.querySelector('textarea#log');
			el.value += text + "\r\n";
		}

		function parseHtml(result, base, callback) {
			let parser = new DOMParser();
			let resultDom = parser.parseFromString(result, 'text/html');
			let links = '';
			let urls = resultDom.getElementsByTagName('a');

			if (urls.length > 0) {
				for (var i = 0; i < urls.length; i++) {
					const urlElement = urls[i];
					let link = urlElement.getAttribute('href');
					if ((link) && (typeof link != 'undefined')) {
						if ((link.includes('tel:')) || (link.includes('mailto:')) || (link.includes('javascript:')) || (link.includes('#'))) {
							link = '';
						}
					} else {
						link = '';
					}
					if (link != '') {
						link = new URL(link, base);
						if (links != '') {
							links += "\r\n";
						};
						links += link;
					};
				};
				document.querySelector('#links').value = links;
				log('Links collected');

				if (typeof callback == 'function') {
					callback();
				};
			};
		}

		function GetUrl(url, callback) {
			log('GetUrl: ' + url);
			fetch(proxy + url, {
				method: 'GET',
				headers: {
					'Content-Type': 'application/json'
				},
			}).then(function(response) {
				return response.text();
			}).then(function(result) {
				callback(result);
			}).catch(function(err) {
				log('ERROR cant get ' + url);
				log('Error: ' + err);
			});
		}

		function GetDirs(callback) {
			const url = '.';
			GetUrl(url, function(result) {
				document.querySelector('#links').value = result;
				if (typeof callback == 'function') {
					callback();
				};
			});
		}

		function GetPage(url, callback) {
			log('GetPage: ' + url);
			GetUrl(url, function(result) {
				log('Start collect links');
				parseHtml(result, url, callback);
			});
		}

		function GetSitemap(url, callback) {
			log('GetSitemap: ' + url);
			if (url != '') {
				GetUrl(url, function(result) {
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
					};
				});
			};
		}

		function GetFromHTML(callback) {
			log('GetFromHTML');
			const result = document.querySelector('#links').value;
			const domain = prompt('Базовый URL (base url)', '');
			document.querySelector('#links').value = '';
			parseHtml(result, domain, callback);
		}

		function getTitle(result) {
			let parser = new DOMParser();
			let resultDom = parser.parseFromString(result, 'text/html');
			let title = resultDom.querySelector('title');
			return 	title?.innerHTML;
		}

		function getDescription(result) {
			let parser = new DOMParser();
			let resultDom = parser.parseFromString(result, 'text/html');
			let description = resultDom.querySelector('meta[name="description"]');
			return 	description?.getAttribute('content');
		}


		function CreateResultTable(links) {

			const res = document.querySelector('#res');
			let table = '';
			let urls = links.split("\n");

			if (urls.length > 0) {
				for (var i = 0; i < urls.length; i++) {
					const urlElement = urls[i];
					const loc = urlElement.trim();
					if (loc != '') {
						table += '<tr>';
						table += '<td class="link">';
						table += '<a href="' + loc + '" target="_blank">' + loc + '</a>';
						table += '</td>';
						table += '<td class="res">';
						table += '</td>';
						table += '<td class="title">';
						table += '</td>';
						table += '<td class="description">';
						table += '</td>';
						table += '<td>';
						table += '<button type="button" title="Повторить проверку">&#10227;</button>';
						table += '</td>';
						table += '<td>';
						table += '<a href="?pageurl=' + loc + '" target="_blank">Проверить ссылки на странице</a>';
						table += '</td>';
						table += '</tr>';
					}
				};

				if (table.length > 0) {
					table = '<table id="urlsTable">' + table + '</table>';
					res.innerHTML = table;
					setTimeout(function() {
						CheckTable();
					}, 100);
				}

			}
		}

		function CheckTable() {
			const table = document.querySelector('#urlsTable');
			const trs = table.querySelectorAll('tr');
			trs.forEach(tr => {
				const reload = tr.querySelector('button');
				reload.addEventListener('click', function(event) {
					CheckLine(tr);
				});
			});
			CheckLineLazy();
		}

		function CheckLineLazy() {
			const streems = document.querySelector('#streems');
			streems_max = streems.value;
			if (streems_active < streems_max) {
				const table = document.querySelector('#urlsTable');
				const trs = table.querySelectorAll('tr');
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

		function CheckLine(tr) {
			const url = tr.querySelector('a').getAttribute('href');
			const res = tr.querySelector('.res');
			res.innerHTML = 'loading....';
			CheckLink(url, res);
		}

		function CheckLink(url, res) {
			if (url != '') {
				streems_active++;
				fetch(proxy + url, {
					method: 'GET',
					headers: {
						'Content-Type': 'application/json'
					},
				}).then(function(response) {
					let status = response.status;
					if (status != 200) {
						res.innerHTML = '<div style="padding: 10px; background: #a00; color: #fff">' + status + '</div>';
					} else {
						res.innerHTML = '200';
						hide200();

					}
					checked++;
					setTimeout(function() {
						streems_active--;
						CheckLineLazy();
					}, 100);
					return response.text();
				}).then(function(result) {
					const need_meta = document.querySelector('#title_descr');
					if (need_meta.checked) {
						const title_place = res.parentElement?.querySelector('.title');
						const desc_place = res.parentElement?.querySelector('.description');
						if (title_place) {
							title_place.innerHTML = getTitle(result);
						}
						if (desc_place) {
							desc_place.innerHTML = getDescription(result);
						}
					}
				}).catch(function(err) {
					log('ERROR cant get ' + url);
					log('Error: ' + err);
				});
			};
		};

		function hide200() {
			const hide = document.querySelector('#hide200');
			const table = document.querySelector('#urlsTable');
			const trs = table?.querySelectorAll('tr');
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

			if (trs.length > 0) {
				content += 'Date;' + yourDate.toISOString().split('T')[0] + "\n";
				content += "\n";
				content += 'URL;HTML CODE' + "\n";

				trs.forEach((tr) => {
					const url = tr.querySelector('a').getAttribute('href');;
					const res = tr.querySelector('.res').textContent;
					const title = tr.querySelector('.title').textContent;
					const desc = tr.querySelector('.description').textContent;
					content += '"' + url + '";"' + res + '";"' + title.replace('"', '&quot;') + '";"' + desc.replace('"', '&quot;') + '"' +  "\n";
				})
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