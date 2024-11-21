<?
/** В компонентах и шаблонах компонентов */
\Bitrix\Iblock\Component\Tools::process404(
	$arParams["MESSAGE_404"],
	$arParams["SET_STATUS_404"] == "Y" ? true : false,
	$arParams["SET_STATUS_404"] == "Y" ? true : false,
	$arParams["SHOW_404"] == "Y" ? true : false,
	$arParams["FILE_404"]
);
?>

