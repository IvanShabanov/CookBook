<?
function getValueByEnum($fieldName, $fieldEnumValue) {
	$oFieldEnum = new \CUserFieldEnum();
	$rsValues   = $oFieldEnum->GetList(
		[],
		[
			'USER_FIELD_NAME' => $fieldName,
			'ID'              => $fieldEnumValue
		]
	);
	$arResult = [];
	while ($value = $rsValues->Fetch()) {
		$arResult[] = $value;
	}
	return $arResult;
}
