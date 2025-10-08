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

/* ----------------------------------------- */
/* Вариант удаление картинок у элементов     */
/* ----------------------------------------- */

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

// Подключение необходимых модулей
use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;

Loader::includeModule('iblock');

$el = new CIBlockElement;

// ВАШ ИНФОБЛОК
$IBLOCK_ID = 6;

// Получение элементов инфоблока
$rsElements = ElementTable::getList([
    'filter' => [
        'IBLOCK_ID' => $IBLOCK_ID
    ],
    'select' => [
        'ID'
    ]
])->fetchAll();

// Это нужно для метода SetPropertyValuesEx
$property['MORE_PHOTO'] = [
    [
        'VALUE' => '',
        'DESCRIPTION' => ''
    ]
];

foreach ($rsElements as $element) {
    // Очищаем PREVIEW_PICTURE и DETAIL_PICTURE
    $el->Update(
        $element['ID'],
        [
			/* Помечаем PREVIEW_PICTURE на удаление */
            'PREVIEW_PICTURE' => [
                'del' => 'Y'
            ],
			/* Помечаем DETAIL_PICTURE на удаление */
            'DETAIL_PICTURE' => [
                'del' => 'Y'
            ]
        ]
    );

    // Очищаем свойство MORE_PHOTO
    CIBlockElement::SetPropertyValuesEx(
        $element['ID'],
        $IBLOCK_ID,
        $property
    );
}

// Подключение эпилога
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');