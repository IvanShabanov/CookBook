<?
/* result_modifier.php в шаблоне компонента news.list */
if( !defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true ) die();

foreach ($arResult['ITEMS'] as $arItem) {
	/* Получим все разделы элемента */
	$resSections = CIBlockElement::GetElementGroups($arItem['ID'], true);
	$arSection = [];
	while($arSection = $resSections->Fetch()) {
		/* В массив разделов добавим элемент и сам раздел */
		$arResult['SECTIONS'][$arSection['ID']]["ITEMS"][] = $arItem;
		$arResult['SECTIONS'][$arSection['ID']]["SECTION"] = $arSection;
	}
}

/* сортируем разделы по полю сортировка */
if (count($arResult['SECTIONS']) > 1) {
	$sortSections = [];
	foreach ($arResult['SECTIONS'] as $arSection) {
		$key = $arSection['SECTION']['SORT'];
		while (isset($sortSections[$key])) {
			$key++;
		}
		$sortSections[$key] = $arSection;
	}
	ksort($sortSections);
	$arResult['SECTIONS'] = $sortSections;
}







/********************************************************************** */
/* еще вариант, работает если элемент привязан только к одному разделу  */
/********************************************************************** */
if( !defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true ) die();


foreach ($arResult['ITEMS'] as $arItem) {
	/* В массив разделов добавим элемент */
	$arResult['SECTIONS'][$arItem['IBLOCK_SECTION_ID']]["ITEMS"][] = $arItem;
	/* Соберем все разделы у элементов */
	$SECTIONS[$arItem['IBLOCK_SECTION_ID']] = $arItem['IBLOCK_SECTION_ID'];
}

/* Получим инфу о разделах */
$dbRes = CIBlockSection::GetList(
	array(),
	[
		'ID' => $SECTIONS,
		'IBLOCK_ID' => $arResult['IBLOCK_ID'],
	],
	false,
	[
		"ID",
		"IBLOCK_ID",
		"NAME",
		"SORT"
	]
);
while($arSection = $dbRes->GetNext()) {
	$arResult['SECTIONS'][$arSection['ID']]["SECTION"] = $arSection;
};

/* сортируем разделы по полю сортировка */
if (count($arResult['SECTIONS']) > 1) {
	$sortSections = [];
	foreach ($arResult['SECTIONS'] as $arSection) {
		$key = $arSection['SECTION']['SORT'];
		while (isset($sortSections[$key])) {
			$key++;
		}
		$sortSections[$key] = $arSection;
	}
	ksort($sortSections);
	$arResult['SECTIONS'] = $sortSections;
}
