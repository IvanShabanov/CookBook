<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>GoogleFontLocal</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="">
	<style>
		body * {
			font-family: sans-serif;
		}
		body {
			padding: 50px;
		}
		textarea {
			width: 100%;
		}
		li {
			margin: 10px 0px;
		}

	</style>
</head>

<body>

	<?php
	define(DIRCSS, __DIR__ . '/googlefontlocal_css/' . date('Ymdhis'));
	if (!empty($_REQUEST['csscode'])) {
		$css = $_REQUEST['csscode'];
	}
	if (!empty($css) && is_string($css)) {
		$result = parseCss($css);
	}
	if (!empty($result) )  {
		$result = downloadResult($result);
		$newCss = prepareCss($css, $result);
		file_put_contents(DIRCSS . '/style.css', $newCss);
		$link = str_replace($_SERVER['DOCUMENT_ROOT'], '', DIRCSS);

		echo "<h1>Результат</h1>";
		echo "<p><a href='{$link}' target='_blank'>{$link}</a> <br></p>";
	}
	?>
	<h1>Скачать шрифты с Google fonts</h1>
	<p>Инструкция</p>
	<ol>
		<li>
		 	<a href="https://fonts.google.com/" target="_blank">https://fonts.google.com/</a>
		</li>
		<li>
			Найдите шрифты которые вам нужны
		</li>
		<li>
			click "Get font"
		</li>
		<li>
			click "&lt; > Get embed code"
		</li>
		<li>
			В браузере откройте ссылку шрифта (сылка в квадратных скобках)
			<pre>&lt;link href="[https://fonts.googleapis.com/.....&display=swap]" rel="stylesheet"></pre>
		</li>
		<li>
			Скопируйте содержание css в поле ниже
		</li>
		<li>
			"Скачать все шрифты"
		</li>
	</ol>
	<form method="post">
		<textarea name="csscode" placeholder="css code" style="width: 100%; height: 200px"></textarea>
		<input type="submit" value="Скачать все шрифты">
	</form>


</body>

</html>

<?php

function print_r_my($arr)
{
	echo '<pre>';
	echo str_replace('<', '&lt;', print_r($arr, true));
	echo '</pre>';
}

function downloadCss($cssfile)
{
	$result = [];
	$css = file_get_contents($cssfile);
	$result = $css ;
	return $result;
}


function parseCss($css)
{
	$result = [];
	$arFonts = explode('}', $css);
	if (is_array($arFonts)) {
		foreach ($arFonts as $key => $cssfont) {
			if (trim($cssfont) == '') {
				continue;
			}
			preg_match('|\/\* (.*) \*\/|', $cssfont, $collection);

			preg_match('|font-family: (.*);|', $cssfont, $family);

			preg_match('|font-weight: (.*);|', $cssfont, $weight);

			preg_match('|font-style: (.*);|', $cssfont, $style);

			preg_match('|url\((.*)\);|', $cssfont, $url);

			$url[1]      = explode(')', $url[1])[0];
			$family[1]    = str_replace(['"', "'"], '', $family[1]);
			$weight[1]    = str_replace(' ', '-',$weight[1]);
			$result[$key] = [
				'collection' => $collection[1],
				'font-family' => $family[1],
				'font-style' => $style[1],
				'font-weight' => $weight[1],
				'url' => $url[1],
			];

		}
	}

	return $result;
}

function downloadResult($arResult)
{
	if (!is_array($arResult)) {
		return [];
	}
	$dir = DIRCSS;
	@mkdir($dir, 0777, true);
	foreach ($arResult as $key => &$res) {
		$url = $res['url'];
		$ext = mb_strtolower(substr(strrchr($url, '.'), 1));
		$res['filename'] = "{$res['font-family']}_{$res['collection']}_{$res['font-style']}_{$res['font-weight']}.{$ext}";
		dwnloadToLocal($url, $dir . '/' . $res['filename']);
	}
	return $arResult;
}

function dwnloadToLocal($from, $to)
{
	$data = file_get_contents($from);
	$fp = fopen($to, 'w');
	fwrite($fp, $data);
	fclose($fp);
}

function prepareCss($css, $arResult)
{
	foreach ($arResult as $key => &$res) {
		$css = str_replace($res['url'], $res['filename'], $css);
	}
	return $css;
}
?>