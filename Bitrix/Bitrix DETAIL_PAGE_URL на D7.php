<?php

/* Для Элементов */
$rsItems = \Bitrix\Iblock\ElementTable::getList([
    'select' => [
		/* Выбираем все поля участвующие в формировании DETAIL_PAGE_URL */
		'ID',
		'CODE',
		'IBLOCK_ID',
		'IBLOCK_SECTION_ID',
		'DETAIL_PAGE_URL' => 'IBLOCK.DETAIL_PAGE_URL'
	]
]);

while ($arItem = $rsItems->fetch()) {
    $arItem['DETAIL_PAGE_URL'] =
		CIBlock::ReplaceDetailUrl(
			$arItem['DETAIL_PAGE_URL'],
			$arItem,
			false,
			'E'
		);
}


/** Для разделов/секций */
$rsSections = \Bitrix\Iblock\SectionTable::getList([
    'select' => [
		/* Выбираем все поля участвующие в формировании SECTION_PAGE_URL */
		'ID',
		'CODE',
		'IBLOCK_ID',
		'IBLOCK_SECTION_ID',
		'SECTION_PAGE_URL' => 'IBLOCK.SECTION_PAGE_URL'
	]
]);

while ($arSection = $rsSections->fetch()) {
    $arSection['SECTION_PAGE_URL'] =
		CIBlock::ReplaceDetailUrl(
			$arSection['SECTION_PAGE_URL'],
			$arSection,
			false,
			'S'
		);
}