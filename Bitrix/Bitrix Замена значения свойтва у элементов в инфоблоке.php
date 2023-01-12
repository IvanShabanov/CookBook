<?php
error_reporting (E_ALL );
ini_set('error_reporting', E_ALL);

define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if (!CModule::IncludeModule("iblock")) {
	die();
};


/* Фильтр элементов */
$arFilter = Array(
	"IBLOCK_ID" => 12,
	"INCLUDE_SUBSECTIONS" => "Y",
);

/* Свойствa которое надо проставить */
$property['SHOW_ON_INDEX_PAGE'] = false;

/* Выбираем только ID и IBLOCK_ID - остальное не надо */
$arSelect = Array("ID", "IBLOCK_ID");

/* Поехали */
$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>500000), $arSelect);
while($ob = $res->GetNextElement()) {
	$arFields = $ob->GetFields();
	$ELEMENT_ID = $arFields['ID'];
	$IBLOCK_ID = $arFields['IBLOCK_ID'];
    /* Тут устанавливаем нужные свойтсва не затрагивая другие свойства */
	CIBlockElement::SetPropertyValuesEx($ELEMENT_ID, $IBLOCK_ID, $property);
}
