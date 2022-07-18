<?php
use Bitrix\Iblock\InheritedProperty; 

/*Для Элемента */
$ipropValues = new InheritedProperty\ElementValues($IBLOCK_ID, $ELEMENT_ID);
$values = $ipropValues->getValues();

$APPLICATION->SetPageProperty('title', $values['ELEMENT_META_TITLE']);
$APPLICATION->SetPageProperty('keywords', $values['ELEMENT_META_KEYWORDS']);
$APPLICATION->SetPageProperty('description', $values['ELEMENT_META_DESCRIPTION']);

/* Для раздела */
$ipropValues = new InheritedProperty\SectionValues($IBLOCK_ID, $SECTION_ID);
$values = $ipropValues->getValues();

$APPLICATION->SetPageProperty('title', $values['SECTION_META_TITLE']);
$APPLICATION->SetPageProperty('keywords', $values['SECTION_META_KEYWORDS']);
$APPLICATION->SetPageProperty('description', $values['SECTION_META_DESCRIPTION']);

/* Для Блока */
$ipropValues = new InheritedProperty\IblockValues($IBLOCK_ID);
$values = $ipropValues->getValues();
