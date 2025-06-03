<?
/**
 * print_r_tree - выведит в браузере расскрывающийся массив
 * @param mixed $data - данные / массив для вывода
 * @param mixed $level - уровень волженности (не нужно заполнять)
 * @param mixed $parent - Наименование родителя (не нужно заполнять)
 * @return string
 */
function print_r_tree($data, $level = 0, $parent = '')
{
	global $USER;
	$out = '';
	if ($level == 0) {
		$out .= '<script data-skip-moving="true">function showinner(el) {if(el.nextSibling.style.display == "block") {el.nextSibling.style.display = "none";} else {el.nextSibling.style.display = "block";};}; </script>';
	}
	$type = gettype($data);
	if ($type == 'array') {
		if ($level > 0) {
			$out .= '<div style="display: none; padding: 5px; border: 1px solid #000;">';
		}
		foreach ($data as $key => $val) {
			$item    = '["' . $key . '"]';
			$valtype = gettype($val);
			if ($valtype == 'array') {
				$count = '(' . count($val) . ')';
			} else if (in_array($valtype, array('integer', 'double', 'float', 'string'))) {
				$count = '(' . strlen($val) . ')';
			} else {
				$count = '';
			}
			$out .= '<div><div onclick="showinner(this);" style="padding: 5px;">' . gettype($val) . ' ' . $parent . $item . ' &nbsp; &nbsp; ' . $count . '</div>';

			$out .= print_r_tree($val, $level + 1, $parent . $item);

			$out .= '</div>';
		}
		if ($level > 0) {
			$out .= '</div>';
		}
	} else if ($type == 'boolean') {
		if ($data) {
			$out .= '<pre style="display: none;  padding: 5px; border: 1px solid #000;">TRUE</pre>';
		} else {
			$out .= '<pre style="display: none;  padding: 5px; border: 1px solid #000;">FALSE</pre>';
		}
	} else if (in_array($type, array('integer', 'double', 'float', 'string'))) {
		$out .= '<pre style="display: none;  padding: 5px; border: 1px solid #000;">' . str_replace('<', '&lt;', $data) . '</pre>';
	} else {
		$out .= '<pre style="display: none;  padding: 5px; border: 1px solid #000;">' . print_r($data, true) . '</pre>';
	}
	if ($level == 0) {
		$bagtrace = debug_backtrace();
		$display  = ' style="dislay: none"';
		if (isset($USER) && $USER->IsAdmin()) {
			$display = '';
		}
		echo '<div data-id="!!!----------DEBUG----------!!!" data-debug="' . $bagtrace[0]['file'] . ':' . $bagtrace[0]['line'] . '" ' . $display . '>';
		echo '<p style="font-size: 10px; color: #888">' . $bagtrace[0]['file'] . ':' . $bagtrace[0]['line'] . '</p>';
		echo $out;
		echo '</div>';
	}
	return $out;
}

/**
 * print_r_php - вернет/выведит в браузер php код массива
 * @param mixed $value - массив/данные
 * @param mixed $echo - false/true выводить ли в браузер
 * @param mixed $level - уровень вложенности (не нужно заполнять)
 * @return string
 */
function print_r_php($value, $echo = false, $level = 0)
{
	global $USER;
	$result = '';
	$type   = gettype($value);
	if ($type == 'array') {
		$result .= '[' . "\n";
		foreach ($value as $key => $val) {
			$result .= str_repeat("\t", 1 + $level);
			$result .= '"' . $key . '" => ' . print_r_php($val, false, $level + 1);
		}
		$result .= str_repeat("\t", $level);
		$result .= "]";
	} else if ($type == 'boolean') {
		if ($value) {
			$result .= 'true';
		} else {
			$result .= 'false';
		}
	} else if ($type == 'NULL') {
		$result .= 'NULL';
	} else if (!in_array($type, ["object", "resource", "unknown type", "resource (closed)"])) {
		$result .= '"' . $value . '"';
	} else {
		$result .= '"" /* not supported value */';
	}

	if ($level == 0) {
		$result .= ";";
		if ($echo) {
			$bagtrace = debug_backtrace();
			if (isset($USER) && $USER->IsAdmin()) {
				$display = '';
			} else {
				$display = ' style="dislay: none"';
			}
			$result = '<pre data-id="!!!----------DEBUG----------!!!" data-debug="' . $bagtrace[0]['file'] . ':' . $bagtrace[0]['line'] . '" ' . $display . '>' .
				'/* ' . $bagtrace[0]['file'] . ':' . $bagtrace[0]['line'] . '*/' . "\n" .
				$result .
				'</pre>';
			echo $result;
		}
	} else {
		$result .= ",";
	}
	$result .= "\n";
	return $result;
}

/**
 * dtfp - запишет в /__bx_debug.php скрипт показа массива.
 * @param mixed $value - массив/данные
 * @return void
 */
function dtfp($value)
{
	$bagtrace = debug_backtrace();
	$filename = $_SERVER['DOCUMENT_ROOT'] . '/__bx_debug.php';

	$text = '<?php require_once("' . __FILE__ . '");';
	$text .= 'echo "<hr>";';
	$text .= 'echo "<p>' . date('Y.m.d H:i:s') . ' ' . $bagtrace[0]['file'] . ':' . $bagtrace[0]['line'] . '</p>";';
	$text .= '$ar=' . print_r_php($value) . ';';
	$text .= 'print_r_tree($ar);';
	$text .= '?>';
	file_put_contents($filename, $text, FILE_APPEND);
}