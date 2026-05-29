<?php
/**
 * Google Indexing API - управление индексацией URL
 * Поддерживает: отправка на индексацию (URL_UPDATED) и удаление из индекса (URL_DELETED)
 * (c) 2026 Ivan Shabanov
 */

session_start();

// Обработка отправки формы
$result = null;
$submittedUrls = [];
$action = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_REQUEST['submit'])) {
	$jsonKey = trim($_REQUEST['json_key'] ?? '');
	$urlsText = trim($_REQUEST['urls'] ?? '');
	$action = $_REQUEST['action'] ?? 'update';

	// Разбиваем текст на отдельные URL
	$urls = array_filter(array_map('trim', explode("\n", $urlsText)));

	if (empty($jsonKey) || empty($urls)) {
		$result = [
			'success' => false,
			'message' => 'Пожалуйста, заполните все поля'
		];
	} else {
		// Проверяем валидность JSON ключа
		$keyData = json_decode($jsonKey, true);
		if (!$keyData || !isset($keyData['client_email']) || !isset($keyData['private_key'])) {
			$result = [
				'success' => false,
				'message' => 'Неверный формат JSON ключа. Убедитесь, что вы вставили полный JSON файл сервисного аккаунта'
			];
		} else {
			$submittedUrls = $urls;
			$result = processGoogleIndexingAPI($jsonKey, $urls, $action);
		}
	}
}

/**
 * Обработка запросов к Google Indexing API
 */
function processGoogleIndexingAPI($jsonKey, $urls, $action) {
	$results = [];

	// Декодируем JSON ключ
	$keyData = json_decode($jsonKey, true);

	// Получаем access token
	$accessToken = getGoogleAccessToken($keyData);

	if (!$accessToken) {
		return [
			'success' => false,
			'message' => 'Не удалось получить токен доступа. Проверьте правильность JSON ключа'
		];
	}

	// Определяем тип уведомления
	$notificationType = ($action === 'delete') ? 'URL_DELETED' : 'URL_UPDATED';

	// Обрабатываем каждый URL
	foreach ($urls as $url) {
		$result = sendToGoogleIndexingAPI($url, $accessToken, $notificationType);
		$results[] = $result;
	}

	return [
		'success' => true,
		'action' => $action,
		'notification_type' => $notificationType,
		'results' => $results
	];
}

/**
 * Получение access token через JWT (Service Account)
 */
function getGoogleAccessToken($keyData) {
    $clientEmail = $keyData['client_email'];
    $privateKey = $keyData['private_key'];

    // Создаем JWT
    $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
    $claim = json_encode([
        'iss' => $clientEmail,
        'scope' => 'https://www.googleapis.com/auth/indexing',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => time() + 3600,
        'iat' => time()
    ]);

    // Кодируем header и claim
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlClaim = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($claim));

    // Создаем подпись
    $signature = '';
    $success = openssl_sign(
        $base64UrlHeader . '.' . $base64UrlClaim,
        $signature,
        $privateKey,
        OPENSSL_ALGO_SHA256
    );

    if (!$success) {
        return false;
    }

    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    $jwt = $base64UrlHeader . '.' . $base64UrlClaim . '.' . $base64UrlSignature;

    // Отправляем запрос на получение токена
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return false;
    }

    $tokenData = json_decode($response, true);
    return $tokenData['access_token'] ?? false;
}

/**
 * Отправка уведомления в Google Indexing API
 */
function sendToGoogleIndexingAPI($url, $accessToken, $notificationType) {
	$apiUrl = 'https://indexing.googleapis.com/v3/urlNotifications:publish';

	$data = [
		'url' => $url,
		'type' => $notificationType
	];

	$jsonData = json_encode($data);

	$ch = curl_init($apiUrl);
	curl_setopt_array($ch, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $jsonData,
		CURLOPT_HTTPHEADER => [
			'Authorization: Bearer ' . $accessToken,
			'Content-Type: application/json',
			'Content-Length: ' . strlen($jsonData)
		],
		CURLOPT_TIMEOUT => 30,
		CURLOPT_SSL_VERIFYPEER => true
	]);

	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$curlError = curl_error($ch);
	curl_close($ch);

	// Парсим ответ
	$responseData = json_decode($response, true);

	if ($httpCode === 200) {
		$status = 'success';
		$message = '✅ URL успешно обработан';
		if ($notificationType === 'URL_UPDATED') {
			$message .= ' (отправлен на индексацию)';
		} else {
			$message .= ' (удален из индекса)';
		}
	} elseif ($httpCode === 403) {
		$status = 'error';
		$message = '❌ Ошибка доступа. Проверьте, что сервисный аккаунт добавлен в Google Search Console иил не включен доступ по API. ' . $responseData['error']['message'];
	} elseif ($httpCode === 400) {
		$status = 'error';
		$message = '❌ Неверный запрос: ' . ($responseData['error']['message'] ?? 'неизвестная ошибка');
	} elseif ($httpCode === 429) {
		$status = 'warning';
		$message = '⚠️ Слишком много запросов. Попробуйте позже. '. $responseData['error']['message'];
	} else {
		$status = 'error';
		$message = "❌ Ошибка HTTP {$httpCode}: " . ($responseData['error']['message'] ?? $curlError);
	}

	return [
		'url' => $url,
		'status' => $status,
		'message' => $message,
		'http_code' => $httpCode,
		'response' => $responseData
	];
}

/**
 * Валидация URL
 */
function validateUrl($url) {
	return filter_var($url, FILTER_VALIDATE_URL) !== false;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>IS Google Indexing API - управление индексацией</title>
	<link rel="stylesheet" href="style_tools.css">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="header">
				<h1>🔍 IS Google Indexing API</h1>
				<p>Управление индексацией URL в Google: отправка на индексацию и удаление из индекса</p>
			</div>

			<div class="form-section">
				<form method="POST" action="">
					<div class="form-group">
						<label>JSON ключ сервисного аккаунта <span class="required">*</span></label>
						<textarea id="json_key" name="json_key" required placeholder='Вставьте содержимое JSON файла сервисного аккаунта здесь...'><?= htmlspecialchars($_REQUEST['json_key'] ?? '') ?></textarea>
						<div class="help-text">JSON файл, который вы скачали при создании сервисного аккаунта в Google Cloud Console</div>
					</div>

					<div class="form-group">
						<label>Действие <span class="required">*</span></label>
						<div class="radio-group">
							<label>
								<input type="radio" name="action" value="update" <?= (!isset($_REQUEST['action']) || $_REQUEST['action'] === 'update') ? 'checked' : '' ?>>
								<span>🔄 Отправить на индексацию</span>
							</label>
							<label>
								<input type="radio" name="action" value="delete" <?= (isset($_REQUEST['action']) && $_REQUEST['action'] === 'delete') ? 'checked' : '' ?>>
								<span>🗑️ Удалить из индекса</span>
							</label>
						</div>
						<div class="help-text">URL_UPDATED - отправляет URL на переиндексацию, URL_DELETED - удаляет URL из индекса Google</div>
					</div>

					<div class="form-group">
						<label>Список URL <span class="required">*</span></label>
						<textarea id="urls" name="urls" required placeholder="https://example.com/page1&#10;https://example.com/page2&#10;https://example.com/page3"><?= htmlspecialchars($_REQUEST['urls'] ?? '') ?></textarea>
						<div class="help-text">Каждый URL на новой строке. URL должны быть полностью указаны с https://</div>
					</div>

					<button type="submit" name="submit">🚀 Поехали</button>
				</form>

				<button type="button" class="example-btn" onclick="loadExample()">📝 Загрузить пример JSON ключа</button>

				<div class="info-note">
					<strong>📖 Как настроить Google Indexing API:</strong>
					<ol style="margin-left: 20px; margin-top: 10px;">
						<li>Перейдите в <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
						<li>Создайте проект или выберите существующий</li>
						<li>Включите <strong>Indexing API</strong> в библиотеке API</li>
						<li>Создайте сервисный аккаунт в разделе "IAM и администрирование" → "Сервисные аккаунты"</li>
						<li>Создайте ключ в формате JSON и скачайте файл</li>
						<li>Добавьте email сервисного аккаунта в <strong>Google Search Console</strong> как владельца (свойство уровня сайта)</li>
						<li>Скопируйте содержимое JSON файла в поле выше</li>
					</ol>
					<br>
					<strong>⚠️ Важно:</strong> Сервисный аккаунт должен быть добавлен в Google Search Console как владелец для вашего сайта!
				</div>
			</div>

			<?php if ($result): ?>
			<div class="result-section">
				<h3>📊 Результат выполнения</h3>

				<?php if (!$result['success']): ?>
					<div class="alert alert-error">
						<?= htmlspecialchars($result['message']) ?>
					</div>
				<?php else: ?>
					<?php
					$successCount = 0;
					$errorCount = 0;
					$warningCount = 0;

					foreach ($result['results'] as $item) {
						if ($item['status'] === 'success') $successCount++;
						elseif ($item['status'] === 'error') $errorCount++;
						elseif ($item['status'] === 'warning') $warningCount++;
					}
					?>

					<div class="result-stats">
						<div class="stat">
							<div class="stat-number"><?= count($result['results']) ?></div>
							<div class="stat-label">Всего URL</div>
						</div>
						<div class="stat">
							<div class="stat-number" style="color: #28a745;"><?= $successCount ?></div>
							<div class="stat-label">Успешно</div>
						</div>
						<div class="stat">
							<div class="stat-number" style="color: #dc3545;"><?= $errorCount ?></div>
							<div class="stat-label">Ошибок</div>
						</div>
						<div class="stat">
							<div class="stat-number" style="color: #ffc107;"><?= $warningCount ?></div>
							<div class="stat-label">Предупреждений</div>
						</div>
					</div>

					<div class="alert alert-success">
						<strong>Действие:</strong> <?= $result['action'] === 'update' ? 'Отправка на индексацию (URL_UPDATED)' : 'Удаление из индекса (URL_DELETED)' ?><br>
						<strong>Тип уведомления:</strong> <?= $result['notification_type'] ?>
					</div>

					<?php foreach ($result['results'] as $item): ?>
					<div class="result-item <?= $item['status'] ?>">
						<h4><?= htmlspecialchars($item['url']) ?></h4>
						<p><?= htmlspecialchars($item['message']) ?></p>
						<?php if ($item['http_code'] !== 200): ?>
						<p style="font-size: 11px; color: #666; margin-top: 5px;">HTTP <?= $item['http_code'] ?></p>
						<?php endif; ?>
					</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<div class="footer">
				<p>(c) 2026 Ivan Shabanov</p>
			</div>
		</div>
	</div>

	<script>
		function loadExample() {
			const exampleJson = {
				"type": "service_account",
				"project_id": "your-project-id",
				"private_key_id": "example123456789",
				"private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC...\n-----END PRIVATE KEY-----\n",
				"client_email": "your-service-account@your-project.iam.gserviceaccount.com",
				"client_id": "123456789012345678901",
				"auth_uri": "https://accounts.google.com/o/oauth2/auth",
				"token_uri": "https://oauth2.googleapis.com/token",
				"auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
				"client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/your-service-account%40your-project.iam.gserviceaccount.com"
			};

			document.getElementById('json_key').value = JSON.stringify(exampleJson, null, 2);
			alert('Пример JSON ключа загружен! Замените данные на свои из реального файла.');
		}

		// Валидация при отправке
		document.querySelector('form').addEventListener('submit', function(e) {
			const jsonKey = document.getElementById('json_key').value.trim();
			const urls = document.getElementById('urls').value.trim();

			if (!jsonKey) {
				e.preventDefault();
				alert('Пожалуйста, введите JSON ключ сервисного аккаунта');
				return false;
			}

			if (!urls) {
				e.preventDefault();
				alert('Пожалуйста, введите хотя бы один URL');
				return false;
			}

			// Проверка валидности JSON
			try {
				JSON.parse(jsonKey);
			} catch(e) {
				e.preventDefault();
				alert('Неверный формат JSON. Пожалуйста, проверьте корректность JSON ключа');
				return false;
			}

			return true;
		});
	</script>
</body>
</html>