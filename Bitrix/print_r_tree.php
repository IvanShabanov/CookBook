<?
function print_r_tree($data, $level = 0, $parent = '')
{
	global $USER;
	$out = '';
	if ($level == 0) {
		$out .= '<script  data-skip-moving="true">function showinner(el) {if(el.nextSibling.style.display == "block") {el.nextSibling.style.display = "none";} else {el.nextSibling.style.display = "block";};}; </script>';
	}
	$type = gettype($data);
	if ($type == 'array') {
		if ($level > 0) {
			$out .= '<div style="display: none; padding: 5px; border: 1px solid #000;">';
		};
		foreach ($data as $key => $val) {
			$item = '["' . $key . '"]';
			$valtype = gettype($val);
			if ($valtype == 'array') {
				$count = '(' . count($val) . ')';
			} else if (in_array($valtype, array('integer', 'double', 'float', 'string'))) {
				$count = '(' . strlen($val) . ')';
			};
			$out .= '<div><div onclick="showinner(this);" style="padding: 5px;">' . gettype($val) . ' ' . $parent . $item . ' &nbsp; &nbsp; ' . $count . '</div>';

			$out .= print_r_tree($val, $level + 1, $parent . $item);

			$out .= '</div>';
		};
		if ($level > 0) {
			$out .= '</div>';
		};
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
	};
	if ($level == 0) {
		$bagtrace = debug_backtrace();
		if (isset($USER) && $USER->IsAdmin()) {
			echo '<div data-id="!!!----------DEBUG----------!!!" data-debug="' . $bagtrace[0]['file'] . ':' . $bagtrace[0]['line'] . '">';
			echo $out;
			echo '</div>';
		} else {

			echo '<div data-id="!!!----------DEBUG----------!!!" data-debug="' . $bagtrace[0]['file'] . ':' . $bagtrace[0]['line'] . '" style="dislay: none">';
			echo $out;
			echo '</div>';
		}
	};
	return $out;
};

function print_r_php($value, $pre = false, $level = 0)
{
	global $USER;
	$result = '';
	$type = gettype($value);
	if ($type == 'array') {
		$result .= '[' . "\n";
		$level++;
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
		if ($pre) {
			$bagtrace = debug_backtrace();
			if (isset($USER) && $USER->IsAdmin()) {
				$result = '<pre>' . $result . '</pre>';
			} else {
				$result = '<pre data-id="!!!----------DEBUG----------!!!" data-debug="' . $bagtrace[0]['file'] . ':' . $bagtrace[0]['line'] . '" style="dislay: none">' . $result . '</pre>';
			}
			echo $result;
		}
	} else {
		$result .= ",";
	}
	$result .= "\n";
	return $result;
}
