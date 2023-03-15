<?
/* в файл init.php добавить функцию */

/**
 * Собирает массив меню из разделов и элементов инфоблока
 *
 * MenuSectionsElements(int $iblock, int $section_id = 0, int $depth_level = 1, int $max_level = 3)
 *
 * PARAMS:
 * $iblock - IBLOCK ID
 * $section_id - SECTION ID
 * $depth_level - current dephth level
 * $max_level - max depth level
 *
 * USAGE:
 * в файле left.menu_ext.php
 *
 * $aMenuLinksExt = MenuSectionsElements(15, 0, 1, 3);
 * $aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
 */
function MenuSectionsElements(int $iblock, int $section_id = 0, int $depth_level = 1, int $max_level = 3)
{
	$arResult = [];

	if ($depth_level >= $max_level) {
		return $arResult;
	};

	$rsSections = \Bitrix\Iblock\SectionTable::getList([
		'order' => [
			'SORT' => 'ASC'
		],
		'filter' => [
			'IBLOCK_ID' => $iblock,
			'ACTIVE' => 'Y',
			'IBLOCK_SECTION_ID' => $section_id
		],
		'select' => [
			'ID',
			'CODE',
			'NAME',
			'IBLOCK_ID',
			'IBLOCK_SECTION_ID',
			'SECTION_PAGE_URL' => 'IBLOCK.SECTION_PAGE_URL'
		]
	]);

	while ($arSection = $rsSections->fetch()) {
		$arSection['SECTION_PAGE_URL'] =
			CIBlock::ReplaceDetailUrl(
				$arSection['SECTION_PAGE_URL'],
				$arSection,
				false,
				'S'
			);

		$arResultSection = MenuSectionsElements($iblock, $arSection['ID'], $depth_level + 1, $max_level);

		$arResult[] = [
			$arSection['NAME'],
			$arSection['SECTION_PAGE_URL'],
			[],
			[
				'FROM_IBLOCK' => true,
				'IS_PARENT' => (count($arResultSection) ? true : false),
				'DEPTH_LEVEL' => $depth_level,
			],
			""
		];

		$arResult = array_merge($arResult, $arResultSection);
	}

	$rsItems = \Bitrix\Iblock\ElementTable::getList([
		'order' => [
			'SORT' => 'ASC'
		],
		'filter' => [
			'IBLOCK_ID' => $iblock,
			'ACTIVE' => 'Y',
			'IBLOCK_SECTION_ID' => $section_id
		],
		'select' => [
			'ID',
			'CODE',
			'NAME',
			'IBLOCK_ID',
			'IBLOCK_SECTION_ID',
			'DETAIL_PAGE_URL' => 'IBLOCK.DETAIL_PAGE_URL'
		]
	]);

	while ($arItem = $rsItems->fetch()) {
		$arItem['DETAIL_PAGE_URL'] =
			CIBlock::ReplaceDetailUrl(
				$arItem['DETAIL_PAGE_URL'],
				$arItem,
				false,
				'E'
			);
		$arResult[] = [
			$arItem['NAME'],
			$arItem['DETAIL_PAGE_URL'],
			[],
			[
				'FROM_IBLOCK' => true,
				'IS_PARENT' => false,
				'DEPTH_LEVEL' => $depth_level,
			],
			""
		];
	}
	return $arResult;
};
