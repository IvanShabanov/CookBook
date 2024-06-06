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
				if ($arProp['PROPERTY_TYPE'] == 'L' && !empty($arProp['VALUE_ENUM'])) {
					$arProp['VALUE'] = $arProp['VALUE_ENUM'];
				}
				if ($arProp['MULTIPLE'] == 'Y') {
					$arEl['PROPERTIES'][$arProp['CODE']][] = $arProp['VALUE'];
				} else {
					$arEl['PROPERTIES'][$arProp['CODE']] = $arProp['VALUE'];
				}
			};
		}

		print_r ($arEl);
	};