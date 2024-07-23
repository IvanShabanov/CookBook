<?
$ip = $_SERVER['REMOTE_ADDR'];
$arResult = \Bitrix\Main\Service\GeoIp\Manager::getDataResult($ip, "ru");
print_r($arResult);