<?
/*
Если надо чистить Кеш чаше чем считает система
1) Добавляем эту функцию в init.php
2) Добавляем агента clean_expire_cache();
*/
function clean_expire_cache($path = "")
{
    if (!class_exists("CFileCacheCleaner")) {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/cache_files_cleaner.php");
    }

    $curentTime = time();

    if (defined("BX_CRONTAB") && BX_CRONTAB === true)
        $endTime = time() + 5; //Если на кроне, то работаем 5 секунд
    else
        $endTime = time() + 1; //Если на хитах, то не более секунды

    //Работаем со всем кешем
    $obCacheCleaner = new CFileCacheCleaner("all");

    if (!$obCacheCleaner->InitPath($path)) {
        //Произошла ошибка
        return "clean_expire_cache();";
    }

    $obCacheCleaner->Start();

    while ($file = $obCacheCleaner->GetNextFile()) {
        if (is_string($file)) {
            $date_expire = $obCacheCleaner->GetFileExpiration($file);
            if ($date_expire) {
                if ($date_expire < $curentTime) {
                    unlink($file);
                }
            }
            if (time() >= $endTime)
                break;
        }
    }

    if (is_string($file)) {
        return "clean_expire_cache(\"" . $file . "\");";
    } else {
        return "clean_expire_cache();";
    }
}
