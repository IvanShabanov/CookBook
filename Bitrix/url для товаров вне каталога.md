# Настройка ЧПУ каталога для SEO

Задача: Надо, чтобы

- каталог открывался по адресу - "/catalog/"

- разделы каталога открывался по адресу - "/catalog/[символьный_код_раздела]/"

- товары каталога открывались по адресу - "/product/[символьный_код_товара]/"

- использовать только стандартный комплексный компонент bitrix:catalog

## 1. Настроим урлы в catalog

в файле catalog/index.php делаем такую настройку комплексного компонента

	"DETAIL_STRICT_SECTION_CHECK" => "N",
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "/",
	"SEF_URL_TEMPLATES" => Array(
		"compare" => "catalog/compare.php?action=#ACTION_CODE#",
		"element" => "product/#ELEMENT_CODE#/",
		"search" => "catalog/search/",
		"section" => "catalog/#SECTION_CODE#/",
		"sections" => "catalog/",
		"smart_filter" => "catalog/#SECTION_CODE#/filter/#SMART_FILTER_PATH#/apply/"
	),

## 2. Настроим правильные хлебные крошки

Чтобы при показе товара у нас в хлебных крошках была ссылка на каталог в catalog/index.php добавляем перед вызовом комплексного компонента

	if (mb_strpos($_SERVER['REQUEST_URI'], '/product/') !== false) {
		$APPLICATION->AddChainItem("Каталог", "/catalog/");
	}

## 3. Настройка в инфоблоке

В настройках инфоблока Каталога

	URL страницы детального просмотра: #SITE_DIR#product/#ELEMENT_CODE#/


## 4. Настройка urlrewrite.php

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

## 5. Редирект со старых урлов

если у нас ранее товары открывались по

	/catalog/[символьный_код_раздела]/[символьный_код_товара]/

в /catalog/index.php перед вызовом комплексного компонента и заданием хлебных крошек

	/*
	Дата отключения редиректа обычно 2 месяца после текущей даты,
	после этот код вообще можно удалить
	*/
	if ((date('Ymd') < 20230715) && (mb_strpos($_SERVER['REQUEST_URI'], '/catalog/') !== false)) {
		[$url, $gets] = explode('?', $_SERVER['REQUEST_URI']);
		$url_parts = explode('/', trim($url,'/'));
		$arFilter = [
			"IBLOCK_ID" => 1,
			"ACTIVE" => "Y",
			"CODE" => $url_parts[count($url_parts) - 1]
		];
		$res_count = CIBlockElement::GetList(Array(), $arFilter, Array(), false, Array());
		if ($res_count > 0) {
			LocalRedirect(
				'/product/'.$url_parts[count($url_parts) - 1].'/',
				'301 Moved Permanently'
			);
		}
	}

