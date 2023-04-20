<?php
if ((!empty($_GET['token'])) && ($_GET['token'] == md5(date('YmdH')))) {
	if (!empty($_GET['url'])) {
		$data = file_get_contents($_GET['url']);
		if ($data === false) {
			$error = error_get_last();
			if (!empty($error['message'])) {
				if (mb_strpos($error['message'], 'HTTP/1.1') !== false) {
					list($trash, $header) = explode('HTTP/1.1', $error['message']);
					$header = 'HTTP/1.1' . $header;
					header($header);
					die();
				}
			}
		} else {
			echo $data;
		}
	}
	die();
}
$sitemap = '';
if (!empty($_GET['sitemap'])) {
	$sitemap = $_GET['sitemap'];
}
$streems = 3;
if ((!empty($_GET['streems'])) && ((int) $_GET['streems'] > 0)) {
	$streems = (int) $_GET['streems'];
}

?>
<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Links chekcer</title>
</head>

<body>
	<h1>Проверка ссылок</h1>
	<p>Проверить все ссылки и после автоматом поставиться на скачивание результат</p>
	<table>
		<tr>
			<td>
				Ссылки на проверку
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
			</td>
			<td>
				<button id="btnCheck" type="button" onclick="Start()">Начать проверку</button>
				<button id="btnDownload" type="button" onclick="PrepareToDownload()" disabled>Скачать результат</button>
			</td>
		</tr>




	</table>



	<div id="res"></div>

	<script>
		const proxy = '<?= basename(__FILE__ . '?token=' . md5(date('YmdH')) . '&url='); ?>';
		let counter = 0;
		let checked = 0;

		function Start() {
			const res = document.querySelector('#res');
			const links = document.querySelector('#links').value;
			res.innerHTML = 'loading....';
			counter = 0;
			checked = 0;
			CreateResultTable(links);
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
						table += '<td class="link"  style="border-bottom: 1px solid #888;">';
						table += '<a href="' + loc + '" target="_blank">' + loc + '</a>';
						table += '</td>';
						table += '<td class="res">';
						table += '</td>';
						table += '<td>';
						table += '<button type="button">Повторить проверку</button>';
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
			const streems = document.querySelector('#streems');
			const imax = 0 + streems.value;
			for (let i = 0; i < Math.min(imax, trs.length); i++) {
				CheckLineLazy();
			}
		}

		function CheckLineLazy() {
			const table = document.querySelector('#urlsTable');
			const trs = table.querySelectorAll('tr');
			const btnDownload = document.querySelector('#btnDownload');
			if ((trs.length > 0) && (counter < trs.length)) {
				CheckLine(trs[counter]);
				counter++;
			} else if (counter == checked) {
				btnDownload.disabled = false;
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
					}
					checked++;
					setTimeout(function() {
						CheckLineLazy();
					}, 100);
				}).catch(function(err) {
					res.innerHTML = 'ERROR';
					console.log('Something went wrong. ', err);
				});
			};
		};

		function PrepareToDownload() {
			const table = document.querySelector('#urlsTable');
			const trs = table.querySelectorAll('tr');
			let content = '';
			let yourDate = new Date()

			if (trs.length > 0) {
				content += 'Date;' + yourDate.toISOString().split('T')[0] + "\n";
				content += "\n";
				content += 'URL;HTML CODE' + "\n";

				trs.forEach((tr) => {
					const url = tr.querySelector('a').getAttribute('href');;
					const res = tr.querySelector('.res').textContent;
					content += '' + url + ';' + res + "\n";
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
	</script>

</body>

</html>