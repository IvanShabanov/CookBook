<?php
use Bitrix\Iblock\InheritedProperty;

/*Для Элемента */
$ipropValues = new InheritedProperty\ElementValues($IBLOCK_ID, $ELEMENT_ID);
$values = $ipropValues->getValues();
if (!empty($values['ELEMENT_META_TITLE'])) {
	$APPLICATION->SetPageProperty('title', $values['ELEMENT_META_TITLE']);
}
if (!empty($values['ELEMENT_META_KEYWORDS'])) {
	$APPLICATION->SetPageProperty('keywords', $values['ELEMENT_META_KEYWORDS']);
}
if (!empty($values['ELEMENT_META_DESCRIPTION'])) {
	$APPLICATION->SetPageProperty('description', $values['ELEMENT_META_DESCRIPTION']);
}
if (!empty($values['ELEMENT_PAGE_TITLE'])) {
	$APPLICATION->SetTitle($values['ELEMENT_PAGE_TITLE']);
}

/* Для раздела */
$ipropValues = new InheritedProperty\SectionValues($IBLOCK_ID, $SECTION_ID);
$values = $ipropValues->getValues();
if (!empty($values['SECTION_META_TITLE'])) {
	$APPLICATION->SetPageProperty('title', $values['SECTION_META_TITLE']);
}
if (!empty($values['SECTION_META_KEYWORDS'])) {
	$APPLICATION->SetPageProperty('keywords', $values['SECTION_META_KEYWORDS']);
}
if (!empty($values['SECTION_META_DESCRIPTION'])) {
	$APPLICATION->SetPageProperty('description', $values['SECTION_META_DESCRIPTION']);
}
if (!empty($values['SECTION_PAGE_TITLE'])) {
	$APPLICATION->SetTitle($values['SECTION_PAGE_TITLE']);
}

/* Для Блока */
$ipropValues = new InheritedProperty\IblockValues($IBLOCK_ID);
$values = $ipropValues->getValues();
