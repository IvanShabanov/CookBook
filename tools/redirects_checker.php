<?php
/**
 * Скрипт для отслеживания цепочки HTTP-редиректов
 * Использование: просто откройте файл в браузере, введите URL и нажмите "Проверить"
 */

// -----------------------------------------------------------
// Вспомогательные функции
// -----------------------------------------------------------

/**
 * Приводит URL к нормальному виду (добавляет http://, если нет схемы)
 */
function normalizeUrl($url)
{
    $url = trim($url);
    if (!preg_match('#^https?://#i', $url)) {
        $url = 'http://' . $url;
    }
    return $url;
}

/**
 * Разрешает относительный Location на основе базового URL
 */
function resolveUrl($base, $location)
{
    // Абсолютный URL
    if (preg_match('#^https?://#i', $location)) {
        return $location;
    }

    // Protocol-relative URL (начинается с //)
    if (strpos($location, '//') === 0) {
        $parts  = parse_url($base);
        $scheme = isset($parts['scheme']) ? $parts['scheme'] : 'http';
        return $scheme . ':' . $location;
    }

    // Парсим базовый URL
    $parts = parse_url($base);
    if ($parts === false || !isset($parts['host'])) {
        // Некорректный базовый URL – вернём как есть
        return $location;
    }

    $scheme  = $parts['scheme'] ?? 'http';
    $host    = $parts['host'];
    $port    = isset($parts['port']) ? ':' . $parts['port'] : '';
    $baseUrl = $scheme . '://' . $host . $port;

    // Абсолютный путь на том же хосте
    if (strpos($location, '/') === 0) {
        return $baseUrl . $location;
    }

    // Относительный путь – объединяем с путём базового URL
    $path     = $parts['path'] ?? '/';
    $basePath = dirname($path); // может вернуть '.' или '/'

    if ($basePath === '.') {
        $basePath = '';
    }
    if ($basePath !== '' && substr($basePath, -1) !== '/') {
        $basePath .= '/';
    }
    return $baseUrl . $basePath . $location;
}

/**
 * Основная функция – строит цепочку редиректов
 * @param string $startUrl   начальный URL
 * @param int    $maxRedirects максимальное число переходов
 * @return array  массив шагов, каждый шаг содержит:
 *                url, status (код или описание), location (если есть), error (если есть)
 */
function getRedirectChain($startUrl, $maxRedirects = 10)
{
    $chain         = [];
    $url           = $startUrl;
    $visited       = [];
    $redirectCount = 0;

    while ($redirectCount < $maxRedirects) {
        // Проверка на зацикливание
        if (in_array($url, $visited)) {
            $chain[] = [
                'url'    => $url,
                'status' => 'loop',
                'error'  => 'Обнаружен цикл редиректов'
            ];
            break;
        }
        $visited[] = $url;

        // Выполняем HEAD-запрос
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,   // хотим видеть заголовки
            CURLOPT_NOBODY         => true,   // HEAD-запрос
            CURLOPT_FOLLOWLOCATION => false,  // сами обрабатываем редиректы
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; RedirectChecker)',
            CURLOPT_SSL_VERIFYPEER => false,  // для тестов (в проде лучше true)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        // Ошибка соединения
        if ($error) {
            $chain[] = [
                'url'    => $url,
                'status' => 'error',
                'error'  => $error
            ];
            break;
        }

        // Извлекаем Location из заголовков
        $location = null;
        if ($response && preg_match('/^Location: (.+)$/im', $response, $matches)) {
            $location = trim($matches[1]);
        }

        // Сохраняем текущий шаг
        $chain[] = [
            'url'      => $url,
            'status'   => $httpCode,
            'location' => $location,
            'response' => $response,
        ];

        // Если это не редирект или нет Location – цепочка закончена
        if ($httpCode < 300 || $httpCode >= 400 || !$location) {
            break;
        }

        // Переходим на следующий URL
        $url = resolveUrl($url, $location);
        $redirectCount++;
    }

    // Превышен лимит редиректов
    if ($redirectCount >= $maxRedirects) {
        $chain[] = [
            'url'    => $url,
            'status' => 'max_redirects',
            'error'  => 'Достигнуто максимальное количество редиректов'
        ];
    }

    return $chain;
}

// -----------------------------------------------------------
// Обработка запроса и вывод результата
// -----------------------------------------------------------
if (isset($_REQUEST['url'])) {
    $inputUrl = $_REQUEST['url'];
    $startUrl = normalizeUrl($inputUrl);
    $chain    = getRedirectChain($startUrl);
    ob_start();
    $result = '';
    ?>
    <h3>Цепочка редиректов для: <?= htmlspecialchars($inputUrl) ?></h3>
    <pre><?php
    foreach ($chain as $step) {
        echo 'URL: ' . htmlspecialchars($step['url']) . "\n";
        if (isset($step['status'])) {
            echo 'Status: ' . $step['status'] . "\n";
        }
        if (isset($step['location'])) {
            echo 'Location: ' . htmlspecialchars($step['location']) . "\n";
        }
        if (isset($step['error'])) {
            echo 'Error: ' . htmlspecialchars($step['error']) . "\n";
        }
        if (isset($step['response'])) {
            echo 'response: ' . "\n" . htmlspecialchars($step['response']) . "\n";
        }
    }
    ?></pre>
    <?php
    $result = ob_get_contents();
    ob_end_clean();
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="autor" content="Ivan Shabanov">
    <title>IS Redirect-checker - Проверка цепочки редиректов</title>
    <link rel="stylesheet" href="style_tools.css">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="header">
                <h1>🔀 IS Redirect-checker</h1>
                <p>Проверка цепочки редиректов</p>
            </div>

            <div class="form-section">
                <form method="get" action="">
                    <div class="form-group">
                        <label>URL для проверки редиректов <span class="required">*</span></label>
                        <input type="url" name="url" placeholder="https://example.com" required size="50"
                            value="<?= $_REQUEST['url'] ?>">
                        <div class="help-text">Полностью с http (протоколом) https://example.com</div>
                    </div>
                    <button type="submit">📤 Проверить</button>
                </form>
            </div>

            <?php if (isset($_GET['url']) && !empty($result)): ?>
                <div class="result-section">
                    <?= $result; ?>
                </div>
            <?php endif; ?>

            <div class="footer">
                <p>(c) 2026 Ivan Shabanov</p>
            </div>
        </div>
    </div>
</body>

</html>