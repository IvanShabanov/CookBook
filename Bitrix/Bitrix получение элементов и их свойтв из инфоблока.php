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

	while ($arEl = $rsElements->fetch()) {
		/* Получим свойтва элемента */
		$obProp = \CIBlockElement::GetProperty(
			$arEl['IBLOCK_ID'],
			$arEl['ID'],
			[],
			['CODE' => [
			/* Коды нужных свойств */
				'PRICE',
				'PRICE_PER',
			]]
		);
		while ($arProp = $obProp->GetNext()) {
			$arEl['PROPERTIES'][$arProp['CODE']] = $arProp['VALUE'];
		};

		print_r ($arEl);
	};