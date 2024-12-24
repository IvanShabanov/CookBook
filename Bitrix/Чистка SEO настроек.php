<?
$IBLOCK_ID = 43;

CModule::IncludeModule('iblock');
//Устанавливаем значения шаблонов SEO-данных у секция, в данном случае пустые, т.к. нужно было их удалить
$res = CIBlockSection::GetList(
	false,
	[
		'IBLOCK_ID' => $IBLOCK_ID,
	],
	[
		'IBLOCK_ID',
		'ID'
	]
);
while($el = $res->fetch()):
	$ipropTemplates = new \Bitrix\Iblock\InheritedProperty\SectionTemplates ($el['IBLOCK_ID'], $el['ID']); //еще раз уточняем ID инфоблока
	$ipropTemplates->set(array(
		"SECTION_META_TITLE" => "",
		"SECTION_META_KEYWORDS" => "",
		"SECTION_META_DESCRIPTION" => "",
		"SECTION_PAGE_TITLE" => "",
		"ELEMENT_META_TITLE" => "",
		"ELEMENT_META_KEYWORDS" => "",
		"ELEMENT_META_DESCRIPTION" => "",
		"ELEMENT_PAGE_TITLE" => "",
	));
endwhile;

//Устанавливаем значения шаблонов SEO-данных у элементов, в данном случае пустые, т.к. нужно было их удалить
$res = CIBlockElement::GetList(
	false,
	[
		'IBLOCK_ID' => $IBLOCK_ID,
	],
	[
		'IBLOCK_ID',
		'ID'
	]
);
while ($el = $res->fetch()):
	$ipropTemplates = new \Bitrix\Iblock\InheritedProperty\ElementTemplates($el['IBLOCK_ID'], $el['ID']); //еще раз уточняем ID инфоблока
	$ipropTemplates->set(array(
		"ELEMENT_META_TITLE" => "",
		"ELEMENT_META_KEYWORDS" => "",
		"ELEMENT_META_DESCRIPTION" => "",
		"ELEMENT_PAGE_TITLE" => "",
	));
endwhile;
