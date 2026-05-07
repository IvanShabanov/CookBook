# Страницы пагинации


Надо
На страницах пагинации начиная со второй страницы в мета тегах  добавить " - страницы #Номер_Страницы#" и проставить canonical c параметром пагинации.


## Шаблон пагинации

### result_modifier.php

	if ($cpParent = $this->getComponent()->GetParent()) {
		$cpParent->arResult = array_merge($cpParent->arResult, $arResult);
		$cpParent->SetResultCacheKeys(array(
			"NavQueryString",
			"NavPageNomer",
			'sUrlPath',
			"NavNum",
			"NavPageCount"
		));
	}
	if ($cp = $this->getComponent()) {
		$cp->SetResultCacheKeys(array(
			"NavQueryString",
			"NavPageNomer",
			'sUrlPath',
			"NavNum",
			"NavPageCount"
		));
	}

### component_epilog.php

	global $APPLICATION;
	$APPLICATION->SetPageProperty('NavPageNumber', 'PAGEN_' . $arResult["NavNum"] . '=' . $arResult['NavPageNomer']);


## Событие OnEpilog

	$eventManager = \Bitrix\Main\EventManager::getInstance();
	$eventManager->addEventHandler("main", "OnEpilog", ["MyClass", "onEpilog"]);

	class MyClass
	{

		public static function SetPageCanonical() {
			global $APPLICATION;
			/* Если canonical уже установлен или если это 404 ничего не делаем */
			if  (!empty($APPLICATION->GetProperty('canonical')) || !defined('ERROR_404') ) {
				return;
			}
			$context = \Bitrix\Main\Application::getInstance()->getContext();
			$request = $context->getRequest();
			$server = $context->getServer();
			$cururl = ($request->isHttps() ? 'https://' : 'http://') . explode(':', $server->getHttpHost())[0] . $request->getRequestedPage();
			/* Убираем index.php */
			$cururl  = str_replace('index.php', '', $cururl);
			/* Убираем GET параметры */
			if (mb_strpos($cururl, '?') !== false) {
				$cururl = explode('?', $cururl)[0];
			}
			/* Страницы отфильтрованных товаров */
			if (mb_strpos($cururl, '/filter/') !== false) {
				$cururl = explode('/filter/', $cururl)[0] . '/';
			}
			$APPLICATION->SetPageProperty('canonical', $cururl);
		}

		public static function onEpilog()
		{
			global $APPLICATION;
			/** ## Корректируем мету, если у нас пагинация */

			/** проверяем установлено ли свойство NavPageNumber в шаблоне пагинации */
			$NavPageNumber = $APPLICATION->GetPageProperty('NavPageNumber');
			if (!empty($NavPageNumber) && mb_str_contains($NavPageNumber, 'PAGEN_') && mb_str_contains($NavPageNumber, '=')) {
				$number = explode('=', $NavPageNumber)[1];

				if ((int) $number > 1) {

					/** поправим canonical */
					$canonical = $APPLICATION->GetPageProperty('canonical');
					if (empty($canonical)) {
						self::SetPageCanonical();
						$canonical = $APPLICATION->GetPageProperty('canonical');
					}
					if (mb_str_contains($canonical, '?')) {
						$canonical .= '&' . $NavPageNumber;
					} else {
						$canonical .= '?' . $NavPageNumber;
					}
					$APPLICATION->SetPageProperty('canonical', $canonical);

					/** поправим мету */
					$title       = $APPLICATION->GetProperty('title');
					$description = $APPLICATION->GetProperty('description');
					$H1          = $APPLICATION->GetTitle();
					$title       .= " - страница $number";
					$description .= " - страница $number";
					$H1          .= " - страница $number";
					$APPLICATION->SetPageProperty('title', $title);
					$APPLICATION->SetPageProperty('description', $description);
					$APPLICATION->SetTitle($H1);
				}
			}

		}
	}