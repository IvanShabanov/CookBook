<?php
error_reporting (E_ALL );
ini_set('error_reporting', E_ALL);

define("NO_KEEP_STATISTIC", true); 
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if (!CModule::IncludeModule("iblock")) {
    $this->AbortResultCache();
    ShowError("IBLOCK_MODULE_NOT_INSTALLED");
    die();
};


$iBlock = 12;

/* ?????? ? ????? ????????? ?????? */
$arFilter = Array(
    "IBLOCK_ID" => $iBlock, 
    "INCLUDE_SUBSECTIONS" => "Y",
    'SORT' => 500
);

/* ????? ???????? ????????? */
$property['SHOW_ON_INDEX_PAGE'] = false;

SearchAndChange ($arFilter, $property);


/********************************** */
function SearchAndChange ($arFilter, $property) {
    $arSelect = Array("ID", "IBLOCK_ID");

    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>500000), $arSelect);

    while($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        $ELEMENT_ID = $arFields['ID'];
        $IBLOCK_ID = $arFields['IBLOCK_ID'];

        CIBlockElement::SetPropertyValuesEx($ELEMENT_ID, $IBLOCK_ID, $property);
    }
    
};