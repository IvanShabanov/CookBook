require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/backup.php");
var_dump(CPasswordStorage::Get('dump_temporary_cache'));