<?
/* выберим нужные элементы инфоблока */
$rsElements = \Bitrix\Iblock\ElementTable::getList([
	'filter' => [
		'ID' => $arID,
		'ACTIVE' => 'Y'
	],
	'select' => [
		'ID',
		'IBLOCK_ID',
		'*'
	]
]);

/* Коды нужных свойств */
$props = [
	'PRICE',
	'PRICE_PER',
];

while ($arEl = $rsElements->fetch()) {
	/* Получим свойтва элемента */
	foreach ($props as $CODE) {
		$obProp = \CIBlockElement::GetProperty(
			$arEl['IBLOCK_ID'],
			$arEl['ID'],
			[],
			['CODE' => $CODE]
		);
		while ($arProp = $obProp->GetNext()) {

			/* ENUM */
			if ($arProp['PROPERTY_TYPE'] == 'L' && !empty($arProp['VALUE_ENUM'])) {
				$arProp['VALUE'] = $arProp['VALUE_ENUM'];
			}

			/* Справочник */
			if ($arProp['PROPERTY_TYPE'] == 'S' && $arProp['USER_TYPE'] == 'directory') {
				$XML_ID = $arProp['VALUE'];
				$userTypeSetting = unserialize($arProp['USER_TYPE_SETTINGS']);
				if (!empty($userTypeSetting['TABLE_NAME'])) {
					$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList(
						[
							"filter" => [
								'TABLE_NAME' => $userTypeSetting['TABLE_NAME']
							]
						]
					)->fetch();
				}
				if (isset($hlblock['ID'])) {
					$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
					$entity_data_class = $entity->getDataClass();
					$res = $entity_data_class::getList(
						[
							'filter' => [
								'UF_XML_ID' => $XML_ID,
							]
						]
					);
					if ($item = $res->fetch()) {
						$arProp['VALUE'] = $item['UF_NAME'];
					}
				}
			}

			if ($arProp['MULTIPLE'] == 'Y') {
				$arEl['PROPERTIES'][$arProp['CODE']][] = $arProp['VALUE'];
			} else {
				$arEl['PROPERTIES'][$arProp['CODE']] = $arProp['VALUE'];
			}
		};
	}

	print_r($arEl);
};
