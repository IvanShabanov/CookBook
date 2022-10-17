<?


$entity = \Bitrix\Iblock\Model\Section::compileEntityByIblock($arResult["IBLOCK_ID"]);
$rsSection = $entity::getList(array(
	'filter' => array(
		'GLOBAL_ACTIVE' => 'Y',
		'IBLOCK_ID' => $arResult["IBLOCK_ID"],
		'ID' => $arResult["SECTION_ID"],
	),
	"select" => array("UF_FIELD_CODE")
 ));
 while($arSection=$rsSection->Fetch())
 {
	$arResult['SECTION_CUR'][] = $arSection;
 }