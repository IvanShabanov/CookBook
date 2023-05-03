<?
/* Получить массив всех торговых предложений
 * 		getOffers(
 * 			$elementId - ID товара
 * 			$iblockId - ID инфоблока каталога товаров
 *			$offersFields -  массив необходимых свойств торговых предложений
 *		)
 */

function getOffers(
	int $elementId,
	int $iblockId,
	array $offersFields = [
		'ID',
		'IBLOCK',
		'PRICE_1',
		'CURRENCY_1',
		'WEIGHT',
		'MEASURE'
	])
{
	$arResult = [];
	$arInfoIblock = CCatalogSku::GetInfoByIBlock($iblockId);

	if ($arInfoIblock['CATALOG_TYPE']) { //инфоблок является торговым каталогом

		$productId = $elementId;
		$productIblockId = $iblockId;

		if ($arInfoIblock['CATALOG_TYPE'] == CCatalogSku::TYPE_OFFERS) { //торговое предложение
			$arItem = CCatalogSKU::GetProductInfo($elementId);
			$productId = $arItem['ID'];
			$productIblockId = $arItem['IBLOCK_ID'];
			$offersIblockId = $productIblockId;
		} else {
			$offersIblockId = CCatalogSKU::GetInfoByProductIBlock($productIblockId);
		}

		$arOffers = CCatalogSKU::getOffersList(
			$productId
		);

		$arProductOffers = array_column($arOffers[$productId], "ID");
		if (!is_array($arProductOffers)) {
			$arProductOffers = [$productId];
		}

		$res = CIBlockElement::GetList(
			[],
			[
				"ID" => $arProductOffers,
				"IBLOCK_ID" => $offersIblockId
			],
			false,
			[],
			$offersFields
		);

		while ($ob = $res->GetNextElement()) {
			$arFields = $ob->GetFields();
			$arResult[] = $arFields;
		}
	}

	return $arResult;
}
