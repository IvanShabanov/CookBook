# Настройка link prev/next в 1С Битрикс

Задача: на страницах сайта уже настроены канонические url (canonical), надо на страницах с пагинацией проставить link prev / next использовать стандартные компоненты


## Модифицирем шаблон компонента bitrix system.pagenavigation

### в result_modifier.php

добавляем в кеш необходимые нам параметры

	/* Так как system.pagenavigation это подчиненные компонент, то сначала сохраним в кеш родительском компоненте */
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
	/* а теперь сохраним в кеш самого компоненте */
	if ($cp = $this->getComponent()) {
		$cp->SetResultCacheKeys(array(
			"NavQueryString",
			"NavPageNomer",
			'sUrlPath',
			"NavNum",
			"NavPageCount"
		));
	}

### в файле component_epilog.php

установим link prev / next в заголовке страницы

	global $APPLICATION;

	$strNavQueryString = ($arResult["NavQueryString"] != "" ? $arResult["NavQueryString"]."&amp;" : "");

	if (empty($APPLICATION->GetProperty('link_prev')) &&  $arResult['NavPageNomer'] > 1) {
		$page = $arResult['NavPageNomer'] - 1;
		$rel_prev = '<link rel="prev" href="'.$arResult["sUrlPath"].'?'.$strNavQueryString.'PAGEN_'.$arResult["NavNum"].'='.($arResult["NavPageNomer"]-1).'"  />';
		if ($arResult['NavPageNomer'] == 2) {
			$rel_prev = trim(str_replace('PAGEN_' . $arResult['NavNum'] . '=1', '', $rel_prev), '?');
		}
		$APPLICATION->SetPageProperty('link_prev' , $rel_prev);
		$APPLICATION->AddHeadString($rel_prev, true);
	}
	if (empty($APPLICATION->GetProperty('link_next')) && $arResult['NavPageNomer'] + 1 <= $arResult['NavPageCount']) {
		$page = $arResult['NavPageNomer'] + 1;
		$rel_next = '<link rel="next" href="'.$arResult["sUrlPath"].'?'.$strNavQueryString.'PAGEN_'.$arResult["NavNum"].'='.($arResult["NavPageNomer"]+1).'"  />';
		$APPLICATION->SetPageProperty('link_next', $rel_next);
		$APPLICATION->AddHeadString($rel_next, true);
	}

