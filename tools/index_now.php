<?php
/**
 * IndexNow - массовая отправка URL на преиндексацию
 * Поддерживает Яндекс и Google
 */

// Обработка отправки формы
$result = null;
$submittedUrls = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_REQUEST['submit'])) {
	$host = trim($_REQUEST['host'] ?? '');
	$key = trim($_REQUEST['key'] ?? '');
	$searchEngine = $_REQUEST['search_engine'] ?? 'both';
	$urlsText = trim($_REQUEST['urls'] ?? '');

	// Разбиваем текст на отдельные URL (по одному на строку)
	$urls = array_filter(array_map('trim', explode("\n", $urlsText)));

	if (empty($host) || empty($key) || empty($urls)) {
		$result = [
			'success' => false,
			'message' => 'Пожалуйста, заполните все поля и добавьте хотя бы один URL'
		];
	} else {
		$submittedUrls = $urls;
		$result = sendToIndexNow($host, $key, $urls, $searchEngine);
	}
}

/**
 * Отправка URL через IndexNow API
 */
function sendToIndexNow($host, $key, $urls, $searchEngine = 'both') {
	// Эндпоинты поисковых систем
	$endpoints = [];

	if ($searchEngine === 'yandex' || $searchEngine === 'both') {
		$endpoints['Yandex'] = 'https://yandex.com/indexnow';
	}

	if ($searchEngine === 'bing' || $searchEngine === 'both') {
		$endpoints['Bing'] = 'https://www.bing.com/indexnow';
	}

	// Подготовка данных для запроса
	$keyLocation = "https://{$host}/{$key}.txt";
	$data = [
		'host' => $host,
		'key' => $key,
		'keyLocation' => $keyLocation,
		'urlList' => $urls
	];

	$jsonData = json_encode($data);
	$results = [];

	foreach ($endpoints as $engineName => $endpoint) {
		$ch = curl_init();

		curl_setopt_array($ch, [
			CURLOPT_URL => $endpoint,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $jsonData,
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/json',
				'Content-Length: ' . strlen($jsonData)
			],
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2
		]);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlError = curl_error($ch);
		curl_close($ch);

		// Определяем статус ответа
		if ($httpCode === 200) {
			$status = 'success';
			$message = '✅ URL успешно отправлены на индексацию';
		} elseif ($httpCode === 202) {
			$status = 'success';
			$message = '✅ Запрос принят, URL будут обработаны';
		} elseif ($httpCode === 400) {
			$status = 'error';
			$message = '❌ Ошибка: неверный формат запроса';
		} elseif ($httpCode === 403) {
			$status = 'error';
			$message = '❌ Ошибка: неверный ключ или ключевой файл не доступен';
		} elseif ($httpCode === 429) {
			$status = 'warning';
			$message = '⚠️ Слишком много запросов, попробуйте позже';
		} else {
			$status = 'error';
			$message = "❌ Ошибка HTTP {$httpCode}: " . ($response ?: $curlError);
		}

		$results[] = [
			'engine' => $engineName,
			'status' => $status,
			'message' => $message,
			'http_code' => $httpCode,
			'response' => $response
		];
	}

	return $results;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>IS IndexNow - массовая отправка URL на индексацию</title>
	<meta name="autor" content="Ivan Shabanov">
	<link rel="stylesheet" href="style_tools.css">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="header">
				<h1>🚀 IS IndexNow</h1>
				<p>Мгновенная отправка URL на индексацию в Яндекс и Bing</p>
			</div>

			<div class="form-section">
				<form method="POST" action="">
					<div class="form-group">
						<label>Домен сайта <span class="required">*</span></label>
						<input type="text" name="host" required placeholder="example.com" value="<?= htmlspecialchars($_REQUEST['host'] ?? '') ?>">
						<div class="help-text">Только домен, без http:// и www</div>
					</div>

					<div class="form-group">
						<label>Ключ IndexNow <span class="required">*</span></label>
						<input type="text" name="key" required placeholder="ваш_секретный_ключ" value="<?= htmlspecialchars($_REQUEST['key'] ?? '') ?>">
						<div class="help-text">Сгенерируйте случайную строку (10-128 символов) и разместите файл <?= htmlspecialchars($_REQUEST['host'] ?? 'domain') ?>/{ключ}.txt в корне сайта</div>
					</div>

					<div class="form-group">
						<label>Поисковая система <span class="required">*</span></label>
						<div class="radio-group">
							<label>
								<input type="radio" name="search_engine" value="both" <?= (!isset($_REQUEST['search_engine']) || $_REQUEST['search_engine'] === 'both') ? 'checked' : '' ?>>
								📊 Яндекс + Bing
							</label>
							<label>
								<input type="radio" name="search_engine" value="yandex" <?= (isset($_REQUEST['search_engine']) && $_REQUEST['search_engine'] === 'yandex') ? 'checked' : '' ?>>
								🟡 Только Яндекс
							</label>
							<label>
								<input type="radio" name="search_engine" value="bing" <?= (isset($_REQUEST['search_engine']) && $_REQUEST['search_engine'] === 'google') ? 'checked' : '' ?>>
								🔵 Только Bing
							</label>
						</div>
					</div>

					<div class="form-group">
						<label>Список URL <span class="required">*</span></label>
						<textarea name="urls" required placeholder="https://example.com/page1&#10;https://example.com/page2&#10;https://example.com/page3"><?= htmlspecialchars($_REQUEST['urls'] ?? '') ?></textarea>
						<div class="help-text">Каждый URL на новой строке. Максимум 10 000 URL за один запрос</div>
					</div>

					<button type="submit" name="submit">📤 Отправить на индексацию</button>
				</form>

				<div class="info-note">
					<strong>📖 Как получить ключ IndexNow:</strong>
					1. Сгенерируйте случайный ключ, например: <code>7f3d8a9b2c1e5f4g6h7i8j9k0l</code><br>
					2. Создайте файл <code>7f3d8a9b2c1e5f4g6h7i8j9k0l.txt</code> с содержимым только ключа<br>
					3. Разместите файл в корне вашего сайта<br>
					4. Убедитесь, что файл доступен по адресу: <code>https://ваш-сайт/7f3d8a9b2c1e5f4g6h7i8j9k0l.txt</code>
				</div>
			</div>

			<?php if ($result): ?>
			<div class="result-section">
				<h3>📊 Результат отправки</h3>

				<?php foreach ($result as $item): ?>
				<div class="result-item <?= $item['status'] ?>">
					<h4>
						<?= $item['engine'] ?>
						<span class="badge badge-info">HTTP <?= $item['http_code'] ?></span>
					</h4>
					<p><?= $item['message'] ?></p>
				</div>
				<?php endforeach; ?>

				<?php if (!empty($submittedUrls)): ?>
				<div class="urls-list">
					<h4>📄 Отправленные URL (<?= count($submittedUrls) ?> шт.)</h4>
					<ul>
						<?php foreach ($submittedUrls as $url): ?>
						<li>• <?= htmlspecialchars($url) ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<div class="form-section">
				<h3 style="margin-bottom: 15px;">⚡ Генератор ключа IndexNow</h3>
				<div class="form-group">
					<button type="button" onclick="generateKey()" style="background: #6c757d; margin-bottom: 10px;">🎲 Сгенерировать случайный ключ</button>
					<input type="text" id="generatedKey" readonly placeholder="Нажмите кнопку для генерации" style="background: #f8f9fa; cursor: pointer;" onclick="this.select()">
				</div>
			</div>

			<div class="footer">
				<p>(c) 2026 Ivan Shabanov</p>
			</div>
		</div>
		<!--
		<div class="row">
			<div class="form-section">
				<h3 style="margin-bottom: 15px;">⚡ Генератор ключа IndexNow</h3>
				<div class="form-group">
					<button type="button" onclick="generateKey()" style="background: #6c757d; margin-bottom: 10px;">🎲 Сгенерировать случайный ключ</button>
					<input type="text" id="generatedKey" readonly placeholder="Нажмите кнопку для генерации" style="background: #f8f9fa; cursor: pointer;" onclick="this.select()">
				</div>
			</div>
		</div>
						-->

	</div>

	<script>
		function generateKey() {
			const length = 32;
			const chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
			let key = '';
			for (let i = 0; i < length; i++) {
				key += chars.charAt(Math.floor(Math.random() * chars.length));
			}
			document.getElementById('generatedKey').value = key;

			// Предлагаем заполнить поле ключа
			if (confirm('Скопировать ключ в форму?')) {
				const keyInput = document.querySelector('input[name="key"]');
				if (keyInput) {
					keyInput.value = key;
				}
				navigator.clipboard.writeText(key).then(() => {
					alert('Ключ скопирован в буфер обмена и вставлен в форму!');
				});
			}
		}
	</script>
</body>
</html>