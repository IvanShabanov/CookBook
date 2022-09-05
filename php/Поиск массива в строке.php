<?php
$searchWords = array('first', 'second');
$string = 'Big text';
if (preg_match('/' . implode('|', $searchWords) . '/', $string)) {
	/* Слова найдены */
}