# Настройка ЧПУ каталога для SEO

Задача: Надо, чтобы

- каталог открывался по адресу - "/catalog/"
- разделы каталога открывался по адресу - "/catalog/[символьный_код_раздела]/"
- товары каталога открывались по адресу - "/product/[символьный_код_товара]/"
- использовать только стандартный комплексный компонент bitrix:catalog

## 1. Настроим урлы в catalog

в файле /catalog/index.php делаем такую настройку комплексного компонента

	"DETAIL_STRICT_SECTION_CHECK" => "N", /* Отключаем проверку правильности пути */
	"SEF_MODE" => "Y",  /* Включаем ЧПУ */
	"SEF_FOLDER" => "/", /* Ставим папку каталога коневую сайта */
	"SEF_URL_TEMPLATES" => Array(
		"compare" => "catalog/compare.php?action=#ACTION_CODE#", /* Сравнение товаров */
		"element" => "product/#ELEMENT_CODE#/", /* Детальная товара */
		"search" => "catalog/search/",  /* Страница поиска */
		"section" => "catalog/#SECTION_CODE#/", /* Раздел каталога */
		"sections" => "catalog/", /* Список разделов */
		"smart_filter" => "catalog/#SECTION_CODE#/filter/#SMART_FILTER_PATH#/apply/" /* Фильтр */
	),


## 2. Настройка в инфоблоке

В настройках инфоблока Каталога

	URL страницы детального просмотра: #SITE_DIR#product/#ELEMENT_CODE#/


## 3. Настройка urlrewrite.php

в urlrewrite.php должно быть

	array (
		'CONDITION' => '#^/catalog/#',
		'RULE' => '',
		'ID' => NULL,
		'PATH' => '/catalog/index.php',
		'SORT' => 100,
	),

	array (
		'CONDITION' => '#^/product/#',
		'RULE' => '',
		'ID' => NULL,
		'PATH' => '/catalog/index.php',
		'SORT' => 100,
	),

## 4. Редирект со старых урлов и настройка хлебных крошек

если у нас ранее товары открывались по

	/catalog/[символьный_код_раздела или путь_к_разделу]/[символьный_код_товара]/

в /catalog/index.php перед вызовом комплексного компонента и заданием хлебных крошек


	if (\CSite::InDir('/catalog/')) { {
		$IBLOCK_ID = IB_CATALOG; /* ID инфоблока каталога */

		/* Получим текущий урл */
		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$request = $context->getRequest();
		$url = (new \Bitrix\Main\Web\Uri($request->getRequestUri()))->getPath();
		/* Разобьем его на части и соберем все части в массив */
		$urlPartsFull = explode('/', trim($url, '/'));
		$urlParts = [];
		if (is_array($urlPartsFull)) {
			$collectPart = false;
			foreach ($urlPartsFull as $key=>$uriPart) {
				if ($uriPart == 'catalog') {
					$collectPart =  true;
				};
				if (in_array($uriPart, 'serach', 'filter')) {
					$collectPart =  false;
				};
				if ($collectPart) {
					$urlParts[] = $uriPart;
				};
			}
		}
		if (count($urlParts) > 2) {
			/* Если кол-во частей урла больше 2 - скорее всго нам надо сделать редирект */
			$urlLastPart = $url_parts[count($url_parts) - 1];
			/* Проверим есть ли товар с символьныйм кодом из последней части урла */
			$arFilter = [
				"IBLOCK_ID" => $IBLOCK_ID,
				"ACTIVE" => "Y",
				"SECTION_ACTIVE" => "Y",
				"SECTION_GLOBAL_ACTIVE" => "Y",
				"CODE" => $urlLastPart
			];
			$res_count = \CIBlockElement::GetList([], $arFilter, [], false, []);
			if ($res_count > 0) {
				/* Редирект на товар */
				LocalRedirect(
					'/product/' . $urlLastPart . '/',
					'301 Moved Permanently'
				);
			} else {
				/* Проверим есть ли раздел с символьныйм кодом из последней части урла */
				$arFilterSection = [
					"IBLOCK_ID" => $IBLOCK_ID,
					"ACTIVE" => "Y",
					"GLOBAL_ACTIVE" => "Y",
					"CODE" => $urlLastPart
				];
				$res_count = \CIBlockSection::GetList([], $arFilterSection, [], false, []);
				if ($res_count > 0) {
					/* Редирект на раздел */
					LocalRedirect(
						'/catalog/' . $urlLastPart . '/',
						'301 Moved Permanently'
					);
				}
			}
		}
	} else {
		/* Мы показываем товар и Добавим хлебную крошку на каталог */
		$APPLICATION->AddChainItem("Каталог", "/catalog/");
	}

