<?php
ini_set('max_execution_time', '300');
set_time_limit(300);
session_start();

$config = [
	'max_file_size'	  => 10 * 1024 * 1024, // 10MB
	'max_results'		=> 500,
	'exclude_dirs'	   => ['.git', 'node_modules', 'vendor', 'cache', 'tmp', 'logs'],
	'allowed_extensions' => ['php', 'html', 'txt', 'js', 'css', 'json', 'xml', 'md', 'ini'],
	'case_sensitive'	 => false,
	'regular'			=> false,
];

// Обновляем настройки из формы
if (isset($_POST['case_sensitive'])) {
	$config['case_sensitive'] = true;
}

if (isset($_POST['regular'])) {
	$config['regular'] = true;
}

if (isset($_POST['allowed_extensions']) && !empty($_POST['allowed_extensions'])) {
	$arExt						= explode(',', $_POST['allowed_extensions']);
	$arExt						= array_map('trim', $arExt);
	$config['allowed_extensions'] = $arExt;
}

if (isset($_POST['exclude_dirs']) && !empty($_POST['exclude_dirs'])) {
	$arDirs				 = explode(',', $_POST['exclude_dirs']);
	$arDirs				 = array_map('trim', $arDirs);
	$config['exclude_dirs'] = $arDirs;
}

if (isset($_POST['exclude_git'])) {
	$config['exclude_dirs'][] = '.git';
}

$config['allowed_extensions'] = array_unique($config['allowed_extensions']);
$config['exclude_dirs']	   = array_unique($config['exclude_dirs']);

function is_valid_regex($pattern)
{
	return @preg_match($pattern, '') !== false;
}

function searchInFile($path, $searchString, &$results, $config)
{
	$content = @file_get_contents($path);
	if ($content !== false) {
		$found = false;
		if ($config['regular'] && preg_match($searchString, $content)) {
			$found = true;
		} else {
			if ($config['case_sensitive']) {
				$found = strpos($content, $searchString) !== false;
			} else {
				$found = stripos($content, $searchString) !== false;
			}
		}

		if ($found) {
			$results[] = [
				'path'	 => realpath($path),
				'size'	 => filesize($path),
				'modified' => date('Y-m-d H:i:s', filemtime($path))
			];
		}
	}

}

function advancedSearch($dir, $searchString, &$results, $config)
{
	if (!is_readable($dir) || count($results) >= $config['max_results']) {
		return;
	}

	$items = scandir($dir);

	foreach ($items as $item) {
		if ($item == '.' || $item == '..') {
			continue;
		}

		$path = $dir . DIRECTORY_SEPARATOR . $item;

		if (is_dir($path) && in_array($item, $config['exclude_dirs'])) {
			continue;
		}

		if (is_dir($path)) {
			advancedSearch($path, $searchString, $results, $config);
		} elseif (is_file($path) && is_readable($path)) {
			$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

			if (
				!empty($config['allowed_extensions']) &&
				!in_array($extension, $config['allowed_extensions'])
			) {
				continue;
			}

			if (filesize($path) > $config['max_file_size']) {
				continue;
			}

			searchInFile($path, $searchString, $results, $config);
		}
	}
}

$searchString	= '';
$results		 = [];
$error		   = '';
$searchPerformed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
	$searchString = trim($_POST['search']);

	if (empty($searchString)) {
		$error = 'Введите текст для поиска';
	} elseif (strlen($searchString) < 2) {
		$error = 'Минимум 2 символа';
	} elseif ($config['regular'] && !is_valid_regex($searchString)) {
		$error = 'Ошибка в регулярном выражении';
	}

	if (empty($error)) {
		$searchPerformed = true;
		$currentDir	  = __DIR__;
		$startTime	   = microtime(true);
		if (!empty($_POST['search_in_files'])) {
			$arFiles = explode(',', $_POST['search_in_files']);
			foreach ($arFiles as $path) {
				searchInFile($path, $searchString, $results, $config);
			}
		} else {
			advancedSearch($currentDir, $searchString, $results, $config);
		}
		$endTime		   = microtime(true);
		$executionTime	 = round(($endTime - $startTime) * 1000, 2);
		$_SESSION['token'] = uniqid();
	}
}

$filecontent = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['token']) && $_GET['token'] == $_SESSION['token'] && isset($_GET['show_file'])) {
	$filename = $_GET['show_file'];
	if (file_exists($filename)) {
		$filecontent = file_get_contents($filename);
	}
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<title>Поиск файлов по содерждания</title>
	<style>
		* {
			box-sizing: border-box;
		}

		/* Стили аналогичны предыдущей версии */
		body {
			font-family: system-ui, -apple-system, sans-serif;
			max-width: 1000px;
			margin: 0 auto;
			padding: 20px;
			background: #f5f5f5;
		}


		.container {
			background: white;
			border-radius: 8px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
			padding: 20px;
		}

		h1 {
			color: #333;
			border-bottom: 3px solid #667eea;
			padding-bottom: 10px;
		}

		h2 {
			color: #444;
			white-space: normal;
			word-break: break-all;
		}

		.search-box {
			margin: 20px 0;
		}

		input[type="text"] {
			width: 100%;
			padding: 12px;
			font-size: 16px;
			border: 1px solid #ddd;
			border-radius: 4px;
			font-family: monospace;
		}

		.options {
			background: #f8f9fa;
			padding: 15px;
			margin: 15px 0;
			border-radius: 4px;
			display: flex;
			flex-wrap: wrap;
		}

		.options label {
			margin-right: 20px;
			cursor: pointer;
		}

		button {
			background: #667eea;
			color: white;
			padding: 12px 30px;
			border: none;
			border-radius: 4px;
			cursor: pointer;
			font-size: 16px;
		}

		button:hover {
			background: #5a67d8;
		}

		.result-item {
			border-left: 3px solid #667eea;
			padding: 10px;
			margin: 10px 0;
			background: #fafafa;
		}

		.result-item pre {
			width: 100%;
			height: 500px;
			overflow: auto;
		}

		.file-path {
			font-family: monospace;
			color: #667eea;
			font-weight: bold;
			white-space: normal;
			word-break: break-all;
			text-decoration: none;
		}

		.file-info {
			font-size: 12px;
			color: #666;
			margin-top: 5px;
		}

		.stats {
			background: #e8f5e9;
			padding: 10px;
			margin: 20px 0;
			border-radius: 4px;
		}

		.error {
			background: #ffebee;
			color: #c62828;
			padding: 10px;
			border-radius: 4px;
			margin: 10px 0;
		}
	</style>
</head>

<body>
	<div class="container">
		<h1>🔍 Поиск файлов</h1>
		<?php if (empty($filecontent)): ?>
			<form method="POST">
				<div class="search-box">
					<input type="text" name="search" placeholder="Введите текст для поиска..."
						value="<?= htmlspecialchars($searchString); ?>" required>
				</div>
				<div class="options">
					Исключаемые дириктории<br>
					<input type="text" name="exclude_dirs" placeholder="Исключаемые дириктории через запятую"
						value="<?= htmlspecialchars(implode(',', $config['exclude_dirs'])); ?>">
				</div>
				<div class="options">
					Расширения файлов<br>
					<input type="text" name="allowed_extensions" placeholder="Расширения файлов через запятую"
						value="<?= htmlspecialchars(implode(',', $config['allowed_extensions'])); ?>">
				</div>
				<div class="options">
					<label>
						<input type="checkbox" name="case_sensitive" <?= isset($_POST['case_sensitive']) ? 'checked' : ''; ?>>
						Учитывать регистр
					</label>
					<label>
						<input type="checkbox" name="regular" <?= isset($_POST['regular']) ? 'checked' : ''; ?>>
						Поиск по регулярному выражению
					</label>
					<label>
						<input type="checkbox" name="exclude_git" <?= !isset($_POST['exclude_git']) ? '' : 'checked'; ?>>
						Исключить .git директорию
					</label>
					<?php if ($searchPerformed && !$error): ?>
						<?php $search_in_files = implode(',', array_column($results, 'path')); ?>
						<label>
							<input type="checkbox" name="search_in_files" value="<?= $search_in_files; ?>">
							Искать в найденом
						</label>
					<?php endif; ?>
				</div>
				<button type="submit">🔍 Найти</button>
			</form>
		<?php endif; ?>
		<?php if ($error): ?>
			<div class="error">⚠️ <?= htmlspecialchars($error); ?></div>
		<?php endif; ?>

		<?php if ($searchPerformed && !$error): ?>
			<div class="stats">
				📊 Найдено файлов: <strong><?= count($results); ?></strong><br>
				⚡ Время выполнения: <strong><?= $executionTime; ?> мс</strong><br>
				📁 Директория: <strong><?= __DIR__; ?></strong>
			</div>

			<?php foreach ($results as $result): ?>
				<div class="result-item">
					<a class="file-path"
						href="?token=<?= $_SESSION['token']; ?>&show_file=<?= htmlspecialchars($result['path']); ?>"
						target="_blank">
						📄 <?= htmlspecialchars($result['path']); ?>
					</a>
					<div class="file-info">
						Размер: <?= round($result['size'] / 1024, 2); ?> KB |
						Изменен: <?= $result['modified']; ?>
					</div>
				</div>
			<?php endforeach; ?>

			<?php if (count($results) == 0): ?>
				<div style="text-align: center; padding: 40px; color: #999;">
					😕 Файлы не найдены
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if (!empty($filecontent) && !$error): ?>
			<h2>Просмотр содержания файла: <?= htmlspecialchars($_GET['show_file']); ?></h2>
			<div class="result-item">
				<pre><?= htmlspecialchars($filecontent); ?></pre>
			</div>

		<?php endif; ?>

	</div>
</body>

</html>