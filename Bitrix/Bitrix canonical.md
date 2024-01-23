
# Настройка canonical в Bitrix (1С-Битрикс управление сайтом)

## 1)	Установка каноникла по текущему урлу

в init.php добавляем обработчик на событие OnEpilog

	$eventManager = \Bitrix\Main\EventManager::getInstance();
	$eventManager->addEventHandler("main", "OnEpilog", "SetPageCanonical");

	function SetPageCanonical() {
		global $APPLICATION;
		if  (!empty($APPLICATION->GetProperty('canonical'))) {
			return;
		}
		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$request = $context->getRequest();
		$server = $context->getServer();
		$cururl = ($request->isHttps() ? 'https://' : 'http://') . explode(':', $server->getHttpHost())[0] . $request->getRequestedPage();
		$cururl  = str_replace('index.php', '', $cururl);
		$APPLICATION->SetPageProperty('canonical', $cururl);
	}

## На этом в принципе можно и закончить, но иногда бывает что одна и таже страница может открываться по разным адресам, тогда надо провести дальнейшую настроку

## 2) 	В настройках модуля - Управление структурой

Тут

	/bitrix/admin/settings.php?lang=ru&mid=fileman&mid_menu=1

создать свойство

	canonical - Канонический URL

В свойствах страниц и разделов появиться свойство "Канонический URL"

## 3) 	На страницах без комплексных компонентов

Заполнить свойство "Канонический URL" текущим URL

## 4) 	На страницах с комплексными компонентами (Товарные каталоги, Новости и т.д.):

### 4.1)	В настройках текущего сайта

проверить правильно ли настроено поле "URL сервера"

### 4.2)	В настройках инфоблока

проверить что заполнено поле "Канонический URL элемента"

### 4.3)	В параметрах комплексного компонента

проставить

	"DETAIL_SET_CANONICAL_URL" => "Y"

На детальных страницах конпонента должен появиться canonical

### 4.4)	В шаблоне комплексного компонента

В файле section.php
В зависимости от того как у вас строиться урл для раздела/секции

	$APPLICATION->SetPageProperty("canonical", "https://" . SITE_SERVER_NAME . $arResult['FOLDER'] . $arResult['VARIABLES']['SECTION_CODE_PATH'] . '/');

или

	$APPLICATION->SetPageProperty("canonical", "https://" . SITE_SERVER_NAME . $arResult['FOLDER'] . $arResult['VARIABLES']['SECTION_CODE'] . '/');

На страницах разделов/секций должен появиться canonical

### 4.5)	В свойствах страницы

Заполнить свойство "Канонический URL" - URL страницы с комплексным компонентом без вложенности раздела или элемента

